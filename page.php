<?php
/**
 * Default classic page and post template. Source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="nada-content">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<h1><?php the_title(); ?></h1>
			<?php the_post_thumbnail( 'large' ); ?>
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
