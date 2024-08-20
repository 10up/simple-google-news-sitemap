=== Simple Google News Sitemap ===
Contributors:      10up, jeffpaul, dkotter, akshitsethi, ritteshpatel, brentvr
Tags:              sitemap, Google News
Tested up to:      6.6
Stable tag:        1.1.1
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A simple Google News sitemap is generated on-the-fly for articles that were published in the last two days.

== Description ==

A simple Google News sitemap is generated on-the-fly for articles that were published in the last two days. Output is saved in cache or as a transient for fast reading and displaying on the front end.

== Overview ==

- By default, the plugin supports all post types (inc. custom ones). To filter out supported post types, the `simple_google_news_sitemap_post_types` hook can be used. The example is shown down below.

- Cached sitemap data is set to expire after 2 days. Also, the data gets purged whenever a new post is published so that it can be included in the sitemap instantly.

- No sitemap file is stored on disk. Data is served either from the cache or from the DB if caching is not enabled.

- The plugin also pings the Google service whenever a new post is published. This behaviour can be toggled using the `simple_google_news_sitemap_ping` filter hook.

- Utilise the `simple_google_news_sitemap_start` and `simple_google_news_sitemap_end` hooks to add data to the beginning and end of the sitemap, respectively.

- Once the sitemap is generated, add it to the Google Search Console.

== Requirements ==

- PHP 7.4+
- [WordPress](http://wordpress.org/) 5.7+

== Usage ==

1. Install the plugin. You can upload and install the archived (zip) plugin via the WordPress dashboard (`Plugins` > `Add New` -> `Upload Plugin`) or manually inside of the `wp-content/plugins` directory, and activate on the Plugins dashboard.
2. To generate the sitemap, simply visit `<YOUR_BLOG_URL>/news-sitemap.xml`.
3. The sitemap will be stored in cache for faster access with an expiry set to 2 days.

= Hook Usage =

Example (for filtering supported post types):

`
add_filter( 'simple_google_news_sitemap_post_types', 'filter_post_types' );

function filter_post_types( array $post_types ) {
    // Return the filtered post types
    return $post_types;
}
`

= Troubleshooting =

If `<YOUR_BLOG_URL>/news-sitemap.xml` results into 404, try saving permalinks and check the sitemap again.

= Developers =

== Local Requirements ==

- [WP Local Docker](https://github.com/10up/wp-local-docker-v2)
- [Composer](https://getcomposer.org)

Initialise a `wp-local-docker` instance and inside the `wp-content/plugins` folder, run the following steps:

`
git clone git@gitlab.10up.com:10up-internal/simple-google-news-sitemap.git
cd simple-google-news-sitemap
composer install
`

Once done, go to the plugins page and activate the plugin.

If using Windows, it is recommended to [use WSL2 as mentioned here](https://github.com/10up/wp-local-docker-v2#windows).

= Unit Tests =

All commands listed below should be run from the root of the plugin folder in your local environment, using 10updocker v2.

`
10updocker shell
cd wp-content/plugins/simple-google-news-sitemap
composer setup-tests:local
`

Once the above steps are completed, run `composer test` for running the unit tests.

== Support Level ==

**Beta:** This project is quite new and we're not sure what our ongoing support level for this will be. Bug reports, feature requests, questions, and pull requests are welcome. If you like this project please let us know, but be cautious using this in a Production environment!

== Contributing ==

Please read [CODE_OF_CONDUCT.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CODE_OF_CONDUCT.md) for details on our code of conduct, [CONTRIBUTING.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CONTRIBUTING.md) for details on the process for submitting pull requests to us, and [CREDITS.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CREDITS.md) for a list of maintainers, contributors, and libraries used in this repository.

== Changelog ==

= 1.1.1 - 2024-08-20 =
* **Changed:** Bump WordPress "tested up to" version to 6.6 (props [@QAharshalkadu](https://github.com/QAharshalkadu), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#37](https://github.com/10up/simple-google-news-sitemap/pull/37), [#40](https://github.com/10up/simple-google-news-sitemap/pull/40), [#42](https://github.com/10up/simple-google-news-sitemap/pull/42)).
* **Changed:** Bump WordPress minimum from 5.7 to 6.4 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#40](https://github.com/10up/simple-google-news-sitemap/pull/40), [#42](https://github.com/10up/simple-google-news-sitemap/pull/42)).
* **Developer:** Replaced `lee-dohm/no-response` with `actions/stale` to help with closing no-response/stale issues (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#39](https://github.com/10up/simple-google-news-sitemap/pull/39)).

= 1.1.0 - 2023-08-31 =
* **Added:** Plugin banner and image assets (props [Brooke Campbell](https://www.linkedin.com/in/brookecampbelldesign/), [@jeffpaul](https://github.com/jeffpaul) via [#26](https://github.com/10up/simple-google-news-sitemap/pull/26)).
* **Added:** Developer documentation site generated using hookdocs (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#30](https://github.com/10up/simple-google-news-sitemap/pull/30)).
* **Changed:** Modified the way `Core::ping_google()` responds when `wp_remote_get()` method doesn't return a `WP_Error` object for non-blocking requests (props [@kmgalanakis](https://github.com/kmgalanakis), [@brentvr](https://github.com/brentvr) via [#32](https://github.com/10up/simple-google-news-sitemap/pull/32)).
* **Changed:** Bump WordPress "tested up to" version to 6.3 (props [@dkotter](https://github.com/dkotter) via [#35](https://github.com/10up/simple-google-news-sitemap/pull/35)).
* **Fixed:** Various coding standards issues (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@akshitsethi](https://github.com/akshitsethi) via [#24](https://github.com/10up/simple-google-news-sitemap/pull/24)).
* **Fixed:** Potential 404 for sitemap URL added to robots.txt (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@akshitsethi](https://github.com/akshitsethi) via [#25](https://github.com/10up/simple-google-news-sitemap/pull/25)).
* **Fixed:** Prevent PHP deprecations causing linting failures (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@cadic](https://github.com/cadic) via [#28](https://github.com/10up/simple-google-news-sitemap/pull/28)).
* **Fixed:** Wrong character removal from post title (props [@jayedul](https://github.com/jayedul), [@cadic](https://github.com/cadic) via [#31](https://github.com/10up/simple-google-news-sitemap/pull/31)).

[View historical changelog details here](https://github.com/10up/simple-google-news-sitemap/blob/develop/CHANGELOG.md).
