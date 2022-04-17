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
	 * Setup hooks
	 */
	public function __construct() {
		add_filter( 'template_include', [ $this, 'load_sitemap_template' ] );
		add_action( 'init', [ $this, 'create_rewrites' ] );
		add_filter( 'posts_pre_query', [ $this, 'disable_main_query_for_sitemap_xml' ], 10, 2 );
		add_filter( 'robots_txt', [ $this, 'add_sitemap_robots_txt' ] );
	}

	/**
	 * Render sitemap
	 *
	 * @param  string $template Template file to use.
	 *
	 * @return string
	 */
	public function load_sitemap_template( $template ) {
		if ( 'true' === get_query_var( 'news-sitemap' ) ) {
			return dirname( __DIR__ ) . '/templates/google-news-sitemap.php';
		}

		return $template;
	}

	/**
	 * Add rewrite rules/tags
	 */
	public function create_rewrites() {
		add_rewrite_tag( '%news-sitemap%', 'true' );
		add_rewrite_rule( '^news-sitemap.xml$', 'index.php?news-sitemap=true', 'top' );

		add_action( 'redirect_canonical', [ $this, 'disable_canonical_redirects_for_sitemap_xml' ], 10, 2 );
	}

	/**
	 * Disable Main Query when rendering sitemaps
	 *
	 * @param array|null $posts array of post data or null
	 * @param \WP_Query  $query The WP_Query instance.
	 * @return  array
	 */
	public function disable_main_query_for_sitemap_xml( $posts, $query ) {
		if ( $query->is_main_query() && ! empty( $query->query_vars['news-sitemap'] ) ) {
			$posts = [];
		}

		return $posts;
	}

	/**
	 * Disable canonical redirects for the sitemap files
	 *
	 * @param  string $redirect_url URL to redirect to
	 * @param  string $requested_url Originally requested url
	 * @return string URL to redirect
	 */
	public function disable_canonical_redirects_for_sitemap_xml( $redirect_url, $requested_url ) {
		if ( preg_match( '/news-sitemap.xml/i', $requested_url ) ) {
			return $requested_url;
		}

		return $redirect_url;
	}

	/**
	 * Add the sitemap URL to robots.txt
	 *
	 * @param string $output Robots.txt output.
	 * @return string
	 */
	public function add_sitemap_robots_txt( $output ) {
		$url     = site_url( '/news-sitemap.xml' );
		$output .= "\nNews Sitemap: {$url}\n";

		return $output;
	}

}
