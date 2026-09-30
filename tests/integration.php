<?php
/**
 * Disposable WordPress integration checks for the NADAfinal3.3.8 conversion.
 * Run with wp eval-file tests/integration.php after importing starter content.
 *
 * @package NADA
 */

function check_nada( $condition, $message ) {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
    echo 'PASS: ' . $message . "\n";
}
check_nada( count( get_posts( array( 'post_type' => 'recipe', 'posts_per_page' => -1, 'lang' => '', 'suppress_filters' => true ) ) ) === 38, '38 translated recipe posts' );
check_nada( count( get_posts( array( 'post_type' => 'product', 'posts_per_page' => -1, 'lang' => '', 'suppress_filters' => true ) ) ) === 26, '26 translated product posts' );
$pages = get_option( 'nada_import_pages' );
check_nada( get_post( $pages['en']['why-greek'] )->post_name === get_post( $pages['ar']['why-greek'] )->post_name, 'English and Arabic pages share slugs' );
check_nada( pll_get_post( $pages['en']['home'], 'ar' ) === $pages['ar']['home'], 'Homepage translations linked' );
$recipes = get_posts( array( 'post_type' => 'recipe', 'posts_per_page' => -1, 'suppress_filters' => true ) );
foreach ( $recipes as $recipe ) {
    check_nada( has_post_thumbnail( $recipe->ID ), 'Featured image for recipe ' . $recipe->ID );
    check_nada( count( get_field( 'recipe_ingredients', $recipe->ID ) ) > 0, 'Ingredients repeater for recipe ' . $recipe->ID );
}
$id = $pages['en']['home'];
$field = 'home_hero_greek_wins_everyday';
$before = get_field( $field, $id );
update_field( 'field_nada_' . $field, '<b>Test</b><script>alert(1)</script>', $id );
check_nada( ! str_contains( get_field( $field, $id ), '<' ), 'Custom fields strip HTML' );
update_field( 'field_nada_' . $field, $before, $id );
$release = array( 'tag_name' => 'v1.0.123', 'assets' => array( array( 'name' => 'nada.zip', 'browser_download_url' => 'https://github.com/pmunankarmi/nada/releases/download/v1.0.123/nada.zip' ) ) );
set_transient( 'nada_github_release', $release, 60 );
$update = nada_github_update( false, array( 'UpdateURI' => 'https://github.com/pmunankarmi/nada' ), 'nada' );
check_nada( '1.0.123' === $update['version'], 'GitHub release offered through native theme update filter' );
$release['assets'][0]['browser_download_url'] = 'https://example.com/evil.zip';
set_transient( 'nada_github_release', $release, 60 );
check_nada( false === nada_github_update( false, array( 'UpdateURI' => 'https://github.com/pmunankarmi/nada' ), 'nada' ), 'Updater rejects non-repository download URLs' );
delete_transient( 'nada_github_release' );
check_nada( ! apply_filters( 'use_block_editor_for_post_type', true, 'page' ), 'Block editor disabled' );
echo "Integration checks complete\n";
