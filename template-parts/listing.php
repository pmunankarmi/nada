<?php
/**
 * Server-rendered taxonomy filtering and pagination. Source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
$type = $args['type'] ?? 'recipe';
$taxonomy = 'recipe' === $type ? 'recipe_category' : 'product_fat';
$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
?>
<section class="<?php echo 'recipe' === $type ? 'rbook' : 'skus'; ?>">
	<nav class="filters" aria-label="<?php echo esc_attr( nada_text( 'Filter by category' ) ); ?>">
		<a class="chip <?php echo is_post_type_archive() ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( $type ) ); ?>"><?php echo esc_html( nada_text( 'All' ) ); ?></a>
		<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : ?>
			<a class="chip <?php echo is_tax( $taxonomy, $term->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
		<?php endforeach; endif; ?>
	</nav>
	<form class="nada-search" role="search" method="get" action="<?php echo esc_url( nada_url( 'index.html' ) ); ?>">
		<label for="nada-search"><?php echo esc_html( nada_text( 'Search' ) ); ?></label>
		<input id="nada-search" name="s" type="search" value="<?php echo esc_attr( get_search_query() ); ?>">
		<input name="post_type" type="hidden" value="<?php echo esc_attr( $type ); ?>">
		<button class="btn btn--solid" type="submit"><?php echo esc_html( nada_text( 'Search' ) ); ?></button>
	</form>
	<div class="<?php echo 'recipe' === $type ? 'recipe-grid' : 'skus__grid'; ?>">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/cards/' . $type ); ?>
		<?php endwhile; else : ?>
			<p><?php echo esc_html( nada_text( 'No results found.' ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php the_posts_pagination(); ?>
</section>
