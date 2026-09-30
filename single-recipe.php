<?php
/**
 * Full recipe with structured, text-only ACF repeaters. NADAfinal3.3.8.
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
			<?php the_post_thumbnail( 'large', array( 'class' => 'nada-detail-image' ) ); ?>
			<dl class="nada-recipe-times">
				<?php foreach ( array( 'prep' => 'Preparation time', 'cook' => 'Cooking time', 'total' => 'Total time', 'serves' => 'Servings' ) as $key => $label ) : $value = nada_field( 'recipe_' . $key ); ?>
					<?php if ( $value ) : ?><div><dt><?php echo esc_html( nada_text( $label ) ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endif; ?>
				<?php endforeach; ?>
			</dl>
			<?php the_content(); ?>
			<div class="nada-recipe-columns">
				<section><h2><?php echo esc_html( nada_text( 'Ingredients' ) ); ?></h2>
					<ul><?php foreach ( (array) nada_field( 'recipe_ingredients', array() ) as $row ) : ?><li><?php echo esc_html( $row['text'] ); ?></li><?php endforeach; ?></ul>
				</section>
				<section><h2><?php echo esc_html( nada_text( 'How to prepare' ) ); ?></h2>
					<ol><?php foreach ( (array) nada_field( 'recipe_steps', array() ) as $row ) : ?><li><?php echo esc_html( $row['text'] ); ?></li><?php endforeach; ?></ol>
				</section>
			</div>
			<?php the_terms( get_the_ID(), 'recipe_category' ); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
