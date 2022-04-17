<?php
/**
 * Utility functions
 *
 * @package 10up-google-news-sitemaps
 */

namespace TenupGoogleNewsSitemaps;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility functions.
 */
class Utils {

	/**
	 * Cache key for sitemap data.
	 *
	 * @var string
	 */
	private static $cache_key = 'tenup_google_news_sitemaps_data';

	/**
	 * Cache group
	 *
	 * @var string
	 */
	private static $cache_group = 'tenup_google_news_sitemaps';

	/**
	 * Cache expiry (number of days)
	 *
	 * @var int
	 */
	private static $cache_expiry = 2;

	/**
	 * Stores sitemap data for faster retrieval.
	 *
	 * @param array $data Sitemap data to be stored.
	 */
	public static function set_cache( $data ) {
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			wp_cache_set( self::$cache_key, $data, self::$cache_group, self::$cache_expiry * DAY_IN_SECONDS );
		} else {
			set_transient( self::$cache_key, $data, self::$cache_expiry * DAY_IN_SECONDS );
		}
	}

	/**
	 * Retrieves sitemap data from cache.
	 *
	 * @return array|boolean
	 */
	public static function get_cache() {
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			$data = wp_cache_get( self::$cache_key, self::$cache_group );
		} else {
			$data = get_transient( self::$cache_key );
		}

		/**
		 * Sitemap data does not exist
		 * Attempting to build a fresh one
		 */
		// if ( ! $data ) {
		// 	$sitemap = new Sitemap();

		// 	// Build sitemap.
		// 	$sitemap->build();

		// 	// Fetch fresh items for sitemap.
		// 	$data = $sitemap->get_data();
		// }

		return $data;
	}

}
