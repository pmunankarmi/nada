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
  <a class="rtile reveal" href="<?php echo esc_url( $row['url'] ?? '' ); ?>">
   <div class="rtile__img">
    <?php echo wp_get_attachment_image( absint( $row['image'] ?? 0 ), 'full', false, array( 'loading' => 'lazy' ) ); ?>
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
