<?php
/**
 * Global settings and native Customizer logo integration for NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) { return; }
	acf_add_options_page( array( 'page_title' => 'NADA Theme Options', 'menu_title' => 'Theme Options', 'menu_slug' => 'nada-options', 'capability' => 'edit_theme_options', 'redirect' => false, 'parent_slug' => 'themes.php' ) );
	$fields = array(
		array( 'key' => 'field_nada_logo_help', 'label' => 'Site logo', 'type' => 'message', 'message' => '<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ) . '">Open Customizer → Site Identity</a>. Manage the native WordPress site logo in the Customizer. Theme Options and the site use this same logo. No separate image field is stored.' ),
		nada_acf_text( 'footer_text_en', 'Footer text — English' ),
		nada_acf_text( 'footer_text_ar', 'Footer text — Arabic' ),
		nada_acf_text( 'cta_label_en', 'CTA label — English' ),
		nada_acf_text( 'cta_label_ar', 'CTA label — Arabic' ),
		array( 'key' => 'field_nada_cta_url', 'name' => 'cta_url', 'label' => 'CTA URL (leave empty for Share Recipe)', 'type' => 'url' ),
		array( 'key' => 'field_nada_social_links', 'name' => 'social_links', 'label' => 'Social media links', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array( nada_acf_text( 'social_label', 'Name' ), array( 'key' => 'field_nada_social_url', 'name' => 'social_url', 'label' => 'URL', 'type' => 'url' ) ) ),
	);
	// Archive copy belongs in global options because archives have no page ID.
	foreach ( nada_data( 'page-fields' ) as $name => $definition ) {
		if ( in_array( $definition['page'], array( 'recipes', 'products' ), true ) ) {
			foreach ( array( 'en', 'ar' ) as $language ) {
				$fields[] = nada_acf_text( $name . '_' . $language, $definition['label'] . ' — ' . strtoupper( $language ), $definition['type'] );
			}
		}
	}
	foreach ( nada_data( 'repeaters' ) as $name => $definition ) {
		if ( 'recipes' !== $definition['page'] ) { continue; }
		foreach ( array( 'en', 'ar' ) as $language ) {
			$sub_fields = array();
			foreach ( $definition['fields'] as $key => $field ) {
				$sub = nada_acf_text( $name . '_' . $language . '_' . $key, $field['label'], $field['type'] );
				$sub['name'] = $key;
				$sub_fields[] = $sub;
			}
			$fields[] = array( 'key' => 'field_nada_' . $name . '_' . $language, 'name' => $name . '_' . $language, 'label' => $definition['label'] . ' — ' . strtoupper( $language ), 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => $sub_fields );
		}
	}
	acf_add_local_field_group( array( 'key' => 'group_nada_options', 'title' => 'Global settings', 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'nada-options' ) ) ) ) );
} );
