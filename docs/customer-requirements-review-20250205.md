# Customer Requirements Review – 2025-02-05

## Runtime, Standards, and Text Domain
- ✅ Plugin header declares PHP 7.4, WordPress 6.3.5 minimum, MySQL 5.5.5 minimum, GPLv2+, and text domain `bonus-hunt-guesser`, with version set to 8.0.18. 【F:bonus-hunt-guesser.php†L3-L13】
- ✅ Text domain loads on the `plugins_loaded` hook via `load_plugin_textdomain()`. 【F:bonus-hunt-guesser.php†L400-L429】
- ❌ Coding standards: PHPCS against `phpcs.xml` reports 2,882 errors and 1,458 warnings across 42 files (WordPress-Core/Docs/Extra). 【02ee93†L1-L43】

## Leaderboard Shortcode Scope
- ❌ Leaderboard shortcode still accepts and normalizes a `bonushunt` attribute even though the requirement is “tournament only” and bonushunt dropdown should be removed. 【F:includes/class-bhg-shortcodes.php†L4389-L4442】

## Testing Performed
- ✅ `composer install --no-interaction` 【65e6d2†L1-L38】
- ✅ `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails with WordPress coding-standard violations). 【02ee93†L1-L43】
- ✅ `./vendor/bin/phpunit` 【0703ba†L1-L8】

## High-Priority Follow-Ups
1) Fix all PHPCS violations to meet WordPress-Core/Docs/Extra standards.
2) Remove bonushunt handling and dropdown options from the leaderboard shortcode so it is tournament-only, matching the customer requirement.
