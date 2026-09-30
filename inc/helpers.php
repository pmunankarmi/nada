<?php
/**
 * Escaped template data and language-aware URLs for NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_data( $file ) {
	static $cache = array();
	if ( ! isset( $cache[ $file ] ) ) {
		$path = get_template_directory() . '/data/' . basename( $file ) . '.json';
		$cache[ $file ] = is_readable( $path ) ? json_decode( file_get_contents( $path ), true ) : array();
	}
	return $cache[ $file ];
}

function nada_language() {
	return function_exists( 'pll_current_language' ) ? ( pll_current_language( 'slug' ) ?: 'en' ) : ( is_rtl() ? 'ar' : 'en' );
}

function nada_translate( $text, $language = '' ) {
	$language = $language ?: nada_language();
	$dictionary = nada_data( 'translations' );
	return 'ar' === $language ? ( $dictionary[ $text ] ?? $text ) : $text;
}

function nada_text( $text ) {
	$translated = function_exists( 'pll__' ) ? pll__( $text ) : $text;
	return $translated !== $text ? $translated : nada_translate( $text );
}

function nada_field( $name, $default = '', $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );
	if ( ! function_exists( 'get_field' ) && in_array( $name, array( 'recipe_ingredients', 'recipe_steps', 'product_nutrition' ), true ) && is_numeric( $value ) ) {
		$rows = array();
		$columns = 'product_nutrition' === $name ? array( 'nutrient', 'amount' ) : array( 'text' );
		for ( $index = 0; $index < (int) $value; $index++ ) {
			$row = array();
			foreach ( $columns as $column ) { $row[ $column ] = get_post_meta( $post_id, $name . '_' . $index . '_' . $column, true ); }
			$rows[] = $row;
		}
		return $rows;
	}
	return false === $value || null === $value || '' === $value ? $default : $value;
}

function nada_copy( $name ) {
	$defaults = nada_data( 'page-fields' );
	if ( in_array( $defaults[ $name ]['page'] ?? '', array( 'recipes', 'products' ), true ) ) {
		return nada_option( $name . '_' . nada_language(), nada_translate( $defaults[ $name ]['default'] ?? '' ) );
	}
	return nada_field( $name, nada_translate( $defaults[ $name ]['default'] ?? '' ) );
}

function nada_rows( $name ) {
	$defaults = nada_data( 'repeaters' );
	$archive = 'recipes' === ( $defaults[ $name ]['page'] ?? '' );
	$field_name = $archive ? $name . '_' . nada_language() : $name;
	$rows = function_exists( 'get_field' ) ? get_field( $field_name, $archive ? 'option' : get_the_ID() ) : null;
	if ( is_array( $rows ) ) {
		return $rows;
	}
	// An explicitly cleared repeater stays empty instead of restoring demo rows.
	if ( ( $archive && false !== get_option( 'options_' . $field_name, false ) ) || ( ! $archive && metadata_exists( 'post', get_the_ID(), $name ) ) ) {
		return array();
	}
	$rows = $defaults[ $name ]['rows'] ?? array();
	foreach ( $rows as &$row ) {
		foreach ( $row as &$value ) {
			$value = nada_translate( $value );
		}
	}
	return $rows;
}

function nada_url( $target ) {
	$parts = explode( '#', $target, 2 );
	$key = str_replace( '.html', '', $parts[0] );
	if ( 'index' === $key || '' === $key ) {
		$url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	} elseif ( in_array( $key, array( 'recipes', 'products' ), true ) ) {
		$url = get_post_type_archive_link( 'recipes' === $key ? 'recipe' : 'product' );
	} else {
		$map = get_option( 'nada_import_pages', array() );
		$id = $map[ nada_language() ][ $key ] ?? 0;
		$url = $id ? get_permalink( $id ) : home_url( '/' . $key . '/' );
	}
	return $url . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
}

function nada_asset( $path ) {
	// Content image records use native featured images; CSS, SVG and fallback art stay in the package.
	$images = get_option( 'nada_artwork_posts', array() );
	if ( isset( $images[ $path ] ) ) {
		$url = get_the_post_thumbnail_url( $images[ $path ], 'full' );
		if ( $url ) { return $url; }
	}
	$manifest = nada_data( 'media-manifest' );
	if ( isset( $manifest['files'][ $path ] ) ) {
		$uploads = wp_upload_dir();
		return trailingslashit( $uploads['baseurl'] ) . 'nada/3.3.8/' . $path;
	}
	return get_theme_file_uri( '/' . ltrim( $path, '/' ) );
}

function nada_option( $name, $default = '' ) {
	$value = function_exists( 'get_field' ) ? get_field( $name, 'option' ) : null;
	return null === $value || false === $value || '' === $value ? $default : $value;
}

function nada_default_menu() {
	foreach ( array( 'why-greek.html' => 'Why Greek', 'recipes.html' => 'Recipes', 'products.html' => 'Products' ) as $target => $label ) {
		printf( '<a href="%s">%s</a>', esc_url( nada_url( $target ) ), esc_html( nada_text( $label ) ) );
	}
}
