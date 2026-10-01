<?php
/**
 * Centralized Polylang translations for NADAfinal3.3.8 shared theme content.
 * Existing ACF values remain stored as a migration backup; Polylang owns edits.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

/** Read a shared string using its stable theme reference and requested language. */
function nada_string_translation( $reference, $slug ) {
	$strings = get_option( 'nada_string_catalog', array() );
	if ( ! isset( $strings[ $reference ] ) ) { return null; }
	$string = $strings[ $reference ];
	if ( class_exists( 'PLL_MO' ) && function_exists( 'PLL' ) ) {
		static $catalogs = array();
		if ( ! isset( $catalogs[ $slug ] ) ) {
			$language = PLL()->model->get_language( $slug );
			if ( $language ) {
				$catalogs[ $slug ] = new PLL_MO();
				$catalogs[ $slug ]->import_from_db( $language );
			}
		}
		if ( isset( $catalogs[ $slug ] ) ) {
			$value = $catalogs[ $slug ]->translate_if_any( $string['source'] );
			return $value;
		}
	}
	return $string[ $slug ] ?? $string['en'];
}

/** Collect existing shared copy without introducing new hardcoded frontend text. */
function nada_collect_shared_strings() {
	$strings = array();
	$count = (int) get_option( 'options_ui_labels', 0 );
	for ( $index = 0; $index < $count; $index++ ) {
		$prefix = 'options_ui_labels_' . $index;
		$key = get_option( $prefix . '_key', '' );
		if ( ! in_array( $key, nada_data( 'ui-labels' ), true ) ) { continue; }
		$strings[ 'label:' . $key ] = array( 'source' => $key, 'en' => get_option( $prefix . '_ui_en', '' ), 'ar' => get_option( $prefix . '_ui_ar', '' ) );
	}
	$fields = array( 'footer_text' => 'Footer text', 'cta_label' => 'CTA label', 'contact_address' => 'Contact address' );
	foreach ( nada_data( 'page-fields' ) as $name => $definition ) {
		if ( in_array( $definition['page'], array( 'recipes', 'products' ), true ) && in_array( $definition['type'], array( 'text', 'textarea' ), true ) ) {
			$fields[ $name ] = ucfirst( $definition['page'] ) . ': ' . $definition['label'];
		}
	}
	foreach ( nada_data( 'email-templates' ) as $name => $definition ) {
		if ( str_ends_with( $name, '_ar' ) ) { continue; }
		$fields[ str_ends_with( $name, '_en' ) ? substr( $name, 0, -3 ) : $name ] = $definition['label'];
	}
	foreach ( $fields as $name => $label ) {
		$unpaired = in_array( $name, array( 'submission_admin_subject', 'submission_admin_body' ), true );
		$en = (string) get_option( 'options_' . $name . ( $unpaired ? '' : '_en' ), '' );
		$ar = $unpaired ? $en : (string) get_option( 'options_' . $name . '_ar', '' );
		$strings[ 'option:' . $name ] = array( 'source' => $en ?: $label, 'en' => $en, 'ar' => $ar );
	}
	foreach ( nada_data( 'repeaters' ) as $name => $definition ) {
		if ( 'recipes' !== $definition['page'] ) { continue; }
		$count = max( (int) get_option( 'options_' . $name . '_en', 0 ), (int) get_option( 'options_' . $name . '_ar', 0 ) );
		for ( $index = 0; $index < $count; $index++ ) {
			foreach ( $definition['fields'] as $key => $field ) {
				$en = (string) get_option( 'options_' . $name . '_en_' . $index . '_' . $key, '' );
				$ar = (string) get_option( 'options_' . $name . '_ar_' . $index . '_' . $key, '' );
				$strings[ 'row:' . $name . ':' . $index . ':' . $key ] = array( 'source' => $en ?: $definition['label'] . ' ' . ( $index + 1 ) . ': ' . $field['label'], 'en' => $en, 'ar' => $ar );
			}
		}
	}
	return $strings;
}

/** Register at init and after migration so the first admin visit has every row. */
function nada_register_shared_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) { return; }
	foreach ( get_option( 'nada_string_catalog', array() ) as $reference => $string ) {
		pll_register_string( $reference, $string['source'], 'NADA', true );
	}
}
add_action( 'init', 'nada_register_shared_strings', 20 );

/** Import saved English and Arabic values once, preserving existing Polylang edits. */
function nada_migrate_shared_strings() {
	if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'nada_string_catalog_version' ) || ! class_exists( 'PLL_MO' ) || ! get_option( 'nada_setup_complete' ) ) { return; }
	$languages = array();
	foreach ( array( 'en', 'ar' ) as $slug ) {
		$languages[ $slug ] = PLL()->model->get_language( $slug );
		if ( ! $languages[ $slug ] ) { return; }
	}
	$strings = nada_collect_shared_strings();
	foreach ( $languages as $slug => $language ) {
		$catalog = new PLL_MO();
		$catalog->import_from_db( $language );
		foreach ( $strings as $string ) {
			$existing = $catalog->translate_if_any( $string['source'] );
			if ( '' !== $existing && $existing !== $string['source'] ) { continue; }
			$catalog->add_entry( new Translation_Entry( array( 'singular' => $string['source'], 'translations' => array( $string[ $slug ] ) ) ) );
		}
		$catalog->export_to_db( $language );
	}
	update_option( 'nada_string_catalog', $strings, false );
	update_option( 'nada_string_catalog_version', 1, false );
	nada_register_shared_strings();
}
add_action( 'admin_init', 'nada_migrate_shared_strings', 60 );
