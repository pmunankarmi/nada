<?php
/**
 * Missing-page recovery for the NADAfinal3.3.8 theme.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="nada-content">
	<h1><?php echo esc_html( nada_text( 'Page not found' ) ); ?></h1>
	<?php get_search_form(); ?>
</main>
<?php get_footer(); ?>
