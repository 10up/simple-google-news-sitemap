<?php
/**
 * Google News Sitemap testing
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

use SimpleGoogleNewsSitemap\Sitemap;
use WP_UnitTestCase;

/**
 * Sitemap test class
 */
class TestSitemap extends WP_UnitTestCase {

	public function setUp() {
		global $wp_rewrite;

		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		update_option( "rewrite_rules", true );
		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Test building sitemap.
	 */
	public function testBuild() {
		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'post',
			'post_title'  => 'Test Post One',
		);

		wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'post',
			'post_title'  => 'Test Post Two',
		);

		$post_id = wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'post',
			'post_title'  => 'Test Post Three',
		);

		wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'tsm_test',
			'post_title'  => 'Test Custom One',
		);

		wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'tsm_test_private',
			'post_title'  => 'Test Custom Two',
		);

		wp_insert_post( $args );

		$sitemap = new Sitemap();
		$sitemap->build();

		$data = $sitemap->get_data();
		$links = wp_list_pluck( $data, 'url' );
		$ids   = wp_list_pluck( $data, 'ID' );

		$this->assertEquals( 4, count( $data ) );
		$this->assertTrue( in_array( (int) $post_id, $ids, true ) );
		$this->assertTrue( in_array( home_url() . '/test-post-two/', $links, true ) );
	}

}
