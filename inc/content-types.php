<?php
/**
 * Recipe and product publishing models for the NADAfinal3.3.8 conversion.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_register_content_types() {
	foreach ( array( 'recipe' => array( 'Recipes', 'Recipe', 'recipes' ), 'product' => array( 'Products', 'Product', 'products' ) ) as $type => $labels ) {
		register_post_type( $type, array(
			'labels' => array( 'name' => $labels[0], 'singular_name' => $labels[1], 'add_new_item' => 'Add ' . $labels[1], 'edit_item' => 'Edit ' . $labels[1] ),
			'public' => true,
			'has_archive' => $labels[2],
			'rewrite' => array( 'slug' => $labels[2], 'with_front' => false ),
			'show_in_rest' => true,
			'menu_icon' => 'recipe' === $type ? 'dashicons-food' : 'dashicons-cart',
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
		) );
	}
	register_post_type( 'nada_artwork', array(
		'labels' => array( 'name' => 'Content Images', 'singular_name' => 'Content Image' ),
		'public' => false, 'show_ui' => true, 'show_in_menu' => 'themes.php',
		'supports' => array( 'title', 'thumbnail' ),
	) );
	register_taxonomy( 'recipe_category', 'recipe', array(
		'label' => __( 'Recipe categories', 'nada' ), 'public' => true, 'hierarchical' => true,
		'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => array( 'slug' => 'recipe-category', 'with_front' => false ),
	) );
	register_taxonomy( 'product_fat', 'product', array(
		'label' => __( 'Fat types', 'nada' ), 'public' => true, 'hierarchical' => true,
		'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => array( 'slug' => 'fat', 'with_front' => false ),
	) );
}
add_action( 'init', 'nada_register_content_types' );
add_action( 'after_switch_theme', function () {
	nada_register_content_types();
	flush_rewrite_rules();
} );
