<?php
/**
 * Product details and nutrition facts. Source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="nada-content">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'nada-detail' ); ?>>
			<h1><?php the_title(); ?></h1>
			<?php the_post_thumbnail( 'large', array( 'class' => 'nada-product-image' ) ); ?>
			<p><?php echo esc_html( nada_field( 'product_summary' ) ); ?></p>
			<?php the_content(); ?>
			<?php $facts = nada_field( 'product_nutrition', array() ); if ( $facts ) : ?>
				<table><caption><?php echo esc_html( nada_text( 'Nutrition facts' ) ); ?></caption><tbody>
					<?php foreach ( $facts as $fact ) : ?>
						<tr><th scope="row"><?php echo esc_html( $fact['nutrient'] ); ?></th><td><?php echo esc_html( $fact['amount'] ); ?></td></tr>
					<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>
			<?php the_terms( get_the_ID(), 'product_fat' ); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
