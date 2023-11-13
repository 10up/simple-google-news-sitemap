# Simple Google News Sitemap

> A simple Google News sitemap is generated on-the-fly for articles that were published in the last two days. Output is saved in cache or as a transient for fast reading and displaying on the front end.

[![Support Level](https://img.shields.io/badge/support-beta-blueviolet.svg)](#support-level) [![Release Version](https://img.shields.io/github/release/10up/simple-google-news-sitemap.svg)](https://github.com/10up/simple-google-news-sitemap/releases/latest) ![WordPress tested up to version](https://img.shields.io/badge/WordPress-v6.4%20tested-success.svg) [![License](https://img.shields.io/github/license/10up/simple-google-news-sitemap.svg)](https://github.com/10up/simple-google-news-sitemap/blob/develop/LICENSE.md) [![Dependency Review](https://github.com/10up/simple-google-news-sitemap/actions/workflows/dependency-review.yml/badge.svg)](https://github.com/10up/simple-google-news-sitemap/actions/workflows/dependency-review.yml) [![Linting](https://github.com/10up/simple-google-news-sitemap/actions/workflows/lint.yml/badge.svg)](https://github.com/10up/simple-google-news-sitemap/actions/workflows/lint.yml) [![Test](https://github.com/10up/simple-google-news-sitemap/actions/workflows/test.yml/badge.svg)](https://github.com/10up/simple-google-news-sitemap/actions/workflows/test.yml)

## Overview

- By default, the plugin supports all post types (inc. custom ones). To filter out supported post types, the `simple_google_news_sitemap_post_types` hook can be used. The example is shown down below.

- Cached sitemap data is set to expire after 2 days. Also, the data gets purged whenever a new post is published so that it can be included in the sitemap instantly.

- No sitemap file is stored on disk. Data is served either from the cache or from the DB if caching is not enabled.

- The plugin also pings the Google service whenever a new post is published. This behaviour can be toggled using the `simple_google_news_sitemap_ping` filter hook.

- Utilise the `simple_google_news_sitemap_start` and `simple_google_news_sitemap_end` hooks to add data to the beginning and end of the sitemap, respectively.

- Once the sitemap is generated, add it to the Google Search Console.

## Requirements

- PHP 7.4+
- [WordPress](http://wordpress.org/) 5.7+

## Usage

1. Install the plugin. You can upload and install the archived (zip) plugin via the WordPress dashboard (`Plugins` > `Add New` -> `Upload Plugin`) or manually inside of the `wp-content/plugins` directory, and activate on the Plugins dashboard.
2. To generate the sitemap, simply visit `<YOUR_BLOG_URL>/news-sitemap.xml`.
3. The sitemap will be stored in cache for faster access with an expiry set to 2 days.

### Hook Usage

Example (for filtering supported post types):

```php
add_filter( 'simple_google_news_sitemap_post_types', 'filter_post_types' );

function filter_post_types( array $post_types ) {
    // Return the filtered post types
    return $post_types;
}
```

### Troubleshooting

If `<YOUR_BLOG_URL>/news-sitemap.xml` results in a 404, try saving permalinks and check the sitemap again.

## Developers

### Local Requirements

- [WP Local Docker](https://github.com/10up/wp-local-docker-v2)
- [Composer](https://getcomposer.org)

Initialise a `wp-local-docker` instance and inside the `wp-content/plugins` folder, run the following steps:

```console
git clone git@gitlab.10up.com:10up-internal/simple-google-news-sitemap.git
cd simple-google-news-sitemap
composer install
```

Once done, go to the plugins page and activate the plugin.

If using Windows, it is recommended to [use WSL2 as mentioned here](https://github.com/10up/wp-local-docker-v2#windows).

### Unit Tests

All commands listed below should be run from the root of the plugin folder in your local environment, using 10updocker v2.

```console
10updocker shell
cd wp-content/plugins/simple-google-news-sitemap
composer setup-tests:local
```

Once the above steps are completed, run `composer test` for running the unit tests.

## Support Level

**Beta:** This project is quite new and we're not sure what our ongoing support level for this will be. Bug reports, feature requests, questions, and pull requests are welcome. If you like this project please let us know, but be cautious using this in a Production environment!

## Changelog

A complete listing of all notable changes to Simple Google News Sitemap are documented in [CHANGELOG.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CHANGELOG.md).

## Contributing

Please read [CODE_OF_CONDUCT.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CODE_OF_CONDUCT.md) for details on our code of conduct, [CONTRIBUTING.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CONTRIBUTING.md) for details on the process for submitting pull requests to us, and [CREDITS.md](https://github.com/10up/simple-google-news-sitemap/blob/develop/CREDITS.md) for a list of maintainers, contributors, and libraries used in this repository.

## Like what you see?

<a href="http://10up.com/contact/"><img src="https://10up.com/uploads/2016/10/10up-Github-Banner.png" width="850"></a>
