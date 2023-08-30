# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/), and will adhere to [Semantic Versioning](http://semver.org/).

## [Unreleased] - TBD

## [1.1.0] - 2023-08-31
### Added
- Plugin banner and image assets (props [@jeffpaul](https://github.com/jeffpaul) via [#26](https://github.com/10up/simple-google-news-sitemap/pull/26)).
- Developer documentation site generated using hookdocs (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#30](https://github.com/10up/simple-google-news-sitemap/pull/30)).

### Changed
- Modified the way `Core::ping_google()` responds when `wp_remote_get()` method doesn't return a `WP_Error` object for non-blocking requests (props [@kmgalanakis](https://github.com/kmgalanakis), [@brentvr](https://github.com/brentvr) via [#32](https://github.com/10up/simple-google-news-sitemap/pull/32)).
- Bump WordPress "tested up to" version to 6.3 props (props [@dkotter](https://github.com/dkotter) via [#35](https://github.com/10up/simple-google-news-sitemap/pull/35)).

### Fixed
- Various coding standards issues (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@akshitsethi](https://github.com/akshitsethi) via [#24](https://github.com/10up/simple-google-news-sitemap/pull/24)).
- Potential 404 for sitemap URL added to robots.txt (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@akshitsethi](https://github.com/akshitsethi) via [#25](https://github.com/10up/simple-google-news-sitemap/pull/25)).
- Prevent PHP deprecations causing linting failures (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@cadic](https://github.com/cadic) via [#28](https://github.com/10up/simple-google-news-sitemap/pull/28)).
- Wrong character removal from post title (props [@jayedul](https://github.com/jayedul), [@cadic](https://github.com/cadic) via [#31](https://github.com/10up/simple-google-news-sitemap/pull/31)).

## [1.0.2] - 2022-12-07
### Added
- Made sure all custom hooks have docblocks (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- "CodeQL scanning", "Dependency Review", and "No response" GitHub Actions (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#15](https://github.com/10up/simple-google-news-sitemap/pull/15)).

### Changed
- Updated readmes to change structure a bit and add additional information (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- Ensured our minimum PHP version was set the same across all files (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- Modify how we determine which post types to support by default (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- Move hooks from a constructor to an init method (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- Renamed our utility class to `CacheUtils` as it currently only handles caching functionality (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- Add a filter around the post statuses that we clear the cache on (props [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#14](https://github.com/10up/simple-google-news-sitemap/pull/14)).
- Simplified cache handling logic (props [@akshitsethi](https://github.com/akshitsethi), [@dkotter](https://github.com/dkotter), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#19](https://github.com/10up/simple-google-news-sitemap/pull/19)).
- Split "Push" GitHub Action into "Linting" and "Testing" GitHub Actions (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#15](https://github.com/10up/simple-google-news-sitemap/pull/15)).

### Fixed
- "Push" GitHub Action (props [@akshitsethi](https://github.com/akshitsethi), [@jeffpaul](https://github.com/jeffpaul), [@Ritesh-patel](https://github.com/Ritesh-patel) via [#10](https://github.com/10up/simple-google-news-sitemap/pull/10)).

## [1.0.1] - 2022-08-19
## Fixed
- News sitemap entry in robots.txt file (props [@Ritesh-patel](https://github.com/Ritesh-patel) via [#9](https://github.com/10up/simple-google-news-sitemap/pull/9)).

## [1.0.0] - 2022-08-17
### Added
- Initial plugin release 🎉
- Sitemap is generated on-the-fly.
- Output is saved in an option for fast reading and displaying on the front end.

[Unreleased]: https://github.com/10up/simple-google-news-sitemap/compare/trunk...develop
[1.1.0]: https://github.com/10up/simple-google-news-sitemap/compare/1.0.2..1.1.0
[1.0.2]: https://github.com/10up/simple-google-news-sitemap/compare/1.0.1..1.0.2
[1.0.1]: https://github.com/10up/simple-google-news-sitemap/compare/1.0.0..1.0.1
[1.0.0]: https://github.com/10up/simple-google-news-sitemap/releases/tag/1.0.0
