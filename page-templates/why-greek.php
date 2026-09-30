<?php
/**
 * Why-Greek page template. Design source: NADAfinal3.3.8.
 *
 * Template Name: Why Greek
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header(); ?>
<main id="main-content">
<?php while ( have_posts() ) : the_post(); ?>
<?php get_template_part( 'template-parts/sections/why_whytop' ); ?>
<?php get_template_part( 'template-parts/sections/why_section_2' ); ?>
<?php get_template_part( 'template-parts/sections/why_section_3' ); ?>
<?php get_template_part( 'template-parts/sections/why_section_4' ); ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>