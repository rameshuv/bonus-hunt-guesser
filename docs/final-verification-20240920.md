# Bonus Hunt Guesser – Customer Requirements Verification (2024-09-20)

This audit cross-checks the plugin against the consolidated customer contract and add-on scope. The review ran `vendor/bin/phpunit` and a full `vendor/bin/phpcs` sweep on 2024-09-20.

## High-Level Result

* ✅ Core admin flows (Latest Hunts dashboard card, Bonus Hunt list/actions/results, Tournament CRUD, Users table tooling) continue to operate as delivered in v8.0.16.
* ⚠️ Winner-limit messaging and premium/regular prize assignment behave as expected in manual spot checks, but automated regression coverage is still absent.
* ❌ Major contractual items remain outstanding. The most significant are:
  * Jackpots module (admin CRUD, hunt-close integration, and four public shortcodes) – **not implemented** anywhere in the codebase.
  * Global WordPress Coding Standards compliance – `phpcs` reports 11,571 errors and 1,478 warnings across 46 PHP files.
  * Release documentation (readme/changelog) has not been updated to reflect v8.0.16 deliverables or the pending migrations.

Until these blocking items are delivered, the plugin does **not** meet the agreed-upon customer requirements for release 8.0.16.

## Detailed Checklist

| Section | Requirement | Status | Evidence / Required Action |
| --- | --- | --- | --- |
| 0. Plugin Header & Bootstrapping | Header fields (version 8.0.16, Requires at least 6.3.5, Requires PHP 7.4, Requires MySQL 5.5.5) | ✅ | `bonus-hunt-guesser.php` lines 5-15. |
|  | Text domain loading on `plugins_loaded` | ✅ | `bonus-hunt-guesser.php` lines 296-309. |
|  | **PHPCS passes (no errors)** | ❌ | `vendor/bin/phpcs --report=summary` → 11,571 errors, 1,478 warnings. All PHP source files need refactoring to WordPress standards. |
| 1. Admin Dashboard | Latest Hunts card shows 3 hunts, rows per winner, balances aligned | ✅ | `admin/views/dashboard.php`. |
| 2. Bonus Hunts | List columns/actions, edit participant list with remove links, results view with filters/prize column | ✅ | `admin/views/bonus-hunts.php`, `admin/views/bonus-hunts-results.php`. |
| 3. Tournaments | Title/description fields, expanded type selector, edit works | ✅ | `admin/views/tournaments.php`, `admin/class-bhg-tournaments-controller.php`. |
| 4. Users | Search, sorting, pagination, affiliate toggles | ✅ | `admin/views/users.php`. |
| 5. Affiliates Sync | Admin CRUD exists; automated propagation not retested | ⚠️ | Manual verification pending. |
| 6. Prizes | Admin CRUD + dual prize selectors; FE rendering (grid/carousel) | ✅ | `includes/class-bhg-prizes.php`, `assets/css/bhg-shortcodes.css`. |
| 7. Shortcodes & Pages | Existing shortcodes extended; Info & Help enumerates options | ✅ | `includes/class-bhg-shortcodes.php`, `admin/views/info-help.php`. |
| 8. Notifications | Tab renders blocks with enable/BCC; win-limit notices added | ⚠️ | Needs regression test of `includes/notifications.php`. |
| 9. Ranking & Points | Mapping editable, scope toggle, rankings highlight winners | ✅ | `includes/class-bhg-rankings.php`. |
| 10. Global CSS Panel | Global style settings injected across components | ✅ | `includes/helpers.php` (global style helpers). |
| 11. Currency System | `bhg_currency` option + helpers used globally | ✅ | `includes/helpers.php`, `includes/class-bhg-shortcodes.php`. |
| 12. Database & Migrations | Required columns/tables exist; junction table delivered | ⚠️ | Need migration replay + verification on clean install. |
| 13. Security & i18n | Escaping/sanitization present, but phpcs flags numerous direct queries requiring fixes | ⚠️ | Audit `admin` and `includes` directories. |
| 14. Backward Compatibility | Legacy helper fallbacks intact | ✅ | `includes/class-bhg-bonus-hunts-helpers.php`. |
| 15. Global UX Guarantees | Sorting/search/pagination + timeline filters present | ✅ | Tables in admin + `[bhg_hunts]` shortcode templates. |
| 16. Release & Docs | Changelog/Readme/Admin help updated for 8.0.16 | ❌ | `CHANGELOG.md`, `README.md`, `docs/customer-requirements-checklist.md` still reference prior release. |
| 17. QA Acceptance | End-to-end acceptance tests not executed; outstanding defects block sign-off | ❌ | Requires completion of tasks below. |
| Add-on: Winner Limits | Enforcement hooks + notices exist; needs automated tests | ⚠️ | `includes/class-bhg-bonus-hunts.php`, `admin/views/bonus-hunts-results.php`. |
| Add-on: Frontend Adjustments | Table headers recolored, Details column links wired | ✅ | `assets/css/bhg-shortcodes.css`, `includes/class-bhg-shortcodes.php`. |
| Add-on: Prizes Enhancements | Premium prize set + link/category controls implemented | ✅ | `includes/class-bhg-prizes.php`. |
| **Jackpot Feature** | Admin menu, CRUD, hunt-close integration, shortcodes (`bhg_jackpot_current`, `bhg_jackpot_latest`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`) | ❌ | No references in repository; must implement per requirements (see action items). |

## Blocking Action Items

1. **Implement Jackpot Module**
   * Files to create/update: `includes/class-bhg-jackpots.php`, `admin/views/jackpots.php`, `assets/css/bhg-jackpots.css`, `bonus-hunt-guesser.php` (autoload + init), `includes/class-bhg-db.php` (schema + migrations), `includes/class-bhg-models.php` (hook hunt close), `includes/class-bhg-shortcodes.php` (register new shortcodes).
   * Deliver admin CRUD, hunt selection scopes, increase-per-miss logic, hit logging, and all requested shortcodes with filters.

2. **Resolve PHPCS Violations**
   * Run `vendor/bin/phpcs` to list offenders.
   * Apply `phpcbf` where possible, then manually fix remaining issues (escaping, prepared statements, docblocks, spacing).
   * Target directories: `admin/`, `includes/`, `bonus-hunt-guesser.php`, `tests/`.

3. **Documentation & Release Updates**
   * Update `CHANGELOG.md`, `README.md`, `docs/customer-requirements-checklist.md`, and the Admin “Info & Help” screens to describe the jackpot feature, winner limits, and other v8.0.16 changes.
   * Provide upgrade notes for new DB tables/options.

4. **Regression & Acceptance Testing**
   * After delivering the above, rerun: `vendor/bin/phpunit`, `vendor/bin/phpcs`, and full manual QA (hunt closure, jackpot increments, affiliate filters, notification emails).

Until the action items are complete, the release must remain in QA and should not be shipped to the customer.
