# Bonus Hunt Guesser — Verification Report (2025-01-23)

Commit audited: `8eb2f7a63c54ec23621e6fb303b5348e5892fad4`

## Executive Summary

- Core admin panels for hunts, tournaments, users, affiliates, advertising, notifications, and dashboard widgets continue to cover the contracted behaviours, including multi-winner handling, participant pruning, sortable/paginated tables, affiliate flags, Details column links, and shortcode catalogue updates.【F:admin/views/dashboard.php†L33-L214】【F:admin/views/bonus-hunts.php†L33-L253】【F:admin/views/bonus-hunts-edit.php†L120-L252】【F:admin/views/bonus-hunts-results.php†L26-L214】【F:admin/views/tournaments.php†L132-L352】【F:admin/views/users.php†L12-L43】【F:admin/class-bhg-users-table.php†L27-L220】【F:admin/views/affiliate-websites.php†L39-L179】【F:admin/views/advertising.php†L180-L222】【F:admin/views/notifications.php†L16-L101】【F:admin/views/shortcodes.php†L12-L177】【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:assets/css/bhg-shortcodes.css†L509-L546】
- WordPress Coding Standards compliance is still outstanding. `vendor/bin/phpcs --standard=phpcs.xml --report=summary` reports 10,581 errors and 1,428 warnings across 46 files, blocking the “PHPCS passes (no errors)” requirement.【1f6a31†L1-L41】
- The jackpot add-on remains unimplemented—repository-wide search for “jackpot” only returns documentation, with no schema, controller, or shortcode code present.【a7aa67†L1-L60】【F:includes/class-bhg-db.php†L93-L260】
- Prizes enhancement add-on items (prize link field, category link toggle, click-behaviour selector, carousel sizing controls, responsive image sizing logic, heading suppression) are missing from the prize modal, model, and shortcode renderer, leaving that scope incomplete.【F:admin/views/prizes.php†L120-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】
- Release documentation is stale. `CHANGELOG.md` ends at v8.0.14, so the v8.0.16 release notes and migration guidance have not been produced.【F:CHANGELOG.md†L2-L34】

Automated tests: `vendor/bin/phpunit` passes (11 tests, 80 assertions). `vendor/bin/phpcs --standard=phpcs.xml --report=summary` fails as noted above.【b2cd33†L1-L8】【1f6a31†L1-L41】

## Detailed Requirement Audit

| Section | Status | Notes |
| --- | --- | --- |
| 0. Header & PHPCS | ⚠️ | Header metadata matches except `Requires at least` is set to 6.3.5 instead of the requested 6.3.0, and PHPCS still reports 10k+ violations that must be resolved.【F:bonus-hunt-guesser.php†L3-L14】【1f6a31†L1-L41】 |
| 1. Admin Dashboard | ✅ | “Latest Hunts” shows three hunts with per-winner rows, bold usernames, formatted balances, and closed timestamps.【F:admin/views/dashboard.php†L33-L205】 |
| 2. Bonus Hunts | ✅ | List view surfaces Final Balance/Affiliate columns with Edit/Results/Admin Action toggles; edit view allows configurable winner counts and participant removal with profile links; results view defaults to latest closed hunt, supports filters, and highlights winners.【F:admin/views/bonus-hunts.php†L33-L253】【F:admin/views/bonus-hunts-edit.php†L120-L252】【F:admin/views/bonus-hunts-results.php†L26-L214】 |
| 3. Tournaments | ✅ | Admin table includes title/description/type (with quarterly/alltime) plus pagination/sorting; edit form saves correctly.【F:admin/views/tournaments.php†L132-L352】 |
| 4. Users Admin | ✅ | Custom `WP_List_Table` offers search, sortable columns, 30-per-page pagination, affiliate toggles, and profile links.【F:admin/views/users.php†L12-L43】【F:admin/class-bhg-users-table.php†L27-L220】 |
| 5. Affiliates Sync | ✅ | Affiliate website CRUD lists existing sites with edit/remove actions and form to add/update entries tied to user toggles.【F:admin/views/affiliate-websites.php†L39-L179】 |
| 6. Prizes Module | ⚠️ | Core CRUD, CSS controls, and dual prize selectors exist, but required enhancements (prize link field, category link toggle, click behaviour selector, carousel image limits, responsive sizing, heading suppression) are absent.【F:admin/views/prizes.php†L120-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】 |
| 7. Shortcodes & Pages | ✅ | “Info & Help” documents all shortcodes, and `[bhg_hunts]` exposes the Details column with Guess Now / Show Results links per status, alongside white header styling.【F:admin/views/shortcodes.php†L12-L177】【F:includes/class-bhg-shortcodes.php†L2000-L2062】【F:assets/css/bhg-shortcodes.css†L509-L546】 |
| 8. Notifications | ✅ | Admin tab includes Winner/Tournament/Bonushunt sections with enable toggles, HTML bodies, BCC fields; backend normalises BCC and dispatches via `wp_mail()` with headers filters.【F:admin/views/notifications.php†L16-L101】【F:includes/notifications.php†L18-L214】 |
| 9. Ranking & Points | ✅ | Winner logging enforces rolling win limits, records eligibility, and triggers tournament recalculations when needed.【F:includes/class-bhg-models.php†L243-L355】 |
| 10. Global CSS Panel | ✅ | Settings provide typography/color controls applied across public assets and enqueued front end styles.【F:admin/views/settings.php†L171-L214】【F:bonus-hunt-guesser.php†L360-L384】 |
| 11. Currency System | ✅ | Settings expose EUR/USD toggle, and helpers wrap currency symbol/formatting for consistent output.【F:admin/views/settings.php†L76-L108】【F:includes/helpers.php†L967-L987】 |
| 12. Database & Migrations | ⚠️ | Core tables cover hunts, tournaments, prizes, winners, hunt↔tournament maps, etc., but no jackpot tables or migrations exist.【F:includes/class-bhg-db.php†L93-L260】【a7aa67†L1-L60】 |
| 13. Security & i18n | ⚠️ | Sanitisation/escapes present in many views, yet PHPCS highlights numerous nonce/docblock/escaping gaps that must be addressed to satisfy coding standards.【1f6a31†L1-L41】 |
| 14. Backward Compatibility | ✅ | DB installer remains idempotent, ensuring legacy installs pick up schema updates safely.【F:includes/class-bhg-db.php†L262-L359】 |
| 15. Global UX Guarantees | ✅ | Admin/shortcode tables deliver sorting, search, pagination, timeline filters, and affiliate lights with white header link styling.【F:admin/views/bonus-hunts.php†L33-L218】【F:admin/views/tournaments.php†L132-L232】【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:assets/css/bhg-shortcodes.css†L509-L546】 |
| 16. Release & Docs | ❌ | `CHANGELOG.md` stops at 8.0.14—no 8.0.16 release entry, migration notes, or updated help content have been published.【F:CHANGELOG.md†L2-L34】 |
| 17. QA (Acceptance) | ⚠️ | PHPUnit passes, but PHPCS failure plus missing jackpot and prize enhancements block acceptance sign-off.【b2cd33†L1-L8】【1f6a31†L1-L41】 |
| Add-on: Winner Limits | ✅ | Settings store per-type caps/periods and awarding logic enforces eligibility while tracking official/ineligible winners.【F:admin/views/settings.php†L109-L147】【F:includes/class-bhg-models.php†L243-L355】 |
| Add-on: Frontend Adjustments | ✅ | Table header links render white and `[bhg_hunts]` includes the Details column with status-aware CTAs.【F:assets/css/bhg-shortcodes.css†L509-L546】【F:includes/class-bhg-shortcodes.php†L2000-L2062】 |
| Add-on: Prizes Enhancements | ❌ | Enhancement fields/settings (links, click actions, carousel limits, responsive sizing, heading suppression) have not been implemented.【F:admin/views/prizes.php†L120-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】 |
| Add-on: Jackpot Module | ❌ | No jackpot schema, admin UI, hunt-close integration, or shortcodes exist in the codebase; only documentation references remain.【a7aa67†L1-L60】 |

## Remaining Work Items

1. **Resolve PHPCS violations** — Apply WordPress Coding Standards fixes across bootstrap, uninstall script, controllers, templates, helpers, and tests until the phpcs summary is clean.【1f6a31†L1-L41】
2. **Finish the prizes enhancement add-on** — Extend prize CRUD, database model, shortcode renderer, and assets to introduce link fields, category link toggles, click-action selectors, carousel sizing/limit controls, responsive image logic, and optional heading suppression.【F:admin/views/prizes.php†L120-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】
3. **Implement the jackpot module** — Add migrations, admin CRUD, hunt-close integration, and the four mandated shortcodes (`bhg_jackpot_current`, `bhg_jackpot_latest`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`).【a7aa67†L1-L60】【F:includes/class-bhg-db.php†L93-L260】
4. **Update release documentation** — Refresh `CHANGELOG.md`, `README.md`, and Admin “Info & Help” content to describe the 8.0.16 feature set and any new migrations once outstanding work is complete.【F:CHANGELOG.md†L2-L34】【F:admin/views/shortcodes.php†L12-L177】

## Test Log

- `vendor/bin/phpunit` *(passes: 11 tests, 80 assertions).*【b2cd33†L1-L8】
- `vendor/bin/phpcs --standard=phpcs.xml --report=summary` *(fails: 10,581 errors, 1,428 warnings across 46 files).*【1f6a31†L1-L41】
