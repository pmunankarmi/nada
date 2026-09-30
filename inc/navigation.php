<?php
/**
 * Native WordPress navigation and editable mega-menu cards for NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) { return; }
	acf_add_local_field_group( array(
		'key' => 'group_nada_menu_image',
		'title' => 'Menu card image',
		'fields' => array( array( 'key' => 'field_nada_menu_image', 'name' => 'nada_menu_image', 'label' => 'Mega-menu image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'instructions' => 'Used for child items in the header mega menu.' ) ),
		'location' => array( array( array( 'param' => 'nav_menu_item', 'operator' => '==', 'value' => 'all' ) ) ),
	) );
} );

add_filter( 'nav_menu_item_title', function ( $title, $item, $args, $depth ) {
	if ( 'primary' !== ( $args->theme_location ?? '' ) || 1 !== $depth ) { return $title; }
	$image = absint( get_post_meta( $item->ID, 'nada_menu_image', true ) );
	return ( $image ? '<span class="megacard__img">' . wp_get_attachment_image( $image, 'medium_large', false, array( 'alt' => '', 'loading' => 'lazy' ) ) . '</span>' : '' ) . '<b>' . $title . '</b>';
}, 10, 4 );

/** Use the source panel layout while preserving native menu items and filters. */
class NADA_Mega_Menu_Walker extends Walker_Nav_Menu {
    private $panel_title = '';

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
        if ( 0 === $depth ) {
            $this->panel_title = apply_filters( 'the_title', $data_object->title, $data_object->ID );
        }
        parent::start_el( $output, $data_object, $depth, $args, $current_object_id );
    }

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 !== $depth ) {
            parent::start_lvl( $output, $depth, $args );
            return;
        }
        $output .= '<div class="navdd__panel"><div class="mega">';
        $output .= '<div class="mega__head"><span class="mega__title">' . esc_html( wp_strip_all_tags( $this->panel_title ) ) . '</span></div>';
        $output .= '<ul class="sub-menu nada-mega-list mega__grid mega__grid--rec">';
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth ) {
            $output .= '</ul></div></div>';
        } else {
            parent::end_lvl( $output, $depth, $args );
        }
    }
}

add_filter( 'nav_menu_css_class', function ( $classes, $item, $args, $depth ) {
    if ( ! empty( $args->nada_mega ) && 0 === $depth && in_array( 'menu-item-has-children', $classes, true ) ) {
        $classes[] = 'navdd';
        $classes[] = 'navdd--mega';
    }
    return $classes;
}, 10, 4 );

add_filter( 'nav_menu_link_attributes', function ( $attributes, $item, $args, $depth ) {
    if ( ! empty( $args->nada_mega ) && 0 === $depth && in_array( 'menu-item-has-children', $item->classes, true ) ) {
        $attributes['class'] = trim( ( $attributes['class'] ?? '' ) . ' navdd__top' );
    }
    if ( ! empty( $args->nada_mega ) && 1 === $depth ) {
        $attributes['class'] = trim( ( $attributes['class'] ?? '' ) . ' megacard megacard--rec' );
    }
    return $attributes;
}, 10, 4 );

add_filter( 'nav_menu_item_title', function ( $title, $item, $args, $depth ) {
    if ( ! empty( $args->nada_mega ) && 0 === $depth && in_array( 'menu-item-has-children', $item->classes, true ) ) {
        $title .= '<span class="navdd__chev" aria-hidden="true"></span>';
    }
    return $title;
}, 15, 4 );

/** Restore source navigation only for the starter menus owned by this theme. */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'nada_navigation_design_version' ) || ! function_exists( 'PLL' ) ) { return; }
	$pages = get_option( 'nada_import_pages', array() );
	if ( ! $pages ) { return; }
	$artwork = get_option( 'nada_artwork_posts', array() );
	$assignments = PLL()->options->get( 'nav_menus' );
	$theme = get_option( 'stylesheet' );
	$footer_menus = array();
	foreach ( $pages as $language => $language_pages ) {
		$menu = wp_get_nav_menu_object( 'NADA navigation ' . strtoupper( $language ) );
		if ( ! $menu ) { continue; }
		$items = wp_get_nav_menu_items( $menu );
		$recipes_id = 0;
		$has_community = false;
		foreach ( $items as $item ) {
			if ( str_contains( $item->url, '#community' ) ) { $has_community = true; }
			if ( $item->url === nada_starter_link( 'recipes.html', $language, $pages ) && ! $item->menu_item_parent ) { $recipes_id = $item->ID; }
			if ( $item->url === nada_starter_link( 'products.html', $language, $pages ) && $item->title === nada_translate( 'Products', $language ) ) {
				wp_update_post( array( 'ID' => $item->ID, 'post_title' => nada_translate( 'The Range', $language ), 'menu_order' => 4 ) );
			}
		}
		if ( ! $has_community ) {
			wp_update_nav_menu_item( $menu->term_id, 0, array( 'menu-item-title' => nada_translate( 'Community', $language ), 'menu-item-url' => nada_starter_link( 'index.html#community', $language, $pages ), 'menu-item-position' => 3, 'menu-item-status' => 'publish' ) );
		}
		$children = array_filter( $items, static function ( $item ) use ( $recipes_id ) { return $recipes_id && (int) $item->menu_item_parent === $recipes_id; } );
		if ( $recipes_id && ! $children ) {
			$cards = array( 'Breakfast' => 'breakfast', 'Smoothies' => 'smoothies', 'Dips & Sauces' => 'dips', 'Savoury' => 'savoury', 'Desserts' => 'desserts', 'Snacks' => 'snacks' );
			foreach ( $cards as $label => $image ) {
				$id = wp_update_nav_menu_item( $menu->term_id, 0, array( 'menu-item-title' => nada_translate( $label, $language ), 'menu-item-url' => nada_starter_link( 'recipes.html', $language, $pages ), 'menu-item-parent-id' => $recipes_id, 'menu-item-status' => 'publish' ) );
				$path = 'assets/img/recipes/recipe-' . $image . '.jpg';
				if ( ! is_wp_error( $id ) && isset( $artwork[ $path ] ) ) { update_field( 'field_nada_menu_image', get_post_thumbnail_id( $artwork[ $path ] ), $id ); }
			}
		}
		// The original footer has its own order and labels, separate from the header.
		if ( (int) ( $assignments[ $theme ]['footer'][ $language ] ?? 0 ) === (int) $menu->term_id ) {
			$footer = wp_create_nav_menu( 'NADA footer ' . strtoupper( $language ) );
			if ( is_wp_error( $footer ) ) { continue; }
			$footer_menus[ $language ] = $footer;
			foreach ( array( 'products.html' => 'Products', 'recipes.html' => 'Recipes', 'why-greek.html' => 'Nutrition', 'index.html#community' => 'Community' ) as $target => $label ) {
				wp_update_nav_menu_item( $footer, 0, array( 'menu-item-title' => nada_translate( $label, $language ), 'menu-item-url' => nada_starter_link( $target, $language, $pages ), 'menu-item-status' => 'publish' ) );
			}
			$assignments[ $theme ]['footer'][ $language ] = $footer;
		}
	}
	if ( $footer_menus ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations['footer'] = $footer_menus['en'] ?? reset( $footer_menus );
		set_theme_mod( 'nav_menu_locations', $locations );
		PLL()->options->set( 'nav_menus', $assignments );
	}
	if ( false === get_option( 'options_footer_logo', false ) && function_exists( 'nada_import_image' ) ) {
		$id = nada_import_image( 'assets/img/nada-logo.png', get_bloginfo( 'name' ) );
		if ( $id ) { update_field( 'field_nada_footer_logo', $id, 'option' ); }
	}
	update_option( 'nada_navigation_design_version', 1, false );
} );

/** Replace only the earlier starter thumbnails with the original menu photography. */
add_action( 'admin_init', function () {
    if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'nada_mega_photos_version' ) || ! function_exists( 'update_field' ) ) { return; }
    $photos = array( 'breakfast' => 'parfait', 'smoothies' => 'smoothie', 'dips' => 'tzatziki', 'savoury' => 'chicken', 'desserts' => 'bark', 'snacks' => 'honeybowl' );
    $complete = true;
    foreach ( array( 'en', 'ar' ) as $language ) {
        $menu = wp_get_nav_menu_object( 'NADA navigation ' . strtoupper( $language ) );
        if ( ! $menu ) { continue; }
        foreach ( wp_get_nav_menu_items( $menu ) as $item ) {
            if ( ! $item->menu_item_parent ) { continue; }
            $current = absint( get_post_meta( $item->ID, 'nada_menu_image', true ) );
            $source = get_post_meta( $current, '_nada_source_image', true );
            foreach ( $photos as $old => $new ) {
                if ( 'assets/img/recipes/recipe-' . $old . '.jpg' !== $source ) { continue; }
                $image = nada_import_image( 'assets/img/recipe-' . $new . '.jpg', $item->title );
                if ( $image ) { update_field( 'field_nada_menu_image', $image, $item->ID ); }
                else { $complete = false; }
            }
        }
    }
    if ( $complete ) { update_option( 'nada_mega_photos_version', 1, false ); }
}, 35 );
