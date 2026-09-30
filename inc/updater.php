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

add_action( 'admin_menu', function () {
	add_theme_page( 'NADA Updates', 'NADA Updates', 'update_themes', 'nada-updates', 'nada_updates_screen' );
} );

/** Allow administrators to diagnose host connectivity without exposing credentials. */
function nada_updates_screen() {
	if ( ! current_user_can( 'update_themes' ) ) { return; }
	$message = '';
	if ( isset( $_POST['nada_updates_nonce'] ) ) {
		check_admin_referer( 'nada_updates', 'nada_updates_nonce' );
		delete_transient( 'nada_github_release' );
		$response = wp_remote_get( 'https://api.github.com/repos/pmunankarmi/nada/releases/latest', array( 'timeout' => 15, 'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'NADA-WordPress-Theme' ) ) );
		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
		} else {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$message = 'GitHub HTTP ' . wp_remote_retrieve_response_code( $response ) . ': ' . ( $body['tag_name'] ?? $body['message'] ?? 'No release metadata returned.' );
		}
		delete_site_transient( 'update_themes' );
		wp_update_themes();
	}
	?>
	<div class="wrap">
		<h1>NADA Updates</h1>
		<p>Installed version: <?php echo esc_html( wp_get_theme( get_template() )->get( 'Version' ) ); ?></p>
		<?php if ( $message ) : ?><div class="notice notice-info"><p><?php echo esc_html( $message ); ?></p></div><?php endif; ?>
		<p>Each push to the repository's main branch publishes a versioned theme release. WordPress checks for updates automatically. Use this check to refresh release metadata and test GitHub connectivity.</p>
		<form method="post"><?php wp_nonce_field( 'nada_updates', 'nada_updates_nonce' ); submit_button( 'Check GitHub connection and updates' ); ?></form>
		<p><a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>">Open WordPress Updates</a></p>
	</div>
	<?php
}
