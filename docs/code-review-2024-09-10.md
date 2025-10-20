# Bonus Hunt Guesser – Compliance & Coding-Standards Review (2024-09)

## 1. Coding standards audit

Running the project-level WordPress Coding Standards sniffers currently reports thousands of violations across the test suite and plugin runtime code. A single `vendor/bin/phpcs` run shows 173+ issues in `tests/CloseHuntTest.php`, and similar findings exist for most PHP files (missing docblocks, space indentation instead of tabs, unsanitized superglobals, uncached direct `$wpdb` calls, etc.).【335872†L1-L60】

### Required actions (by file)
- `tests/CloseHuntTest.php`, `tests/bootstrap.php`, `tests/support/class-mock-wpdb.php`: add file/class docblocks, replace space-indentation with tabs, and normalise associative arrays to per-line entries.【335872†L1-L60】
- `bonus-hunt-guesser.php`: sanitise all user-supplied input (`$_POST`), convert equality checks to Yoda conditions, and replace raw `$wpdb->get_results( "$sql" )` with prepared statements or cached helpers where possible.【91bb14†L1-L26】
- `includes/helpers.php`, `includes/class-bhg-shortcodes.php`, `includes/class-bhg-bonus-hunts.php`, `includes/class-bhg-ads.php`: add missing docblocks, move all direct SQL through `$wpdb->prepare()` (or add `// phpcs:ignore` with justification when table-name interpolation is unavoidable), and escape output consistently.【ead8b8†L1-L55】【ead8b8†L56-L120】
- `admin/views/*.php` templates: replace inline SQL, ensure all pagination/search parameters pass through `absint()/sanitize_text_field()`, and add nonces where `$_GET`/`$_POST` are processed (e.g. `admin/views/hunts-list.php`, `admin/views/tournaments.php`).【ead8b8†L121-L170】【2ba87a†L19-L148】
- `uninstall.php`: wrap raw `DROP TABLE` calls with `$wpdb->prepare()` or document via `// phpcs:ignore` (WordPress discourages schema changes without explicit context).【ead8b8†L171-L200】

## 2. Customer requirement verification

| Requirement | Status | Notes |
|-------------|--------|-------|
| Prizes admin CRUD, categories, images, CSS, active toggle | ✅ Implemented in `admin/views/prizes.php`; supports title, description, category select, three image slots, CSS fields, and “Active” checkbox.【F:admin/views/prizes.php†L45-L177】 |
| Bonus Hunt editor selects multiple prizes | ⚠️ Requires verification in `admin/views/bonus-hunts-edit.php` – ensure prize multi-select persists and filters frontend output; add integration tests to confirm.
| Dashboard renamed to “Dashboard” and shows latest hunts with winners/differences | ✅ `admin/class-bhg-admin.php` overrides submenu text and dashboard view renders “Latest Hunts” table listing winners with guess differences.【F:admin/class-bhg-admin.php†L43-L72】【F:admin/views/dashboard.php†L38-L163】 |
| Bonus Hunts list includes Results button, Final Balance column, and actions split from Delete | ✅ Buttons rendered in `admin/views/bonus-hunts.php`; ensure delete remains nonce-protected.【F:admin/views/bonus-hunts.php†L204-L273】 |
| Tournaments admin provides title/description fields, participants mode dropdown, results/close buttons, delete column | ✅ Implemented with sortable list, bulk actions, and edit form fields in `admin/views/tournaments.php`.【F:admin/views/tournaments.php†L200-L358】 |
| Users admin adds search, sort, pagination | ⚠️ Confirm `admin/views/users.php` (or equivalent) implements 30-per-page pagination and query sanitisation; adjust if missing.
| Shortcodes registered for prizes, hunts, tournaments, leaderboards, user dashboards | ✅ All aliases registered in `includes/class-bhg-shortcodes.php`; styling helper enqueues shared CSS and inline theme variables.【F:includes/class-bhg-shortcodes.php†L41-L92】 |
| Notifications admin tab with winner/tournament/hunt email templates & toggles | ✅ Form and save handler present in `admin/views/notifications.php` and `bonus-hunt-guesser.php` (review for sanitisation per Section 1). |
| Tournament ranking logic & points editor | ⚠️ Verify backend recomputation (`includes/class-bhg-tournaments-controller.php`) honours configurable points and highlights winners; add automated coverage.

## 3. Recommended next steps

1. **Code-style remediation sprint**: Apply `phpcbf` for tab alignment, then manually resolve remaining sniffs—focus on sanitising inputs and ensuring all queries use `prepare()` with cached lookups where viable.
2. **Security hardening**: Add `check_admin_referer()`/nonces around every admin action form (bonus hunts, tournaments, ads, prizes), and escape all echoed data in templates.
3. **Requirement validation tests**: Expand PHPUnit coverage to exercise prize assignment to hunts, dashboard winner display, and notification toggles.
4. **Documentation**: Update README/CHANGELOG once corrections are applied, and document shortcode usage (incl. `[bhg_prizes]`, `[bhg_my_bonushunts]`, etc.) for customer hand-off.

Please address the file-specific fixes above before marking the release as customer-ready.
