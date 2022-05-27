<?php
/**
 * Core plugin functionality
 *
 * @package 10up-google-news-sitemaps
 */

namespace TenupGoogleNewsSitemaps;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin functionality.
 */
class Core {

	/**
	 * News sitemap slug.
	 *
	 * @var string
	 */
	private $sitemap_slug = 'news-sitemap';

	/**
	 * Setup hooks.
	 */
	public function __construct() {
		add_filter( 'template_include', [ $this, 'load_sitemap_template' ] );
		add_filter( 'posts_pre_query', [ $this, 'disable_main_query_for_sitemap_xml' ], 10, 2 );
		add_filter( 'robots_txt', [ $this, 'add_sitemap_robots_txt' ] );

		add_action( 'init', [ $this, 'create_rewrites' ] );
		add_action( 'publish_post', [ $this, 'purge_sitemap_data' ], 1000, 3 );
		add_action( 'trash_post', [ $this, 'purge_sitemap_data' ], 1000, 3 );
		add_action( 'publish_post', [ $this, 'ping_google' ], 2000 );
		add_action( 'delete_post', [ $this, 'purge_sitemap_data_on_delete' ], 1000, 2 );
	}

	/**
	 * Render sitemap.
	 *
	 * @param string $template Template file to use.
	 *
	 * @return string
	 */
	public function load_sitemap_template( string $template ): string {
		if ( 'true' === get_query_var( $this->sitemap_slug ) ) {
			return dirname( __DIR__ ) . '/templates/google-news-sitemap.php';
		}

		return $template;
	}

	/**
	 * Add rewrite rules/tags.
	 *
	 * @return void
	 */
	public function create_rewrites() {
		add_rewrite_tag( '%' . $this->sitemap_slug . '%', 'true' );
		add_rewrite_rule( sprintf( '^%s.xml$', $this->sitemap_slug ), sprintf( 'index.php?%s=true', $this->sitemap_slug ), 'top' );

		add_action( 'redirect_canonical', [ $this, 'disable_canonical_redirects_for_sitemap_xml' ], 10, 2 );
	}

	/**
	 * Disable Main Query when rendering sitemaps.
	 *
	 * @param array|null $posts array of post data or null.
	 * @param \WP_Query  $query The WP_Query instance.
	 *
	 * @return array|null
	 */
	public function disable_main_query_for_sitemap_xml( $posts, \WP_Query $query ) {
		if ( $query->is_main_query() && ! empty( $query->query_vars[ $this->sitemap_slug ] ) ) {
			$posts = [];
		}

		return $posts;
	}

	/**
	 * Disable canonical redirects for the sitemap files.
	 *
	 * @param string $redirect_url  URL to redirect to.
	 * @param string $requested_url Originally requested url.
	 *
	 * @return string URL to redirect
	 */
	public function disable_canonical_redirects_for_sitemap_xml( string $redirect_url, string $requested_url ): string {
		if ( preg_match( sprintf( '/%s.xml/i', $this->sitemap_slug ), $requested_url ) ) {
			return $requested_url;
		}

		return $redirect_url;
	}

	/**
	 * Add the sitemap URL to robots.txt file.
	 *
	 * @param string $output Robots.txt output.
	 *
	 * @return string
	 */
	public function add_sitemap_robots_txt( string $output ): string {
		$url     = site_url( sprintf( '/%s.xml', $this->sitemap_slug ) );
		$output .= "\n" . esc_html__( 'News Sitemap', 'tenup-google-news-sitemaps' ) . ": {$url}\n";

		return $output;
	}

	/**
	 * Purges sitemap data on post publish (called upon post updates as well).
	 * Also, this function is used when post is moved to trash.
	 *
	 * @param int      $post_id     Post ID.
	 * @param \WP_Post $post        Post object.
	 * @param string   $old_status  Old post status
	 *
	 * @return boolean
	 */
	public function purge_sitemap_data( int $post_id, \WP_Post $post, string $old_status ): bool {
		$sitemap = new Sitemap();

		// Don't purge cache for non-supported post types.
		if ( ! in_array( $post->post_type, $sitemap->get_post_types(), true ) ) {
			return false;
		}

		// Post date & range converted to timestamp.
		$post_publish_date = strtotime( $post->post_date_gmt );
		$range             = strtotime( $sitemap->get_range() );

		/**
		 * POST is moved to trash.
		 * If the publish date falls within the range, we need to purge the cache.
		 */
		if ( 'trash' === $post->post_status && $post_publish_date > $range ) {
			return Utils::delete_cache();
		}

		/**
		 * POST is updated.
		 * Case 1: where the publish date is modified and it falls within range from current time.
		 * Case 2: where the publish date is modified and it falls outside range.
		 */
		if ( 'publish' === $old_status && $old_status === $post->post_status ) {
			if ( $post_publish_date > $range || $post_publish_date < $range ) {
				return Utils::delete_cache();
			}

			// For any other changes, we don't flush cache.
			return false;
		}

		return Utils::delete_cache();
	}

	/**
	 * Ping Google News after a news post is published.
	 *
	 * @return boolean
	 */
	public function ping_google(): bool {
		if ( false === apply_filters( 'tenup_google_news_sitemaps_ping', true ) ) {
			return false;
		}

		if ( '0' === get_option( 'blog_public' ) ) {
			return false;
		}

		// Sitemap URL.
		$url = site_url( sprintf( '/%s.xml', $this->sitemap_slug ) );

		// Ping Google.
		$ping = wp_remote_get( sprintf( 'https://www.google.com/ping?sitemap=%s', esc_url_raw( $url ) ), [ 'blocking' => false ] );

		if ( ! is_array( $ping ) || is_wp_error( $ping ) ) {
			return false;
		}

		// Successful request only if the response code is 200.
		if ( 200 === wp_remote_retrieve_response_code( $ping ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Purges sitemap data on post_delete.
	 *
	 * @param int      $post_id     Post ID.
	 * @param \WP_Post $post        Post object.
	 *
	 * @return boolean
	 */
	public function purge_sitemap_data_on_delete( int $post_id, \WP_Post $post ): bool {
		$sitemap = new Sitemap();

		// Don't purge cache for non-supported post types.
		if ( ! in_array( $post->post_type, $sitemap->get_post_types(), true ) ) {
			return false;
		}

		// If the publish date is within range from current time, we need to flush the cache.
		if ( strtotime( $post->post_date_gmt ) > strtotime( $sitemap->get_range() ) ) {
			return Utils::delete_cache();
		}

		// For rest, we don't need to flush cache.
		return false;
	}

}
