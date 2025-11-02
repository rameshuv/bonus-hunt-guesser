# Customer Requirements Checklist — Bonus Hunt Guesser v8.0.14

Status legend: ✅ Complete · ⚠️ Not verified · ❌ Missing / incomplete · ➖ Not applicable

## 0) Plugin Header & Bootstrapping
- ✅ Plugin header exposes the requested metadata (name, version 8.0.14, PHP 7.4, WP 6.3.0+, MySQL 5.5.5+, text domain, license). 【F:bonus-hunt-guesser.php†L1-L16】
- ✅ Text domain loads during `plugins_loaded`. 【F:bonus-hunt-guesser.php†L386-L404】
- ⚠️ PHPCS compliance not confirmed (sniffs not executed in this review).

## 1) Admin Dashboard (Latest Hunts)
- ✅ "Latest Hunts" card renders latest three hunts with Bonushunt, All Winners, Start Balance, Final Balance (– if open), and Closed At columns; winners show name/guess/difference chips with bold usernames. 【F:admin/views/dashboard.php†L111-L205】

## 2) Bonus Hunts (List · Edit · Results)
- ✅ List view includes Final Balance (em dash when open), Affiliate, Winners count, and actions for Edit/Close/Results plus Admin Action (Delete) and Guessing toggle. 【F:admin/views/bonus-hunts.php†L217-L288】
- ✅ Edit view restricts tournaments to active entries, exposes winners count, participant removal table with profile links, and affiliate selection. 【F:admin/views/bonus-hunts-edit.php†L100-L226】
- ❌ Results screen lacks hunt/tournament selector and timeframe filter; it only outputs a static table without controls for selecting other hunts or changing the "This Month/Year/All Time" range. 【F:admin/views/hunt-results.php†L23-L70】
- ✅ Empty state text matches "There are no winners yet." 【F:admin/views/hunt-results.php†L66-L69】
- ✅ Winners highlighted via inline style for top ranks. 【F:admin/views/hunt-results.php†L62-L66】
- ✅ Database migrations add `guessing_enabled` and `affiliate_id` columns. 【F:includes/class-bhg-db.php†L101-L113】【F:includes/class-bhg-db.php†L269-L293】

## 3) Tournaments (List · Edit)
- ✅ List screen implements search, sorting, pagination, and action buttons (Edit, Close, Results, Delete). 【F:admin/views/tournaments.php†L144-L296】
- ✅ Edit form exposes Title, Description, Participants Mode (winners/all guessers), and additional settings. 【F:admin/views/tournaments.php†L309-L380】
- ❌ Legacy `type` select remains instead of being removed as requested. 【F:admin/views/tournaments.php†L325-L349】
- ✅ Delete action present. 【F:admin/views/tournaments.php†L264-L271】
- ✅ Database migration adds `participants_mode` column. 【F:includes/class-bhg-db.php†L136-L140】【F:includes/class-bhg-db.php†L317-L323】

## 4) Users (Admin)
- ✅ Custom `WP_List_Table` supports search, sortable columns, and pagination at 30 per page with affiliate toggles per user. 【F:admin/views/users.php†L10-L42】【F:admin/class-bhg-users-table.php†L27-L256】

## 5) Affiliates (Sync)
- ⚠️ Automated verification not performed in this review; requires functional testing of affiliate CRUD syncing with user meta.

## 6) Prizes (Admin · Frontend · Shortcode)
- ⚠️ Not exhaustively validated in this pass; core admin CRUD and shortcode wiring require interactive testing beyond static review.

## 7) Shortcodes (Catalog & Pages)
- ⚠️ Shortcode catalogue and pages not fully audited in this pass; confirm options/examples and required pages exist.

## 8) Notifications
- ⚠️ Email notification tab configuration and BCC handling not runtime-tested; static code indicates templating support but needs verification.

## 9) Ranking & Points
- ⚠️ Centralized ranking service and unit tests not exercised during this review.

## 10) Global CSS / Color Panel
- ⚠️ Implementation present in assets/helpers but not validated end-to-end in this review.

## 11) Currency System
- ✅ Currency helpers provide EUR/USD toggle and formatting used across dashboard displays. 【F:bonus-hunt-guesser.php†L1029-L1046】【F:admin/views/dashboard.php†L132-L195】

## 12) Database & Migrations
- ✅ Migrations ensure `guessing_enabled`, `participants_mode`, `affiliate_id`, and related indexes; hunt↔tournament tables managed via `BHG_DB`. 【F:includes/class-bhg-db.php†L80-L205】【F:includes/class-bhg-db.php†L269-L332】
- ⚠️ Idempotent behavior and junction-table integrity not tested against live database.

## 13) Security & i18n
- ⚠️ Spot checks show nonce usage and escaping, but comprehensive security/i18n audit still pending.

## 14) Backward Compatibility
- ⚠️ Legacy data migration paths not validated in this pass.

## 15) Global UX Guarantees
- ⚠️ Sorting/search/pagination confirmed for major admin tables, but timeline filters and affiliate lights across all shortcode tables need UI testing.

## 16) Release & Docs
- ⚠️ Changelog, readme, and Info & Help updates require manual verification.

## 17) QA (Acceptance)
- ❌ Winner-limit add-on (per-user max wins with rolling periods, logging, enforcement, and admin settings) is not implemented anywhere in the codebase. 【F:includes/class-bhg-models.php†L24-L226】
- ⚠️ Remaining end-to-end acceptance scenarios (currency switch, guessing toggle behavior, prizes FE grid/carousel, notifications) were not executed in this review session.

## Add-On: Winner Limits per User
- ❌ No settings page or database log exists for per-user win limits; awarding logic (`close_bonus_hunt`) lacks rolling-window checks, so the add-on requirement is unmet. 【F:includes/class-bhg-models.php†L24-L226】

---

### Follow-up Actions
1. Implement hunt/tournament selectors and timeframe filters on the Results screen to satisfy section 2 requirements.
2. Remove or repurpose the legacy tournament "type" field per section 3 guidance.
3. Design and build the per-user winner limit feature (settings UI, logging table, enforcement) outlined in the add-on brief.
4. Execute PHPCS and functional tests for sections flagged ⚠️ to confirm compliance.
