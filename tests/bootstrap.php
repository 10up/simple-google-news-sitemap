<?php
/**
 * Simple Google News Sitemap test bootstrap
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

set_time_limit( 0 );

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

/**
 * Bootstrap plugin
 */
function load_plugin() {
	global $wp_version;

	require_once __DIR__ . '/../simple-google-news-sitemap.php';

	add_action( 'init', __NAMESPACE__ . '\register_post_types' );

	echo 'WordPress version ' . $wp_version . "\n"; // phpcs:ignore
}

tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\load_plugin' );

/**
 * Completely skip looking up translations
 *
 * @return array
 */
function skip_translations_api() {
	return [
		'translations' => [],
	];
}

tests_add_filter( 'translations_api', __NAMESPACE__ . '\skip_translations_api' );

/**
 * Register post types for testing
 *
 * @since 1.2
 */
function register_post_types() {
	$args = array(
		'public'     => true,
		'taxonomies' => array( 'post_tag', 'category' ),
	);

	register_post_type( 'tsm_test', $args );

	$args = array(
		'taxonomies' => array( 'post_tag', 'category' ),
		'public'     => false,
	);

	register_post_type( 'tsm_test_private', $args );
}

require_once dirname( __DIR__ ) . '/vendor/antecedent/patchwork/Patchwork.php';
require_once $_tests_dir . '/includes/bootstrap.php';

\WP_Mock::setUsePatchwork( true );
\WP_Mock::bootstrap();
