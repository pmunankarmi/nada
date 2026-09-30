<?php
/**
 * Home Community section. Design source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="oband oband--left" id="community" tabindex="-1">
 <div class="oband__bg oband__bg--wide" style="background-image:url(<?php echo esc_url( nada_asset( 'assets/img/bands/band-community.jpg' ) ); ?>)">
 </div>
 <div class="oband__bg oband__bg--sq" style="background-image:url(<?php echo esc_url( nada_asset( 'assets/img/bands/band-community-sq.jpg' ) ); ?>)">
 </div>
 <div class="oband__scrim">
 </div>
 <div class="oband__inner">
  <div class="oband__copy">
   <h2 class="oband__title oband__title--bullets reveal">
    <span>
     <?php echo esc_html( nada_copy( 'home_community_real_people' ) ); ?>
    </span>
    <span>
     <?php echo esc_html( nada_copy( 'home_community_real_recipes' ) ); ?>
    </span>
    <span>
     <?php echo esc_html( nada_copy( 'home_community_real_good' ) ); ?>
    </span>
   </h2>
   <div class="hbanner__cta reveal">
    <a class="btn btn--solid btn--lg" href="<?php echo esc_url( nada_url( 'share-recipe.html' ) ); ?>">
     <?php echo esc_html( nada_copy( 'home_community_share_your_recipe' ) ); ?>
    </a>
   </div>
  </div>
 </div>
</section>
