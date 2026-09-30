<?php
/**
 * Seed newly editable content from NADAfinal3.3.8 without replacing saved edits.
 * Starter values belong in the database; templates render the editor's values.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

/** Resolve starter links to the matching translated WordPress destination. */
function nada_starter_link( $target, $language, $pages ) {
	if ( str_starts_with( $target, '#' ) ) { return $target; }
	$parts = explode( '#', $target, 2 );
	$slug = str_replace( '.html', '', $parts[0] );
	if ( in_array( $slug, array( 'recipes', 'products' ), true ) ) {
		$url = get_post_type_archive_link( 'recipes' === $slug ? 'recipe' : 'product' );
		if ( function_exists( 'PLL' ) ) {
			$url = PLL()->links_model->switch_language_in_link( $url, $language );
		}
	} else {
		$id = $pages[ $language ][ 'index' === $slug ? 'home' : $slug ] ?? 0;
		$url = $id ? get_permalink( $id ) : home_url( '/' );
	}
	return $url . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
}

function nada_seed_editor_content() {
	if ( ! function_exists( 'get_field' ) || get_option( 'nada_editor_content_version' ) === '4' ) { return; }
	$pages = get_option( 'nada_import_pages', array() );
	if ( ! $pages ) { return; }
	$artwork = get_option( 'nada_artwork_posts', array() );
	$page_slugs = array( 'home' => 'home', 'why' => 'why-greek', 'share' => 'share-recipe' );
	foreach ( $pages as $language => $language_pages ) {
		foreach ( nada_data( 'page-fields' ) as $name => $definition ) {
			if ( isset( nada_data( 'content-links' )[ $name ] ) ) { continue; }
			$archive = in_array( $definition['page'], array( 'recipes', 'products' ), true );
			$id = $archive ? 'option' : ( $language_pages[ $page_slugs[ $definition['page'] ] ] ?? 0 );
			$field_name = $archive ? $name . '_' . $language : $name;
			$exists = $archive ? false !== get_option( 'options_' . $field_name, false ) : metadata_exists( 'post', $id, $field_name );
			if ( $id && ! $exists ) { update_field( 'field_nada_' . $field_name, nada_translate( $definition['default'], $language ), $id ); }
		}
		foreach ( array( 'footer_text' => 'Taste & Health, Everyday', 'cta_label' => 'Share Your Recipe' ) as $name => $text ) {
			if ( false === get_option( 'options_' . $name . '_' . $language, false ) ) {
				update_field( 'field_nada_' . $name . '_' . $language, nada_translate( $text, $language ), 'option' );
			}
		}

		foreach ( nada_data( 'repeater-images' ) as $name => $paths ) {
			$definition = nada_data( 'repeaters' )[ $name ];
			$archive = 'recipes' === $definition['page'];
			$id = $archive ? 'option' : ( $language_pages[ $page_slugs[ $definition['page'] ] ] ?? 0 );
			if ( ! $id ) { continue; }
			$field_name = $archive ? $name . '_' . $language : $name;
			$rows = get_field( $field_name, $id );
			if ( ! is_array( $rows ) ) {
				$exists = $archive ? false !== get_option( 'options_' . $field_name, false ) : metadata_exists( 'post', $id, $field_name );
				if ( $exists ) { continue; }
				$rows = $definition['rows'];
			}
			foreach ( $rows as $index => &$row ) {
				$meta = $field_name . '_' . $index . '_image';
				$exists = $archive ? false !== get_option( 'options_' . $meta, false ) : metadata_exists( 'post', $id, $meta );
				if ( ! $exists ) {
					$path = $paths[ $index ] ?? '';
					$row['image'] = isset( $artwork[ $path ] ) ? get_post_thumbnail_id( $artwork[ $path ] ) : 0;
				}
				if ( 'home_section_5_rtiles_grid' === $name && ! metadata_exists( 'post', $id, $field_name . '_' . $index . '_url' ) ) {
					$row['url'] = nada_starter_link( 'recipes.html', $language, $pages );
				}
			}
			unset( $row );
			update_field( 'field_nada_' . $field_name, $rows, $id );
		}
		$home_id = $language_pages['home'] ?? 0;
		if ( $home_id && ! metadata_exists( 'post', $home_id, 'home_reasons_image' ) ) {
			update_field( 'field_nada_home_reasons_image', get_post_thumbnail_id( $artwork['assets/img/stage-cup-v3.webp'] ?? 0 ), $home_id );
		}
		foreach ( nada_data( 'content-links' ) as $name => $definition ) {
			$archive = in_array( $definition['page'], array( 'recipes', 'products' ), true );
			$id = $archive ? 'option' : ( $language_pages[ $page_slugs[ $definition['page'] ] ] ?? 0 );
			$field_name = $archive ? $name . '_' . $language : $name;
			$exists = $archive ? false !== get_option( 'options_' . $field_name, false ) : metadata_exists( 'post', $id, $field_name );
			if ( $id && ! $exists ) { update_field( 'field_nada_' . $field_name, nada_starter_link( $definition['target'], $language, $pages ), $id ); }
		}
	}
	if ( false === get_option( 'options_ui_labels', false ) ) {
		$labels = array();
		foreach ( nada_data( 'ui-labels' ) as $text ) {
			$labels[] = array( 'key' => $text, 'ui_en' => $text, 'ui_ar' => nada_translate( $text, 'ar' ) );
		}
		update_field( 'field_nada_ui_labels', $labels, 'option' );
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) && empty( $locations['footer'] ) ) {
		$menus = array();
		foreach ( $pages as $language => $language_pages ) {
			$menu = wp_create_nav_menu( 'NADA navigation ' . strtoupper( $language ) );
			if ( is_wp_error( $menu ) ) { continue; }
			$menus[ $language ] = $menu;
			if ( function_exists( 'pll_set_term_language' ) ) { pll_set_term_language( $menu, $language ); }
			foreach ( array( 'why-greek.html' => 'Why Greek', 'recipes.html' => 'Recipes', 'products.html' => 'Products' ) as $target => $label ) {
				wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => nada_translate( $label, $language ), 'menu-item-url' => nada_starter_link( $target, $language, $pages ), 'menu-item-status' => 'publish' ) );
			}
		}
		if ( $menus ) {
			if ( function_exists( 'pll_save_term_translations' ) ) { pll_save_term_translations( $menus ); }
			$locations['primary'] = $menus['en'] ?? reset( $menus );
			$locations['footer'] = $locations['primary'];
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
	// Polylang stores menu assignments per language in addition to native locations.
	if ( function_exists( 'PLL' ) && method_exists( PLL()->options, 'set' ) ) {
		$assignments = PLL()->options->get( 'nav_menus' );
		$theme = get_option( 'stylesheet' );
		foreach ( array_keys( $pages ) as $language ) {
			$menu = wp_get_nav_menu_object( 'NADA navigation ' . strtoupper( $language ) );
			if ( ! $menu ) { continue; }
			foreach ( array( 'primary', 'footer' ) as $location ) {
				if ( empty( $assignments[ $theme ][ $location ][ $language ] ) ) {
					$assignments[ $theme ][ $location ][ $language ] = $menu->term_id;
				}
			}
		}
		PLL()->options->set( 'nav_menus', $assignments );
	}
	// Repair only archive URLs generated by the earlier starter-link migration.
	foreach ( $pages as $language => $language_pages ) {
		foreach ( array( 'recipes', 'products' ) as $archive ) {
			$old_url = trailingslashit( pll_home_url( $language ) ) . $archive . '/';
			$new_url = nada_starter_link( $archive . '.html', $language, $pages );
			if ( $old_url === $new_url ) { continue; }
			foreach ( nada_data( 'content-links' ) as $name => $definition ) {
				$is_archive = in_array( $definition['page'], array( 'recipes', 'products' ), true );
				$id = $is_archive ? 'option' : ( $language_pages[ $page_slugs[ $definition['page'] ] ] ?? 0 );
				$field = $is_archive ? $name . '_' . $language : $name;
				if ( $id && $old_url === get_field( $field, $id ) ) { update_field( 'field_nada_' . $field, $new_url, $id ); }
			}
			$home = $language_pages['home'] ?? 0;
			$rows = $home ? get_field( 'home_section_5_rtiles_grid', $home ) : array();
			if ( is_array( $rows ) ) {
				foreach ( $rows as &$row ) { if ( ( $row['url'] ?? '' ) === $old_url ) { $row['url'] = $new_url; } }
				unset( $row );
				update_field( 'field_nada_home_section_5_rtiles_grid', $rows, $home );
			}
			$menu = wp_get_nav_menu_object( 'NADA navigation ' . strtoupper( $language ) );
			foreach ( $menu ? (array) wp_get_nav_menu_items( $menu ) : array() as $item ) {
				if ( $old_url === $item->url ) { update_post_meta( $item->ID, '_menu_item_url', $new_url ); }
			}
		}
	}
	update_option( 'nada_editor_content_version', '4', false );
}
add_action( 'admin_init', function () {
	if ( current_user_can( 'edit_theme_options' ) ) { nada_seed_editor_content(); }
} );
