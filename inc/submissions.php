<?php
/**
 * Validated, rate-limited recipe submissions awaiting editorial review.
 * NADAfinal3.3.8 conversion. Notifications are optional; recipes require editorial approval.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_submit_recipe() {
	$return_id = absint( $_POST['return_id'] ?? 0 );
	$return = 'page-templates/share-recipe.php' === get_page_template_slug( $return_id ) ? get_permalink( $return_id ) : home_url( '/' );
	$fail = static function () use ( $return ) {
		wp_safe_redirect( add_query_arg( 'recipe-status', 'error', $return ) );
		exit;
	};
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nada_nonce'] ?? '' ) ), 'nada_submit_recipe' ) || ! empty( $_POST['website'] ) ) {
		$fail();
	}
	$rate_key = 'nada_submit_' . hash_hmac( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '', wp_salt() );
	if ( get_transient( $rate_key ) ) {
		$fail();
	}
	$title = sanitize_text_field( wp_unslash( $_POST['recipe'] ?? '' ) );
	$ingredients = sanitize_textarea_field( wp_unslash( $_POST['ingredients'] ?? '' ) );
	$method = sanitize_textarea_field( wp_unslash( $_POST['method'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$language = sanitize_key( wp_unslash( $_POST['language'] ?? 'en' ) );
	$term = get_term( absint( $_POST['category'] ?? 0 ), 'recipe_category' );
	if ( ! $title || mb_strlen( $title ) > 120 || ! $ingredients || strlen( $ingredients ) > 30000 || ! $method || strlen( $method ) > 45000 || ! is_email( $email ) || ! $term || is_wp_error( $term ) || ! in_array( $language, array( 'en', 'ar' ), true ) ) {
		$fail();
	}
	if ( function_exists( 'pll_get_term_language' ) && $language !== pll_get_term_language( $term->term_id ) ) {
		$fail();
	}
	$id = wp_insert_post( array( 'post_type' => 'recipe', 'post_status' => 'pending', 'post_title' => $title ), true );
	if ( is_wp_error( $id ) ) {
		$fail();
	}
	foreach ( array( 'ingredients' => $ingredients, 'steps' => $method ) as $key => $text ) {
		$rows = array_map( static function ( $line ) { return array( 'text' => trim( $line ) ); }, array_values( array_filter( preg_split( '/\R/u', $text ), 'strlen' ) ) );
		if ( function_exists( 'update_field' ) ) {
			update_field( 'field_nada_recipe_' . $key, $rows, $id );
		} else {
			update_post_meta( $id, 'recipe_' . $key, $rows );
		}
	}
	update_post_meta( $id, '_nada_submitter_email', $email );
	wp_set_object_terms( $id, array( $term->term_id ), 'recipe_category' );
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $id, $language );
	}
	set_transient( $rate_key, 1, 5 * MINUTE_IN_SECONDS );
	nada_notify_recipe_submission( $id );
	wp_safe_redirect( add_query_arg( 'recipe-status', 'success', $return ) );
	exit;
}
add_action( 'admin_post_nopriv_nada_submit_recipe', 'nada_submit_recipe' );
add_action( 'admin_post_nada_submit_recipe', 'nada_submit_recipe' );

/** Seed editable email copy once; preserve intentionally cleared templates. */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'update_field' ) ) { return; }
	foreach ( nada_data( 'email-templates' ) as $name => $template ) {
		if ( false === get_option( 'options_' . $name, false ) ) {
			update_field( 'field_nada_' . $name, $template['default'], 'option' );
		}
	}
} );

/** Deliver one plain-text message and record acceptance without losing the recipe. */
function nada_send_submission_email( $id, $recipient, $sender, $reply_to, $subject_name, $body_name, $values, $status_key ) {
	$subject = sanitize_text_field( strtr( (string) nada_option( $subject_name, '' ), $values ) );
	$body = strtr( (string) nada_option( $body_name, '' ), $values );
	if ( '' === $subject || '' === trim( $body ) ) {
		update_post_meta( $id, $status_key, 'template_missing' );
		return false;
	}
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $sender ) ) { $headers[] = 'From: ' . $sender; }
	if ( is_email( $reply_to ) ) { $headers[] = 'Reply-To: ' . $reply_to; }
	$sent = wp_mail( $recipient, $subject, $body, $headers );
	update_post_meta( $id, $status_key, $sent ? 'sent' : 'failed' );
	return $sent;
}

/** Send separate reviewer and visitor emails; never expose the review URL to visitors. */
function nada_notify_recipe_submission( $id ) {
	$receiver = sanitize_email( nada_option( 'submission_receiver_email', '' ) );
	$sender = sanitize_email( nada_option( 'submission_sender_email', '' ) );
	$submitter = sanitize_email( get_post_meta( $id, '_nada_submitter_email', true ) );
	$values = array( '{site_name}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), '{recipe_title}' => get_the_title( $id ), '{submitter_email}' => $submitter );
	if ( is_email( $receiver ) ) {
		$admin_values = $values + array( '{ingredients}' => nada_submission_lines( $id, 'recipe_ingredients' ), '{method}' => nada_submission_lines( $id, 'recipe_steps' ), '{review_url}' => admin_url( 'post.php?post=' . absint( $id ) . '&action=edit' ) );
		nada_send_submission_email( $id, $receiver, $sender, $submitter, 'submission_admin_subject', 'submission_admin_body', $admin_values, '_nada_notification_status' );
	} else {
		update_post_meta( $id, '_nada_notification_status', 'not_configured' );
	}
	if ( ! nada_option( 'submission_confirmation_enabled', true ) ) {
		update_post_meta( $id, '_nada_confirmation_status', 'disabled' );
		return;
	}
	if ( ! is_email( $sender ) || ! is_email( $submitter ) ) {
		update_post_meta( $id, '_nada_confirmation_status', 'not_configured' );
		return;
	}
	$language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'en';
	$language = 'ar' === $language ? 'ar' : 'en';
	nada_send_submission_email( $id, $submitter, $sender, $sender, 'submission_confirmation_subject_' . $language, 'submission_confirmation_body_' . $language, $values, '_nada_confirmation_status' );
}
