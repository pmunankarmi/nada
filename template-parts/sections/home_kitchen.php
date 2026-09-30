<?php
/**
 * Home Kitchen section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="kitchen" id="kitchen" tabindex="-1">
 <div aria-hidden="true" class="kitchen__pattern">
 </div>
 <div class="kitchen__inner">
  <p class="eyebrow reveal">
   <?php echo esc_html( nada_copy( 'home_kitchen_nada_greek_wins_in_the_kitchen' ) ); ?>
  </p>
  <h2 class="section-title reveal">
   <?php echo esc_html( nada_copy( 'home_kitchen_your_secret_ingredient_wins' ) ); ?>
  </h2>
  <div class="swapgrid">
   <?php foreach ( nada_rows( 'home_kitchen_swapgrid' ) as $row_index => $row ) : ?>
   <article class="swaptile reveal">
    <div class="swaptile__img">
     <?php echo wp_get_attachment_image( absint( $row['image'] ?? 0 ), 'full', false, array( 'loading' => 'lazy' ) ); ?>
    </div>
    <div class="swaptile__txt">
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
 </div>
</section>
