<?php
/**
 * Recipes Swaps section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="kitchen" id="swaps" tabindex="-1">
 <div aria-hidden="true" class="kitchen__pattern">
 </div>
 <div class="kitchen__inner">
  <p class="eyebrow reveal">
   <?php echo esc_html( nada_copy( 'recipes_swaps_nada_greek_wins_in_the_kitchen' ) ); ?>
  </p>
  <h2 class="section-title reveal">
   <?php echo esc_html( nada_copy( 'recipes_swaps_your_secret_ingredient_wins' ) ); ?>
  </h2>
  <p class="kitchen__body reveal">
   <?php echo esc_html( nada_copy( 'recipes_swaps_once_you_start_cooking_with_nada_greek_yog' ) ); ?>
   <br/>
   <?php echo esc_html( nada_copy( 'recipes_swaps_go_greek_and_craft_recipes_regular_yogurt_' ) ); ?>
  </p>
  <div class="swapgrid">
   <?php foreach ( nada_rows( 'recipes_swaps_swapgrid' ) as $row_index => $row ) : ?>
   <article class="swaptile reveal">
    <div class="swaptile__img">
     <img alt="" decoding="async" loading="lazy" src="<?php echo esc_url( nada_asset( array( 'assets/img/swaps/swap-butter.jpg', 'assets/img/swaps/swap-cheese.jpg', 'assets/img/swaps/swap-sour.jpg', 'assets/img/swaps/swap-mayo.jpg', 'assets/img/swaps/swap-cream.jpg' )[ $row_index % 5 ] ) ); ?>"/>
    </div>
    <div class="swaptile__txt">
     <p aria-hidden="true" class="swaptile__mark">
      <svg class="swaptile__ic" focusable="false" viewbox="0 0 24 24">
       <use href="#ic-butter">
       </use>
      </svg>
      <svg class="swaptile__swap" viewbox="0 0 24 24">
       <use href="#ic-swap">
       </use>
      </svg>
     </p>
     <h3 class="swaptile__title">
      <?php echo esc_html( $row['text_1'] ?? '' ); ?>
     </h3>
     <p class="swaptile__body">
      <?php echo esc_html( $row['text_2'] ?? '' ); ?>
     </p>
    </div>
   </article>
   <?php endforeach; ?>
  </div>
  <p class="kitchen__close reveal">
   <?php echo esc_html( nada_copy( 'recipes_swaps_one_cup_recipes_that_take_you_places_it_al' ) ); ?>
  </p>
 </div>
</section>
