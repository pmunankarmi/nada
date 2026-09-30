<?php
/**
 * Validated, rate-limited recipe submissions awaiting editorial review.
 * NADAfinal3.3.8 conversion. No email is sent and no recipe is auto-published.
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
	wp_safe_redirect( add_query_arg( 'recipe-status', 'success', $return ) );
	exit;
}
add_action( 'admin_post_nopriv_nada_submit_recipe', 'nada_submit_recipe' );
add_action( 'admin_post_nada_submit_recipe', 'nada_submit_recipe' );
