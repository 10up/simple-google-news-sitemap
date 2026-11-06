<?php
/**
 * Registers abilities with the WordPress Abilities API.
 *
 * @package simple-google-news-sitemap
 */

namespace SimpleGoogleNewsSitemap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes the news sitemap to AI agents and other clients.
 *
 * The Abilities API ships with WordPress 6.9. On older versions the hooks
 * below never fire, so this class does nothing.
 *
 * @since x.x.x
 */
class Abilities {

	/**
	 * Slug of the ability category.
	 *
	 * @var string
	 */
	const CATEGORY = 'simple-google-news-sitemap';

	/**
	 * Name of the get sitemap ability.
	 *
	 * @var string
	 */
	const GET_ABILITY = 'simple-google-news-sitemap/get-sitemap';

	/**
	 * Name of the regenerate sitemap ability.
	 *
	 * @var string
	 */
	const REGENERATE_ABILITY = 'simple-google-news-sitemap/regenerate-sitemap';

	/**
	 * Register the hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public function register_category() {
		wp_register_ability_category(
			self::CATEGORY,
			[
				'label'       => __( 'Simple Google News Sitemap', 'simple-google-news-sitemap' ),
				'description' => __( 'Abilities for reading and rebuilding the Google News sitemap.', 'simple-google-news-sitemap' ),
			]
		);
	}

	/**
	 * Register the abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		wp_register_ability(
			self::GET_ABILITY,
			[
				'label'               => __( 'Get the Google News sitemap', 'simple-google-news-sitemap' ),
				'description'         => __( 'Returns the articles in the Google News sitemap and the sitemap URL. The list covers posts published in the last two days. It reads the cached sitemap and builds it first if the cache is empty.', 'simple-google-news-sitemap' ),
				'category'            => self::CATEGORY,
				// No input schema: core then rejects any input sent to this ability.
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'sitemap_url' => [
							'description' => __( 'Public URL of the sitemap.', 'simple-google-news-sitemap' ),
							'type'        => 'string',
						],
						'count'       => [
							'description' => __( 'Number of articles in the sitemap.', 'simple-google-news-sitemap' ),
							'type'        => 'integer',
						],
						'items'       => [
							'description' => __( 'The articles in the sitemap.', 'simple-google-news-sitemap' ),
							'type'        => 'array',
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'url'              => [ 'type' => 'string' ],
									'title'            => [ 'type' => 'string' ],
									'publication_date' => [
										'description' => __( 'Publication date in W3C format.', 'simple-google-news-sitemap' ),
										'type'        => 'string',
									],
								],
							],
						],
					],
					'required'   => [ 'sitemap_url', 'count', 'items' ],
				],
				'execute_callback'    => [ $this, 'execute_get_sitemap' ],
				'permission_callback' => [ $this, 'can_get_sitemap' ],
				'meta'                => [
					'show_in_rest' => true,
					// Opt in to the MCP Adapter so AI agents can discover this ability.
					'mcp'          => [
						'public' => true,
					],
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);

		wp_register_ability(
			self::REGENERATE_ABILITY,
			[
				'label'               => __( 'Regenerate the Google News sitemap', 'simple-google-news-sitemap' ),
				'description'         => __( 'Clears the cached sitemap and builds it again from the latest published posts. It does not ping Google.', 'simple-google-news-sitemap' ),
				'category'            => self::CATEGORY,
				// No input schema: core then rejects any input sent to this ability.
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success'     => [
							'description' => __( 'Whether the sitemap was rebuilt.', 'simple-google-news-sitemap' ),
							'type'        => 'boolean',
						],
						'count'       => [
							'description' => __( 'Number of articles in the new sitemap.', 'simple-google-news-sitemap' ),
							'type'        => 'integer',
						],
						'sitemap_url' => [
							'description' => __( 'Public URL of the sitemap.', 'simple-google-news-sitemap' ),
							'type'        => 'string',
						],
					],
					'required'   => [ 'success', 'count', 'sitemap_url' ],
				],
				'execute_callback'    => [ $this, 'execute_regenerate_sitemap' ],
				'permission_callback' => [ $this, 'can_regenerate_sitemap' ],
				'meta'                => [
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
					'annotations'  => [
						'readonly'    => false,
						// The cache is replaced with fresh data and nothing is deleted for good.
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Permission check for the get sitemap ability.
	 *
	 * @return bool
	 */
	public function can_get_sitemap(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Permission check for the regenerate sitemap ability.
	 *
	 * @return bool
	 */
	public function can_regenerate_sitemap(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Run the get sitemap ability.
	 *
	 * @return array
	 */
	public function execute_get_sitemap(): array {
		$links = CacheUtils::get_cache();

		/** This filter is documented in includes/templates/google-news-sitemap.php */
		$links = apply_filters( 'simple_google_news_sitemap_data', $links );

		$items = [];

		foreach ( (array) $links as $link ) {
			// The data filter can return anything, so skip items that are not usable.
			if ( ! is_array( $link ) || empty( $link['url'] ) || empty( $link['title'] ) || empty( $link['modified'] ) ) {
				continue;
			}

			$url   = esc_url_raw( (string) $link['url'] );
			$title = trim( wp_strip_all_tags( str_replace( '&nbsp;', ' ', (string) $link['title'] ) ) );

			if ( '' === $url || '' === $title ) {
				continue;
			}

			$items[] = [
				'url'              => $url,
				'title'            => $title,
				'publication_date' => gmdate( DATE_W3C, (int) $link['modified'] ),
			];
		}

		return [
			'sitemap_url' => Core::get_sitemap_url(),
			'count'       => count( $items ),
			'items'       => $items,
		];
	}

	/**
	 * Run the regenerate sitemap ability.
	 *
	 * @return array
	 */
	public function execute_regenerate_sitemap(): array {
		CacheUtils::delete_cache();

		$sitemap = new Sitemap();
		$sitemap->build();

		return [
			'success'     => true,
			'count'       => count( $sitemap->get_data() ),
			'sitemap_url' => Core::get_sitemap_url(),
		];
	}
}
