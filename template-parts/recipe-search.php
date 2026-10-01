<?php
/**
 * Live recipe search controls, NADAfinal3.3.8.
 *
 * @package NADA
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="rbook-tools">
    <div class="rbook-search">
        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m15 15 6 6" stroke="currentColor" stroke-width="1.8"/></svg>
        <input type="search" data-recipe-search aria-label="<?php echo esc_attr( nada_text( 'Search' ) ); ?>" placeholder="<?php echo esc_attr( nada_text( 'Search recipes, ingredients…' ) ); ?>">
    </div>
    <span class="rbook-count" data-recipe-count data-label="<?php echo esc_attr( nada_text( 'recipes' ) ); ?>" aria-live="polite"></span>
</div>
