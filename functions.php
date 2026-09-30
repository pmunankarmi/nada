<?php
/**
 * NADA theme bootstrap. Design source: NADAfinal3.3.8 HTML package.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

foreach ( array( 'setup', 'content-types', 'helpers', 'media', 'fields', 'options', 'navigation', 'editor-content', 'link-fields', 'section-images', 'languages', 'updater', 'submissions', 'submission-admin', 'import' ) as $module ) {
	require_once get_template_directory() . '/inc/' . $module . '.php';
}
