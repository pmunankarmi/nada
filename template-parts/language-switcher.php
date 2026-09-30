<?php
/**
 * Real translated URLs, supplied by Polylang. NADAfinal3.3.8 conversion.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;
if ( ! function_exists( 'pll_the_languages' ) ) {
	return;
}
$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
?>
<div class="nada-languages">
	<?php foreach ( $languages as $language ) : ?>
		<a class="lang-toggle" href="<?php echo esc_url( $language['url'] ); ?>" lang="<?php echo esc_attr( $language['slug'] ); ?>" hreflang="<?php echo esc_attr( $language['slug'] ); ?>" <?php echo $language['current_lang'] ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $language['name'] ); ?></a>
	<?php endforeach; ?>
</div>
