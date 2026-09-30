<?php
/**
 * ACF Pro field definitions: text content only, with markup in PHP templates.
 * Converted from the NADAfinal3.3.8 package.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_acf_text( $name, $label, $type = 'text' ) {
	return array( 'key' => 'field_nada_' . $name, 'name' => $name, 'label' => $label, 'type' => $type, 'new_lines' => '', 'instructions' => __( 'Plain text only. Layout and HTML are supplied by the theme.', 'nada' ) );
}

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) { return; }
	$locations = array(
		'home' => array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ),
		'why' => array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/why-greek.php' ),
		'share' => array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/share-recipe.php' ),
	);
	foreach ( $locations as $page => $location ) {
		$fields = array();
		foreach ( nada_data( 'page-fields' ) as $name => $definition ) {
			if ( $page === $definition['page'] ) {
				$fields[] = nada_acf_text( $name, $definition['label'], $definition['type'] );
			}
		}
		foreach ( nada_data( 'repeaters' ) as $name => $definition ) {
			if ( $page !== $definition['page'] ) {
				continue;
			}
			$sub_fields = array();
			foreach ( $definition['fields'] as $key => $field ) {
				$sub = nada_acf_text( $name . '_' . $key, $field['label'], $field['type'] );
				$sub['name'] = $key;
				$sub_fields[] = $sub;
			}
			$fields[] = array( 'key' => 'field_nada_' . $name, 'name' => $name, 'label' => $definition['label'], 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add item', 'sub_fields' => $sub_fields );
		}
		acf_add_local_field_group( array( 'key' => 'group_nada_' . $page, 'title' => 'NADA ' . ucfirst( $page ) . ' content', 'fields' => $fields, 'location' => array( array( $location ) ) ) );
	}

	$recipe_fields = array();
	foreach ( array( 'prep' => 'Preparation time', 'cook' => 'Cooking time', 'total' => 'Total time', 'serves' => 'Servings' ) as $key => $label ) {
		$recipe_fields[] = nada_acf_text( 'recipe_' . $key, $label );
	}
	foreach ( array( 'ingredients' => 'Ingredients', 'steps' => 'Method steps' ) as $name => $label ) {
		$sub = nada_acf_text( 'recipe_' . $name . '_text', 'Text', 'textarea' );
		$sub['name'] = 'text';
		$recipe_fields[] = array( 'key' => 'field_nada_recipe_' . $name, 'name' => 'recipe_' . $name, 'label' => $label, 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array( $sub ), 'button_label' => 'Add ' . ( 'steps' === $name ? 'step' : 'ingredient' ) );
	}
	acf_add_local_field_group( array( 'key' => 'group_nada_recipe', 'title' => 'Recipe details', 'fields' => $recipe_fields, 'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'recipe' ) ) ) ) );
	acf_add_local_field_group( array( 'key' => 'group_nada_product', 'title' => 'Product details', 'fields' => array( nada_acf_text( 'product_summary', 'Product summary', 'textarea' ), array( 'key' => 'field_nada_product_nutrition', 'name' => 'product_nutrition', 'label' => 'Nutrition facts', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array( nada_acf_text( 'nutrient', 'Nutrient' ), nada_acf_text( 'amount', 'Amount' ) ) ) ), 'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) ) ) );
} );

// Enforce text-only storage for all theme-owned text and textarea fields.
add_filter( 'acf/update_value', function ( $value, $post_id, $field ) {
	if ( str_starts_with( $field['key'] ?? '', 'field_nada_' ) && in_array( $field['type'], array( 'text', 'textarea' ), true ) ) {
		return 'textarea' === $field['type'] ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
	}
	return $value;
}, 10, 3 );
