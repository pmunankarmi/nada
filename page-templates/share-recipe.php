<?php
/**
 * Share-Recipe page template. Design source: NADAfinal3.3.8.
 *
 * Template Name: Share Recipe
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header(); ?>
<main id="main-content">
<?php while ( have_posts() ) : the_post(); ?>
<?php get_template_part( 'template-parts/sections/share_sharetop' ); ?>
<?php get_template_part( 'template-parts/share-form' ); ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>