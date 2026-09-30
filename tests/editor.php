<?php
/**
 * Editable content and logo integration checks for NADAfinal3.3.8.
 * Run only against a disposable WordPress installation.
 *
 * @package NADA
 */
wp_set_current_user( 1 );
$original = get_theme_mod( 'custom_logo' );
$images = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'posts_per_page' => 2, 'fields' => 'ids', 'lang' => '' ) );
function ensure_nada_option( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } echo "PASS: $message\n"; }
try {
 update_field( 'field_nada_site_logo', $images[0], 'option' );
 ensure_nada_option( (int) get_theme_mod( 'custom_logo' ) === $images[0], 'Options logo updates native setting' );
 set_theme_mod( 'custom_logo', $images[1] );
 acf_flush_value_cache( 'options', 'site_logo' );
 ensure_nada_option( (int) get_field( 'site_logo', 'option' ) === $images[1], 'Customizer logo appears in options' );
 update_field( 'field_nada_site_logo', false, 'option' );
 ensure_nada_option( ! get_theme_mod( 'custom_logo' ), 'Options logo removal syncs' );
 set_theme_mod( 'custom_logo', $images[0] );
 remove_theme_mod( 'custom_logo' );
 acf_flush_value_cache( 'options', 'site_logo' );
 ensure_nada_option( ! get_field( 'site_logo', 'option' ), 'Customizer logo removal syncs' );
} finally { set_theme_mod( 'custom_logo', $original ); }
ensure_nada_option( (bool) wp_next_scheduled( 'nada_hourly_theme_updates' ), 'Automatic hourly update scheduled' );
ensure_nada_option( ! function_exists( 'nada_updates_screen' ), 'Separate update screen removed' );

$pages = get_option( 'nada_import_pages' );
foreach ( $pages as $language => $language_pages ) {
 foreach ( nada_data( 'repeater-images' ) as $name => $paths ) {
  $page = nada_data( 'repeaters' )[ $name ]['page'];
  $id = 'recipes' === $page ? 'option' : $language_pages[ 'why' === $page ? 'why-greek' : 'home' ];
  $field_name = 'recipes' === $page ? $name . '_' . $language : $name;
  $rows = get_field( $field_name, $id );
  ensure_nada_option( count( $rows ) === count( $paths ), $field_name . ' rows preserved' );
  foreach ( $rows as $row ) { ensure_nada_option( wp_attachment_is_image( $row['image'] ), $field_name . ' Media Library image' ); }
 }
}
$id = $pages['en']['home'];
$before = get_field( 'home_reasons_wgclaims', $id );
try {
 $changed = $before;
 $changed[0]['image'] = 0;
 $changed[0]['text_1'] = '';
 update_field( 'field_nada_home_reasons_wgclaims', $changed, $id );
 nada_seed_editor_content();
 $after = get_field( 'home_reasons_wgclaims', $id );
 ensure_nada_option( empty( $after[0]['image'] ) && '' === $after[0]['text_1'], 'Cleared content stays empty' );
} finally { update_field( 'field_nada_home_reasons_wgclaims', $before, $id ); }
ensure_nada_option( count( get_field( 'ui_labels', 'option' ) ) >= 30, 'Shared labels editable' );
$calls = 0;
$mock = function ( $response, $args, $url ) use ( &$calls ) {
 if ( str_contains( $url, 'api.wordpress.org/themes/update-check/' ) ) { return array( 'headers' => array(), 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'themes' => array(), 'no_update' => array(), 'translations' => array() ) ) ); }
 if ( ! str_contains( $url, 'api.github.com/repos/pmunankarmi/nada/releases/latest' ) ) { return $response; }
 $calls++;
 return array( 'headers' => array(), 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'tag_name' => 'v9.0.0', 'assets' => array( array( 'name' => 'nada.zip', 'browser_download_url' => 'https://github.com/pmunankarmi/nada/releases/download/v9.0.0/nada.zip' ) ) ) ) );
};
add_filter( 'pre_http_request', $mock, 10, 3 );
try {
 delete_transient( 'nada_update_check_recent' );
 nada_refresh_theme_updates();
 $updates = get_site_transient( 'update_themes' );
 ensure_nada_option( '9.0.0' === $updates->response['nada']['new_version'], 'Automatic check populates native update notice' );
 nada_refresh_theme_updates();
 ensure_nada_option( 1 === $calls, 'Repeated admin visits throttle GitHub requests' );
} finally {
 remove_filter( 'pre_http_request', $mock, 10 );
 delete_transient( 'nada_github_release' );
 delete_transient( 'nada_update_check_recent' );
 delete_site_transient( 'update_themes' );
}
