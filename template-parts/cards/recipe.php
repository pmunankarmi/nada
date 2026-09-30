<?php
/**
 * Recipe card with native permalink and featured image. NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'nada-recipe-card' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<?php the_post_thumbnail( 'nada-card', array( 'loading' => 'lazy' ) ); ?>
		<div class="nada-card-copy">
			<h2><?php the_title(); ?></h2>
			<?php the_excerpt(); ?>
			<span><?php echo esc_html( nada_text( 'Read recipe' ) ); ?> →</span>
		</div>
	</a>
</article>
