<?php
/**
 * Cache utility functions
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache utility functions.
 */
class CacheUtils {

	/**
	 * Cache key for sitemap data.
	 *
	 * @var string
	 */
	private static $cache_key = 'simple_google_news_sitemap_data';

	/**
	 * Cache group
	 *
	 * @var string
	 */
	private static $cache_group = 'simple_google_news_sitemap';

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
	 *
	 * @return boolean True if the value was set, false otherwise.
	 */
	public static function set_cache( $data ): bool {
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return wp_cache_set( self::$cache_key, $data, self::$cache_group, self::$cache_expiry * DAY_IN_SECONDS );
		} else {
			return set_transient( self::$cache_key, $data, self::$cache_expiry * DAY_IN_SECONDS );
		}
	}

	/**
	 * Retrieves sitemap data from cache.
	 *
	 * @return array
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
		if ( ! $data ) {
			$sitemap = new Sitemap();

			// Build sitemap.
			$sitemap->build();

			// Fetch fresh items for sitemap.
			$data = $sitemap->get_data();
		}

		return $data;
	}

	/**
	 * Deletes stored sitemap cache.
	 *
	 * @return boolean True if the data was deleted, false otherwise.
	 */
	public static function delete_cache(): bool {
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return wp_cache_delete( self::$cache_key, self::$cache_group );
		} else {
			return delete_transient( self::$cache_key );
		}
	}

}
