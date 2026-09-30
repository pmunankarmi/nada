<?php
/**
 * Seed editable banner images from the original NADAfinal3.3.8 design assets.
 * The source artwork stays bundled; selected attachments live in uploads.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
	if ( ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'update_field' ) || get_option( 'nada_section_images_seeded' ) ) { return; }
	$pages = get_option( 'nada_import_pages', array() );
	if ( ! $pages ) { return; }
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$complete = true;
	foreach ( nada_data( 'section-images' ) as $name => $definition ) {
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_nada_design_source', 'meta_value' => $definition['path'], 'fields' => 'ids', 'numberposts' => 1, 'suppress_filters' => true ) );
		$attachment = $existing[0] ?? 0;
		if ( ! $attachment ) {
			$source = get_theme_file_path( '/' . $definition['path'] );
			$temp = wp_tempnam( basename( $source ) );
			if ( ! $temp || ! copy( $source, $temp ) ) { $complete = false; continue; }
			$attachment = media_handle_sideload( array( 'name' => basename( $source ), 'tmp_name' => $temp ), 0 );
			if ( is_wp_error( $attachment ) ) { wp_delete_file( $temp ); $complete = false; continue; }
			update_post_meta( $attachment, '_nada_design_source', $definition['path'] );
		}
		foreach ( $pages as $language => $language_pages ) {
			$archive = in_array( $definition['page'], array( 'recipes', 'products' ), true );
			$id = $archive ? 'option' : ( $language_pages[ 'why' === $definition['page'] ? 'why-greek' : 'home' ] ?? 0 );
			$field = $archive ? $name . '_' . $language : $name;
			$exists = $archive ? false !== get_option( 'options_' . $field, false ) : metadata_exists( 'post', $id, $field );
			if ( $id && ! $exists ) { update_field( 'field_nada_' . $field, $attachment, $id ); }
		}
	}
	if ( $complete ) { update_option( 'nada_section_images_seeded', 1, false ); }
}, 25 );
