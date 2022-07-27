<?php
/**
 * Google News Sitemap testing
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

use SimpleGoogleNewsSitemap\Core;
use WP_UnitTestCase, WP_Mock, Mockery;

/**
 * Core test class
 */
class TestCore extends WP_UnitTestCase {

	public function setUp(): void {
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		$this->addToAssertionCount(
			Mockery::getContainer()->mockery_getExpectationCount()
		);

		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * Test for loading regular template file.
	 * news-sitemap is NOT queried
	 */
	public function testLoadRegularTemplate() {
		$core = new Core();

		$this->assertEquals( 'TEMPLATE_FILE', $core->load_sitemap_template( 'TEMPLATE_FILE' ) );
	}

	/**
	 * Test for loading sitemap template file.
	 * news-sitemap is queried.
	 */
	public function testLoadSitemapTemplate() {
		$core = new Core();

		\WP_Mock::userFunction(
			'get_query_var',
			[
				'return' => 'true'
			]
		);

		$this->assertEquals( dirname( __DIR__ ) . '/includes/templates/google-news-sitemap.php', $core->load_sitemap_template( 'TEMPLATE_FILE' ) );
	}

	/**
	 * Checks main query when NOT rendering sitemap.
	 */
	public function testDisableMainQueryNoSitemap() {
		$core = new Core();

		$this->assertEquals( [ 'posts' ], $core->disable_main_query_for_sitemap_xml( [ 'posts' ], new \WP_Query() ) );
	}

	/**
	 * Checks main query when rendering sitemap.
	 */
	public function testDisableMainQuerySitemap() {
		$core = new Core();

		$wp_query = Mockery::mock( '\WP_Query' );
		$wp_query->shouldReceive( 'is_main_query' )->andReturn( true );
		$wp_query->query_vars = [ 'news-sitemap' => 'NOT_EMPTY' ];

		$this->assertEquals( [], $core->disable_main_query_for_sitemap_xml( [ 'posts' ], $wp_query ) );
	}

	/**
	 * Disable canonical redirects only for sitemap files.
	 * Regular URL requested.
	 */
	public function testDisableCanonicalRedirectsNoSitemap() {
		$core = new Core();

		$this->assertEquals( 'https://redirect_url.com', $core->disable_canonical_redirects_for_sitemap_xml( 'https://redirect_url.com', 'https://requested_url.com/no-sitemap-page' ) );
	}

	/**
	 * Disable canonical redirects only for sitemap files.
	 * With sitemap URL requested.
	 */
	public function testDisableCanonicalRedirectsSitemap() {
		$core = new Core();

		$this->assertEquals( 'https://requested_url.com/news-sitemap.xml', $core->disable_canonical_redirects_for_sitemap_xml( 'https://redirect_url.com', 'https://requested_url.com/news-sitemap.xml' ) );
	}

	/**
	 * Adds sitemap URL to robots.txt file.
	 */
	public function testAddSitemapRobotsTxt() {
		$core = new Core();
		$url = site_url( '/news-sitemap.xml' );

		$this->assertEquals( "\nNews Sitemap: {$url}\n", $core->add_sitemap_robots_txt( '' ) );
	}

	/**
	 * Pings google service for newly updated sitemap.
	 * When pinging is not enabled.
	 */
	public function testPingGoogleNotEnabled() {
		$core = new Core();

		add_filter( 'simple_google_news_sitemap_ping', '__return_false' );

		$this->assertFalse( $core->ping_google() );
	}

	/**
	 * Pings google service for newly updated sitemap.
	 * When blog is not public.
	 */
	public function testPingGooglePrivateBlog() {
		$core = new Core();

		add_filter( 'simple_google_news_sitemap_ping', '__return_true' );

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => '0'
			]
		);

		$this->assertFalse( $core->ping_google() );
	}

	/**
	 * Pings google service for newly updated sitemap.
	 * Not a valid response received.
	 */
	public function testPingGoogleInvalidResponse() {
		$core = new Core();

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => '1'
			]
		);

		\WP_Mock::userFunction(
			'wp_remote_get',
			[
				'return' => 'INVALID_RESPONSE'
			]
		);

		$this->assertFalse( $core->ping_google() );
	}

	/**
	 * Pings google service for newly updated sitemap.
	 * Valid response received.
	 */
	public function testPingGoogleValidResponse() {
		$core = new Core();

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => '1'
			]
		);

		\WP_Mock::userFunction(
			'wp_remote_get',
			[
				'return' => [
					'response' => [
						'code' => 200
					]
				]
			]
		);

		\WP_Mock::userFunction(
			'is_wp_error',
			[
				'return' => false
			]
		);

		$this->assertTrue( $core->ping_google() );
	}

}
