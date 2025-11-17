# Final QA Checklist – 2025-05-23

## Runtime & Bootstrapping
- [x] Plugin header declares PHP 7.4, WordPress 6.3.5 minimums, MySQL 5.5.5, text domain, and GPLv2+ license. **File:** `bonus-hunt-guesser.php` lines 2–15.【F:bonus-hunt-guesser.php†L2-L15】
- [x] Text domain loads on `plugins_loaded` via `load_plugin_textdomain()`. **File:** `bonus-hunt-guesser.php` lines 400–423.【F:bonus-hunt-guesser.php†L400-L423】

## Coding Standards
- [ ] PHPCS (WordPress-Core/Docs/Extra) fails: 2,867 errors and 1,459 warnings across 42 files. Prioritize `includes/class-bhg-shortcodes.php` (2,494 errors, 1,079 warnings) plus other listed files before release. **Command:** `vendor/bin/phpcs --standard=phpcs.xml --report=summary`.【dfbc4a†L1-L46】
  - Primary files to clean: `includes/class-bhg-shortcodes.php`, `admin/class-bhg-admin.php`, `includes/class-bhg-bonus-hunts-helpers.php`, `includes/class-bhg-bonus-hunts.php`, `includes/helpers.php`, `tests/bootstrap.php`, and other paths in the PHPCS summary.

## Customer Requirements – Outstanding Gaps
- [ ] Admin main menu label still “Bonus Hunt” instead of requested “Dashboard”. Update the root menu title/label when registering the admin menu. **File to modify:** `admin/class-bhg-admin.php` lines 57–68.【F:admin/class-bhg-admin.php†L57-L68】
- [ ] Leaderboard shortcode continues to accept a `bonushunt` attribute and related fields, conflicting with the requirement that leaderboards target tournaments only (bonushunt dropdown removed). Remove the `bonushunt` attribute handling and adjust filters/fields accordingly. **File to modify:** `includes/class-bhg-shortcodes.php` lines 4387–4399.【F:includes/class-bhg-shortcodes.php†L4387-L4399】
- [ ] Coding standards remediation required before delivery (see above). Apply PHPCS fixes or manual adjustments per file list. **Files to modify:** see PHPCS summary.

## Tests Executed (current branch)
- ✅ `vendor/bin/phpunit`【3edf4f†L1-L8】
- ❌ `vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails with 2,867 errors, 1,459 warnings)【dfbc4a†L1-L46】
