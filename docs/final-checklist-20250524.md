# Final QA Checklist – 2025-05-24

## Runtime & Bootstrapping
- [x] Plugin header declares PHP 7.4, WordPress 6.3.5 minimums, MySQL 5.5.5, text domain, and GPLv2+ license. **File:** `bonus-hunt-guesser.php` lines 2–15.【F:bonus-hunt-guesser.php†L2-L15】
- [x] Text domain loads on `plugins_loaded` via `load_plugin_textdomain()`. **File:** `bonus-hunt-guesser.php` lines 400–423.【F:bonus-hunt-guesser.php†L400-L423】

## Coding Standards
- [ ] PHPCS (WordPress-Core/Docs/Extra) currently fails: 2,867 errors and 1,459 warnings across 42 files. Primary offenders include `includes/class-bhg-shortcodes.php` (2,494 errors, 1,079 warnings) and other admin/helper classes. **Command:** `vendor/bin/phpcs --standard=phpcs.xml --report=summary`.【831eb7†L1-L40】

## Customer Requirements – Outstanding Gaps
- [ ] Admin main menu label still shows “Bonus Hunt” instead of requested “Dashboard”. Update the root menu title/label when registering the admin menu. **File to modify:** `admin/class-bhg-admin.php` lines 57–68.【F:admin/class-bhg-admin.php†L57-L68】
- [ ] Leaderboard shortcode continues to accept a `bonushunt` attribute even though the dropdown should be removed and leaderboards must target tournaments only. Remove `bonushunt` handling and adjust filters accordingly. **File to modify:** `includes/class-bhg-shortcodes.php` lines 4387–4399.【F:includes/class-bhg-shortcodes.php†L4387-L4399】
- [ ] Complete coding standards remediation before delivery (see PHPCS summary for per-file counts). **Files to modify:** see PHPCS summary.【831eb7†L1-L40】

## Tests Executed (current branch)
- ✅ `vendor/bin/phpunit`【5d6b45†L1-L8】
- ❌ `vendor/bin/phpcs --standard=phpcs.xml --report=summary` (fails with 2,867 errors, 1,459 warnings)【831eb7†L1-L40】
