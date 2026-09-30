<?php
/**
 * Homepage template. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header(); ?>
<main id="main-content">
<?php if ( have_posts() ) : the_post(); endif; ?>
<?php get_template_part( 'template-parts/sections/home_hero' ); ?>
<?php get_template_part( 'template-parts/sections/home_reasons' ); ?>
<?php get_template_part( 'template-parts/sections/home_kitchen' ); ?>
<?php get_template_part( 'template-parts/sections/home_recipes' ); ?>
<?php get_template_part( 'template-parts/sections/home_section_5' ); ?>
<?php get_template_part( 'template-parts/sections/home_community' ); ?>
<?php get_template_part( 'template-parts/sections/home_section_7' ); ?>
<?php get_template_part( 'template-parts/sections/home_section_8' ); ?>
</main>
<?php get_footer(); ?>