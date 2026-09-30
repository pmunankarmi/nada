<?php
/**
 * Product archive. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header(); ?>
<main id="main-content">
<?php get_template_part( 'template-parts/sections/products_rangetop' ); ?>
<?php get_template_part( 'template-parts/listing', null, array( 'type' => 'product' ) ); ?>
<?php get_template_part( 'template-parts/sections/products_section_3' ); ?>
</main>
<?php get_footer(); ?>