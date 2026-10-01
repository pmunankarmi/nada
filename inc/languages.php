<?php
/**
 * Polylang integration for the bilingual NADAfinal3.3.8 theme.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'pll_get_post_types', function ( $types ) {
	$types['recipe'] = 'recipe';
	$types['product'] = 'product';
	return $types;
} );
add_filter( 'pll_get_taxonomies', function ( $taxonomies ) {
	$taxonomies['recipe_category'] = 'recipe_category';
	$taxonomies['product_fat'] = 'product_fat';
	return $taxonomies;
} );

// Load the supplied GPL Polylang Slug helper only for the free edition.
// Polylang Pro already supplies shared slugs, so never register both implementations.
add_action( 'after_setup_theme', function () {
	if ( function_exists( 'pll_get_post_language' ) && ! defined( 'POLYLANG_PRO' ) && ! function_exists( 'polylang_slug_unique_slug_in_language' ) ) {
		require_once get_template_directory() . '/inc/polylang-slug.php';
	}
} );

require_once get_template_directory() . '/inc/string-translations.php';

// Do not copy translation-specific text fields or thumbnails across languages.
add_filter( 'pll_copy_post_metas', function ( $metas, $sync ) {
	if ( $sync ) {
		$metas = array_filter( $metas, function ( $key ) {
			return '_thumbnail_id' !== $key && ! preg_match( '/^_?(home_|why_|share_|recipe_|product_)/', $key );
		} );
	}
	return $metas;
}, 10, 2 );
