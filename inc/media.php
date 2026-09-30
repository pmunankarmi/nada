<?php
/**
 * Store admin-editable NADAfinal3.3.8 images in uploads, outside theme updates.
 * Fixed backgrounds and design artwork remain in theme assets.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_media_directory() {
	$uploads = wp_upload_dir();
	return trailingslashit( $uploads['basedir'] ) . 'nada/3.3.8/';
}

/** Download the immutable, checksum-verified starter images once per site. */
function nada_prepare_media() {
	$manifest = nada_data( 'media-manifest' );
	$directory = nada_media_directory();
	if ( get_option( 'nada_media_version' ) === $manifest['sha256'] && is_file( $directory . 'assets/img/nada-mark.png' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$archive = download_url( $manifest['url'], 120 );
	if ( is_wp_error( $archive ) ) {
		throw new RuntimeException( $archive->get_error_message() );
	}
	try {
		if ( ! hash_equals( $manifest['sha256'], hash_file( 'sha256', $archive ) ) ) {
			throw new RuntimeException( 'Starter media checksum did not match. No files were installed.' );
		}
		if ( ! WP_Filesystem() ) {
			throw new RuntimeException( 'WordPress could not access uploads. Check filesystem permissions.' );
		}
		$result = unzip_file( $archive, $directory );
		if ( is_wp_error( $result ) ) {
			throw new RuntimeException( $result->get_error_message() );
		}
		foreach ( $manifest['files'] as $path => $checksum ) {
			if ( ! is_file( $directory . $path ) || ! hash_equals( $checksum, hash_file( 'sha256', $directory . $path ) ) ) {
				throw new RuntimeException( 'An imported media file failed verification: ' . $path );
			}
		}
		update_option( 'nada_media_version', $manifest['sha256'], false );
	} finally {
		wp_delete_file( $archive );
	}
}

// Existing installations migrate when an administrator opens the dashboard after updating.
// Fresh installations prepare uploads as part of the explicit starter-content import.
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || ! get_option( 'nada_setup_complete' ) || get_option( 'nada_media_version' ) === ( nada_data( 'media-manifest' )['sha256'] ?? '' ) ) {
		return;
	}
	try {
		nada_prepare_media();
	} catch ( Throwable $error ) {
		add_action( 'admin_notices', static function () use ( $error ) {
			echo '<div class="notice notice-error"><p>' . esc_html( 'NADA media migration: ' . $error->getMessage() ) . '</p></div>';
		} );
	}
} );
