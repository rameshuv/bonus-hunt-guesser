# Bonus Hunt Guesser — Verification & Handoff Document (Final)

**Version:** 1.0  
**Scope:** Admin UI, Frontend, DB migrations, Prizes, Affiliates, Tournaments, Results/Dashboard cards  
**Audience:** Developer • QA • PM  
**Status Legend:** ✅ Done • ⚠️ Needs QA/Review • ❌ Not Started

---

## 0) Executive Summary

This document consolidates the functional checklist, data model expectations, capability matrix, QA scenarios, and release-readiness steps for the **Bonus Hunt Guesser** plugin features delivered in this cycle. It is production-oriented and suitable for handoff and verification.

---

## 1) Dashboard — Latest Hunts Card

### 1.1 Functional Checklist

| Requirement | Status | Notes |
| --- | --- | --- |
| Card lists latest **3 hunts** with: Title, Winners (+guess/+diff), Start Balance, Final Balance, Closed At | ⚠️ Needs QA | Template outputs required fields; verify rendering with live data and edge cases (no hunts / fewer than 3). |
| Each **winner** on its own row with **bold username** | ⚠️ Needs UI check | Logic implemented; confirm typography with theme CSS. |
| Start/Final balance **left-aligned** | ⚠️ Styling Review | Table defaults to left; confirm no theme override. |

### 1.2 Data Sources & Rules
- Source: `bhg_bonus_hunts` (latest by `closed_at` DESC, fallback to `created_at` when open).
- Winners: from `bhg_winners` joined by `hunt_id`; include `guess` value and calculated `diff` vs actual.

### 1.3 Edge Cases
- No hunts exist → show “No hunts available.”
- Hunt open (no `final_balance`, no `closed_at`) → show `—` for missing fields.

---

## 2) Bonus Hunts (Admin List / Edit / Results)

### 2.1 Admin List

| Requirement | Status | Notes |
| --- | --- | --- |
| Columns: **Final Balance** (`—` if open) and **Affiliate** | ⚠️ Needs review | DB + render logic present; verify formatting for null/0. |
| Row Actions: **Edit**, **Results**, **Admin Delete**, **Enable/Disable Guessing** | ⚠️ Regression Test | Confirm nonces, redirects, capability checks. |
| Sorting & Pagination | ⚠️ Needs QA | Ensure stable order for open/closed hunts; 30 per page (or global setting). |

### 2.2 Edit Screen

| Requirement | Status | Notes |
| --- | --- | --- |
| Tournament multiselect limited to **active** tournaments | ✅ Done | Query filters by `active = 1`. |
| Winners count configurable and persisted | ✅ Done | Field `winners_count` on hunts table. |
| Participants list with **remove** action and profile links | ⚠️ Needs flow test | Confirm capability `manage_options` (or custom cap), nonce, and audit log. |
| Fields: title, start_balance, final_balance, closed_at, guessing_enabled, affiliate_id | ✅/⚠️ | Verify validation/sanitization and autosave behavior. |

### 2.3 Results View

| Requirement | Status | Notes |
| --- | --- | --- |
| Defaults to **latest closed hunt**; supports selectors (timeframe / hunt) | ⚠️ Verify data loading | Implemented in `bonus-hunts-results.php`. |
| Empty state message | ✅ Done | “There are no winners yet.” |
| Time filter: **This Month (default) / This Year / All Time** | ⚠️ Query validation | Confirm correctness for month/year boundaries & TZ. |
| Winners highlighted (green + bold), zebra rows, **Prize** column | ⚠️ UI Sign-off | CSS exists; check for theme collisions. |

### 2.4 Data/Queries
- Filtering by timeframe uses `closed_at` (month/year server time); ensure consistent TZ.
- Winner ranking based on minimal `diff` (absolute difference). Ties resolved by earlier `guess_time`.

---

## 3) Tournaments (Admin)

| Requirement | Status | Notes |
| --- | --- | --- |
| Fields: **Title**, **Description** | ✅ Done | Standard edit UI. |
| Type options include **Quarterly** / **All-time**; legacy period removed | ⚠️ Migration Check | Confirm prior data mapped safely to new types. |
| Participants mode toggle (**Winners Only** | **All Guessers**) | ✅ Done | Stored in `participants_mode`. |
| Actions: **Edit**, **Results**, **Close**, **Admin Delete** | ⚠️ Capability Review | Ensure caps + nonces implemented. |
| DB column `participants_mode` | ✅ Done | Added via migrations. |

---

## 4) Users (Admin)

| Requirement | Status | Notes |
| --- | --- | --- |
| Search by username/email | ✅ Done | `WP_User_Query` integrated. |
| Sortable columns | ⚠️ UI review | Flags present; verify sort keys & directions. |
| Pagination (30 per page) | ✅ Done | Consistent UX with other admin tables. |
| Profile shows **affiliate toggles per affiliate website** | ⚠️ Test persistence | Verify create/update, validator, defaults. |

---

## 5) Affiliates (Sync & Frontend)

| Requirement | Status | Notes |
| --- | --- | --- |
| Add/remove affiliate websites syncs user profile fields | ⚠️ Integration Test | Exercise add, edit, remove; verify orphan cleanup. |
| Frontend affiliate lights + optional website display | ✅ Done | Shortcodes produce colored dot + label. |

**Sync Rules**
- When affiliates added/removed, ensure user meta mirrors active set.
- Deleting an affiliate removes related user meta entries.

---

## 6) Prizes (Admin + Frontend + Shortcode)

| Requirement | Status | Notes |
| --- | --- | --- |
| CRUD: title, description, category, image, CSS class, active flag | ✅ Done | Managed via `BHG_Prizes`. |
| Dual prize sets (regular + premium) selectable in Admin | ✅ Done | Stored and retrievable per hunt. |
| Affiliate winners see **premium prize set above** regular | ⚠️ Needs UAT | Frontend conditional display toggled for affiliates. |

---

## 7) Capability Matrix

| Action | Capability | Notes |
| --- | --- | --- |
| View Hunts Admin | `manage_options` or custom `bhg_manage` | Consider custom capability for finer control. |
| Edit Hunt | `manage_options` or `bhg_edit_hunt` | Nonce required. |
| Delete Hunt (Admin Delete) | `manage_options` | Soft delete recommended; confirm intent. |
| Toggle Guessing | `manage_options` | Audit log useful. |
| View/Manage Tournaments | `manage_options` | Include close action guardrails. |
| Edit User Affiliate Toggles | `manage_options` | Only visible to admins. |
| Manage Prizes | `manage_options` | Validate image/CSS inputs. |

---

## 8) Database Schema (Conceptual)

> Actual SQL resides in `BHG_DB::create_tables()`; this section summarizes expected columns and relationships.

### 8.1 `bhg_bonus_hunts`
- `id` (PK), `title`, `start_balance` (DECIMAL), `final_balance` (DECIMAL, nullable),  
  `closed_at` (DATETIME, nullable), `guessing_enabled` (TINYINT 0/1), `affiliate_id` (INT nullable),
  `winners_count` (INT), `created_at` (DATETIME), `updated_at` (DATETIME)

### 8.2 `bhg_guesses`
- `id` (PK), `hunt_id` (FK), `user_id` (FK WP users), `guess_value` (DECIMAL),  
  `guess_time` (DATETIME), `affiliate_id` (INT nullable)

### 8.3 `bhg_winners`
- `id` (PK), `hunt_id` (FK), `user_id` (FK), `rank` (INT), `actual_value` (DECIMAL),  
  `guess_value` (DECIMAL), `diff_value` (DECIMAL), `prize_id` (FK)

### 8.4 `bhg_tournaments`
- `id` (PK), `title`, `description`, `type` ENUM(`quarterly`,`all_time`),  
  `participants_mode` ENUM(`winners_only`,`all_guessers`), `active` (TINYINT), timestamps

### 8.5 `bhg_prizes`
- `id` (PK), `title`, `description`, `category`, `image_url`, `css_class`, `active` (TINYINT), timestamps

### 8.6 `bhg_hunt_prizes`
- `id` (PK), `hunt_id` (FK), `prize_id` (FK), `tier` ENUM(`regular`,`premium`), `rank_from`, `rank_to`

### 8.7 `bhg_affiliates`
- `id` (PK), `name`, `website`, `active` (TINYINT), timestamps

> **Migrations:** Ensure idempotent checks (`IF NOT EXISTS`) and column existence guards.

---

## 9) Frontend Rendering Rules

- **Winners Table (Results):**  
  - Winner rows: **bold username**, green highlight class for winners, alternating row classes (`.row-alt`).  
  - Columns: Username, Guess, Actual, Diff, Prize (resolved by rank & prize tier).  
  - **Affiliate premium-first:** If winner is affiliate-associated and hunt has premium prizes, render premium block first.

- **Latest Hunts Card:**  
  - Show at most **3 latest** items.  
  - For each: Title → link to results; list winners with `(guess / ±diff)`; balances; closed timestamp (or `—` if open).

---

## 10) Validation & Sanitization

- **Admin Inputs:** `sanitize_text_field`, `floatval`/`wc_format_decimal` equivalents for balances, `absint` for IDs, `wp_verify_nonce`.
- **Output Escaping:** `esc_html`, `esc_attr`, `esc_url`; use `wp_kses_post` for safe rich text (prize descriptions).
- **Timezones:** Normalize to WP timezone for queries and display; prefer `get_date_from_gmt` helpers when needed.

---

## 11) QA Test Scenarios

### 11.1 Dashboard Card
1. **No Hunts:** Expect “No hunts available.”  
2. **Open Hunts Only:** Final balance/closed at show `—`.  
3. **Mixed State:** Closed hunts sorted above, latest first.  
4. **Winner Rows:** Bold usernames, `(guess / ±diff)` formatting correct.

### 11.2 Admin Hunts
1. **List Columns:** Final Balance shows `—` if open; Affiliate label correct.  
2. **Row Actions:** Edit, Results, Delete, Toggle Guessing all work with correct caps and nonces.  
3. **Edit:** Changing `winners_count` persists; multiselect shows only active tournaments.  
4. **Participants:** Remove flow respects capability and nonces; audit entry written if enabled.

### 11.3 Results View
1. **Default Hunt:** Loads latest closed hunt.  
2. **Time Filter:** This Month/Year/All Time return expected sets at month/year boundaries.  
3. **Styling:** Winners highlighted (green + bold), alternating rows, Prize column not empty when mapped.

### 11.4 Tournaments
1. **Types:** Quarterly/All-time selectable; legacy removed.  
2. **Participants Mode:** Winners Only vs All Guessers reflected in results aggregation.

### 11.5 Prizes
1. **CRUD:** Create/update with image + CSS class; inactive prizes not assigned.  
2. **Dual Sets:** Premium + Regular saved and correctly mapped to ranks.  
3. **Affiliate View:** Premium-first display for affiliate winners.

### 11.6 Affiliates
1. **Sync:** Add/remove affiliate → user meta updated accordingly.  
2. **Frontend:** Dot color and optional website label appear when enabled.

---

## 12) Performance & Reliability

- **Queries:** Add indexes on `bhg_bonus_hunts.closed_at`, `bhg_guesses.hunt_id/user_id`, `bhg_winners.hunt_id/user_id`.  
- **N+1 Avoidance:** Preload winners and prizes for visible hunts.  
- **Caching:** Consider transient cache for dashboard card (e.g., 60s).  
- **Pagination:** Enforce 30 rows per list to protect admin screens.  
- **Errors:** Use `WP_Error` patterns; admin notices for user actions.

---

## 13) Accessibility & i18n

- **A11y:** Table headers `<th scope="col">`, adequate contrast on green highlight, focus states on row actions.  
- **i18n:** Wrap strings with `__()`/`_e()` domain `'bonushuntguesser'`.  
- **RTL:** Validate table alignment and icons in RTL languages.

---

## 14) Security

- **Nonces:** All POST actions; GET actions that mutate state (delete/toggle) use nonces.  
- **Caps:** Do not rely on `is_admin()`; enforce `current_user_can`.  
- **Escaping:** Escape on output, not on save; URLs via `esc_url`.  
- **Uploads:** Prizes’ images use WordPress media library.

---

## 15) Release Checklist

1. **DB Migrations** pass on fresh and upgraded installs.  
2. **Capabilities** verified for all actions.  
3. **Nonce** coverage on all mutating endpoints.  
4. **UI Pass**: Theme conflict check (tables, highlights, spacing).  
5. **Performance**: Admin lists under 200ms on 5k records with indexes.  
6. **Accessibility**: Lint with axe; contrast pass.  
7. **i18n**: POT regenerated.  
8. **Docs**: This file committed as `docs/verification-checklist.md`.  
9. **Changelog** updated (see template below).  
10. **Tag Release** in version control and prepare rollback plan.

---

## 16) Changelog Template (for this Release)

