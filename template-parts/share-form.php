<?php
/**
 * Moderated recipe submission form for NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
$status = sanitize_key( wp_unslash( $_GET['recipe-status'] ?? '' ) );
?>
<section class="sharewrap">
	<?php if ( 'success' === $status ) : ?>
		<p class="nada-message" role="status"><?php echo esc_html( nada_text( 'Thanks for sharing!' ) . ' ' . nada_text( 'Your recipe is awaiting review.' ) ); ?></p>
	<?php elseif ( 'error' === $status ) : ?>
		<p class="nada-message" role="alert"><?php echo esc_html( nada_text( 'Something went wrong. Please try again.' ) ); ?></p>
	<?php endif; ?>
	<form class="shareform" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'nada_submit_recipe', 'nada_nonce' ); ?>
		<input type="hidden" name="action" value="nada_submit_recipe">
		<input type="hidden" name="language" value="<?php echo esc_attr( nada_language() ); ?>">
		<input type="hidden" name="return_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
		<div class="nada-honeypot" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
		<div class="sfield"><label for="recipe-name"><?php echo esc_html( nada_text( 'Recipe name' ) ); ?></label><input type="text" id="recipe-name" name="recipe" required maxlength="120"></div>
		<div class="sfield"><label for="recipe-category"><?php echo esc_html( nada_text( 'Category' ) ); ?></label>
			<select id="recipe-category" name="category" required>
				<?php $terms = get_terms( array( 'taxonomy' => 'recipe_category', 'hide_empty' => false ) ); ?>
				<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : ?><option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option><?php endforeach; endif; ?>
			</select>
		</div>
		<div class="sfield"><label for="recipe-ingredients"><?php echo esc_html( nada_text( 'Ingredients' ) ); ?></label><textarea id="recipe-ingredients" name="ingredients" rows="6" maxlength="10000" required></textarea><small><?php echo esc_html( nada_text( 'One ingredient per line.' ) ); ?></small></div>
		<div class="sfield"><label for="recipe-method"><?php echo esc_html( nada_text( 'How to prepare' ) ); ?></label><textarea id="recipe-method" name="method" rows="6" maxlength="15000" required></textarea></div>
		<div class="sfield"><label for="recipe-email"><?php echo esc_html( nada_text( 'Your email' ) ); ?></label><input id="recipe-email" type="email" name="email" required autocomplete="email" maxlength="254"></div>
		<button class="btn btn--solid btn--lg" type="submit"><?php echo esc_html( nada_text( 'Send My Recipe →' ) ); ?></button>
	</form>
</section>
