<?php
/**
 * Sitemap template
 *
 * @package  simple-google-news-sitemap
 */

use SimpleGoogleNewsSitemap\CacheUtils;

$links = CacheUtils::get_cache();

/**
 * Filter all items that will be output in the sitemap.
 *
 * @since 1.0.0
 *
 * @param array $links Array of items to be output.
 */
$links = apply_filters( 'simple_google_news_sitemap_data', $links );

// Used for publication name and language.
$publication = get_bloginfo( 'name' );
$language    = get_bloginfo( 'language' );

if ( empty( $links ) ) {
	$links = [];
}

header( 'Content-type: application/xml; charset=UTF-8' );
echo '<?xml version="1.0" encoding="UTF-8"?>';

?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
	<?php
	/**
	 * Add extra data to the start of the sitemap.
	 *
	 * @since 1.0.0
	 */
	do_action( 'simple_google_news_sitemap_start' );

	foreach ( $links as $link ) :
		if ( empty( $link['url'] ) ) {
			continue;
		}
		?>
		<url>
			<loc><?php echo esc_url( $link['url'] ); ?></loc>
			<news:news>
				<news:publication>
					<news:name><?php echo esc_html( $publication ); ?></news:name>
					<news:language><?php echo esc_html( $language ); ?></news:language>
				</news:publication>

				<news:publication_date><?php echo esc_html( date( DATE_W3C, $link['modified'] ) ); // phpcs:ignore ?></news:publication_date>
				<news:title><?php echo esc_html( trim( $link['title'], '&nbsp;' ) ); ?></news:title>
			</news:news>
		</url>
		<?php

	endforeach;

	/**
	 * Add extra data to the end of the sitemap.
	 *
	 * @since 1.0.0
	 */
	do_action( 'simple_google_news_sitemap_end' );
	?>
</urlset>
