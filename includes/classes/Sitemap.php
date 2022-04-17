<?php
/**
 * Sitemap class
 *
 * @package 10up-google-news-sitemaps
 */

namespace TenupGoogleNewsSitemaps;

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
	private $data;

	/**
	 * Range of news items to include in the sitemap e.g. 2 days.
	 *
	 * @var int
	 */
	private $range = 2;

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
		$this->range = date( 'Y-m-d H:i:s', strtotime( '-' . (int) $this->range . ' day' ) );
	}

	/**
	 * Build news items sitemap.
	 *
	 * @return void
	 */
	public function build(): void {
		global $wpdb;

		$args = [
			'public' => true,
		];

		$post_types = get_post_types( $args );

		if ( ! empty( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$post_types = apply_filters( 'tenup_google_news_sitemaps_post_types', $post_types );

		foreach ( $post_types as $post_type ) {
			$offset = 0;

			while ( true ) {
				// phpcs:disable
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_date_gmt FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '%s' AND post_date_gmt >= '%s' ORDER BY post_date_gmt DESC LIMIT %d, %d", $post_type, $this->range, (int) $offset, (int) $this->process_page_size ), ARRAY_A );
				// phpcs:enable

				if ( empty( $results ) ) {
					break;
				}

				foreach ( $results as $result ) {
					$permalink = get_permalink( $result['ID'] );

					$item = [
						'ID'       => (int) $result['ID'],
						'url'      => $permalink,
						'title'    => $result['post_title'],
						'modified' => strtotime( $result['post_date_gmt'] ),
					];

					$item = apply_filters( 'tenup_google_news_sitemaps_post', $item, $post_type );

					if ( ! empty( $item ) && ! empty( $item['url'] ) ) {
						$this->data[] = $item;
					}

					$this->stop_the_insanity();
				}

				$offset += $this->process_page_size;
			}
		}

		// Add sitemap data to cache (if available) or wp_options.
		Utils::set_cache( $this->data );
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
	 * Clear all of the caches for memory management.
	 *
	 * @return void
	 */
	private function stop_the_insanity(): void {
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
