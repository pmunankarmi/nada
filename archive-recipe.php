<?php
/**
 * Recipe archive. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header(); ?>
<main id="main-content">
<?php get_template_part( 'template-parts/sections/recipes_rbooktop' ); ?>
<?php get_template_part( 'template-parts/listing', null, array( 'type' => 'recipe' ) ); ?>
<?php get_template_part( 'template-parts/sections/recipes_swaps' ); ?>
<?php get_template_part( 'template-parts/sections/recipes_section_4' ); ?>
</main>
<?php get_footer(); ?>