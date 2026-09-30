<?php
/**
 * Native WordPress theme features and asset loading for NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'nada', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 160, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	register_nav_menus( array( 'primary' => __( 'Primary menu', 'nada' ), 'footer' => __( 'Footer menu', 'nada' ) ) );
	add_image_size( 'nada-card', 800, 600, true );
} );

// This is a classic theme: keep the classic editor for posts and widgets.
add_filter( 'use_block_editor_for_post', '__return_false', 100 );
add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
add_filter( 'use_widgets_block_editor', '__return_false' );

add_action( 'wp_enqueue_scripts', function () {
	$version = wp_get_theme( get_template() )->get( 'Version' );
	$previous = array();
	foreach ( array( 'style', 'oikos', 'v3', 'wordpress' ) as $file ) {
		wp_enqueue_style( 'nada-' . $file, get_theme_file_uri( '/assets/css/' . $file . '.css' ), $previous, $version );
		$previous = array( 'nada-' . $file );
	}
	wp_enqueue_style( 'nada-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Baloo+Bhaijaan+2:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800&family=Rubik:wght@300;400;500;600;700&display=swap', array(), null );
	wp_enqueue_script( 'nada-theme', get_theme_file_uri( '/assets/js/wordpress.js' ), array(), $version, true );
} );

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'NADA: install and activate your licensed ACF Pro plugin for field editing and theme options.', 'nada' ) . '</p></div>';
	}
	if ( ! function_exists( 'pll_current_language' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'NADA: activate Polylang and add English and Arabic to enable bilingual content.', 'nada' ) . '</p></div>';
	}
} );
