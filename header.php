<?php
/**
 * Shared site header and native WordPress menus. Source: NADAfinal3.3.8.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main-content"><?php echo esc_html( nada_text( 'Skip to content' ) ); ?></a>
<?php get_template_part( 'template-parts/sprites' ); ?>
<header class="nav" id="nav">
	<div class="nav__inner">
		<div class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo str_replace( 'class="custom-logo"', 'class="custom-logo brand__logo"', get_custom_logo() ); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>
		<nav class="nav__links" aria-label="<?php echo esc_attr( nada_text( 'Primary' ) ); ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'nada-menu', 'fallback_cb' => 'nada_default_menu' ) ); ?>
		</nav>
		<div class="nav__cta">
			<?php get_template_part( 'template-parts/language-switcher' ); ?>
		</div>
		<button class="nav__burger" id="burger" aria-label="<?php echo esc_attr( nada_text( 'Open menu' ) ); ?>" aria-controls="drawer" aria-expanded="false"><span></span><span></span><span></span></button>
	</div>
</header>
<div class="drawer" id="drawer" aria-hidden="true" inert>
	<nav class="drawer__nav" aria-label="<?php echo esc_attr( nada_text( 'Mobile' ) ); ?>">
		<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'nada-menu', 'fallback_cb' => 'nada_default_menu' ) ); ?>
		<a href="<?php echo esc_url( nada_link_url( nada_option( 'cta_url', nada_url( 'share-recipe.html' ) ) ) ); ?>"<?php echo nada_link_target( nada_option( 'cta_url' ) ); ?>><?php echo esc_html( nada_option( 'cta_label_' . nada_language(), nada_text( 'Share Your Recipe' ) ) ); ?></a>
		<?php get_template_part( 'template-parts/language-switcher' ); ?>
	</nav>
</div>
