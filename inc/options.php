<?php
/**
 * Global settings and native Customizer logo integration for NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) { return; }
	acf_add_options_page( array( 'page_title' => 'NADA Theme Options', 'menu_title' => 'Theme Options', 'menu_slug' => 'nada-options', 'capability' => 'edit_theme_options', 'redirect' => false, 'position' => 60, 'icon_url' => 'dashicons-admin-generic' ) );
	$fields = array(
		array( 'key' => 'field_nada_options_global', 'label' => 'Global', 'type' => 'tab' ),
		array( 'key' => 'field_nada_site_logo', 'name' => 'site_logo', 'label' => 'Site logo', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Uses the same site logo as Customize → Site Identity. Changes in either place stay in sync.' ),
		array( 'key' => 'field_nada_footer_logo', 'name' => 'footer_logo', 'label' => 'Footer logo', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'instructions' => 'Optional footer version of the logo. Leave empty to use the synchronized site logo.' ),
		nada_acf_text( 'footer_text_en', 'Footer text — English' ),
		nada_acf_text( 'footer_text_ar', 'Footer text — Arabic' ),
		nada_acf_text( 'cta_label_en', 'CTA label — English' ),
		nada_acf_text( 'cta_label_ar', 'CTA label — Arabic' ),
		array( 'key' => 'field_nada_cta_url', 'name' => 'cta_url', 'label' => 'CTA link', 'type' => 'link', 'return_format' => 'array' ),

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
			if ( isset( nada_data( 'repeater-images' )[ $name ] ) ) {
				$sub_fields[] = array( 'key' => 'field_nada_' . $name . '_' . $language . '_image', 'name' => 'image', 'label' => 'Image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail' );
			}
			$fields[] = array( 'key' => 'field_nada_' . $name . '_' . $language, 'name' => $name . '_' . $language, 'label' => $definition['label'] . ' — ' . strtoupper( $language ), 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => $sub_fields );
		}
	}

	foreach ( nada_data( 'section-images' ) as $name => $image ) {
		if ( ! in_array( $image['page'], array( 'recipes', 'products' ), true ) ) { continue; }
		foreach ( array( 'en', 'ar' ) as $language ) {
			$fields[] = array( 'key' => 'field_nada_' . $name . '_' . $language, 'name' => $name . '_' . $language, 'label' => ucfirst( $image['page'] ) . ' ' . $image['label'] . ' — ' . strtoupper( $language ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail' );
		}
	}
	$fields[] = array( 'key' => 'field_nada_options_translation', 'label' => 'Language Translation', 'type' => 'tab' );
	$fields[] = array( 'key' => 'field_nada_translation_location', 'label' => 'Edit shared text', 'type' => 'message', 'message' => '<a href="' . esc_url( admin_url( 'admin.php?page=mlang_strings&group=NADA' ) ) . '">Open Languages → Translations → NADA</a><p>English and Arabic labels, footer and CTA copy, contact address, recipe/product page copy, kitchen swap text, and notification messages are edited there. Page content and navigation remain in their native WordPress editors.</p>', 'esc_html' => 0 );
	$fields[] = array( 'key' => 'field_nada_ui_labels', 'name' => 'ui_labels', 'label' => 'Site labels and messages', 'instructions' => 'These labels are synchronized with Languages → Translations → NADA. Edit English or Arabic in either location.', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array( array( 'key' => 'field_nada_ui_key', 'name' => 'key', 'label' => 'Label reference', 'type' => 'text', 'readonly' => 1 ), nada_acf_text( 'ui_en', 'English' ), nada_acf_text( 'ui_ar', 'Arabic' ) ) );
	$fields[] = array( 'key' => 'field_nada_options_social', 'label' => 'Social Media', 'type' => 'tab' );
	$fields[] = array( 'key' => 'field_nada_social_links', 'name' => 'social_links', 'label' => 'Social media links', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array( nada_acf_text( 'social_label', 'Name' ), array( 'key' => 'field_nada_social_url', 'name' => 'social_url', 'label' => 'Link', 'type' => 'link', 'return_format' => 'array' ) ) );
	$fields[] = array( 'key' => 'field_nada_options_contact', 'label' => 'Contact', 'type' => 'tab' );
	$fields[] = nada_acf_text( 'contact_email', 'Email', 'email' );
	$fields[] = nada_acf_text( 'contact_phone', 'Phone' );
	$fields[] = nada_acf_text( 'contact_address_en', 'Address — English', 'textarea' );
	$fields[] = nada_acf_text( 'contact_address_ar', 'Address — Arabic', 'textarea' );
	$fields[] = array( 'key' => 'field_nada_options_notifications', 'label' => 'Notifications', 'type' => 'tab' );
	$fields[] = array( 'key' => 'field_nada_submission_sender_email', 'name' => 'submission_sender_email', 'label' => 'Recipe submissions — sender email', 'type' => 'email', 'instructions' => 'From address for admin notifications and submitter acknowledgements. Use an address authorized by your mail service. Submitter acknowledgements require a valid sender address.' );
	$fields[] = array( 'key' => 'field_nada_submission_receiver_email', 'name' => 'submission_receiver_email', 'label' => 'Recipe submissions — receiver email', 'type' => 'email', 'instructions' => 'Notifications go to this address. Leave empty to disable email notifications; submissions are still saved in Recipe Submissions.' );
	$fields[] = array( 'key' => 'field_nada_submission_confirmation_enabled', 'name' => 'submission_confirmation_enabled', 'label' => 'Email acknowledgement to submitter', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'instructions' => 'Send a receipt to the visitor who submitted the recipe. Requires a sender email above.' );
	foreach ( nada_data( 'email-templates' ) as $name => $template ) {
		$fields[] = array( 'key' => 'field_nada_' . $name, 'name' => $name, 'label' => $template['label'], 'type' => $template['type'], 'new_lines' => '', 'instructions' => $template['instructions'] );
	}
    // Keep archive settings together and explain the page where they appear.
    $grouped = array();
    $archive_fields = array( 'recipes' => array(), 'products' => array() );
    foreach ( $fields as $field ) {
        $name = $field['name'] ?? '';
        $archive = str_starts_with( $name, 'recipes_' ) ? 'recipes' : ( str_starts_with( $name, 'products_' ) ? 'products' : '' );
        if ( $archive ) {
            $archive_fields[ $archive ][] = $field;
        } else {
            $grouped[] = $field;
        }
    }
    foreach ( $archive_fields as $archive => $items ) {
        $grouped[] = array( 'key' => 'field_nada_options_' . $archive, 'label' => ucfirst( $archive ) . ' page', 'type' => 'tab' );
        foreach ( $items as $field ) {
            $name = $field['name'];
            $section = str_contains( $name, '_rbooktop_' ) ? 'Page introduction' : ( str_contains( $name, '_swaps_' ) ? 'Kitchen swaps' : ( str_contains( $name, '_section_4_' ) || str_contains( $name, '_section_3_' ) ? 'Bottom call to action' : 'Page banner' ) );
            $field['instructions'] = 'Used on the ' . ucfirst( $archive ) . ' page → ' . $section . '. ' . ( $field['instructions'] ?? '' );
            if ( str_contains( $name, '_rbooktop_a_pot_' ) ) { $field['label'] = 'Page heading — ' . strtoupper( substr( $name, -2 ) ); }
            if ( str_contains( $name, '_rbooktop_thick_' ) ) { $field['label'] = 'Page introduction — ' . strtoupper( substr( $name, -2 ) ); }
            $grouped[] = $field;
        }
    }
    $fields = $grouped;
	acf_add_local_field_group( array( 'key' => 'group_nada_options', 'title' => 'Global settings', 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'nada-options' ) ) ) ) );
} );

/** Read the native logo directly so Customizer changes are immediately reflected. */
add_filter( 'acf/load_value/key=field_nada_site_logo', function ( $value, $post_id ) {
	return 'options' === $post_id ? (int) get_theme_mod( 'custom_logo', 0 ) : $value;
}, 10, 2 );

/** Theme Options writes to the same native setting used by the Customizer. */
add_filter( 'acf/update_value/key=field_nada_site_logo', function ( $value, $post_id ) {
	if ( 'options' === $post_id && current_user_can( 'edit_theme_options' ) ) {
		$logo_id = absint( $value );
		if ( ! $logo_id || wp_attachment_is_image( $logo_id ) ) {
			set_theme_mod( 'custom_logo', $logo_id );
		}
	}
	return $value;
}, 10, 2 );

/** Fill missed starter translations and remove unused labels without replacing edits. */
add_action( 'admin_init', function () {
    if ( ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'update_field' ) || get_option( 'nada_options_cleanup_version' ) ) { return; }
    $rows = get_field( 'ui_labels', 'option' );
    if ( ! is_array( $rows ) ) { return; }
    // Preserve a recoverable copy of the three retired navigation labels.
    add_option( 'nada_ui_labels_before_cleanup', $rows, '', false );
    $rows = array_values( array_filter( $rows, static function ( $row ) {
        return ! in_array( $row['key'] ?? '', array( 'Why Greek', 'Recipes', 'Products' ), true );
    } ) );
    update_field( 'field_nada_ui_labels', $rows, 'option' );
    foreach ( nada_data( 'repeaters' ) as $name => $definition ) {
        if ( 'recipes' !== $definition['page'] ) { continue; }
        $rows = get_field( $name . '_ar', 'option' );
        if ( ! is_array( $rows ) ) { continue; }
        foreach ( $rows as &$row ) {
            foreach ( $definition['fields'] as $key => $field ) {
                $value = $row[ $key ] ?? '';
                if ( is_string( $value ) && isset( nada_data( 'translations' )[ $value ] ) ) {
                    $row[ $key ] = nada_translate( $value, 'ar' );
                }
            }
        }
        unset( $row );
        update_field( 'field_nada_' . $name . '_ar', $rows, 'option' );
    }
    update_option( 'nada_options_cleanup_version', 1, false );
}, 40 );

/** Keep original field definitions for imports, but edit translated copy in Polylang. */
add_filter( 'acf/prepare_field', function ( $field ) {
	$key = $field['key'] ?? '';
	if ( ! str_starts_with( $key, 'field_nada_' ) || ! get_option( 'nada_string_catalog_version' ) ) { return $field; }
	$name = $field['name'] ?? '';
	if ( 'ui_labels' === $name ) { return false; }
	$base = preg_replace( '/_(en|ar)$/', '', $name );
	if ( isset( get_option( 'nada_string_catalog', array() )[ 'option:' . $base ] ) ) { return false; }
	if ( 'repeater' === $field['type'] && str_starts_with( $name, 'recipes_' ) ) { return false; }
	return $field;
} );

/** Provide image controls independently of the translated, fixed archive cards. */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) { return; }
	$fields = array();
	foreach ( nada_data( 'repeaters' ) as $name => $definition ) {
		if ( 'recipes' !== $definition['page'] ) { continue; }
		foreach ( array( 'en', 'ar' ) as $language ) {
			$count = (int) get_option( 'options_' . $name . '_' . $language, 0 );
			for ( $index = 0; $index < $count; $index++ ) {
				$field_name = $name . '_' . $language . '_' . $index . '_image';
				$fields[] = array( 'key' => 'field_nada_archive_image_' . $language . '_' . $index, 'name' => $field_name, 'label' => 'Kitchen swap ' . ( $index + 1 ) . ' image — ' . strtoupper( $language ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail' );
			}
		}
	}
	if ( $fields ) {
		acf_add_local_field_group( array( 'key' => 'group_nada_archive_images', 'title' => 'Recipe page — kitchen swap images', 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'nada-options' ) ) ) ) );
	}
}, 20 );

/** Dark preview backing makes the original white claim icons readable in ACF. */
add_action( 'admin_enqueue_scripts', function () {
	wp_register_style( 'nada-admin', get_theme_file_uri( '/assets/css/admin.css' ), array(), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'nada-admin' );
} );
