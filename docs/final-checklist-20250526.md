# Final checklist – 2025-05-26

## Runtime & metadata
- [x] Runtime targets: PHP 7.4, WordPress 6.3.5, MySQL 5.5.5 reflected in plugin header. **File:** `bonus-hunt-guesser.php` lines 3–15.【F:bonus-hunt-guesser.php†L3-L15】
- [x] Text domain present in header; translations continue to load via `load_plugin_textdomain()` (see prior checklists). **File:** `bonus-hunt-guesser.php` lines 3–15.【F:bonus-hunt-guesser.php†L3-L15】

## Conflict & integrity checks
- [x] Searched repository for merge conflict markers; none found (`rg '<<<<<<<'`).【45146b†L1-L2】

## Testing status
- ✅ `composer install --no-interaction`【e3a5eb†L1-L35】
- ❌ `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` — 2,882 errors / 1,458 warnings remain across 42 files; PHPCBF can auto-fix 3,519 sniffs. Primary hotspots include `includes/class-bhg-shortcodes.php`, `includes/helpers.php`, `admin/class-bhg-admin.php`, `tests/bootstrap.php`, and related admin views.【857873†L1-L29】

## Required remediation (file names to modify)
- **Coding standards:** Resolve PHPCS violations in the flagged files, starting with `includes/class-bhg-shortcodes.php`, `includes/helpers.php`, `admin/class-bhg-admin.php`, and `tests/bootstrap.php`. Apply PHPCS fixes then re-run the standard until clean.【857873†L1-L29】
- **Tests:** After lint fixes, re-run PHPCS and existing PHPUnit suite to confirm compatibility with PHP 7.4 / WP 6.3.5.【857873†L1-L29】

## Frontend responsiveness & customer requirements
- [ ] Re-verify mobile responsiveness across all frontend shortcodes/tables (leaderboards, tournaments, hunts, prizes) after coding-standard fixes; adjust CSS/layouts as needed to meet “fully responsive” requirement.
- [ ] Cross-check customer feature list (leaderboard filters, prize displays, jackpots, winner limits, notifications) against implementation in `includes/class-bhg-shortcodes.php`, `admin` views, and related service classes; fill gaps where behavior diverges.

## Notes
- Keep changes limited to customer requirements; no new enhancements beyond the specified scope.
