<?php
/**
 * Featured-image recipe card and accessible inline detail drawer. NADAfinal3.3.8.
 *
 * @package NADA
 */
defined( 'ABSPATH' ) || exit;
$terms = get_the_terms( get_the_ID(), 'recipe_category' );
$category = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
$term_ids = $terms && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'term_id' ) : array();
$dialog_id = 'recipe-dialog-' . get_the_ID();
?>
<article <?php post_class( 'rec in' ); ?> data-categories="<?php echo esc_attr( implode( ' ', $term_ids ) ); ?>">
    <div class="rec__img">
        <?php if ( $category ) : ?><span class="rec__cat"><?php echo esc_html( $category ); ?></span><?php endif; ?>
        <?php if ( nada_field( 'recipe_total' ) ) : ?><span class="rec__time"><?php echo esc_html( nada_field( 'recipe_total' ) ); ?></span><?php endif; ?>
        <?php the_post_thumbnail( 'nada-card', array( 'loading' => 'lazy' ) ); ?>
    </div>
    <div class="rec__body">
        <h3><?php the_title(); ?></h3>
        <?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
        <?php if ( nada_field( 'recipe_pairing' ) ) : ?><span class="rec__foot"><i class="dotc"></i><?php echo esc_html( nada_text( 'Pairs with' ) . ' ' . nada_field( 'recipe_pairing' ) ); ?></span><?php endif; ?>
    </div>
    <button class="rec__link" type="button" data-recipe-open="<?php echo esc_attr( $dialog_id ); ?>" aria-label="<?php the_title_attribute(); ?>" aria-haspopup="dialog"></button>
    <dialog class="modal nada-recipe-dialog" id="<?php echo esc_attr( $dialog_id ); ?>" aria-labelledby="<?php echo esc_attr( $dialog_id . '-title' ); ?>">
        <div class="modal__scrim" data-recipe-close></div>
        <div class="modal__panel">
            <button class="modal__x" type="button" data-recipe-close aria-label="<?php echo esc_attr( nada_text( 'Close recipe' ) ); ?>">×</button>
            <div class="rvid"><?php the_post_thumbnail( 'large', array( 'class' => 'rvid__media', 'loading' => 'lazy' ) ); ?><div class="rvid__tags"><span><?php echo esc_html( $category ); ?></span></div></div>
            <div class="modal__content">
                <h2 id="<?php echo esc_attr( $dialog_id . '-title' ); ?>"><?php the_title(); ?></h2>
                <?php if ( has_excerpt() ) : ?><p class="modal__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
                <div class="modal__meta">
                    <?php foreach ( array( 'prep' => 'Preparation time', 'cook' => 'Cooking time', 'total' => 'Total time', 'serves' => 'Servings' ) as $key => $label ) : $value = nada_field( 'recipe_' . $key ); ?>
                        <?php if ( $value ) : ?><div><b><?php echo esc_html( $value ); ?></b><span><?php echo esc_html( nada_text( $label ) ); ?></span></div><?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ( nada_field( 'recipe_pairing' ) ) : ?><div><b>◆</b><span><?php echo esc_html( nada_field( 'recipe_pairing' ) ); ?></span></div><?php endif; ?>
                </div>
                <div class="modal__split">
                    <section><h4><?php echo esc_html( nada_text( 'How to prepare' ) ); ?></h4><ol class="modal__steps">
                        <?php foreach ( (array) nada_field( 'recipe_steps', array() ) as $row ) : ?><li><?php echo esc_html( $row['text'] ); ?></li><?php endforeach; ?>
                    </ol></section>
                    <section><h4><?php echo esc_html( nada_text( 'Ingredients' ) ); ?></h4><ul class="modal__ing">
                        <?php foreach ( (array) nada_field( 'recipe_ingredients', array() ) as $row ) : ?><li><?php echo esc_html( $row['text'] ); ?></li><?php endforeach; ?>
                    </ul></section>
                </div>
            </div>
        </div>
    </dialog>
</article>
