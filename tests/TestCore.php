<?php
/**
 * Google News Sitemap testing
 *
 * @package 10up-google-news-sitemaps
 */

namespace TenupGoogleNewsSitemaps;

use TenupGoogleNewsSitemaps\Core;
use WP_UnitTestCase;

/**
 * Core test class
 */
class TestCore extends WP_UnitTestCase {

	public function setUp() {
		global $wp_rewrite;

		$wp_rewrite->set_permalink_structure('/%postname%/');

		update_option( "rewrite_rules", true );

		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Test setting up the sitemap class.
	 */
	public function testConstruct() {
		$core = new Core();
	}
}
