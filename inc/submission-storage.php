<?php
/**
 * Dedicated, private submission storage for the NADAfinal3.3.8 contact form.
 * Form entries never create WordPress posts or appear in recipe archives.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_submissions_table() {
	global $wpdb;
	return $wpdb->prefix . 'nada_submissions';
}

function nada_install_submission_storage() {
	if ( '1' === get_option( 'nada_submission_storage_version' ) ) { return; }
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table = nada_submissions_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		submitted_at datetime NOT NULL,
		title varchar(255) NOT NULL,
		email varchar(254) NOT NULL,
		language varchar(10) NOT NULL DEFAULT 'en',
		category varchar(255) NOT NULL DEFAULT '',
		ingredients longtext NOT NULL,
		method longtext NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'new',
		admin_email_status varchar(30) NOT NULL DEFAULT '',
		submitter_email_status varchar(30) NOT NULL DEFAULT '',
		legacy_post_id bigint(20) unsigned DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY legacy_post_id (legacy_post_id),
		KEY status_date (status,submitted_at)
	) $charset;" );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) === $table ) {
		update_option( 'nada_submission_storage_version', '1', false );
	}
}
add_action( 'admin_init', 'nada_install_submission_storage', 5 );
add_action( 'after_switch_theme', 'nada_install_submission_storage' );

function nada_store_submission( $data ) {
	global $wpdb;
	nada_install_submission_storage();
	$record = array_intersect_key( $data, array_flip( array( 'title', 'email', 'language', 'category', 'ingredients', 'method', 'legacy_post_id', 'admin_email_status', 'submitter_email_status' ) ) );
	$record['submitted_at'] = $data['submitted_at'] ?? current_time( 'mysql' );
	$record['status'] = in_array( $data['status'] ?? '', array( 'new', 'reviewed', 'trash' ), true ) ? $data['status'] : 'new';
	return $wpdb->insert( nada_submissions_table(), $record ) ? (int) $wpdb->insert_id : 0;
}

function nada_get_submission( $id ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . nada_submissions_table() . ' WHERE id = %d', $id ) );
}

function nada_update_submission( $id, $values ) {
	global $wpdb;
	$values = array_intersect_key( $values, array_flip( array( 'status', 'admin_email_status', 'submitter_email_status' ) ) );
	return $values ? $wpdb->update( nada_submissions_table(), $values, array( 'id' => absint( $id ) ) ) : false;
}

/** Use prepared filters for both the paginated admin page and CSV batches. */
function nada_query_submissions( $input, $page = 1, $per_page = 20 ) {
	global $wpdb;
	$table = nada_submissions_table();
	$status = sanitize_key( wp_unslash( $input['status'] ?? '' ) );
	$search = sanitize_text_field( wp_unslash( $input['s'] ?? '' ) );
	$where = in_array( $status, array( 'new', 'reviewed', 'trash' ), true ) ? $wpdb->prepare( 'status = %s', $status ) : "status <> 'trash'";
	if ( $search ) { $where .= $wpdb->prepare( ' AND (title LIKE %s OR email LIKE %s)', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' ); }
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where" );
	$limit = max( 1, min( 100, (int) $per_page ) );
	$offset = ( max( 1, (int) $page ) - 1 ) * $limit;
	$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE $where ORDER BY submitted_at DESC, id DESC LIMIT %d OFFSET %d", $limit, $offset ) );
	return array( 'items' => $items, 'total' => $total, 'pages' => (int) ceil( $total / $limit ), 'search' => $search, 'status' => in_array( $status, array( 'new', 'reviewed', 'trash' ), true ) ? $status : '' );
}

/** Preserve earlier form entries; move their pending recipe copies to recoverable Trash. */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'nada_legacy_submissions_migrated' ) || '1' !== get_option( 'nada_submission_storage_version' ) ) { return; }
	global $wpdb;
	$posts = get_posts( array( 'post_type' => 'recipe', 'post_status' => array( 'pending', 'draft', 'publish', 'private', 'future', 'trash' ), 'meta_key' => '_nada_submitter_email', 'numberposts' => -1, 'suppress_filters' => true, 'lang' => '' ) );
	$complete = true;
	foreach ( $posts as $post ) {
		$id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . nada_submissions_table() . ' WHERE legacy_post_id = %d', $post->ID ) );
		if ( ! $id ) {
			$terms = wp_get_post_terms( $post->ID, 'recipe_category', array( 'fields' => 'names' ) );
			$id = nada_store_submission( array( 'status' => 'trash' === $post->post_status ? 'trash' : ( 'publish' === $post->post_status ? 'reviewed' : 'new' ), 'legacy_post_id' => $post->ID, 'submitted_at' => $post->post_date, 'title' => $post->post_title, 'email' => get_post_meta( $post->ID, '_nada_submitter_email', true ), 'language' => function_exists( 'pll_get_post_language' ) ? ( pll_get_post_language( $post->ID ) ?: 'en' ) : 'en', 'category' => is_wp_error( $terms ) ? '' : implode( ', ', $terms ), 'ingredients' => nada_submission_lines( $post->ID, 'recipe_ingredients' ), 'method' => nada_submission_lines( $post->ID, 'recipe_steps' ), 'admin_email_status' => get_post_meta( $post->ID, '_nada_notification_status', true ), 'submitter_email_status' => get_post_meta( $post->ID, '_nada_confirmation_status', true ) ) );
		}
		if ( ! $id ) { $complete = false; continue; }
		// Never remove published editorial recipes, and never resend migration emails.
		if ( 'pending' === $post->post_status && defined( 'EMPTY_TRASH_DAYS' ) && EMPTY_TRASH_DAYS > 0 ) { wp_trash_post( $post->ID ); }
	}
	if ( $complete ) { update_option( 'nada_legacy_submissions_migrated', 1, false ); }
}, 15 );
