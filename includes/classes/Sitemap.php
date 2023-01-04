<?php
/**
 * Sitemap class
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents the entire sitemap
 */
class Sitemap {

	/**
	 * News items to be included in the sitemap.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Range of news items to include in the sitemap e.g. 2 days.
	 *
	 * @var int
	 */
	private $range = 2;

	/**
	 * Support post types.
	 *
	 * @var array
	 */
	private $post_types = [];

	/**
	 * Items to process in each DB cycle.
	 *
	 * @var int
	 */
	private $process_page_size = 500;

	/**
	 * Create a new empty sitemap.
	 */
	public function __construct() {
		$this->range      = date( 'Y-m-d H:i:s', strtotime( '-' . (int) $this->range . ' day' ) ); // phpcs:ignore
		$this->post_types = $this->supported_post_types();
	}

	/**
	 * Retrieve supported post types.
	 *
	 * @return array
	 */
	public function supported_post_types(): array {
		$post_types = array_filter( get_post_types(), 'is_post_type_viewable' );

		$exclude_post_types = [
			'attachment',
			'redirect_rule',
		];

		foreach ( $exclude_post_types as $exclude_post_type ) {
			unset( $post_types[ $exclude_post_type ] );
		}

		/**
		 * Filter the list of supported post types.
		 *
		 * @since 1.0.0
		 *
		 * @hook simple_google_news_sitemap_post_types
		 * @param {array} $post_types List of post types to support.
		 * @returns {array} List of post types to support.
		 */
		return apply_filters( 'simple_google_news_sitemap_post_types', $post_types );
	}

	/**
	 * Build news items sitemap.
	 *
	 * @return void
	 */
	public function build() {
		global $wpdb;

		foreach ( $this->post_types as $post_type ) {
			$offset = 0;

			while ( true ) {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_date FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = %s AND post_date >= %s ORDER BY post_date DESC LIMIT %d, %d", $post_type, $this->range, (int) $offset, (int) $this->process_page_size ), ARRAY_A );

				if ( empty( $results ) ) {
					break;
				}

				foreach ( $results as $result ) {
					$permalink = get_permalink( $result['ID'] );

					$item = [
						'ID'       => (int) $result['ID'],
						'url'      => $permalink,
						'title'    => $result['post_title'],
						'modified' => strtotime( $result['post_date'] ),
					];

					/**
					 * Filter an individual item before it goes to the sitemap.
					 *
					 * This can be used to modify a specific item or remove an
					 * item all together.
					 *
					 * @since 1.0.0
					 *
					 * @hook simple_google_news_sitemap_post
					 * @param {array}  $item The item that will be displayed.
					 * @param {string} $post_type The post type of the item.
					 * @returns {array} The item that will be displayed.
					 */
					$item = apply_filters( 'simple_google_news_sitemap_post', $item, $post_type );

					if ( ! empty( $item ) && ! empty( $item['url'] ) ) {
						$this->data[] = $item;
					}

					$this->stop_the_insanity();
				}

				$offset += $this->process_page_size;
			}
		}

		// Add sitemap data to cache (if available) or wp_options.
		CacheUtils::set_cache( $this->data );
	}

	/**
	 * Get data range.
	 *
	 * @return string
	 */
	public function get_range(): string {
		return $this->range;
	}

	/**
	 * Get sitemap data.
	 *
	 * @return array
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * Get post types.
	 *
	 * @return array
	 */
	public function get_post_types(): array {
		return $this->post_types;
	}

	/**
	 * Clear all of the caches for memory management.
	 *
	 * @return void
	 */
	private function stop_the_insanity() {
		global $wpdb, $wp_object_cache;

		$one_hundred_mb = 104857600;
		if ( memory_get_usage() <= $one_hundred_mb ) {
			return;
		}

		$wpdb->queries = array();

		if ( is_object( $wp_object_cache ) ) {
			$wp_object_cache->group_ops      = array();
			$wp_object_cache->stats          = array();
			$wp_object_cache->memcache_debug = array();
			$wp_object_cache->cache          = array();

			if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
				$wp_object_cache->__remoteset(); // important
			}
		}

		gc_collect_cycles();
	}
}
