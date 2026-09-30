<?php
/**
 * Home Section 5 section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="recipehub">
 <div class="rtiles__grid rtiles__grid--3">
  <?php foreach ( nada_rows( 'home_section_5_rtiles_grid' ) as $row_index => $row ) : ?>
  <a class="rtile reveal" href="<?php echo esc_url( nada_url( 'recipes.html' ) ); ?>">
   <div class="rtile__img">
    <img alt="" loading="lazy" src="<?php echo esc_url( nada_asset( array( 'assets/img/recipes/recipe-dips.jpg', 'assets/img/recipes/recipe-savoury.jpg', 'assets/img/recipes/recipe-desserts.jpg', 'assets/img/recipes/recipe-snacks.jpg', 'assets/img/recipes/recipe-breakfast.jpg', 'assets/img/recipes/recipe-smoothies.jpg' )[ $row_index % 6 ] ) ); ?>"/>
   </div>
   <div class="rtile__cap">
    <h3>
     <?php echo esc_html( $row['text_1'] ?? '' ); ?>
    </h3>
    <p>
     <?php echo esc_html( $row['text_2'] ?? '' ); ?>
    </p>
   </div>
  </a>
  <?php endforeach; ?>
 </div>
</section>
