<?php
/**
 * Private submission review and CSV exports for the NADAfinal3.3.8 theme.
 * Uses existing pending recipe posts and WordPress capabilities and nonces.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'Recipe Submissions', 'Recipe Submissions', 'manage_options', 'nada-submissions', 'nada_submissions_screen', 'dashicons-feedback', 27 );
} );

/** Share the same filters between the screen and its export. */
function nada_submission_query_args( $input, $page = 1, $per_page = 20 ) {
	$status = sanitize_key( wp_unslash( $input['status'] ?? '' ) );
	$statuses = array( 'pending', 'draft', 'publish', 'private', 'future', 'trash' );
	return array(
		'post_type' => 'recipe',
		'post_status' => in_array( $status, $statuses, true ) ? $status : array_diff( $statuses, array( 'trash' ) ),
		'meta_key' => '_nada_submitter_email',
		'meta_compare' => 'EXISTS',
		's' => sanitize_text_field( wp_unslash( $input['s'] ?? '' ) ),
		'posts_per_page' => $per_page,
		'paged' => max( 1, $page ),
		'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		'lang' => '',
		'suppress_filters' => true,
	);
}

function nada_submission_lines( $id, $field ) {
	$rows = nada_field( $field, array(), $id );
	return implode( "\n", array_map( static function ( $row ) { return $row['text'] ?? ''; }, is_array( $rows ) ? $rows : array() ) );
}

function nada_submission_notification_label( $id, $key = '_nada_notification_status' ) {
	$labels = array( 'sent' => 'Accepted by mail service', 'failed' => 'Sending failed', 'not_configured' => 'Email not configured', 'disabled' => 'Disabled', 'template_missing' => 'Email template empty' );
	return $labels[ get_post_meta( $id, $key, true ) ] ?? 'Not sent (existing submission)';
}

function nada_submissions_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$page = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$args = nada_submission_query_args( $_GET, $page );
	$query = new WP_Query( $args );
	$status = is_string( $args['post_status'] ) ? $args['post_status'] : '';
	?>
	<div class="wrap">
		<h1>Recipe Submissions</h1>
		<p>Review recipes submitted through the website. Open a recipe to edit its ingredients and method, then publish or move it to Trash using WordPress.</p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=nada-options' ) ); ?>">Configure notification emails in Theme Options → Notifications</a>. Mail acceptance does not confirm delivery to the recipient.</p>
		<form method="get">
			<input type="hidden" name="page" value="nada-submissions">
			<label for="nada-submission-status">Status</label>
			<select id="nada-submission-status" name="status">
				<option value="">All active submissions</option>
				<?php foreach ( array( 'pending' => 'Pending review', 'draft' => 'Draft', 'publish' => 'Published', 'private' => 'Private', 'future' => 'Scheduled', 'trash' => 'Trash' ) as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<label for="nada-submission-search">Recipe search</label>
			<input id="nada-submission-search" type="search" name="s" value="<?php echo esc_attr( $args['s'] ); ?>">
			<?php submit_button( 'Filter', 'secondary', '', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nada_export_submissions">
			<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
			<input type="hidden" name="s" value="<?php echo esc_attr( $args['s'] ); ?>">
			<?php wp_nonce_field( 'nada_export_submissions' ); ?>
			<?php submit_button( 'Export CSV', 'secondary', 'export', true ); ?>
			<p>Exports every submission matching the current filters, including ingredients and method.</p>
		</form>
		<table class="widefat striped">
			<thead><tr><th>Recipe</th><th>Submitted</th><th>Submitter email</th><th>Language</th><th>Status</th><th>Admin email</th><th>Submitter email status</th></tr></thead>
			<tbody>
				<?php foreach ( $query->posts as $submission ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $submission->ID ) ); ?>"><?php echo esc_html( $submission->post_title ); ?></a></td>
						<td><?php echo esc_html( get_the_date( 'Y-m-d H:i', $submission ) ); ?></td>
						<td><?php echo esc_html( get_post_meta( $submission->ID, '_nada_submitter_email', true ) ); ?></td>
						<td><?php echo esc_html( function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $submission->ID, 'name' ) : '' ); ?></td>
						<td><?php echo esc_html( get_post_status_object( $submission->post_status )->label ); ?></td>
						<td><?php echo esc_html( nada_submission_notification_label( $submission->ID ) ); ?></td>
						<td><?php echo esc_html( nada_submission_notification_label( $submission->ID, '_nada_confirmation_status' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if ( ! $query->posts ) : ?><tr><td colspan="7">No recipe submissions match these filters.</td></tr><?php endif; ?>
			</tbody>
		</table>
		<div class="tablenav"><div class="tablenav-pages">
			<?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( array( 'page' => 'nada-submissions', 'status' => $status, 's' => $args['s'], 'paged' => '%#%' ), admin_url( 'admin.php' ) ), 'format' => '', 'current' => $page, 'total' => $query->max_num_pages ) ) ); ?>
		</div></div>
	</div>
	<?php
}

/** Prevent spreadsheet programs from interpreting visitor text as formulas. */
function nada_csv_cell( $value ) {
	$value = (string) $value;
	return preg_match( '/^(?:\s*[=+@-]|[\t\r\n])/u', $value ) ? "'" . $value : $value;
}

/** Stream batches to keep memory bounded for larger submission lists. */
function nada_write_submissions_csv( $stream, $filters ) {
	fwrite( $stream, "\xEF\xBB\xBF" );
	fputcsv( $stream, array( 'ID', 'Submitted (site time)', 'Recipe', 'Submitter email', 'Language', 'Status', 'Categories', 'Ingredients', 'Method', 'Admin email status', 'Submitter email status' ), ',', '"', '' );
	$page = 1;
	do {
		$query = new WP_Query( nada_submission_query_args( $filters, $page, 100 ) );
		foreach ( $query->posts as $post ) {
			$terms = wp_get_post_terms( $post->ID, 'recipe_category', array( 'fields' => 'names' ) );
			$row = array( $post->ID, $post->post_date, $post->post_title, get_post_meta( $post->ID, '_nada_submitter_email', true ), function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID ) : '', $post->post_status, is_wp_error( $terms ) ? '' : implode( ', ', $terms ), nada_submission_lines( $post->ID, 'recipe_ingredients' ), nada_submission_lines( $post->ID, 'recipe_steps' ), nada_submission_notification_label( $post->ID ), nada_submission_notification_label( $post->ID, '_nada_confirmation_status' ) );
			fputcsv( $stream, array_map( 'nada_csv_cell', $row ), ',', '"', '' );
		}
		$page++;
	} while ( $page <= $query->max_num_pages );
}

add_action( 'admin_post_nada_export_submissions', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'You cannot export recipe submissions.', '', array( 'response' => 403 ) ); }
	check_admin_referer( 'nada_export_submissions' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="nada-recipe-submissions-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$stream = fopen( 'php://output', 'w' );
	nada_write_submissions_csv( $stream, $_POST );
	fclose( $stream );
	exit;
} );

add_action( 'add_meta_boxes_recipe', function ( $post ) {
	if ( ! current_user_can( 'manage_options' ) || ! metadata_exists( 'post', $post->ID, '_nada_submitter_email' ) ) { return; }
	add_meta_box( 'nada-submitter', 'Recipe submission', function ( $post ) {
		echo '<p><strong>Submitter email:</strong> ' . esc_html( get_post_meta( $post->ID, '_nada_submitter_email', true ) ) . '</p>';
		echo '<p><strong>Admin email:</strong> ' . esc_html( nada_submission_notification_label( $post->ID ) ) . '</p>';
		echo '<p><strong>Submitter email:</strong> ' . esc_html( nada_submission_notification_label( $post->ID, '_nada_confirmation_status' ) ) . '</p>';
	}, 'recipe', 'side' );
} );
