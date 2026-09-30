<?php
/**
 * Products Section 3 section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="oband oband--top oband--card">
 <div class="oband__bg oband__bg--wide" style="background-image:url(<?php echo esc_url( nada_section_image( 'products_section_3_background_desktop' ) ); ?>)">
 </div>
 <div class="oband__bg oband__bg--sq" style="background-image:url(<?php echo esc_url( nada_section_image( 'products_section_3_background_mobile' ) ); ?>)">
 </div>
 <div class="oband__scrim">
 </div>
 <div class="oband__inner">
  <div class="oband__copy">
   <h2 class="oband__title reveal">
    <?php echo esc_html( nada_copy( 'products_section_3_one_cup' ) ); ?>
    <br/>
    <?php echo esc_html( nada_copy( 'products_section_3_a_hundred_ways' ) ); ?>
   </h2>
   <div class="hbanner__cta reveal">
    <a class="btn btn--solid btn--lg" href="<?php echo esc_url( nada_link_url( nada_copy( 'products_section_3_link_1' ) ) ); ?>"<?php echo nada_link_target( nada_copy( 'products_section_3_link_1' ) ); ?>>
     <?php echo esc_html( nada_link_label( nada_copy( 'products_section_3_link_1' ), nada_copy( 'products_section_3_all_recipes' ) ) ); ?>
    </a>
   </div>
  </div>
 </div>
</section>
