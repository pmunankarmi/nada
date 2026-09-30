<?php
/**
 * Home Hero section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="hero5 hero5--food" id="hero" tabindex="-1">
 <div class="hero5__track">
  <div class="hero5__slide hero5__slide--products">
   <span aria-hidden="true" class="hbanner__wm">
    <?php echo esc_html( nada_copy( 'home_hero_nada' ) ); ?>
   </span>
   <div class="hbanner__inner">
    <div class="hbanner__stage">
     <span class="hbanner__glow">
     </span>
     <?php if ( has_post_thumbnail() ) : ?>
<?php the_post_thumbnail( 'full', array( 'class' => 'herobowl', 'fetchpriority' => 'high' ) ); ?>
<?php endif; ?>
    </div>
    <div class="hbanner__copy">
     <p class="eyebrow reveal">
      <?php echo esc_html( nada_copy( 'home_hero_more_protein_healthy_lifestyle' ) ); ?>
     </p>
     <h1 class="hbanner__title">
      <?php echo esc_html( nada_copy( 'home_hero_greek_wins_everyday' ) ); ?>
     </h1>
     <div class="hbanner__cta">
      <a class="btn btn--solid btn--lg" href="<?php echo esc_url( nada_link_url( nada_copy( 'home_hero_link_1' ) ) ); ?>"<?php echo nada_link_target( nada_copy( 'home_hero_link_1' ) ); ?>>
       <?php echo esc_html( nada_link_label( nada_copy( 'home_hero_link_1' ), nada_copy( 'home_hero_see_the_wins_yourself' ) ) ); ?>
      </a>
     </div>
     <p class="herosub">
      <?php echo esc_html( nada_copy( 'home_hero_one_cup_many_possibilities' ) ); ?>
     </p>
    </div>
   </div>
  </div>
 </div>
</section>
