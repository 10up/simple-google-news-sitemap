<?php
/**
 * Sitemap template
 *
 * @package  10up-google-news-sitemaps
 */

use TenupGoogleNewsSitemaps\Utils;

$links = Utils::get_cache();
$links = apply_filters( 'tenup_google_news_sitemaps_data', $links );

// Used for publication name and language.
$publication = get_bloginfo( 'name' );
$language = get_bloginfo( 'language' );

if ( empty( $links ) ) {
	$links = [];
}

header( 'Content-type: application/xml; charset=UTF-8' );
echo '<?xml version="1.0" encoding="UTF-8"?>';

?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
	<?php
	// Hook for adding data at the start of sitemap.
	do_action( 'tenup_google_news_sitemaps_start' );

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

				<news:publication_date><?php echo esc_html( date( 'Y-m-d', $link['modified'] ) ); ?></news:publication_date>
				<news:title><?php echo esc_html( $link['title'] ); ?></news:title>
			</news:news>
		</url>
	<?php

	endforeach;

	// Hook for adding data at the end of sitemap.
	do_action( 'tenup_google_news_sitemaps_end' );
	?>
</urlset>
