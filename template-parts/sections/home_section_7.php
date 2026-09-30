<?php
/**
 * Home Section 7 section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="community">
 <h3 class="community__ugch reveal">
  <?php echo esc_html( nada_copy( 'home_section_7_loved_by_you_made_with_nada_greek' ) ); ?>
 </h3>
 <p class="community__sub reveal">
  <?php echo esc_html( nada_copy( 'home_section_7_your_nada_greek_moments' ) ); ?>
 </p>
 <div class="community__grid">
  <?php foreach ( nada_rows( 'home_section_7_gallery' ) as $row ) : ?>
  <figure class="upost reveal">
   <?php echo wp_get_attachment_image( absint( $row['image'] ?? 0 ), 'full', false, array( 'loading' => 'lazy' ) ); ?>
  </figure>
  <?php endforeach; ?>
 </div>
 <p class="community__ugccta reveal">
  <?php echo esc_html( nada_copy( 'home_section_7_share_yours_using' ) ); ?>
 </p>
</section>
