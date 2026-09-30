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
			<span class="foot__brandlogo"><?php if ( has_custom_logo() ) { the_custom_logo(); } else { ?><img src="<?php echo esc_url( nada_asset( 'assets/img/nada-logo.png' ) ); ?>" alt="NADA"><?php } ?></span>
			<p class="foot__slogan"><?php echo esc_html( nada_option( 'footer_text_' . nada_language(), nada_translate( 'Taste & Health, Everyday' ) ) ); ?></p>
		</div>
		<nav class="foot__cols" aria-label="<?php echo esc_attr( nada_text( 'Footer' ) ); ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'nada-menu', 'fallback_cb' => 'nada_default_menu' ) ); ?>
		</nav>
		<div class="foot__social">
			<?php foreach ( (array) nada_option( 'social_links', array() ) as $social ) : ?>
				<a href="<?php echo esc_url( $social['social_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $social['social_label'] ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
