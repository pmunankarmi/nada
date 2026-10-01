<?php
/**
 * Disposable WordPress checks for NADAfinal3.3.8 submission exports and email.
 * Mail is intercepted: these checks never contact a recipient.
 *
 * @package NADA
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) { exit; }

function nada_submission_assert( $value, $label ) {
	if ( ! $value ) { throw new RuntimeException( $label ); }
	echo "PASS: $label\n";
}
wp_set_current_user( 1 );
set_transient( 'nada_update_check_recent', 1, HOUR_IN_SECONDS );
do_action( 'admin_init' );
$ids = array();
global $wpdb;
$recipe_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'recipe'" );
$sender = get_field( 'submission_sender_email', 'option' );
$receiver = get_field( 'submission_receiver_email', 'option' );
$confirmation = get_field( 'submission_confirmation_enabled', 'option' );
$mail = array();
$mail_result = true;
$intercept = function ( $return, $attributes ) use ( &$mail, &$mail_result ) { $mail[] = $attributes; return $mail_result; };
add_filter( 'pre_wp_mail', $intercept, 10, 2 );
try {
	for ( $index = 0; $index < 102; $index++ ) {
		$id = nada_store_submission( array( 'title' => '=NADAEXPORTTEST ' . $index . ' وصفة', 'email' => 'visitor@example.test', 'language' => 'en', 'category' => 'Breakfast', 'ingredients' => "Yogurt, honey\nزبادي", 'method' => "Mix \"gently\".\nServe." ) );
		$ids[] = $id;
	}
	$stream = fopen( 'php://temp', 'w+' );
	nada_write_submissions_csv( $stream, array( 's' => 'NADAEXPORTTEST', 'status' => 'new' ) );
	rewind( $stream );
	nada_submission_assert( "\xEF\xBB\xBF" === fread( $stream, 3 ), 'CSV includes UTF-8 marker for Arabic' );
	$header = fgetcsv( $stream, 0, ',', '"', '' );
	$rows = array();
	while ( false !== ( $row = fgetcsv( $stream, 0, ',', '"', '' ) ) ) { $rows[] = $row; }
	fclose( $stream );
	nada_submission_assert( 102 === count( $rows ), 'CSV exports every batch, beyond first 100 rows' );
	nada_submission_assert( 11 === count( $header ) && 11 === count( $rows[0] ), 'CSV column alignment' );
	nada_submission_assert( str_starts_with( $rows[0][2], "'=NADAEXPORTTEST" ), 'CSV formula injection neutralized' );
	nada_submission_assert( "Yogurt, honey\nزبادي" === $rows[0][7] && "Mix \"gently\".\nServe." === $rows[0][8], 'CSV preserves Arabic, commas, quotes and newlines' );
	$filtered = nada_query_submissions( array( 's' => 'NADAEXPORTTEST', 'status' => 'reviewed' ) );
	nada_submission_assert( 0 === $filtered['total'], 'Reviewed filter excludes new submissions' );
	nada_submission_assert( $recipe_count === (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'recipe'" ), 'Saving submissions creates zero Recipe posts' );
	update_field( 'field_nada_submission_sender_email', '', 'option' );
	update_field( 'field_nada_submission_receiver_email', '', 'option' );
	update_field( 'field_nada_submission_confirmation_enabled', 1, 'option' );
	nada_notify_recipe_submission( $id );
	nada_submission_assert( ! $mail, 'Blank receiver disables notifications' );
	update_field( 'field_nada_submission_receiver_email', 'reviewer@example.test', 'option' );
	update_field( 'field_nada_submission_sender_email', 'sender@example.test', 'option' );
	nada_notify_recipe_submission( $id );
	nada_submission_assert( 'reviewer@example.test' === $mail[0]['to'] && in_array( 'From: sender@example.test', $mail[0]['headers'], true ) && in_array( 'Reply-To: visitor@example.test', $mail[0]['headers'], true ), 'Configured sender, receiver and submitter reply-to used' );
	nada_submission_assert( 2 === count( $mail ) && 'visitor@example.test' === $mail[1]['to'], 'Submitter receives a separate acknowledgement' );
	nada_submission_assert( ! str_contains( $mail[1]['message'], 'wp-admin' ) && str_contains( $mail[1]['message'], 'awaiting review' ), 'Acknowledgement has no private admin link or publication promise' );
	$wpdb->update( nada_submissions_table(), array( 'language' => 'ar' ), array( 'id' => $id ) );
	nada_notify_recipe_submission( $id );
	nada_submission_assert( str_contains( $mail[3]['message'], 'بانتظار المراجعة' ), 'Arabic submitter receives Arabic acknowledgement' );
	update_field( 'field_nada_submission_confirmation_enabled', 0, 'option' );
	nada_notify_recipe_submission( $id );
	nada_submission_assert( 5 === count( $mail ) && 'disabled' === nada_get_submission( $id )->submitter_email_status, 'Acknowledgements can be disabled independently' );
	$mail_result = false;
	nada_notify_recipe_submission( $id );
	nada_submission_assert( 'failed' === nada_get_submission( $id )->admin_email_status && 'new' === nada_get_submission( $id )->status, 'Mail failure preserves submission and records failure' );
} finally {
	remove_filter( 'pre_wp_mail', $intercept, 10 );
	update_field( 'field_nada_submission_sender_email', $sender, 'option' );
	update_field( 'field_nada_submission_receiver_email', $receiver, 'option' );
	update_field( 'field_nada_submission_confirmation_enabled', $confirmation, 'option' );
	foreach ( $ids as $id ) { $wpdb->delete( nada_submissions_table(), array( 'id' => $id ) ); }
}
