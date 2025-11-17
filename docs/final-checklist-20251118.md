# Final Checklist — 2025-11-18

## Runtime targets
- PHP 7.4
- WordPress 6.3.5
- MySQL 5.5.5+
- Plugin version constant: 8.0.18 (header + `BHG_VERSION`).

## Conflict check
- `git status` clean before commit.

## Tests executed
- `./vendor/bin/phpunit` (pass)
- `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails: 2881 errors / 1444 warnings across 41 files; primary concentration in `includes/class-bhg-shortcodes.php`)

## Outstanding remediation
- Resolve remaining PHPCS violations (focus on `includes/class-bhg-shortcodes.php` plus admin/views/helpers classes) until the configured WordPress standards pass cleanly.
- Re-verify mobile/responsive layouts across all frontend shortcodes and tables after PHPCS cleanup.
- Confirm shortcode parameter handling aligns with customer filters (timeline/status toggles) after refactors.

## Notes
- No merge conflicts detected; proceed with PHPCS cleanup and responsive retest next.
