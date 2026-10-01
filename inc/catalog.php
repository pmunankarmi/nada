<?php
/**
 * Native catalog queries and design migration for NADAfinal3.3.8.
 *
 * @package NADA
 */
defined( 'ABSPATH' ) || exit;

function nada_page_design() {
    if ( is_front_page() ) { return 'home'; }
    if ( is_post_type_archive( 'product' ) || is_tax( 'product_fat' ) ) { return 'products'; }
    if ( is_post_type_archive( 'recipe' ) || is_tax( 'recipe_category' ) || is_singular( 'recipe' ) ) { return 'recipes'; }
    if ( is_page_template( 'page-templates/why-greek.php' ) ) { return 'why'; }
    if ( is_page_template( 'page-templates/share-recipe.php' ) ) { return 'share'; }
    return '';
}

add_action( 'pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) { return; }
    if ( $query->is_post_type_archive( array( 'recipe', 'product' ) ) || $query->is_tax( array( 'recipe_category', 'product_fat' ) ) ) {
        $query->set( 'posts_per_page', -1 );
        $query->set( 'orderby', array( 'menu_order' => 'ASC', 'ID' => 'ASC' ) );
    }
} );

/** Seed missing presentation data once; keep editor-authored content and order. */
function nada_migrate_catalog_design() {
    if ( ! function_exists( 'update_field' ) || ! current_user_can( 'edit_theme_options' ) || get_option( 'nada_catalog_design_version' ) || ! get_option( 'nada_setup_complete' ) ) { return; }
    $library = nada_data( 'library' );
    $flavors = array_column( $library['KORA']['FLAVORS'], 'name', 'id' );
    foreach ( array( 'recipe' => $library['KORA']['RECIPES'], 'product' => nada_data( 'products' ) ) as $type => $items ) {
        foreach ( $items as $index => $item ) {
            foreach ( array( 'en', 'ar' ) as $lang ) {
                $posts = get_posts( array( 'post_type' => $type, 'numberposts' => 1, 'suppress_filters' => true, 'meta_key' => '_nada_source_id', 'meta_value' => $type . ':' . $item['id'] . ':' . $lang ) );
                if ( ! $posts ) { continue; }
                $post = $posts[0];
                $changes = array( 'ID' => $post->ID );
                if ( ! $post->menu_order ) { $changes['menu_order'] = $index + 1; }
                if ( 'recipe' === $type ) {
                    $localized = 'ar' === $lang ? array_merge( $item, $library['KORA_AR']['recipes'][ $item['id'] ] ?? array() ) : $item;
                    if ( ! $post->post_excerpt && ! empty( $localized['blurb'] ) ) { $changes['post_excerpt'] = $localized['blurb']; }
                    $pair = 'ar' === $lang ? ( $library['KORA_AR']['flavors'][ $item['pair'] ] ?? '' ) : ( $flavors[ $item['pair'] ] ?? '' );
                    if ( ! metadata_exists( 'post', $post->ID, 'recipe_pairing' ) ) { update_field( 'field_nada_recipe_pairing', $pair, $post->ID ); }
                }
                if ( count( $changes ) > 1 ) { wp_update_post( $changes ); }
            }
        }
    }
    $strings = get_option( 'nada_string_catalog', array() );
    $labels = nada_data( 'catalog-labels' );
    foreach ( $labels as $en => $ar ) {
        if ( ! isset( $strings[ 'label:' . $en ] ) ) { $strings[ 'label:' . $en ] = array( 'source' => $en, 'en' => $en, 'ar' => $ar ); }
    }
    if ( class_exists( 'PLL_MO' ) ) {
        foreach ( array( 'en', 'ar' ) as $slug ) {
            $language = PLL()->model->get_language( $slug );
            if ( ! $language ) { continue; }
            $mo = new PLL_MO();
            $mo->import_from_db( $language );
            foreach ( $labels as $en => $ar ) {
                if ( ! $mo->translate_if_any( $en ) ) { $mo->add_entry( new Translation_Entry( array( 'singular' => $en, 'translations' => array( 'ar' === $slug ? $ar : $en ) ) ) ); }
            }
            $mo->export_to_db( $language );
        }
    }
    update_option( 'nada_string_catalog', $strings, false );
    update_option( 'nada_catalog_design_version', 1, false );
}
add_action( 'admin_init', 'nada_migrate_catalog_design', 70 );
