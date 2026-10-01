<?php
/**
 * Private submission review and CSV exports for the NADAfinal3.3.8 theme.
 * Uses a dedicated submission table with WordPress capabilities and nonces.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'Recipe Submissions', 'Recipe Submissions', 'manage_options', 'nada-submissions', 'nada_submissions_screen', 'dashicons-feedback', 27 );
} );

function nada_submission_lines( $id, $field ) {
	$rows = nada_field( $field, array(), $id );
	return implode( "\n", array_map( static function ( $row ) { return $row['text'] ?? ''; }, is_array( $rows ) ? $rows : array() ) );
}

function nada_submission_notification_label( $id, $key = 'admin_email_status' ) {
	$labels = array( 'sent' => 'Accepted by mail service', 'failed' => 'Sending failed', 'not_configured' => 'Email not configured', 'disabled' => 'Disabled', 'template_missing' => 'Email template empty' );
	return $labels[ nada_get_submission( $id )->$key ?? '' ] ?? 'Not sent (existing submission)';
}

function nada_submissions_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$page = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$query = nada_query_submissions( $_GET, $page );
	$status = $query['status'];
	if ( ! empty( $_GET['submission'] ) ) { nada_submission_detail( absint( $_GET['submission'] ) ); return; }
	?>
	<div class="wrap">
		<h1>Recipe Submissions</h1>
		<p>Review form entries here. Submitting this form does not create or publish a Recipe post.</p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=nada-options' ) ); ?>">Configure notification emails in Theme Options → Notifications</a>. Mail acceptance does not confirm delivery to the recipient.</p>
		<form method="get">
			<input type="hidden" name="page" value="nada-submissions">
			<label for="nada-submission-status">Status</label>
			<select id="nada-submission-status" name="status">
				<option value="">All active submissions</option>
				<?php foreach ( array( 'new' => 'New', 'reviewed' => 'Reviewed', 'trash' => 'Trash' ) as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<label for="nada-submission-search">Recipe search</label>
			<input id="nada-submission-search" type="search" name="s" value="<?php echo esc_attr( $query['search'] ); ?>">
			<?php submit_button( 'Filter', 'secondary', '', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nada_export_submissions">
			<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
			<input type="hidden" name="s" value="<?php echo esc_attr( $query['search'] ); ?>">
			<?php wp_nonce_field( 'nada_export_submissions' ); ?>
			<?php submit_button( 'Export CSV', 'secondary', 'export', true ); ?>
			<p>Exports every submission matching the current filters, including ingredients and method.</p>
		</form>
		<table class="widefat striped">
			<thead><tr><th>Recipe</th><th>Submitted</th><th>Submitter email</th><th>Language</th><th>Status</th><th>Admin email</th><th>Submitter email status</th></tr></thead>
			<tbody>
				<?php foreach ( $query['items'] as $submission ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=nada-submissions&submission=' . $submission->id ) ); ?>"><?php echo esc_html( $submission->title ); ?></a></td>
						<td><?php echo esc_html( $submission->submitted_at ); ?></td>
						<td><?php echo esc_html( $submission->email ); ?></td>
						<td><?php echo esc_html( $submission->language ); ?></td>
						<td><?php echo esc_html( ucfirst( $submission->status ) ); ?></td>
						<td><?php echo esc_html( nada_submission_notification_label( $submission->id ) ); ?></td>
						<td><?php echo esc_html( nada_submission_notification_label( $submission->id, 'submitter_email_status' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if ( ! $query['items'] ) : ?><tr><td colspan="7">No recipe submissions match these filters.</td></tr><?php endif; ?>
			</tbody>
		</table>
		<div class="tablenav"><div class="tablenav-pages">
			<?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( array( 'page' => 'nada-submissions', 'status' => $status, 's' => $query['search'], 'paged' => '%#%' ), admin_url( 'admin.php' ) ), 'format' => '', 'current' => $page, 'total' => $query['pages'] ) ) ); ?>
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
		$query = nada_query_submissions( $filters, $page, 100 );
		foreach ( $query['items'] as $submission ) {
			$row = array( $submission->id, $submission->submitted_at, $submission->title, $submission->email, $submission->language, $submission->status, $submission->category, $submission->ingredients, $submission->method, nada_submission_notification_label( $submission->id ), nada_submission_notification_label( $submission->id, 'submitter_email_status' ) );
			fputcsv( $stream, array_map( 'nada_csv_cell', $row ), ',', '"', '' );
		}
		$page++;
	} while ( $page <= $query['pages'] );
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

/** Display a private entry without linking to the Recipe post editor. */
function nada_submission_detail( $id ) {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$submission = nada_get_submission( $id );
	if ( ! $submission ) { echo '<div class="wrap"><h1>Submission not found</h1></div>'; return; }
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $submission->title ); ?></h1>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=nada-submissions' ) ); ?>">← Recipe Submissions</a></p>
		<table class="widefat striped"><tbody>
			<?php foreach ( array( 'submitted_at' => 'Submitted', 'email' => 'Sender email', 'language' => 'Language', 'category' => 'Category', 'ingredients' => 'Ingredients', 'method' => 'Method', 'status' => 'Status' ) as $key => $label ) : ?>
				<tr><th scope="row" style="width:180px"><?php echo esc_html( $label ); ?></th><td><?php echo nl2br( esc_html( $submission->$key ) ); ?></td></tr>
			<?php endforeach; ?>
			<tr><th>Admin email</th><td><?php echo esc_html( nada_submission_notification_label( $id ) ); ?></td></tr>
			<tr><th>Sender acknowledgement</th><td><?php echo esc_html( nada_submission_notification_label( $id, 'submitter_email_status' ) ); ?></td></tr>
		</tbody></table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nada_submission_status">
			<input type="hidden" name="submission" value="<?php echo esc_attr( $id ); ?>">
			<?php wp_nonce_field( 'nada_submission_status_' . $id ); ?>
			<p><?php foreach ( array( 'new' => 'Mark as new', 'reviewed' => 'Mark as reviewed', 'trash' => 'Move to Trash' ) as $value => $label ) : ?>
				<button class="button" name="status" value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?></p>
		</form>
	</div>
	<?php
}

add_action( 'admin_post_nada_submission_status', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'You cannot manage submissions.', '', array( 'response' => 403 ) ); }
	$id = absint( $_POST['submission'] ?? 0 );
	check_admin_referer( 'nada_submission_status_' . $id );
	$status = sanitize_key( wp_unslash( $_POST['status'] ?? '' ) );
	if ( in_array( $status, array( 'new', 'reviewed', 'trash' ), true ) && nada_get_submission( $id ) ) {
		nada_update_submission( $id, array( 'status' => $status ) );
	}
	wp_safe_redirect( admin_url( 'admin.php?page=nada-submissions&submission=' . $id ) );
	exit;
} );
