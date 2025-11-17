# Final Checklist – 2025-05-27

## Runtime targets
- PHP **7.4**, WordPress **6.3.5**, MySQL **5.5.5+** (reflected in plugin header).【F:bonus-hunt-guesser.php†L3-L15】
- Text domain `bonus-hunt-guesser` registered via `load_plugin_textdomain()` during plugin boot.【F:bonus-hunt-guesser.php†L409-L423】

## Changes in this pass
- Sanitized the shortcode rows-per-page setting input before saving to options to address the outstanding PHPCS sanitizer finding.【F:bonus-hunt-guesser.php†L717-L725】

## Outstanding remediation
- PHPCS still reports **2881 errors / 1458 warnings** across 42 files (primary hotspots: `includes/class-bhg-shortcodes.php`, tests/bootstrap, helpers).【5c62e2†L1-L40】
- Direct database-call warnings in `bonus-hunt-guesser.php` remain; refactor to `wpdb` helpers with escaping and optional object caching where feasible.【F:bonus-hunt-guesser.php†L1184-L1516】
- Full mobile/responsive QA for all front-end shortcodes and tables still needed per customer requirements.

## Conflict check
- `git status` clean aside from tracked changes in this branch (no merge conflicts observed).

## Tests executed
- `composer install --no-interaction`【c45d8f†L1-L36】
- `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails with remaining standard violations)【5c62e2†L1-L40】
- `./vendor/bin/phpunit`【c9ad3f†L1-L8】
