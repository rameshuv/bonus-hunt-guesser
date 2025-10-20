# Bonus Hunt Guesser – Customer Requirements Checklist

This checklist consolidates every customer requirement that must be verified or implemented for the Bonus Hunt Guesser plugin. Use it during development and QA to confirm compliance with the agreed scope. **Do not introduce features beyond the items below; the customer has requested that only the documented requirements are delivered.** Update the status column as work progresses. All file paths are relative to the plugin root.

### Scope Guardrails

| Rule | Action | Key Files / Notes |
|------|:------:|-------------------|
| No undocumented enhancements. | [ ] | Before starting work, confirm the task exists in this checklist. If not, log a change request instead of coding. |
| Follow WordPress coding standards only. | [ ] | Run `composer lint` / `phpcs` against touched files; configuration lives in `phpcs.xml`. |
| Record rectification touchpoints. | [ ] | When defects are found, note the file to change in the "Key Files / Notes" column for rapid follow-up. |


---

## 1. Global Setup & Settings

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Confirm plugin header metadata (name, version, requirements) matches release. | [ ] | `bonus-hunt-guesser.php` |
| Ensure currency dropdown (EUR/USD) persists to an option and helper returns symbol. | [ ] | `admin/views/settings.php`, `admin/class-bhg-admin.php`, `includes/helpers.php` |
| Confirm money formatting helper prefixes all balance outputs with configured currency. | [ ] | `includes/helpers.php`, templates & shortcode renderers |
| Verify WordPress coding standards (PHPCS) compliance across modified files. | [ ] | Run `composer lint` or `phpcs` |
| Ensure compatibility targets (PHP 7.4, WP 6.3.5, MySQL 5.5.5) are respected in new code. | [ ] | Development checklist |

---

## 2. Backend Administration

### 2.1 Dashboard (`bhg`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Rename submenu label from "Bonushunt" to "Dashboard". | [ ] | `admin/class-bhg-admin.php` |
| Display latest three hunts with winners list (up to 25 entries per hunt). | [ ] | `admin/views/dashboard.php`, data providers |
| Replace "Recent Winners" header with "Latest Hunts" and left-align balances. | [ ] | Dashboard template |

### 2.2 Bonus Hunts (`bhg-bonus-hunts`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Add "Final Balance" column ("-" when hunt open). | [ ] | `admin/views/bonus-hunts.php` |
| Include "Results" action button for closed hunts; list guesses ranked with winners highlighted. | [ ] | `admin/views/bonus-hunts.php`, `admin/views/hunt-results.php` |
| Allow configurable number of winners per hunt. | [ ] | `admin/views/bonus-hunts-edit.php`, related model |
| Show participant list (with removal links and user profile shortcuts) on hunt edit screen. | [ ] | `admin/views/bonus-hunts-edit.php` |
| Add guessing enable/disable toggle on list & edit screens. | [ ] | Hunts admin list/controller |
| Support sorting, searching, pagination (30/page), and affiliate column in list table. | [ ] | `admin/views/bonus-hunts.php`, list table controller |
| Provide delete action under new "Admin Action" column separated from other controls. | [ ] | List table rendering |

### 2.3 Bonus Hunt Results (`bhg-bonus-hunts-results`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Default view shows latest hunt; dropdown to choose any hunt/tournament. | [ ] | Results admin view/controller |
| Add time filter (This Month, This Year, All Time) for dropdown list. | [ ] | Results admin view |
| Display styled message "There are no winners yet" when applicable. | [ ] | Results admin view |

### 2.4 Tournaments (`bhg-tournaments`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Ensure title & description fields exist and save correctly. | [ ] | `admin/views/tournaments-edit.php`, controller |
| Replace type field with participants dropdown (winners/all) and remove obsolete period field. | [ ] | Tournament admin templates & model |
| Enable edit, close, results, and delete actions (delete in "Admin Action" column). | [ ] | `admin/views/tournaments.php` |
| Implement sorting, searching, pagination (30/page). | [ ] | Tournaments list table |
| Allow linking multiple hunts to a tournament and configure connection mode. | [ ] | Tournament edit form, DB migration |
| Create junction table for hunt ↔ tournament relationships if missing. | [ ] | `includes/class-bhg-db.php` |

### 2.5 Users (`bhg-users`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Add search by username/email. | [ ] | Users list controller |
| Provide sortable columns and pagination (30/page). | [ ] | Users list table |
| Extend profile view/edit with affiliate indicators and custom fields. | [ ] | `admin/views/users.php`, profile handling |

### 2.6 Ads (`bhg-ads`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Add actions column with Edit/Delete controls. | [ ] | `admin/views/ads.php` |
| Include "None" option in placement dropdown. | [ ] | Ads form template |
| Shortcode `[bhg_advertising]` respects status & ID filters. | [ ] | Shortcode handler |

### 2.7 Translations & Tools

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Populate translations dashboard with front-end strings for easy editing. | [ ] | `admin/views/translations.php`, localization loader |
| Ensure tools page surfaces relevant maintenance data (avoid empty view). | [ ] | `admin/views/tools.php` |

---

## 3. Frontend Features & Shortcodes

| Shortcode / Feature | Requirement | Status | Key Files / Notes |
|---------------------|-------------|:------:|-------------------|
| `[bhg_user_profile]` | Display full profile (real name, email, affiliate status/links). | [ ] | `includes/shortcodes/class-bhg-shortcode-user-profile.php` |
| `[bhg_active_hunt]` | Show active hunt details, dropdown for multiples, guesses list & pagination. | [ ] | Shortcode class/template |
| `[bhg_guess_form]` | Offer hunt selector when multiple open, redirect setting, dynamic button text. | [ ] | Guess form shortcode, settings |
| `[bhg_user_guesses]` | Support filters `id`, `aff`, `website`; include difference column and ranking fallback. | [ ] | Shortcode handler |
| `[bhg_hunts]` | Filters `status`, `bonushunt`, `website`, `timeline`; display winners count. | [ ] | Shortcode handler |
| `[bhg_tournaments]` | Filters `status`, `tournament`, `website`, `timeline`; add name column and participants mode. | [ ] | Shortcode handler |
| `[bhg_leaderboard]` | Maintain existing leaderboard tabs & affiliate indicators. | [ ] | Shortcode handler |
| `[bhg_leaderboards]` | Advanced filters (tournament, hunt, affiliate, website, ranking, timeline) and selectable fields (Pos, User, Avg Ranking, Times Won, etc.). | [ ] | Shortcode handler |
| `[bhg_winner_notifications]` | Confirm notification logic & email triggers. | [ ] | Notification shortcode |
| `[bhg_tournaments]` (legacy) | Verify no duplication with enhanced version. | [ ] | Review usage |
| `[bhg_advertising]` | Filters `status`, `ad`; respects placement rules. | [ ] | Shortcode handler |

### Timeline Filters & Sorting Verification

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Timeline filters (This Week, This Month, This Year, Last Year, All-Time) available across applicable shortcodes. | [ ] | Shared query helpers |
| Sorting, search, pagination (30/page) across shortcode tables. | [ ] | Frontend table components |
| Affiliate indicator (green/red lights) shows wherever guesses or leaderboards appear. | [ ] | Shared template partial |
| Affiliate website names surface when set. | [ ] | Data formatters |

---

## 4. Pages & Shortcode Placement

Create or update WordPress pages with the following recommended shortcode mapping. Pages may exist already; overwrite or adjust content as needed.

| Page Title | Suggested Permalink | Shortcode(s) |
|------------|--------------------|--------------|
| Bonus Hunt Dashboard | `/bonus-hunt-dashboard/` | `[bhg_active_hunt]` |
| Submit Guess | `/submit-guess/` | `[bhg_guess_form]` |
| Hunt Leaderboard | `/hunt-leaderboard/` | `[bhg_leaderboard]` or `[bhg_leaderboards]` |
| User Guesses | `/user-guesses/` | `[bhg_user_guesses]` |
| Bonus Hunts Archive | `/bonus-hunts/` | `[bhg_hunts]` |
| Tournaments Archive | `/tournaments/` | `[bhg_tournaments]` |
| Winner Notifications | `/winner-notifications/` | `[bhg_winner_notifications]` |
| User Profile | `/my-profile/` | `[bhg_user_profile]` |
| Advertising Blocks | `/advertising/` | `[bhg_advertising]` |

> **Note:** Pages can be adjusted to fit existing site structure. Ensure duplicate pages are consolidated or updated rather than creating redundant entries.

---

## 5. Testing & Validation Checklist

| Task | Status | Notes |
|------|:------:|-------|
| Verify smart redirect after login for gated pages. | [ ] | Use social login if applicable |
| Confirm guess alteration works only when hunt open and guessing enabled. | [ ] | Functional test |
| Validate winner calculation (closest to final balance) and notification emails. | [ ] | Manual review |
| Check affiliate adjustments across hunts, tournaments, and advertising targeting. | [ ] | Data integrity |
| Run end-to-end pass covering CRUD operations for hunts, tournaments, users, ads, prizes. | [ ] | Admin QA |
| Confirm translation tab exposes all strings and saving works. | [ ] | Localization test |

---

## 6. Prizes Module (`bhg-prizes`)

| Requirement | Status | Key Files / Notes |
|-------------|:------:|-------------------|
| Backend menu "Prizes" with add/edit/delete capabilities. | [ ] | `admin/views/prizes.php`, controller |
| Fields: title, description, category (cash money, casino money, coupons, merchandise, various), image (small/medium/big). | [ ] | Prizes form |
| CSS panel options: border, border color, padding, margin, background color. | [ ] | Prizes settings |
| Active toggle (Yes/No) for availability. | [ ] | Data model |
| Ensure frontend uses configured image sizes and styles. | [ ] | Template integration |

---

## Usage Notes

* Update the **Status** column to `[x]` once a requirement is completed and verified.
* When modifying code, keep a log referencing the files listed above so reviewers can trace the changes quickly.
* Use this document as the single source of truth when planning releases (e.g., v8.0.12) to avoid scope creep.

