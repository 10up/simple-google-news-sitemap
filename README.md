# Simple Google News Sitemap

> This is a simple Google News sitemap plugin. Sitemap is generated on the fly for articles that were published in the last two days. Output is saved in cache or as a transient for fast reading/displaying on the front end.

## Usage

1. Install the plugin
2. To generate the sitemap, simply visit `<YOUR_BLOG_URL>/news-sitemap.xml`
3. The sitemap will be stored in cache for faster access with an expiry set to 2 days

### Important Points

- By default, the plugin supports all post types (inc. custom ones). To filter out supported post types, the `simple_google_news_sitemap_post_types` hook can be used. The example is shown down below.

- Cached sitemap data is set to expire after 2 days. Also, the data gets purged whenever a new post is published so that it can be included in the sitemap instantly.

- No sitemap file is stored on disk. Data is served either from the cache or from the DB if caching is not enabled.

- The plugin also pings the Google service whenever a new post is published. This behaviour can be toggled using the `simple_google_news_sitemap_ping` filter hook.

- Utilise the `simple_google_news_sitemap_start` and `simple_google_news_sitemap_end` hooks to add data to the beginning and end of the sitemap, respectively.

- Once the sitemap is generated, add it to the Google Search Console.

#### Hook Usage

Example (for filtering supported post types):
```
add_filter( 'simple_google_news_sitemap_post_types', 'filter_post_types' );

function filter_post_types( array $post_types ) {
    // Return the filtered post types
    return $post_types;
}

```

#### Troubleshooting

If `<YOUR_BLOG_URL>/news-sitemap.xml` results into 404, try saving permalinks and check the sitemap again.

## Local Setup

If using Windows, it is recommended to use WSL2 as mentioned here - https://github.com/10up/wp-local-docker-v2#windows.

### Requirements checklist

- WP Local Docker: https://github.com/10up/wp-local-docker-v2
- Composer: https://getcomposer.org

Initialise a `wp-local-docker` instance and inside the `wp-content/plugins` folder, run the following steps:

```
$  git clone git@gitlab.10up.com:10up-internal/simple-google-news-sitemap.git
$  cd simple-google-news-sitemap
$  composer install
```

Once done, go to the plugins page and activate the plugin.

### Unit Tests

All commands listed below should be run from the root of the plugin folder in your local environment, using 10updocker v2.

```
$  10updocker shell
$  cd wp-content/plugins/simple-google-news-sitemap
$  composer setup-tests:local
```

Once the above steps are completed, run `composer test` for running the unit tests.
