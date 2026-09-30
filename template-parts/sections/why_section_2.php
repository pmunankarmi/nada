<?php
/**
 * Why Section 2 section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="vsec">
 <div class="vsec__inner">
  <h2 class="section-title reveal">
   <?php echo esc_html( nada_copy( 'why_section_2_more_than_a_thicker_yogurt' ) ); ?>
  </h2>
  <ol class="prow prow--light prow--4 vsec__row">
   <?php foreach ( nada_rows( 'why_section_2_vsec_row' ) as $row_index => $row ) : ?>
   <li class="whypoint whypoint--photo reveal">
    <span class="whypoint__shot">
     <img alt="" decoding="async" loading="lazy" src="<?php echo esc_url( nada_asset( array( 'assets/img/stats/stat-everyone.jpg', 'assets/img/stats/stat-versatile.jpg', 'assets/img/stats/stat-quality.jpg' )[ $row_index % 3 ] ) ); ?>"/>
    </span>
    <h3 class="whypoint__title">
     <?php echo esc_html( $row['text_1'] ?? '' ); ?>
    </h3>
    <p class="whypoint__body">
     <?php echo esc_html( $row['text_2'] ?? '' ); ?>
    </p>
   </li>
   <?php endforeach; ?>
  </ol>
 </div>
</section>
