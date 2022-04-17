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
		add_action( 'publish_post', [ $this, 'purge_sitemap_data' ] );
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
	 * Purges sitemap data.
	 *
	 * @return boolean
	 */
	public function purge_sitemap_data(): bool {
		return Utils::delete_cache();
	}

}
