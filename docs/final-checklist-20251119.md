# Final Checklist — 2025-11-19

## Runtime targets
- PHP 7.4
- WordPress 6.3.5
- MySQL 5.5.5+
- Plugin version constant: 8.0.18 (header + `BHG_VERSION`).

## Conflict check
- `git status` clean before commit.

## Tests executed
- `composer install --no-interaction` (installs PHPCS + PHPUnit toolchain)
- `./vendor/bin/phpunit` (pass)
- `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails: 2881 errors / 1444 warnings across 41 files; major concentration in `includes/class-bhg-shortcodes.php`)

## Outstanding remediation (file names to modify)
- Resolve WordPress coding-standard violations, starting with `includes/class-bhg-shortcodes.php`, `tests/bootstrap.php`, `admin/class-bhg-admin.php`, and `includes/helpers.php`.
- Re-verify mobile/responsive behavior across all frontend tables/shortcodes after PHPCS cleanup (notably templates in `admin/views` and `includes/views`).
- Confirm shortcode filter handling for tournament-only leaderboards and timeline/status filters in `includes/class-bhg-shortcodes.php` aligns with customer requirements.
- Validate i18n/text-domain coverage for new/changed strings in `includes` and `admin` classes.

## Notes
- No merge conflicts detected; address the above remediation items to align fully with customer requirements and WordPress coding standards.
