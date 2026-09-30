<?php
/**
 * Taxonomy archive for the NADAfinal3.3.8 theme.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content">
    <section class="rbook-hero"><div class="rbook-hero__inner"><h1 class="rbook-hero__title"><?php single_term_title(); ?></h1></div></section>
    <?php get_template_part( 'template-parts/listing', null, array( 'type' => 'product' ) ); ?>
</main>
<?php get_footer(); ?>
