# Customer Requirements Review – 2025-02-06 (Final Checklist)

## Runtime, Standards, and Text Domain
- ✅ Plugin header declares PHP 7.4, WordPress 6.3.5 minimum, MySQL 5.5.5 minimum, GPLv2+, and text domain `bonus-hunt-guesser`, with version set to 8.0.18. 【F:bonus-hunt-guesser.php†L3-L13】
- ✅ Text domain loads on the `plugins_loaded` hook via `load_plugin_textdomain()`. 【F:bonus-hunt-guesser.php†L400-L429】
- ❌ Coding standards: PHPCS against `phpcs.xml` reports 2,882 errors and 1,458 warnings across 42 files (WordPress-Core/Docs/Extra). 【03f34c†L3-L40】

## Leaderboard Shortcode Scope
- ✅ Leaderboard shortcode no longer exposes or normalizes a `bonushunt` attribute, keeping the shortcode tournament-only as requested. 【F:includes/class-bhg-shortcodes.php†L4389-L4445】

## Testing Performed
- ✅ `composer install --no-interaction` 【de3b36†L1-L32】
- ✅ `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails with WordPress coding-standard violations). 【03f34c†L3-L40】
- ✅ `./vendor/bin/phpunit` 【cf419d†L1-L8】

## Outstanding Actions Before Release
1) Resolve all PHPCS violations (2,882 errors / 1,458 warnings) to meet WordPress-Core/Docs/Extra standards.
2) Re-verify all frontend tables and shortcodes on mobile to ensure responsive behavior matches the customer’s requirement.
3) After code fixes, rerun PHPCS and PHPUnit to confirm a clean build.
