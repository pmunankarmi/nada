<?php
/**
 * WordPress fallback for posts, archives and search. NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="nada-content">
	<?php if ( is_search() ) : ?><h1><?php echo esc_html( nada_text( 'Search' ) . ': ' . get_search_query() ); ?></h1><?php endif; ?>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php the_post_thumbnail( 'nada-card' ); ?>
			<?php the_excerpt(); ?>
		</article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php echo esc_html( nada_text( 'No results found.' ) ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
