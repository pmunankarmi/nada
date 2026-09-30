<?php
/**
 * Home Reasons section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="wgband" id="reasons" tabindex="-1">
 <div aria-hidden="true" class="wgband__pattern">
 </div>
 <div aria-hidden="true" class="wgband__glow">
 </div>
 <div class="wgband__inner">
  <div class="wgopen wgopen--centred">
   <div class="wgopen__copy">
    <p class="eyebrow reveal">
     <?php echo esc_html( nada_copy( 'home_reasons_why_greek_yogurt_wins' ) ); ?>
    </p>
    <h2 class="wgband__title reveal">
     <?php echo esc_html( nada_copy( 'home_reasons_you_ve_tried_yogurt' ) ); ?>
     <br/>
     <?php echo esc_html( nada_copy( 'home_reasons_you_haven_t_tried_this' ) ); ?>
    </h2>
    <p class="wgband__body reveal">
     <?php echo esc_html( nada_copy( 'home_reasons_greek_yogurt_isn_t_just_a_thicker_version_' ) ); ?>
    </p>
   </div>
  </div>
  <div class="wgproof">
   <div class="wgproof__art reveal">
    <?php echo wp_get_attachment_image( absint( nada_field( 'home_reasons_image' ) ), 'full', false, array( 'loading' => 'lazy' ) ); ?>
   </div>
   <ul class="wgclaims">
    <?php foreach ( nada_rows( 'home_reasons_wgclaims' ) as $row_index => $row ) : ?>
    <li class="wgclaim reveal">
     <?php echo wp_get_attachment_image( absint( $row['image'] ?? 0 ), 'full', false, array( 'loading' => 'lazy', 'class' => 'wgclaim__badge', 'alt' => '' ) ); ?>
     <p class="wgclaim__t">
      <?php echo esc_html( $row['text_1'] ?? '' ); ?>
     </p>
    </li>
    <?php endforeach; ?>
   </ul>
  </div>
 </div>
</section>
