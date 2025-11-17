# Final QA Checklist (2024-05-22)

## Runtime & Bootstrap
- [x] Plugin header declares runtime targets (WP 6.3.5, PHP 7.4, MySQL 5.5.5), text domain, and version 8.0.18 in `bonus-hunt-guesser.php`.【F:bonus-hunt-guesser.php†L2-L15】
- [x] Text domain loads on `plugins_loaded` via `load_plugin_textdomain()`.【F:bonus-hunt-guesser.php†L400-L423】

## Coding Standards & Tests
- [ ] PHPCS clean: **fails** with 2,867 errors and 1,459 warnings; largest offender is `includes/class-bhg-shortcodes.php` (2,494 errors, 1,079 warnings).【3fb4fb†L1-L38】
- [x] PHPUnit test suite passes (12 tests).【19ea60†L1-L8】

## High-Priority Gaps vs. Customer Requirements
- [ ] Leaderboard shortcode still exposes a `bonushunt` filter/attribute that should be removed per requirements; filters default to showing bonushunt/tournament selectors together.【F:includes/class-bhg-shortcodes.php†L4386-L4404】 _File to modify: `includes/class-bhg-shortcodes.php`_
- [ ] Leaderboard table/fields still labelled with `user` and existing averaging formats; prize summary/premium prize carousel requirements are not implemented in this handler.【F:includes/class-bhg-shortcodes.php†L4386-L4425】 _File to modify: `includes/class-bhg-shortcodes.php`_
- [ ] PHPCS violations across admin views and controllers block coding-standards acceptance (e.g., `admin/views/bonus-hunts.php`, `admin/class-bhg-admin.php`, `tests/bootstrap.php`).【3fb4fb†L1-L38】 _Files to modify: see PHPCS summary list_
- [ ] Admin “Latest Hunts” dashboard renders multiple winners but needs verification against the exact column/label spec (Latest 3 hunts with winners, start/final balance, closed date).【F:admin/views/dashboard.php†L83-L200】 _File to modify: `admin/views/dashboard.php` if labels/layout need adjustment_

## Next Steps
1. Clean remaining PHPCS violations, starting with `includes/class-bhg-shortcodes.php`, `tests/bootstrap.php`, and admin view templates highlighted above.
2. Update the leaderboard shortcode to remove bonushunt filtering, rename `user` column to `username`, enforce integer display for averages, and add prize/affiliate enhancements per customer spec.
3. Reconfirm dashboard/hunt listings match the “Latest Hunts” spec and adjust labels if needed.
4. Re-run `vendor/bin/phpcs --standard=phpcs.xml --report=summary` and `vendor/bin/phpunit` after changes.
