<?php
/**
 * Core plugin functionality
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

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
	public function init() {
		add_filter( 'template_include', [ $this, 'load_sitemap_template' ] );
		add_filter( 'posts_pre_query', [ $this, 'disable_main_query_for_sitemap_xml' ], 10, 2 );
		add_filter( 'robots_txt', [ $this, 'add_sitemap_robots_txt' ] );

		add_action( 'init', [ $this, 'create_rewrites' ] );
		add_action( 'publish_post', [ $this, 'purge_sitemap_data_on_update' ], 1000, 3 );
		add_action( 'transition_post_status', [ $this, 'purge_sitemap_data_on_status_change' ], 1000, 3 );
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
	 * Remove rewrite rules/tags
	 *
	 * @return void
	 */
	public function remove_rewrites() {
		remove_rewrite_tag( '%' . $this->sitemap_slug . '%', 'true' );

		global $wp_rewrite;
		unset( $wp_rewrite->extra_rules_top[ sprintf( '^%s.xml$', $this->sitemap_slug ) ] );
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
		$url = home_url( sprintf( '/%s.xml', $this->sitemap_slug ) );
		if ( ! get_option( 'permalink_structure' ) ) {
			$url = add_query_arg( $this->sitemap_slug, 'true', home_url( '/' ) );
		}
		$output .= "\n" . esc_html__( 'Sitemap', 'simple-google-news-sitemap' ) . ": {$url}\n";

		return $output;
	}

	/**
	 * Purges sitemap data when the post is updated.
	 *
	 * @param int      $post_id     Post ID.
	 * @param \WP_Post $post        Post object.
	 * @param string   $old_status  Old post status.
	 *
	 * @return boolean
	 */
	public function purge_sitemap_data_on_update( int $post_id, \WP_Post $post, string $old_status ): bool {
		$sitemap = new Sitemap();

		// Don't purge cache for non-supported post types.
		if ( ! in_array( $post->post_type, $sitemap->get_post_types(), true ) ) {
			return false;
		}

		// Purge cache on updates.
		if ( 'publish' === $old_status && $old_status === $post->post_status ) {
			return CacheUtils::delete_cache();
		}

		return false;
	}

	/**
	 * Purges sitemap data when the post is published.
	 *
	 * @param string   $new_status  New post status.
	 * @param string   $old_status  Old post status.
	 * @param \WP_Post $post        Post object.
	 *
	 * @return boolean
	 */
	public function purge_sitemap_data_on_status_change( string $new_status, string $old_status, \WP_Post $post ): bool {
		$sitemap = new Sitemap();

		// Don't purge cache for non-supported post types.
		if ( ! in_array( $post->post_type, $sitemap->get_post_types(), true ) ) {
			return false;
		}

		// Post date & range converted to timestamp.
		$post_publish_date = strtotime( $post->post_date );
		$range             = strtotime( $sitemap->get_range() );

		// Post statuses we clear the cache on.
		$post_statuses = [
			'future',
			'private',
			'pending',
			'draft',
			'trash',
			'auto-draft',
		];

		/**
		 * Filter the post statuses we look for to determine if cache needs cleared.
		 *
		 * @since 1.0.0
		 *
		 * @hook simple_google_news_sitemap_post_statuses_to_clear
		 * @param {array} $post_statuses Post statuses we clear cache on.
		 * @returns {array} Filtered post statuses.
		 */
		$post_statuses = apply_filters( 'simple_google_news_sitemap_post_statuses_to_clear', $post_statuses );

		/*
		 * POST status is updated or changed to trash / future / pending / private / draft.
		 * If the publish date falls within the range, we flush cache.
		 */
		if (
			'publish' === $old_status && in_array( $new_status, $post_statuses, true )
			|| in_array( $old_status, $post_statuses, true ) && 'publish' === $new_status
		) {
			if ( $post_publish_date > $range ) {
				return CacheUtils::delete_cache();
			}
		}

		return false;
	}

	/**
	 * Ping Google News after a news post is published.
	 *
	 * @return boolean
	 */
	public function ping_google(): bool {
		/**
		 * Decide whether to ping Google when the sitemap changes.
		 *
		 * @since 1.0.0
		 *
		 * @hook simple_google_news_sitemap_ping
		 * @param {boolean} $should_ping Should we ping Google? Default true.
		 * @returns {boolean} Should we ping Google?
		 */
		if ( false === apply_filters( 'simple_google_news_sitemap_ping', true ) ) {
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
	 * This one is for the cases when the post is deleted directly via CLI and does
	 * not go to trash.
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

		// Post date & range converted to timestamp.
		$post_publish_date = strtotime( $post->post_date );
		$range             = strtotime( $sitemap->get_range() );

		// If the publish date is within range from current time, we purge the cache.
		if ( $post_publish_date > $range ) {
			return CacheUtils::delete_cache();
		}

		// For rest, we do nothing.
		return false;
	}

}
