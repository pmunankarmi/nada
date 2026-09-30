<?php
/**
 * NADA theme bootstrap. Design source: NADAfinal3.3.8 HTML package.
 *
 * @package NADA
 */

defined( 'ABSPATH' ) || exit;

foreach ( array( 'setup', 'content-types', 'helpers', 'fields', 'options', 'languages', 'updater', 'submissions', 'import' ) as $module ) {
	require_once get_template_directory() . '/inc/' . $module . '.php';
}
