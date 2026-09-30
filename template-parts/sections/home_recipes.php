<?php
/**
 * Home Recipes section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="oband oband--top oband--card" id="recipes" tabindex="-1">
 <div class="oband__bg oband__bg--wide" style="background-image:url(<?php echo esc_url( nada_asset( 'assets/img/bands/band-recipes-v3.jpg' ) ); ?>)">
 </div>
 <div class="oband__bg oband__bg--sq" style="background-image:url(<?php echo esc_url( nada_asset( 'assets/img/bands/band-recipes-sq-v3.jpg' ) ); ?>)">
 </div>
 <div class="oband__scrim">
 </div>
 <div class="oband__inner">
  <div class="oband__copy">
   <h2 class="oband__title reveal">
    <?php echo esc_html( nada_copy( 'home_recipes_endless_possibilities' ) ); ?>
   </h2>
   <p class="oband__lede reveal">
    <?php echo esc_html( nada_copy( 'home_recipes_recipes_that_take_you_places_it_all_starts' ) ); ?>
   </p>
   <div class="hbanner__cta reveal">
    <a class="btn btn--solid btn--lg" href="<?php echo esc_url( nada_copy( 'home_recipes_link_1' ) ); ?>">
     <?php echo esc_html( nada_copy( 'home_recipes_winning_recipes' ) ); ?>
    </a>
   </div>
  </div>
 </div>
</section>
