# Bonus Hunt Guesser – Customer Requirements Checklist

_Status legend:_ `✅` complete, `⚠️` follow-up required, `❓` not yet verified.

## 0. Plugin Header
- ✅ Plugin header matches requested metadata (update `bonus-hunt-guesser.php`).
- ⚠️ Confirm external documentation repeats header values where applicable (`README.md`, `docs/`).

## 1. Security & Architecture
- ✅ Admin capability checks use `manage_options` across views (`admin/views/*.php`).
- ✅ Nonces applied to create/edit/delete/toggle actions (review `admin/class-bhg-admin.php`).
- ⚠️ Audit remaining direct `$wpdb` queries for `$wpdb->prepare()` usage (`admin/views/dashboard.php`, `includes/class-bhg-bonus-hunts.php`).

## 2. Database
- ✅ Schema includes required columns and defaults, including `participants_mode` and `guessing_enabled` (`includes/class-bhg-db.php`).
- ✅ Legacy tournament columns are removed via `drop_legacy_tournament_columns()` (`includes/class-bhg-db.php`).
- ⚠️ Confirm dbDelta migrations handle MySQL 5.5.5 edge cases in junction tables (`includes/class-bhg-db.php`).

## 3. Settings – Currency
- ✅ `bhg_currency` option stored and helpers `bhg_currency_symbol()`/`bhg_format_money()` implemented (`includes/helpers.php`).
- ⚠️ Verify settings page persists currency selection and sanitizes input (`admin/views/settings.php`).

## 4. Admin – Dashboard
- ✅ "Latest Hunts" card shows three hunts with multi-winner rows (`admin/views/dashboard.php`).
- ⚠️ Ensure summary counts cache or pagination won't impact performance on large datasets (`admin/views/dashboard.php`).

## 5. Admin – Bonus Hunts (`bhg-bonus-hunts`)
- ✅ List table includes Final Balance column, search, sort, pagination (`admin/views/bonus-hunts.php`).
- ✅ Actions column exposes Edit, Results, Guessing toggle with nonces (`admin/views/bonus-hunts.php`).
- ⚠️ Edit screen guess list allows deletions with nonce; confirm affiliate dropdown limited to active sites (`admin/views/bonus-hunts.php`).

## 6. Admin – Hunt Results (`bhg-bonus-hunts-results`)
- ✅ Latest closed hunt preselected with Month/Year/All filter and Prize column (`admin/views/bonus-hunts-results.php`).
- ⚠️ Validate absolute difference ordering matches acceptance criteria (`includes/class-bhg-bonus-hunts-helpers.php`).

## 7. Admin – Tournaments (`bhg-tournaments`)
- ✅ Title/Description fields present; participants mode saved to `participants_mode` (`admin/views/tournaments.php`).
- ✅ Manual hunt selector limited to current-year hunts plus linked ones (`admin/views/tournaments.php`).
- ⚠️ Re-test Close/Results actions end-to-end after schema changes (`admin/class-bhg-admin.php`).

## 8. Admin – Users (`bhg-users`)
- ✅ Search, sortable columns, pagination at 30 per page (`admin/views/users.php`).
- ✅ User profile displays affiliate yes/no per site (`admin/views/users.php`).
- ⚠️ Confirm inline guess removal hooks into `bhg_remove_guess()` securely (`admin/class-bhg-admin.php`).

## 9. Admin – Ads (`bhg-ads`)
- ✅ Actions column with Edit/Remove and nonce protection (`admin/views/advertising.php`).
- ✅ Placement dropdown includes `none` option (`admin/views/advertising.php`).
- ⚠️ Review visibility rules for affiliate/login targeting logic (`admin/views/advertising.php`).

## 10. Admin – Translations (`bhg-translations`)
- ⚠️ Validate translations admin preloads all frontend strings and saves overrides (`admin/views/translations.php`).
- ⚠️ Confirm `.mo` loading order respects overrides (`includes/helpers.php`).

## 11. Frontend Shortcodes
- ✅ Shared pagination/sorting/timeline helpers implemented (`includes/class-bhg-shortcodes.php`).
- ✅ Affiliate lights/website rendering uses green/red indicators (`includes/class-bhg-shortcodes.php`, `assets/css/admin.css`).
- ⚠️ Smoke-test each shortcode attribute combination, especially new filters (`tests/` coverage pending additions).

## 12. Page Wiring
- ⚠️ Ensure onboarding instructions create pages with correct shortcodes (document in `README.md`).

## 13. UX & Glue
- ✅ Smart login redirect via helper and Nextend integration (`includes/class-bhg-login-redirect.php`).
- ⚠️ Verify WP menu locations registered and documented for three audience types (`includes/helpers.php` / `admin/views/settings.php`).

## 14. Acceptance Checklist
- ⚠️ Re-run manual QA to confirm currency updates propagate across admin/front (`admin/views/*`, `includes/class-bhg-shortcodes.php`).

## 15. Versioning & Delivery
- ✅ Version constant and changelog updated to 8.0.14 (`bonus-hunt-guesser.php`, `CHANGELOG.md`).
- ⚠️ Prepare rollback guidance and migration guard documentation (`UPGRADE_NOTES.txt`).

## 16. Third-Party Integration
- ✅ Nextend Social Login detection guards optional features (`includes/class-bhg-login-redirect.php`).
- ⚠️ Confirm license check messaging where applicable (`README.md`).

## 17. Developer Notes
- ✅ `WP_List_Table` usage across admin grids (`admin/views/*`).
- ⚠️ Audit enqueued admin assets to ensure page-scoped loading only (`admin/class-bhg-admin.php`).

## 18. Add-On: Prizes (`bhg-prizes`)
- ⚠️ Confirm admin CRUD implemented and image sizes registered (`includes/class-bhg-prizes.php` if present).
- ⚠️ Verify hunt/tournament integration surfaces prizes in edit and frontend views (`admin/views/bonus-hunts.php`, `admin/views/tournaments.php`, `includes/class-bhg-shortcodes.php`).

## 19. Add-On: Notifications (`bhg-notifications`)
- ⚠️ Implement notifications admin tab with enable toggles and sanitization (new `admin/views/notifications.php`, handler in `admin/class-bhg-admin.php`).
- ⚠️ Add notification mailer service that sanitizes HTML with `wp_kses_post()` (`includes/class-bhg-notifications.php`).

## 20. User Shortcodes (Profile Widgets)
- ⚠️ Confirm shortcode availability and admin toggles controlling visibility (`includes/class-bhg-shortcodes.php`, `admin/views/settings.php`).

## 21. Tournament Ranking System (Points)
- ⚠️ Validate editable points table and leaderboard calculations (`includes/class-bhg-shortcodes.php`, `includes/class-bhg-bonus-hunts-helpers.php`).

## 22. Admin Refinements Recap
- ✅ Dashboard, bonus hunts, tournaments, users, affiliates refinements present (see respective views).
- ⚠️ Automate tests covering affiliate field add/remove sync (`admin/class-bhg-admin.php`, `admin/views/affiliate-websites.php`).

## 23. Shortcodes Admin (`bhg-shortcodes`)
- ⚠️ Build help screen summarizing shortcode options (new `admin/views/shortcodes.php`, register via `admin/class-bhg-admin.php`).

## 24. PHPCS & Tooling
- ✅ `phpcs.xml` enforces WordPress Core/Docs/Extra and tests pass (`phpcs.xml`).
- ⚠️ Gradually reduce baseline exclusions noted in phpcs config (`phpcs.xml`).

