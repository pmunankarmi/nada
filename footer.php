<?php
/**
 * Shared global footer for the NADAfinal3.3.8 WordPress theme.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="foot">
	<div class="foot__wave" aria-hidden="true"><svg viewBox="0 0 1440 70" preserveAspectRatio="none"><path d="M0,70 L0,34 C240,4 480,58 720,44 C960,30 1200,-4 1440,20 L1440,70 Z" fill="currentColor"/></svg></div>
	<div class="foot__top">
		<div class="foot__brand">
			<span class="foot__brandlogo"><?php $footer_logo = absint( nada_option( 'footer_logo', 0 ) ); if ( $footer_logo ) { echo wp_get_attachment_image( $footer_logo, 'full' ); } elseif ( has_custom_logo() ) { the_custom_logo(); } else { bloginfo( 'name' ); } ?></span>
			<p class="foot__slogan"><?php echo esc_html( nada_option( 'footer_text_' . nada_language(), '' ) ); ?></p>
		</div>
		<nav class="foot__cols" aria-label="<?php echo esc_attr( nada_text( 'Footer' ) ); ?>">
			<div class="foot__links">
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'nada-menu', 'fallback_cb' => 'nada_default_menu' ) ); ?>
			</div>
			<div class="foot__followcol">
				<div class="foot__social">
					<?php foreach ( (array) nada_option( 'social_links', array() ) as $social ) : ?>
						<a href="<?php echo esc_url( nada_link_url( $social['social_url'] ) ); ?>" <?php echo nada_link_target( $social['social_url'] ); ?> aria-label="<?php echo esc_attr( nada_link_label( $social['social_url'], $social['social_label'] ) ); ?>">
							<?php get_template_part( 'template-parts/social-icon', null, array( 'name' => $social['social_label'] ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</nav>
		<?php
		$contact_email = sanitize_email( nada_option( 'contact_email', '' ) );
		$contact_phone = nada_option( 'contact_phone', '' );
		$contact_address = nada_option( 'contact_address_' . nada_language(), '' );
		?>
		<?php if ( $contact_email || $contact_phone || $contact_address ) : ?>
			<address class="foot__contact">
				<?php if ( $contact_email ) : ?><p><a href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a></p><?php endif; ?>
				<?php if ( $contact_phone ) : ?><p><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^+0-9]/', '', $contact_phone ) ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p><?php endif; ?>
				<?php if ( $contact_address ) : ?><p><?php echo nl2br( esc_html( $contact_address ) ); ?></p><?php endif; ?>
			</address>
		<?php endif; ?>

	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
