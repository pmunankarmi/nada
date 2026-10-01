<?php
/**
 * Server-rendered taxonomy filtering and pagination. Source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
$type = $args['type'] ?? 'recipe';
$taxonomy = 'recipe' === $type ? 'recipe_category' : 'product_fat';
$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'orderby' => 'term_id' ) );
$is_recipe = 'recipe' === $type;
$filter_class = $is_recipe ? 'chip' : 'skufilter';
?>
<section class="<?php echo $is_recipe ? 'rbook' : 'skus'; ?>" data-catalog>
    <nav class="<?php echo $is_recipe ? 'filters' : 'skufilters'; ?>" aria-label="<?php echo esc_attr( nada_text( 'Filter by category' ) ); ?>">
        <a class="<?php echo esc_attr( $filter_class ); ?> <?php echo is_post_type_archive() ? 'is-active' : ''; ?>" data-filter="all" href="<?php echo esc_url( get_post_type_archive_link( $type ) ); ?>"><?php echo esc_html( nada_text( $is_recipe ? 'All' : 'All Flavors' ) ); ?></a>
        <?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : ?>
            <a class="<?php echo esc_attr( $filter_class ); ?> <?php echo is_tax( $taxonomy, $term->term_id ) ? 'is-active' : ''; ?>" data-filter="<?php echo esc_attr( $term->term_id ); ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
        <?php endforeach; endif; ?>
    </nav>
    <?php if ( $is_recipe && ! is_post_type_archive() ) : get_template_part( 'template-parts/recipe-search' ); endif; ?>
    <div class="<?php echo $is_recipe ? 'recipe-grid' : 'skus__grid'; ?>" data-catalog-grid data-filter-inline="<?php echo is_post_type_archive() ? 'true' : 'false'; ?>">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php get_template_part( 'template-parts/cards/' . $type ); ?>
        <?php endwhile; endif; ?>
    </div>
    <p data-catalog-empty hidden><?php echo esc_html( nada_text( 'No results found.' ) ); ?></p>
</section>
