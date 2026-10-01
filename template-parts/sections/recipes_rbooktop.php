<?php
/**
 * Recipes Rbooktop section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="rbook-hero" id="rbookTop" tabindex="-1">
 <div aria-hidden="true" class="rbook-hero__wash">
 </div>
 <div class="rbook-hero__inner">
  <h1 class="rbook-hero__title reveal" style="--d:.06s">
   <?php echo esc_html( nada_copy( 'recipes_rbooktop_a_pot_of_nada_a_hundred_ways' ) ); ?>
  </h1>
  <p class="rbook-hero__lede reveal" style="--d:.16s">
   <?php echo esc_html( nada_copy( 'recipes_rbooktop_thick_greek_yogurt_is_the_most_useful_thin' ) ); ?>
  </p>
  <?php get_template_part( 'template-parts/recipe-search' ); ?>
 </div>
</section>
