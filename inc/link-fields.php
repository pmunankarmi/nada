<?php
/**
 * Preserve existing NADAfinal3.3.8 destinations when adopting ACF Link fields.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

// Older imports and integrations may still supply a URL string before the next save.
add_filter( 'acf/load_value/type=link', function ( $value, $post_id, $field ) {
	if ( str_starts_with( $field['key'] ?? '', 'field_nada_' ) && is_string( $value ) && '' !== $value ) {
		return array( 'url' => $value, 'title' => '', 'target' => 'social_url' === ( $field['name'] ?? '' ) ? '_blank' : '' );
	}
	return $value;
}, 10, 3 );

function nada_migrate_link_value( $name, $id ) {
	$value = 'option' === $id ? get_option( 'options_' . $name, '' ) : get_post_meta( $id, $name, true );
	if ( is_string( $value ) && '' !== $value ) {
		update_field( 'field_nada_' . $name, array( 'url' => $value, 'title' => '', 'target' => '' ), $id );
	}
}

add_action( 'admin_init', function () {
	if ( ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'update_field' ) || '2' === get_option( 'nada_link_fields_migrated' ) ) { return; }
	foreach ( nada_data( 'content-links' ) as $name => $definition ) {
		if ( in_array( $definition['page'], array( 'recipes', 'products' ), true ) ) {
			foreach ( array( 'en', 'ar' ) as $language ) { nada_migrate_link_value( $name . '_' . $language, 'option' ); }
		} else {
			$ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => $name, 'fields' => 'ids', 'suppress_filters' => true, 'lang' => '' ) );
			foreach ( $ids as $id ) { nada_migrate_link_value( $name, $id ); }
		}
	}
	$name = 'home_section_5_rtiles_grid';
	$ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => $name, 'fields' => 'ids', 'suppress_filters' => true, 'lang' => '' ) );
	foreach ( $ids as $id ) {
		$count = (int) get_post_meta( $id, $name, true );
		for ( $index = 0; $index < $count; $index++ ) {
			$value = get_post_meta( $id, $name . '_' . $index . '_url', true );
			if ( is_string( $value ) && '' !== $value ) {
				update_sub_field( array( 'field_nada_' . $name, $index + 1, 'field_nada_' . $name . '_url' ), array( 'url' => $value, 'title' => '', 'target' => '' ), $id );
			}
		}
	}
	$count = (int) get_option( 'options_social_links', 0 );
	for ( $index = 0; $index < $count; $index++ ) {
		$value = get_option( 'options_social_links_' . $index . '_social_url', '' );
		if ( is_string( $value ) && '' !== $value ) {
			update_sub_field( array( 'field_nada_social_links', $index + 1, 'field_nada_social_url' ), array( 'url' => $value, 'title' => '', 'target' => '_blank' ), 'option' );
		}
	}
	nada_migrate_link_value( 'cta_url', 'option' );
	update_option( 'nada_link_fields_migrated', '2', false );
}, 30 );
