<?php
/**
 * Abilities API testing
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

use WP_UnitTestCase;

/**
 * Abilities test class
 */
class TestAbilities extends WP_UnitTestCase {

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'The Abilities API requires WordPress 6.9 or later.' );
		}

		CacheUtils::delete_cache();
	}

	/**
	 * Create a published post.
	 *
	 * @param string $title Post title.
	 * @return int
	 */
	private function create_post( string $title ): int {
		return self::factory()->post->create(
			[
				'post_status' => 'publish',
				'post_type'   => 'post',
				'post_title'  => $title,
			]
		);
	}

	/**
	 * Test that the category and abilities are registered.
	 */
	public function testAbilitiesAreRegistered(): void {
		$this->assertNotNull( wp_get_ability_category( Abilities::CATEGORY ) );

		foreach ( [ Abilities::GET_ABILITY, Abilities::REGENERATE_ABILITY ] as $name ) {
			$ability = wp_get_ability( $name );
			$this->assertNotNull( $ability, "$name is not registered" );
			$this->assertTrue( $ability->get_meta()['show_in_rest'] );
		}
	}

	/**
	 * Test the get sitemap permission check.
	 */
	public function testGetSitemapPermissions(): void {
		$ability = wp_get_ability( Abilities::GET_ABILITY );

		wp_set_current_user( 0 );
		$this->assertFalse( $ability->check_permissions() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->assertFalse( $ability->check_permissions() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		$this->assertTrue( $ability->check_permissions() );
	}

	/**
	 * Test the regenerate sitemap permission check.
	 */
	public function testRegenerateSitemapPermissions(): void {
		$ability = wp_get_ability( Abilities::REGENERATE_ABILITY );

		wp_set_current_user( 0 );
		$this->assertFalse( $ability->check_permissions() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		$this->assertFalse( $ability->check_permissions() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( $ability->check_permissions() );
	}

	/**
	 * Test that get sitemap returns the expected shape.
	 */
	public function testGetSitemapOutput(): void {
		$this->create_post( 'Ability Post One' );
		$this->create_post( 'Ability Post Two' );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

		$result = wp_get_ability( Abilities::GET_ABILITY )->execute();

		$this->assertIsArray( $result );
		$this->assertSame( Core::get_sitemap_url(), $result['sitemap_url'] );
		$this->assertSame( 2, $result['count'] );
		$this->assertCount( 2, $result['items'] );

		$titles = wp_list_pluck( $result['items'], 'title' );
		$this->assertContains( 'Ability Post One', $titles );
		$this->assertContains( 'Ability Post Two', $titles );

		foreach ( $result['items'] as $item ) {
			$this->assertNotEmpty( $item['url'] );
			$this->assertNotFalse( strtotime( $item['publication_date'] ) );
		}
	}

	/**
	 * Test that get sitemap respects the sitemap data filter.
	 */
	public function testGetSitemapUsesDataFilter(): void {
		$this->create_post( 'Ability Filtered Post' );

		add_filter( 'simple_google_news_sitemap_data', '__return_empty_array' );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

		$result = wp_get_ability( Abilities::GET_ABILITY )->execute();

		remove_filter( 'simple_google_news_sitemap_data', '__return_empty_array' );

		$this->assertSame( 0, $result['count'] );
		$this->assertSame( [], $result['items'] );
	}

	/**
	 * Test that get sitemap strips markup and drops unusable items.
	 */
	public function testGetSitemapSanitizesOutput(): void {
		$filter = static function () {
			return [
				[
					'url'      => 'https://example.com/good',
					'title'    => '<script>alert(1)</script><b>Safe</b>&nbsp;Title',
					'modified' => 1700000000,
				],
				[
					'url'      => 'javascript:alert(1)',
					'title'    => 'Bad URL',
					'modified' => 1700000000,
				],
				[
					'url'   => 'https://example.com/no-date',
					'title' => 'No date',
				],
				'not-an-array',
			];
		};

		add_filter( 'simple_google_news_sitemap_data', $filter );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

		$result = wp_get_ability( Abilities::GET_ABILITY )->execute();

		remove_filter( 'simple_google_news_sitemap_data', $filter );

		$this->assertSame( 1, $result['count'] );
		$this->assertSame( 'https://example.com/good', $result['items'][0]['url'] );
		$this->assertStringNotContainsString( '<', $result['items'][0]['title'] );
		$this->assertStringContainsString( 'Safe', $result['items'][0]['title'] );
	}

	/**
	 * Test that regenerate sitemap rebuilds the cache.
	 */
	public function testRegenerateSitemapRebuildsCache(): void {
		$this->create_post( 'Ability Regenerate One' );

		// Prime the cache, then add a post that the cache does not know about.
		CacheUtils::get_cache();
		$this->create_post( 'Ability Regenerate Two' );
		CacheUtils::set_cache( [] );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$result = wp_get_ability( Abilities::REGENERATE_ABILITY )->execute();

		$this->assertIsArray( $result );
		$this->assertTrue( $result['success'] );
		$this->assertSame( 2, $result['count'] );
		$this->assertSame( Core::get_sitemap_url(), $result['sitemap_url'] );
		$this->assertCount( 2, get_transient( 'simple_google_news_sitemap_data' ) );
	}
}
