<?php
/**
 * Verify the NADAfinal3.3.8 shared translation migration on a disposable site.
 *
 * @package NADA
 */

if ( ! defined( "WP_CLI" ) || ! WP_CLI || ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( "localhost", "127.0.0.1" ), true ) ) { exit; }
$language = PLL()->model->get_language('ar');
$catalog = new PLL_MO();
$catalog->import_from_db($language);
$before = clone $catalog;
try {
    $source = get_option('nada_string_catalog')['option:footer_text']['source'];
    $catalog->add_entry(new Translation_Entry(array('singular'=>$source,'translations'=>array('اختبار الترجمة'))));
    $catalog->export_to_db($language);
    if (nada_option('footer_text_ar') !== 'اختبار الترجمة') { throw new Exception('Frontend did not use the Polylang edit.'); }
    $group = acf_get_local_field_group('group_nada_options');
    $hidden = 0;
    foreach (acf_get_fields($group) as $field) {
        if (false === apply_filters('acf/prepare_field', $field)) { $hidden++; }
    }
    if ($hidden < 30) { throw new Exception('Duplicate text editors remain visible.'); }
    echo "PASS: Polylang edits reach frontend; $hidden duplicate fields hidden.\n";
} finally {
    $before->export_to_db($language);
}
