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
<?php else : ?>
<img alt="Overhead view of a plate of labneh with olive oil and za’atar, avocado toast, cherry tomatoes, cucumber and radish, beside an open pot of NADA Plain Greek Yogurt with a spoon in it" class="herobowl" decoding="async" fetchpriority="high" loading="eager" src="<?php echo esc_url( nada_asset( 'assets/img/cutouts/hero-plate-pack-en-v2.webp' ) ); ?>"/>
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
      <a class="btn btn--solid btn--lg" href="#kitchen">
       <?php echo esc_html( nada_copy( 'home_hero_see_the_wins_yourself' ) ); ?>
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
