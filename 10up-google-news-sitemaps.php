<?php
/**
 * Plugin Name: 10up Google News Sitemaps
 * Plugin URI:  https://10up.com
 * Description: Google News sitemap plugin
 * Version:     1.0
 * Author:      10up
 * Author URI:  https://10up.com
 * License:     GPLv2+
 * Text Domain: tenup-google-news-sitemaps
 * Update URI:  https://github.com/10up/google-news-sitemaps
 *
 * @package 10up-google-news-sitemaps
 */

namespace TenupGoogleNewsSitemaps;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cannot access page directly' );
}

/**
 * PSR-4 autoloading
 */
spl_autoload_register(
	function( $class ) {
			// Project-specific namespace prefix.
			$prefix = 'TenupGoogleNewsSitemaps\\';
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
new Core();

/**
 * Flush rewrites on activation and deactivation.
 */
register_activation_hook(
	__FILE__,
	function() {
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	function() {
		flush_rewrite_rules();
	}
);
