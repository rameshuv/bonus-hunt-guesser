# Bonus Hunt Guesser — Final Verification & Delivery Checklist (v8.0.16)

**Date:** 2024-09-17
**Audience:** Developer • QA • PM
**Scope:** Admin UI, Frontend, DB Migrations, Prizes, Affiliates, Tournaments, Results/Dashboard cards

> **Note on lineage:** This document consolidates and supersedes prior checklists (e.g., 2024-09-16). For historical snapshots, see `docs/final-checklist-20240917.md`.

---

## Status Legend

* ✅ — Requirement fully satisfied and verified
* ⚠️ — Requirement partially satisfied or requires QA/review
* ❌ — Requirement missing or known to be non-compliant

---

## 0) Executive Summary

This is a production-oriented handoff and verification guide for **Bonus Hunt Guesser v8.0.16**. It unifies the functional checklist, capability matrix, schema expectations, QA scenarios, and release readiness steps. Items marked ⚠️/❌ must be addressed before final client acceptance.

---

## 1) Plugin Bootstrap & Tooling

| Requirement                                                | Status | Notes                                                                                  |
| ---------------------------------------------------------- | ------ | -------------------------------------------------------------------------------------- |
| Plugin header matches contract (metadata + version 8.0.16) | ✅      | `bonus-hunt-guesser.php` exposes version 8.0.16 with agreed WP/PHP/MySQL requirements. |
| Text domain loads on `plugins_loaded`                      | ✅      | Uses `load_plugin_textdomain()` during boot.                                           |
| PHPCS (WordPress Core/Docs/Extra) passes with no errors    | ❌      | Repo-level run fails due to legacy spacing/indentation in admin/controllers/tests.     |

---

## 2) Dashboard — “Latest Hunts” Card

| Requirement                                                                                                | Status | Notes                                                                      |
| ---------------------------------------------------------------------------------------------------------- | ------ | -------------------------------------------------------------------------- |
| Card lists latest **3 hunts** with: Title, Winners (+guess/+diff), Start Balance, Final Balance, Closed At | ⚠️     | Template outputs fields; verify with live data and edge cases (0–3 hunts). |
| Each winner has its own row with **bold username**                                                         | ⚠️     | Logic present; confirm final typography against theme CSS.                 |
| Start/Final balance are **left-aligned**                                                                   | ⚠️     | Defaults left; check for theme overrides.                                  |

**Data sources & rules**

* Hunts: `bhg_bonus_hunts` (sort by `closed_at` DESC; fallback to `created_at` when open).
* Winners: join `bhg_winners` by `hunt_id`; include `guess` and computed `diff` vs actual.

**Edge cases**

* No hunts → “No hunts available.”
* Open hunts (no `final_balance`, `closed_at`) → show `—`.

---

## 3) Bonus Hunts (Admin List / Edit / Results)

### 3.1 Admin List

| Requirement                                                                       | Status | Notes                                            |
| --------------------------------------------------------------------------------- | ------ | ------------------------------------------------ |
| Columns: **Final Balance** (`—` if open) and **Affiliate**                        | ⚠️     | DB + render wired; verify formatting for null/0. |
| Row actions: **Edit**, **Results**, **Admin Delete**, **Enable/Disable Guessing** | ⚠️     | Confirm nonces, redirects, capability checks.    |
| Sorting & pagination (≈30/page)                                                   | ⚠️     | Confirm stable sort for open/closed hunts.       |

### 3.2 Edit Screen

| Requirement                                                              | Status | Notes                                                                 |
| ------------------------------------------------------------------------ | ------ | --------------------------------------------------------------------- |
| Tournament multiselect limited to **active** tournaments                 | ✅      | Query filters `active = 1`.                                           |
| **Winners count** configurable & persisted                               | ✅      | Field `winners_count` in hunts table.                                 |
| Participants list with **remove** action + profile links                 | ⚠️     | Verify capability `manage_options` (or custom cap), nonce, audit log. |
| Fields validated/sanitized (title, balances, dates, guessing, affiliate) | ⚠️     | Review validators and autosave behavior.                              |

### 3.3 Results View

| Requirement                                                      | Status | Notes                                                         |
| ---------------------------------------------------------------- | ------ | ------------------------------------------------------------- |
| Defaults to **latest closed hunt**; selectors for timeframe/hunt | ⚠️     | Implemented in `bonus-hunts-results.php`; validate data load. |
| Empty state message                                              | ✅      | “There are no winners yet.”                                   |
| Time filter: **This Month (default) / This Year / All Time**     | ⚠️     | Confirm month/year boundaries (WP timezone).                  |
| Winners highlighted (green + bold), zebra rows, **Prize** column | ⚠️     | CSS exists; check for theme collisions.                       |

**Ranking rules**

* Rank by minimal absolute `diff`; tie-break by earlier `guess_time`.

---

## 4) Tournaments (Admin)

| Requirement                                                       | Status | Notes                                 |
| ----------------------------------------------------------------- | ------ | ------------------------------------- |
| Fields: **Title**, **Description**                                | ✅      | Standard edit UI.                     |
| Types include **Quarterly** / **All-time**; legacy period removed | ⚠️     | Verify safe migration of legacy data. |
| Participants mode toggle (**Winners Only** / **All Guessers**)    | ✅      | Stored in `participants_mode`.        |
| Actions: **Edit**, **Results**, **Close**, **Admin Delete**       | ⚠️     | Ensure proper caps + nonces.          |
| DB column `participants_mode`                                     | ✅      | Added via migrations.                 |

---

## 5) Users (Admin)

| Requirement                                               | Status | Notes                             |
| --------------------------------------------------------- | ------ | --------------------------------- |
| Search by username/email                                  | ✅      | `WP_User_Query` integration.      |
| Sortable columns                                          | ⚠️     | Verify keys & directions.         |
| Pagination (30/page)                                      | ✅      | Consistent with other lists.      |
| Profile shows **affiliate toggles per affiliate website** | ⚠️     | Confirm persistence and defaults. |

---

## 6) Affiliates (Sync & Frontend)

| Requirement                                                  | Status | Notes                                            |
| ------------------------------------------------------------ | ------ | ------------------------------------------------ |
| Adding/removing affiliate websites syncs user profile fields | ⚠️     | Exercise add/edit/remove; verify orphan cleanup. |
| Frontend affiliate “lights” + optional website label         | ✅      | Shortcodes render colored dot + label.           |

**Sync rules**

* Adding/removing affiliates must mirror in user meta.
* Deleting an affiliate removes related user meta.

---

## 7) Prizes (Admin + Frontend + Shortcodes)

| Requirement                                                  | Status | Notes                                    |
| ------------------------------------------------------------ | ------ | ---------------------------------------- |
| CRUD: title, description, category, image, CSS class, active | ✅      | Managed via `BHG_Prizes`.                |
| **Dual prize sets** (regular + premium) selectable per hunt  | ✅      | Persisted and retrievable.               |
| Affiliate winners see **premium prize set above** regular    | ⚠️     | Frontend conditional display; needs UAT. |
| Three image sizes (small/medium/big) incl. 1200×800 PNG      | ⚠️     | Validate upload rules & rendering.       |
| Frontend grid/carousel with dots/arrows and fallback         | ⚠️     | Cross-device QA outstanding.             |
| Shortcode `[bhg_prizes]` (category, design, size, active)    | ⚠️     | Option parsing implemented; add tests.   |

---

## 8) Shortcodes Catalog & Core Pages

| Requirement                                                                           | Status | Notes                                 |
| ------------------------------------------------------------------------------------- | ------ | ------------------------------------- |
| Admin **Info & Help** lists all shortcodes w/ examples                                | ⚠️     | Expand documentation coverage.        |
| Existing shortcodes remain supported (`[bhg_user_profile]`, `[bhg_guess_form]`, etc.) | ✅      | No regressions detected.              |
| `[bhg_user_guesses]`: **difference** column after final balance                       | ⚠️     | Verify formatting.                    |
| `[bhg_hunts]`: **winners count** + **Details** column (Guess Now/Show Results)        | ⚠️     | Ensure URLs valid.                    |
| `[bhg_tournaments]`: updated columns & naming                                         | ⚠️     | Cross-check type removal.             |
| `[bhg_leaderboards]`: Times Won, Avg positions (hunt/tournament)                      | ⚠️     | Calculations present; add unit tests. |
| `[bhg_advertising]`: `placement="none"` for shortcode-only                            | ✅      | Admin & rendering support.            |
| Required pages auto-created with override metabox                                     | ⚠️     | Confirm on activation.                |

---

## 9) Notifications

| Requirement                                                                          | Status | Notes                      |
| ------------------------------------------------------------------------------------ | ------ | -------------------------- |
| Winners/Tournament/Bonushunt blocks with Title, HTML Description, BCC, enable toggle | ⚠️     | Settings exist; verify UI. |
| Notifications use `wp_mail()` with BCC honored                                       | ✅      | BCC headers handled.       |

---

## 10) Ranking & Points

| Requirement                                           | Status | Notes                                   |
| ----------------------------------------------------- | ------ | --------------------------------------- |
| Editable default mapping (25/15/10/5/4/3/2/1)         | ⚠️     | Settings stored; needs admin QA.        |
| Scope toggle (active/closed/all hunts)                | ⚠️     | Validate calculations.                  |
| Only winners accrue points                            | ⚠️     | Logic present; add regression coverage. |
| Backend + frontend rankings highlight winners + Top 3 | ⚠️     | Styling exists; needs UX sign-off.      |
| Centralized service + unit tests                      | ⚠️     | Tests limited; extend.                  |

---

## 11) Global CSS / Color Panel

| Requirement                                                     | Status | Notes                                      |
| --------------------------------------------------------------- | ------ | ------------------------------------------ |
| Global typography and color controls apply to shared components | ⚠️     | Settings persisted; verify FE application. |

---

## 12) Currency System

| Requirement                                                        | Status | Notes                                 |
| ------------------------------------------------------------------ | ------ | ------------------------------------- |
| Setting `bhg_currency` (EUR/USD) stored                            | ✅      | Option available in settings.         |
| Helpers `bhg_currency_symbol()` & `bhg_format_money()` implemented | ✅      | Defined in bootstrap.                 |
| All monetary outputs use helpers                                   | ⚠️     | Audit for any direct formatting left. |

---

## 13) Database & Migrations (Conceptual)

> Concrete SQL in `BHG_DB::create_tables()`. Idempotent checks for tables/columns/indexes are required.

### 13.1 Tables & Key Columns

* **`bhg_bonus_hunts`** — `id`, `title`, `start_balance` DECIMAL, `final_balance` DECIMAL NULL, `closed_at` DATETIME NULL, `guessing_enabled` TINYINT, `affiliate_id` INT NULL, `winners_count` INT, timestamps.
* **`bhg_guesses`** — `id`, `hunt_id`, `user_id`, `guess_value` DECIMAL, `guess_time` DATETIME, `affiliate_id` INT NULL.
* **`bhg_winners`** — `id`, `hunt_id`, `user_id`, `rank`, `actual_value` DECIMAL, `guess_value` DECIMAL, `diff_value` DECIMAL, `prize_id`.
* **`bhg_tournaments`** — `id`, `title`, `description`, `type` ENUM(`quarterly`,`all_time`), `participants_mode` ENUM(`winners_only`,`all_guessers`), `active` TINYINT, timestamps.
* **`bhg_prizes`** — `id`, `title`, `description`, `category`, `image_url`, `css_class`, `active` TINYINT, timestamps.
* **`bhg_hunt_prizes`** — `id`, `hunt_id`, `prize_id`, `tier` ENUM(`regular`,`premium`), `rank_from`, `rank_to`.
* **`bhg_affiliates`** — `id`, `name`, `website`, `active` TINYINT, timestamps.

### 13.2 Indexing & Performance

* Index: `bhg_bonus_hunts.closed_at`, `bhg_guesses.hunt_id`, `bhg_guesses.user_id`, `bhg_winners.hunt_id`, `bhg_winners.user_id`.
* Avoid N+1 by preloading winners/prizes for visible hunts.

---

## 14) Frontend Rendering Rules

* **Winners (Results):** Bold username; green highlight for winners; zebra rows (`.row-alt`). Columns: Username, Guess, Actual, Diff, Prize. If winner is affiliate-associated **and** hunt has premium prizes → show **premium** prize block above regular.
* **Latest Hunts Card:** Max **3** items; Title (link to results); list winners `(guess / ±diff)`; balances; closed timestamp (or `—` if open).

---

## 15) Validation, Sanitization & Security

* **Admin Inputs:** `sanitize_text_field`, numeric casting for balances/IDs; verify nonces on all mutating actions.
* **Escaping on output:** `esc_html`, `esc_attr`, `esc_url`; `wp_kses_post` for rich descriptions.
* **Timezones:** Normalize to WP timezone for queries/display.
* **Capabilities (suggested):**

  * View Hunts Admin — `manage_options` or custom `bhg_manage`
  * Edit Hunt — `manage_options` or `bhg_edit_hunt`
  * Delete Hunt — `manage_options` (soft-delete recommended)
  * Toggle Guessing — `manage_options`
  * Manage Tournaments/Prizes/Affiliates — `manage_options`

---

## 16) Accessibility & i18n

* **A11y:** `<th scope="col">` on table headers, focus states on actions, adequate contrast for highlights.
* **i18n:** Wrap strings with `__()`/`_e()` using text domain `'bonushuntguesser'`.
* **RTL:** Validate alignment and icon direction.

---

## 17) Performance & Reliability

* Use indexes above; consider transient caching for dashboard card (~60s).
* Keep admin lists ≤200ms on ~5k records; paginate at 30 rows.
* Emit `WP_Error` on failures; surface admin notices on user actions.

---

## 18) QA & Acceptance Tests

### 18.1 Dashboard Card

1. No hunts → “No hunts available.”
2. Only open hunts → Final/Closed show `—`.
3. Mixed states → Closed hunts sorted latest first.
4. Winner rows → Bold usernames; `(guess / ±diff)` formatting correct.

### 18.2 Admin Hunts

1. List columns show `—` when open; Affiliate label correct.
2. Row actions respect caps/nonces and redirect properly.
3. Edit → `winners_count` persists; tournament multiselect shows only active.
4. Participants remove flow enforces caps/nonces; audit entry written (if enabled).

### 18.3 Results View

1. Default loads latest closed hunt.
2. Time filter boundaries correct (month/year).
3. Styling: green highlights + zebra rows; Prize column populated when mapped.

### 18.4 Tournaments

1. Types: Quarterly/All-time selectable; legacy removed.
2. Participants Mode respected in aggregation.

### 18.5 Prizes

1. CRUD with image + CSS class; inactive prizes not assignable.
2. Dual sets (premium + regular) saved and mapped to ranks.
3. Affiliate view shows premium-first when applicable.

### 18.6 Affiliates

1. Sync user meta on add/remove.
2. Frontend dot + optional website label display correctly.

### 18.7 Currency

1. Switching EUR/USD reflects across admin/frontend consistently.

---

## 19) Global UX Guarantees

| Requirement                                                | Status | Notes                         |
| ---------------------------------------------------------- | ------ | ----------------------------- |
| Sorting, search, pagination (~30/page) across admin tables | ⚠️     | QA per screen.                |
| Timeline filters (This Week/Month/Year/Last Year/All-Time) | ⚠️     | Validate data queries.        |
| Affiliate lights & website display                         | ✅      | Shortcodes render indicators. |
| Profile blocks show real name, email, affiliate            | ⚠️     | Confirm accuracy.             |

---

## 20) Add-Ons

### 20.1 Winner Limits per User

| Requirement                                    | Status | Notes                                              |
| ---------------------------------------------- | ------ | -------------------------------------------------- |
| Settings UI for Bonushunt/Tournament limits    | ⚠️     | Needs UX validation.                               |
| Rolling-window enforcement on awarding winners | ⚠️     | Logic in `BHG_Models::close_hunt()`; expand tests. |
| Win logging (timestamp/user/type)              | ⚠️     | Present; add analytics tools.                      |
| Skipped-user notice when limit reached         | ⚠️     | Verify admin/frontend visibility.                  |

### 20.2 Frontend Adjustments

| Requirement                                           | Status | Notes                            |
| ----------------------------------------------------- | ------ | -------------------------------- |
| Table header links are white (`#fff`)                 | ⚠️     | CSS update pending confirmation. |
| `[bhg_hunts]` Details column (Guess Now/Show Results) | ⚠️     | Logic wired; verify links.       |

### 20.3 Prizes Enhancements

| Requirement                                                | Status | Notes                                    |
| ---------------------------------------------------------- | ------ | ---------------------------------------- |
| Large image upload support (1200×800 PNG)                  | ⚠️     | Validate.                                |
| Image size labels (Small/Medium/Big) in admin              | ⚠️     | UI hints partial.                        |
| Prize link field + clickable images                        | ⚠️     | Field exists; confirm FE output.         |
| Category management: links + visibility toggle             | ⚠️     | Model supports; admin UI rough.          |
| Image click behavior (popup/same tab/new tab)              | ⚠️     | Settings present; QA pending.            |
| Carousel controls: visible count, total load, auto-scroll  | ⚠️     | Ensure FE respects options.              |
| Toggles for prize title/category/description               | ⚠️     | Confirm rendering.                       |
| Responsive image size rules (1→big, 2–3→medium, 4–5→small) | ⚠️     | Needs testing.                           |
| Remove automatic “Prizes” heading                          | ⚠️     | Template updated; verify FE layout.      |
| Dual prize sets for affiliate winners                      | ⚠️     | Data persisted; acceptance test pending. |

---

## 21) Jackpot Feature (New Module)

| Requirement                                                                                                  | Status | Notes                  |
| ------------------------------------------------------------------------------------------------------------ | ------ | ---------------------- |
| Admin menu “Jackpots” with CRUD + latest 10 view                                                             | ❌      | Not implemented.       |
| Fields: title, start amount, linked hunts (all/selected/by affiliate/by period), increase per miss           | ❌      | Missing schema/UI.     |
| Logic: exact-guess detection on hunt close; increase otherwise                                               | ❌      | Not integrated.        |
| Currency follows global setting                                                                              | ❌      | No entity implemented. |
| Shortcodes: `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` | ❌      | Not registered.        |

---

## 22) Documentation Follow-Up

1. Update all checklists/docs to **v8.0.16** (remove `8.0.14` references).
2. Capture E2E QA evidence once ❌/⚠️ items are resolved.
3. Prioritize **Jackpot module** and **PHPCS** cleanup across codebase.
4. Expand Admin “Info & Help” with complete shortcode catalog/examples.

---

## 23) Release Checklist

1. **DB migrations** pass on clean & upgrade installs (idempotent guards).
2. **Capabilities** enforced for all actions (no reliance on `is_admin()` alone).
3. **Nonces** on all mutating endpoints (incl. GET actions like delete/toggle).
4. **UI pass** against target theme(s) for tables/highlights/spacing.
5. **Performance**: Admin lists ≤200ms on ~5k rows with indexes.
6. **Accessibility**: Axe scan; contrast pass for highlights.
7. **i18n**: POT regenerated; missing strings wrapped.
8. **Docs**: Commit this file as `docs/verification-checklist.md`.
9. **Changelog** updated (see template).
10. **Tag release** and prepare rollback plan.

---

## 24) Changelog Template (v8.0.16)

```
## [8.0.16] - 2024-09-17
### Added
- Tournament types: Quarterly, All-time.
- Dual prize sets (Regular + Premium) with affiliate-aware display.
- Admin dashboard "Latest Hunts" card (top 3).

### Changed
- `[bhg_hunts]` adds Details column (Guess Now / Show Results).
- Currency helpers centralized; EUR/USD setting applied broadly.

### Fixed
- Results view defaults to latest closed hunt; improved empty state.
- Participants search and admin list pagination consistency.

### Pending (Not in this tag)
- Jackpot module (CRUD, logic, shortcodes).
- Full PHPCS compliance across legacy files.
```

---

## 25) File Placement

* Save this document as: **`docs/verification-checklist.md`**
* Archive the historical snapshot at: **`docs/final-checklist-20240917.md`**

---
