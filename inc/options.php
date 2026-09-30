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
		array( 'key' => 'field_nada_logo_help', 'label' => 'Site logo', 'type' => 'message', 'message' => '<a href="' . esc_url( admin_url( 'themes.php?page=nada-logo' ) ) . '">Choose or remove the site logo</a>. This is the same native logo used by Appearance → Customize → Site Identity. No separate image field is stored.' ),
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

add_action( 'admin_menu', function () {
	add_theme_page( 'NADA Site Logo', 'Site Logo', 'edit_theme_options', 'nada-logo', 'nada_logo_screen' );
} );

function nada_logo_screen() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	if ( isset( $_POST['nada_logo_nonce'] ) ) {
		check_admin_referer( 'nada_logo', 'nada_logo_nonce' );
		$id = absint( $_POST['logo_id'] ?? 0 );
		if ( ! $id || wp_attachment_is_image( $id ) ) {
			set_theme_mod( 'custom_logo', $id );
		}
	}
	wp_enqueue_media();
	wp_enqueue_script( 'nada-admin-logo', get_theme_file_uri( '/assets/js/admin-logo.js' ), array( 'jquery' ), '1.0.0', true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'NADA Site Logo', 'nada' ); ?></h1>
		<p><?php esc_html_e( 'Changes here and in the Customizer use the same WordPress custom_logo setting.', 'nada' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'nada_logo', 'nada_logo_nonce' ); ?>
			<div id="nada-logo-preview"><?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'medium' ); ?></div>
			<input type="hidden" id="nada-logo-id" name="logo_id" value="<?php echo esc_attr( get_theme_mod( 'custom_logo' ) ); ?>">
			<button class="button" id="nada-select-logo" type="button">Choose logo</button>
			<button class="button" id="nada-remove-logo" type="button">Remove logo</button>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
