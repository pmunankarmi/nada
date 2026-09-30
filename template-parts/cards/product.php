<?php
/**
 * Product card with native featured image. Source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'sku' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<div class="sku__img"><?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?></div>
		<h2 class="sku__name"><?php the_title(); ?></h2>
		<p class="sku__sub"><?php echo esc_html( nada_field( 'product_summary' ) ); ?></p>
	</a>
</article>
