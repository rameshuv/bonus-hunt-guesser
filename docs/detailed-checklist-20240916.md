# Bonus Hunt Guesser – Detailed Delivery Checklist (2024-09-16)

> **Superseded:** See `docs/final-checklist-20240917.md` for the most recent compliance snapshot covering all remaining gaps.

Status legend:

* ✅ — Requirement fully satisfied and verified.
* ⚠️ — Requirement partially satisfied or requires more QA.
* ❌ — Requirement missing or known to be non-compliant.

Each requirement below reflects the consolidated customer contract for version **8.0.16**. Where available, notes reference the implementing files and any follow-up actions.

## 0. Plugin Bootstrap & Tooling

| Requirement | Status | Notes |
| --- | --- | --- |
| Plugin header matches contract (metadata + version 8.0.16) | ✅ | `bonus-hunt-guesser.php` exposes version 8.0.16 with the agreed WordPress/PHP/MySQL requirements. |
| Text domain loads on `plugins_loaded` | ✅ | Loader still hooks `load_plugin_textdomain()` during boot. |
| PHPCS (WordPress Core/Docs/Extra) passes with no errors | ❌ | Repository-level run continues to fail because of legacy spacing/indentation violations across admin/controllers/tests. |

## 1. Admin Dashboard – “Latest Hunts”

| Requirement | Status | Notes |
| --- | --- | --- |
| Card lists latest 3 hunts with Title, Winners (+guess/+diff), Start/Final balance, Closed At | ⚠️ | Template outputs requested columns, but needs QA with real data. |
| Each winner rendered on its own row with bold username | ⚠️ | Logic implemented; visual verification pending. |
| Start/Final balance left-aligned | ⚠️ | Default table alignment appears left but needs confirmation after styling review. |

## 2. Bonus Hunts (Admin List/Edit/Results)

| Requirement | Status | Notes |
| --- | --- | --- |
| List includes Final Balance column (shows “–” if open) and Affiliate column | ⚠️ | Columns wired up; confirm formatting. |
| List actions: Edit, Results, Admin Delete, Enable/Disable Guessing | ⚠️ | Actions registered; regression test required. |
| Edit screen: tournament multiselect limited to active tournaments | ✅ | Query filters inactive tournaments. |
| Edit screen: winners count configurable | ✅ | `winners_count` persisted in hunts table. |
| Participants list with remove action and profile links | ⚠️ | UI renders participants; verify delete flow and capability checks. |
| Results view defaults to latest closed hunt and supports selectors | ⚠️ | Data layer in `bonus-hunts-results.php`; manual QA pending. |
| Results empty state message | ✅ | “There are no winners yet” copy present. |
| Time filter: This Month (default) / This Year / All Time | ⚠️ | Filter options exist; confirm queries return expected data. |
| Winners highlighted (green + bold), alternating row colors, Prize column | ⚠️ | Styles exist but require UI sign-off. |
| Database columns `guessing_enabled` & `affiliate_id` enforced | ✅ | `BHG_DB::create_tables()` ensures fields on migrations. |
| Dual prize sets (regular + premium) selectable in admin | ✅ | Add/edit forms expose both selectors and persist via `BHG_Prizes`. |
| Affiliate winners see premium prize set above regular prizes | ⚠️ | Frontend logic toggles premium display for affiliates; needs user acceptance testing. |

## 3. Tournaments (Admin)

| Requirement | Status | Notes |
| --- | --- | --- |
| Title and description fields available | ✅ | Edit view includes both fields. |
| Type options include quarterly/all time; legacy period removed | ⚠️ | Options added, but migration of existing data still needs verification. |
| Participants mode toggle (winners only | all guessers) | ✅ | Stored in `participants_mode`. |
| Actions: Edit, Results, Close, Admin Delete | ⚠️ | Buttons exposed; confirm capabilities. |
| Database column `participants_mode` | ✅ | Added during migrations. |

## 4. Users (Admin)

| Requirement | Status | Notes |
| --- | --- | --- |
| Search by username/email | ✅ | `BHG_Users_Table` integrates `WP_User_Query` search argument. |
| Sortable table columns | ⚠️ | Sorting metadata present but requires QA. |
| Pagination (30 per page) | ✅ | List table enforces 30 item pagination. |
| Profile shows affiliate toggles per affiliate website | ⚠️ | Fields rendered; confirm persistence. |

## 5. Affiliates (Sync)

| Requirement | Status | Notes |
| --- | --- | --- |
| Adding/removing affiliate websites syncs user profile fields | ⚠️ | Helper functions exist but need integration test coverage. |
| Frontend affiliate lights and optional website display | ✅ | Shortcode helpers render affiliate dots and names. |

## 6. Prizes (Admin + Frontend + Shortcode)

| Requirement | Status | Notes |
| --- | --- | --- |
| CRUD supports title, description, category, image, CSS, active flag | ✅ | `BHG_Prizes` handlers persist these properties. |
| Three image sizes (small/medium/big) including large image uploads | ⚠️ | Fields exist; need validation for 1200×800 PNG support. |
| Hunt edit selects 1+ prizes (regular and premium) | ✅ | Admin form persists both sets. |
| Frontend renders grid/carousel with dots/arrows and fallback | ⚠️ | Rendering logic present; cross-device QA outstanding. |
| Shortcode `[bhg_prizes]` parameters (category, design, size, active) | ⚠️ | Option parsing implemented; add automated tests. |
| Premium prize set display rules | ⚠️ | Affiliate gating logic present; verify with affiliate and non-affiliate accounts. |
| Dual prize sets per winner in results view | ⚠️ | Admin results highlight premium prizes but requires review. |

## 7. Shortcodes Catalog & Core Pages

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin “Info & Help” enumerates all shortcodes with examples | ⚠️ | Partial coverage; documentation page needs expansion. |
| Existing shortcodes remain supported (`[bhg_user_profile]`, `[bhg_guess_form]`, etc.) | ✅ | No regressions detected. |
| `[bhg_user_guesses]`: difference column after final balance | ⚠️ | Logic present; verify formatting. |
| `[bhg_hunts]`: winners count + Details column with contextual links | ⚠️ | Column generated; ensure Guess Now/Show Results URLs valid. |
| `[bhg_tournaments]`: updated columns and naming | ⚠️ | Implementation mostly done; cross-check type removal. |
| `[bhg_leaderboards]`: metrics for Times Won, Avg hunt/tournament positions | ⚠️ | Calculations exist; add unit tests. |
| `[bhg_advertising]`: placement="none" for shortcode-only | ✅ | Admin and rendering support option. |
| Required pages (Active Hunt, All Hunts, etc.) auto-created with override metabox | ⚠️ | Page scaffolding script present; confirm on activation. |

## 8. Notifications

| Requirement | Status | Notes |
| --- | --- | --- |
| Winners/Tournament/Bonushunt blocks with Title, HTML Description, BCC, enable toggle | ⚠️ | Settings exist but need UI verification. |
| Notifications use `wp_mail()` with BCC honored | ✅ | Implementation leverages `wp_mail()` and includes BCC header handling. |

## 9. Ranking & Points

| Requirement | Status | Notes |
| --- | --- | --- |
| Editable default mapping (25/15/10/5/4/3/2/1) | ⚠️ | Settings stored but require admin QA. |
| Scope toggle (active/closed/all hunts) | ⚠️ | Option recorded; validate calculations. |
| Only winners accrue points | ⚠️ | Logic attempts to enforce; add regression coverage. |
| Backend + frontend rankings highlight winners + Top 3 | ⚠️ | Styling rules exist; needs UX sign-off. |
| Centralized service + unit tests | ⚠️ | Tests exist but limited in coverage. |

## 10. Global CSS / Color Panel

| Requirement | Status | Notes |
| --- | --- | --- |
| Global typography and color controls apply to shared components | ⚠️ | Settings persisted; verify front-end application. |

## 11. Currency System

| Requirement | Status | Notes |
| --- | --- | --- |
| Setting `bhg_currency` (EUR/USD) stored | ✅ | Option available in settings. |
| Helpers `bhg_currency_symbol()` and `bhg_format_money()` implemented | ✅ | Helper functions defined in bootstrap. |
| All monetary outputs use helpers | ⚠️ | Majority updated; run audit to confirm no direct currency formatting remains. |

## 12. Database & Migrations

| Requirement | Status | Notes |
| --- | --- | --- |
| Columns `guessing_enabled`, `participants_mode`, `affiliate_id` | ✅ | Migration ensures they exist. |
| Junction table for hunt ↔ tournament mapping | ✅ | `bhg_tournaments_hunts` table maintained. |
| Idempotent `dbDelta()` with keys/indexes for all tables | ⚠️ | Core tables covered; new jackpot tables pending (see ❌ below). |
| Dual prize mapping table with prize type handling | ✅ | `bhg_hunt_prizes` tracks prize type. |

## 13. Security & i18n

| Requirement | Status | Notes |
| --- | --- | --- |
| Capability checks, nonces, sanitization/escaping | ⚠️ | Many screens protected; conduct security sweep for recent additions. |
| BCC email validation | ⚠️ | Basic sanitization exists; strengthen validation logic. |
| Strings localized under `bonus-hunt-guesser` | ⚠️ | Most strings translated; audit remaining hard-coded text. |

## 14. Backward Compatibility

| Requirement | Status | Notes |
| --- | --- | --- |
| Legacy data loads with safe defaults | ⚠️ | Migration routines attempt to normalize data; more testing needed. |
| New settings/prize types default safely | ✅ | Regular prize default enforced when type missing. |

## 15. Global UX Guarantees

| Requirement | Status | Notes |
| --- | --- | --- |
| Sorting, search, pagination (30/page) across admin tables | ⚠️ | Implemented across list tables; QA per screen. |
| Timeline filters (This Week/Month/Year/Last Year/All-Time) | ⚠️ | Controls exist; confirm data queries. |
| Affiliate lights and website display | ✅ | Shortcodes render colored indicators. |
| Profile blocks display real name, email, affiliate | ⚠️ | UI present; confirm accuracy. |

## 16. Release & Documentation

| Requirement | Status | Notes |
| --- | --- | --- |
| Version bumped to 8.0.16 across metadata/constants | ⚠️ | Header and constant updated; remaining docs still reference 8.0.14 and need refresh. |
| Changelog updated for new release | ❌ | `CHANGELOG.md` still capped at 8.0.14 entry. |
| Readme/Admin “Info & Help” cover new features | ❌ | Documentation predates jackpot module and other add-ons. |

## 17. QA & Acceptance Tests

| Requirement | Status | Notes |
| --- | --- | --- |
| E2E: create/close hunts → winners highlight & points propagation | ⚠️ | Requires manual QA. |
| Currency switch reflects across admin/frontend | ⚠️ | Needs regression test. |
| Guessing toggle blocks/unblocks form | ⚠️ | Feature implemented; confirm behavior. |
| Tournament participants mode respected in results | ⚠️ | Requires scenario testing. |
| Prizes CRUD + FE grid/carousel + CSS panel | ⚠️ | Implemented; run acceptance tests. |
| Notifications BCC + enable/disable toggles | ⚠️ | Implementation present; QA outstanding. |
| Translations load and strings translatable | ⚠️ | Text domain ready; review translation coverage. |

## Add-On: Winner Limits per User

| Requirement | Status | Notes |
| --- | --- | --- |
| Settings UI for Bonushunt/Tournament limits | ⚠️ | Settings page partially implemented; needs UX validation. |
| Rolling-window enforcement when awarding winners | ⚠️ | Logic exists in `BHG_Models::close_hunt()` but requires more robust testing. |
| Win logging with timestamps/user/type | ⚠️ | Logging exists but lacks analytics tooling. |
| Skipped-user notice when limit reached | ⚠️ | Messaging helpers added; confirm admin/front-end visibility. |

## Add-On: Frontend Adjustments

| Requirement | Status | Notes |
| --- | --- | --- |
| Table header links rendered white (#fff) | ⚠️ | CSS adjustments pending confirmation. |
| `bhg_hunts` Details column with Guess Now / Show Results | ⚠️ | Logic wired; QA required. |

## Add-On: Prizes Enhancements

| Requirement | Status | Notes |
| --- | --- | --- |
| Large image upload support (1200×800 PNG) | ⚠️ | Media handling needs validation. |
| Image size labels (Small/Medium/Big) in admin | ⚠️ | UI hints partially implemented. |
| Prize link field and clickable images | ⚠️ | Field exists; verify output. |
| Category management with optional links and visibility toggle | ⚠️ | Data model supports link toggles; admin UI still rough. |
| Image click behavior options (popup / same tab / new tab) | ⚠️ | Settings available; QA pending. |
| Carousel controls: visible count, total load, auto-scroll | ⚠️ | Options stored; ensure front-end respects them. |
| Toggles for prize title/category/description | ⚠️ | Config options exist; verify rendering. |
| Responsive image size rules (1→big, 2–3→medium, 4–5→small) | ⚠️ | Logic needs testing. |
| Remove automatic "Prizes" heading | ⚠️ | Template updated; confirm front-end layout. |
| Dual prize sets (Regular + Premium) for affiliate winners | ⚠️ | Data persisted; acceptance test outstanding. |

## Jackpot Feature (New Module)

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin menu “Jackpots” with CRUD + latest 10 view | ❌ | No jackpot admin screens or tables exist in codebase. |
| Fields: title, start amount, linked hunts (all/selected/by affiliate/by period), increase amount per miss | ❌ | Absent from schema and UI. |
| Logic: detect exact guess hits on hunt close, increase amount otherwise | ❌ | No jackpot handling integrated with hunt closure. |
| Currency uses global setting | ❌ | No jackpot entity to format. |
| Shortcodes `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` | ❌ | Shortcodes not registered. |

## Documentation Follow-Up

1. Update all existing delivery/verification checklists to reference version 8.0.16 instead of 8.0.14.
2. Add end-to-end QA evidence once the remaining ❌/⚠️ items are resolved.
3. Prioritize implementation of the jackpot module and completion of PHPCS compliance across the codebase.

