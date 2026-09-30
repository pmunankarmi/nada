<?php
/**
 * Native theme updates from GitHub release assets built from each main push.
 * Source theme: NADAfinal3.3.8. Repository: pmunankarmi/nada.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_github_update( $update, $theme_data, $stylesheet ) {
	if ( 'https://github.com/pmunankarmi/nada' !== ( $theme_data['UpdateURI'] ?? '' ) ) {
		return $update;
	}
	$release = get_transient( 'nada_github_release' );
	if ( false === $release ) {
		$response = wp_remote_get( 'https://api.github.com/repos/pmunankarmi/nada/releases/latest', array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'NADA-WordPress-Theme' ),
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $update;
		}
		$release = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $release ) || empty( $release['tag_name'] ) ) {
			return $update;
		}
		set_transient( 'nada_github_release', $release, HOUR_IN_SECONDS );
	}
	$version = ltrim( $release['tag_name'] ?? '', 'v' );
	if ( ! preg_match( '/^\d+\.\d+\.\d+$/', $version ) || ! empty( $release['prerelease'] ) || ! empty( $release['draft'] ) ) {
		return $update;
	}
	$package = '';
	foreach ( $release['assets'] ?? array() as $asset ) {
		$url = $asset['browser_download_url'] ?? '';
		if ( 'nada.zip' === ( $asset['name'] ?? '' ) && str_starts_with( $url, 'https://github.com/pmunankarmi/nada/releases/download/' ) ) {
			$package = $url;
			break;
		}
	}
	if ( ! $package ) {
		return $update;
	}
	return array( 'theme' => $stylesheet, 'version' => $version, 'url' => 'https://github.com/pmunankarmi/nada', 'package' => $package, 'requires' => '6.6', 'requires_php' => '8.1' );
}
add_filter( 'update_themes_github.com', 'nada_github_update', 10, 3 );

// The native “Check again” action should also refresh the GitHub cache.
add_action( 'load-update-core.php', function () {
	if ( current_user_can( 'update_themes' ) && isset( $_GET['force-check'] ) ) {
		delete_transient( 'nada_github_release' );
		delete_site_transient( 'update_themes' );
	}
} );

/** Refresh native update notices hourly without a separate settings screen. */
function nada_refresh_theme_updates() {
	if ( get_transient( 'nada_update_check_recent' ) ) { return; }
	set_transient( 'nada_update_check_recent', 1, HOUR_IN_SECONDS );
	delete_transient( 'nada_github_release' );
	delete_site_transient( 'update_themes' );
	wp_update_themes();
}
add_action( 'nada_hourly_theme_updates', 'nada_refresh_theme_updates' );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'nada_hourly_theme_updates' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'nada_hourly_theme_updates' );
	}
} );
add_action( 'admin_init', function () {
	if ( current_user_can( 'update_themes' ) ) {
		nada_refresh_theme_updates();
	}
} );
add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'nada_hourly_theme_updates' );
	delete_transient( 'nada_update_check_recent' );
} );
