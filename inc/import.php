<?php
/**
 * Explicit, repeatable starter-content import from the NADAfinal3.3.8 package.
 * Existing imported content is preserved; media uses native featured images.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

function nada_seed_field( $name, $value, $id ) {
	if ( function_exists( 'update_field' ) ) {
		update_field( 'field_nada_' . $name, $value, $id );
	} else {
		update_post_meta( $id, $name, $value );
	}
}

function nada_import_image( $path, $title ) {
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_nada_source_image', 'meta_value' => $path, 'fields' => 'ids', 'numberposts' => 1, 'suppress_filters' => true ) );
	if ( $existing ) {
		return $existing[0];
	}
	$source = nada_media_directory() . $path;
	if ( ! is_readable( $source ) || ! str_starts_with( $path, 'assets/img/' ) || str_contains( $path, '..' ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$temp = wp_tempnam( basename( $path ) );
	copy( $source, $temp );
	$id = media_handle_sideload( array( 'name' => basename( $path ), 'tmp_name' => $temp ), 0, $title );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $temp );
		return 0;
	}
	update_post_meta( $id, '_nada_source_image', $path );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return $id;
}

function nada_seed_post( $type, $source_id, $language, $title, $template = '' ) {
	$key = $type . ':' . $source_id . ':' . $language;
	$existing = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'meta_key' => '_nada_source_id', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1, 'suppress_filters' => true ) );
	if ( $existing ) {
		return array( $existing[0], false );
	}
	$id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $source_id, 'meta_input' => array( '_nada_source_id' => $key ) ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	pll_set_post_language( $id, $language );
	// Run uniqueness again now that the post has a language (shared-slug helper).
	wp_update_post( array( 'ID' => $id, 'post_name' => $source_id ) );
	if ( $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	return array( $id, true );
}

function nada_import_content() {
	if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'acf_add_options_page' ) ) {
		throw new RuntimeException( 'Activate Polylang and ACF Pro before importing.' );
	}
	if ( array_diff( array( 'en', 'ar' ), pll_languages_list() ) ) {
		throw new RuntimeException( 'Add English (en) and Arabic (ar) in Languages before importing.' );
	}
	$lock = get_option( 'nada_import_lock' );
	if ( $lock && time() - (int) $lock < 900 ) {
		throw new RuntimeException( 'Another import is running. Please wait before retrying.' );
	}
	update_option( 'nada_import_lock', time(), false );
	try {
		nada_prepare_media();
		$term_ids = array();
		$definitions = array(
			'recipe_category' => array( 'breakfast' => array( 'Breakfast', 'فطور' ), 'dips' => array( 'Dips', 'تغميسات' ), 'dessert' => array( 'Dessert', 'حلويات' ), 'savoury' => array( 'Savoury', 'أطباق مالحة' ), 'drinks' => array( 'Drinks', 'مشروبات' ) ),
			'product_fat' => array( 'full-fat' => array( 'Full Fat', 'كامل الدسم' ), 'low-fat' => array( 'Low Fat', 'قليل الدسم' ), '0-fat' => array( '0% Fat', 'خالي الدسم' ) ),
		);
		foreach ( $definitions as $taxonomy => $terms ) {
			foreach ( $terms as $slug => $labels ) {
				$translations = array();
				foreach ( array( 'en', 'ar' ) as $index => $language ) {
					// Free Polylang Slug shares post/page slugs, not term slugs.
					$term_slug = $slug . ( 'ar' === $language ? '-ar' : '' );
					$term = get_term_by( 'slug', $term_slug, $taxonomy );
					$result = $term ? array( 'term_id' => $term->term_id ) : wp_insert_term( $labels[ $index ], $taxonomy, array( 'slug' => $term_slug ) );
					if ( is_wp_error( $result ) ) {
						throw new RuntimeException( $result->get_error_message() );
					}
					$id = (int) $result['term_id'];
					pll_set_term_language( $id, $language );
					$term_ids[ $taxonomy ][ $language ][ $slug ] = $id;
					$translations[ $language ] = $id;
				}
				pll_save_term_translations( $translations );
			}
		}
		$artwork_posts = get_option( 'nada_artwork_posts', array() );
		foreach ( nada_data( 'artwork' ) as $path ) {
			if ( ! empty( $artwork_posts[ $path ] ) && get_post( $artwork_posts[ $path ] ) ) { continue; }
			$title = ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $path, PATHINFO_FILENAME ) ) );
			$attachment = nada_import_image( $path, $title );
			if ( ! $attachment ) { continue; }
			$id = wp_insert_post( array( 'post_type' => 'nada_artwork', 'post_status' => 'publish', 'post_title' => $title ) );
			if ( $id ) { set_post_thumbnail( $id, $attachment ); $artwork_posts[ $path ] = $id; }
		}
		update_option( 'nada_artwork_posts', $artwork_posts );
		$pages = get_option( 'nada_import_pages', array() );
		foreach ( array( 'home' => array( 'Home', 'الرئيسية', '' ), 'why-greek' => array( 'Why Greek', 'ليش يوناني ندى', 'page-templates/why-greek.php' ), 'share-recipe' => array( 'Share Your Recipe', 'شارك وصفتك', 'page-templates/share-recipe.php' ) ) as $slug => $definition ) {
			$translations = array();
			foreach ( array( 'en', 'ar' ) as $index => $language ) {
				list( $id, $created ) = nada_seed_post( 'page', $slug, $language, $definition[ $index ], $definition[2] );
				$pages[ $language ][ $slug ] = $id;
				$translations[ $language ] = $id;
				if ( ! $created ) {
					continue;
				}
				$page_key = array( 'home' => 'home', 'why-greek' => 'why', 'share-recipe' => 'share' )[ $slug ];
				foreach ( nada_data( 'page-fields' ) as $name => $field ) {
					if ( $page_key === $field['page'] && ! isset( nada_data( 'content-links' )[ $name ] ) ) {
						nada_seed_field( $name, nada_translate( $field['default'], $language ), $id );
					}
				}
				foreach ( nada_data( 'repeaters' ) as $name => $repeater ) {
					if ( $page_key !== $repeater['page'] ) {
						continue;
					}
					$rows = $repeater['rows'];
					foreach ( $rows as &$row ) {
						foreach ( $row as &$value ) {
							$value = nada_translate( $value, $language );
						}
						unset( $value );
					}
					unset( $row );
					nada_seed_field( $name, $rows, $id );
				}
				if ( 'home' === $slug ) {
					$image = nada_import_image( 'assets/img/cutouts/hero-plate-pack-' . $language . '-v2.webp', $definition[ $index ] );
					if ( $image ) { set_post_thumbnail( $id, $image ); }
				}
			}
			pll_save_post_translations( $translations );
		}
		update_option( 'nada_import_pages', $pages );
		$library = nada_data( 'library' );
		foreach ( array( 'recipe' => $library['KORA']['RECIPES'], 'product' => nada_data( 'products' ) ) as $type => $items ) {
			foreach ( $items as $item ) {
				$translations = array();
				foreach ( array( 'en', 'ar' ) as $language ) {
					$localized = $item;
					if ( 'ar' === $language ) {
						if ( 'recipe' === $type ) {
							$localized = array_merge( $item, $library['KORA_AR']['recipes'][ $item['id'] ] ?? array() );
						} else {
							$localized['title'] = $library['KORA_AR']['flavors'][ $item['id'] ] ?? nada_translate( $item['title'], 'ar' );
							$localized['summary'] = nada_translate( $item['summary'], 'ar' );
						}
					}
					list( $id, $created ) = nada_seed_post( $type, $item['id'], $language, $localized['title'] );
					$translations[ $language ] = $id;
					if ( ! $created ) { continue; }
					$image = nada_import_image( 'ar' === $language ? ( $item['imageAr'] ?? $item['image'] ) : $item['image'], $localized['title'] );
					if ( $image ) { set_post_thumbnail( $id, $image ); }
					if ( 'recipe' === $type ) {
						foreach ( array( 'ingredients', 'steps' ) as $key ) {
							$rows = array_map( static function ( $text ) { return array( 'text' => wp_strip_all_tags( $text ) ); }, $localized[ $key ] ?? array() );
							nada_seed_field( 'recipe_' . $key, $rows, $id );
						}
						foreach ( array( 'prep', 'cook', 'total', 'serves' ) as $key ) { nada_seed_field( 'recipe_' . $key, $localized[ $key ] ?? '', $id ); }
						$category = array( 'Breakfast' => 'breakfast', 'Dip' => 'dips', 'Dessert' => 'dessert', 'Savoury' => 'savoury', 'Drink' => 'drinks' )[ $item['cat'] ];
						wp_set_object_terms( $id, array( $term_ids['recipe_category'][ $language ][ $category ] ), 'recipe_category' );
					} else {
						nada_seed_field( 'product_summary', $localized['summary'], $id );
						$fat = array( 'full' => 'full-fat', 'low' => 'low-fat', 'zero' => '0-fat' )[ $item['fat'] ];
						wp_set_object_terms( $id, array( $term_ids['product_fat'][ $language ][ $fat ] ), 'product_fat' );
					}
				}
				pll_save_post_translations( $translations );
			}
		}
		if ( ! get_option( 'nada_setup_complete' ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $pages['en']['home'] );
			$logo = nada_import_image( 'assets/img/nada-mark.png', 'NADA' );
			if ( ! get_theme_mod( 'custom_logo' ) && $logo ) { set_theme_mod( 'custom_logo', $logo ); }
			update_field( 'field_nada_social_links', array(
				array( 'social_label' => 'Instagram', 'social_url' => 'https://www.instagram.com/nadadairy' ),
				array( 'social_label' => 'TikTok', 'social_url' => 'https://www.tiktok.com/@nadadairy' ),
				array( 'social_label' => 'Facebook', 'social_url' => 'https://www.facebook.com/NadaDairy' ),
				array( 'social_label' => 'YouTube', 'social_url' => 'https://www.youtube.com/@NadaDairy' ),
				array( 'social_label' => 'X', 'social_url' => 'https://x.com/nadadairy' ),
				array( 'social_label' => 'Snapchat', 'social_url' => 'https://www.snapchat.com/add/nadadairy' ),
			), 'option' );
			update_option( 'nada_setup_complete', 1 );
		}
		nada_seed_editor_content();
		flush_rewrite_rules();
	} finally {
		delete_option( 'nada_import_lock' );
	}
}

add_action( 'admin_menu', function () {
	add_theme_page( 'NADA Setup', 'NADA Setup', 'manage_options', 'nada-setup', 'nada_import_screen' );
} );

function nada_import_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$message = '';
	if ( isset( $_POST['nada_import_nonce'] ) ) {
		check_admin_referer( 'nada_import', 'nada_import_nonce' );
		try { nada_import_content(); $message = 'Starter content imported. Existing imported content was preserved.'; }
		catch ( Throwable $error ) { $message = $error->getMessage(); }
	}
	?>
	<div class="wrap">
		<h1>NADA Setup</h1>
		<?php if ( $message ) : ?><div class="notice notice-info"><p><?php echo esc_html( $message ); ?></p></div><?php endif; ?>
		<p>Activate ACF Pro and Polylang, create English (en) and Arabic (ar), and use language directories in Languages → Settings → URL modifications. Set a non-Plain permalink structure first.</p>
		<p>This imports 19 recipes and 13 products in each language, translated pages, taxonomy terms, and featured images. On the first run it selects the homepage and supplies the default logo and social links. Re-running preserves existing imported posts.</p>
		<form method="post"><?php wp_nonce_field( 'nada_import', 'nada_import_nonce' ); submit_button( 'Import NADA starter content' ); ?></form>
	</div>
	<?php
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'nada import', function () {
		try { nada_import_content(); WP_CLI::success( 'NADA bilingual content imported.' ); }
		catch ( Throwable $error ) { WP_CLI::error( $error->getMessage() ); }
	} );
}
