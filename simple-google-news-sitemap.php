<?php
/**
 * Simple Google News Sitemap
 *
 * @package           simple-google-news-sitemap
 * @author            10up
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Google News Sitemap
 * Plugin URI:        https://github.com/10up/simple-google-news-sitemap
 * Description:       A simple Google News sitemap is generated on-the-fly for articles that were published in the last two days.
 * Version:           1.0.1
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            10up
 * Author URI:        https://10up.com
 * Text Domain:       simple-google-news-sitemap
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/10up/simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cannot access page directly' );
}

/**
 * PSR-4 autoloading
 */
spl_autoload_register(
	function( $class ) {
		// Project-specific namespace prefix.
		$prefix = 'SimpleGoogleNewsSitemap\\';
		// Base directory for the namespace prefix.
		$base_dir = __DIR__ . '/includes/classes/';
		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}
		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

// Require Composer autoloader if it exists.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Initialise plugin core.
$plugin_core = new Core();

/**
 * Flush rewrites on activation and deactivation.
 */
register_activation_hook(
	__FILE__,
	function() use ( $plugin_core ) {
		$plugin_core->create_rewrites();
		flush_rewrite_rules( false );
	}
);

register_deactivation_hook(
	__FILE__,
	function() use ( $plugin_core ) {
		$plugin_core->remove_rewrites();
		flush_rewrite_rules( false );
	}
);
