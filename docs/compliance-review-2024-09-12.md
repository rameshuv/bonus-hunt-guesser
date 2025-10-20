# Bonus Hunt Guesser – Compliance Review (2024-09-12)

This audit cross-checks the current plugin implementation against the latest customer requirements
and WordPress coding standards expectations. Items marked **Action Required** indicate code that
must be updated before the plugin can be considered compliant.

## Coding Standards Coverage

| Issue | Status | Action | Files to Modify |
|-------|:------:|--------|-----------------|
| `phpcs.xml` only scans the `tests/` directory, leaving production PHP unchecked. | ⚠️ | Update the PHPCS ruleset to include plugin source directories (e.g. `bonus-hunt-guesser.php`, `admin/`, `includes/`, `assets/` PHP templates) while still excluding vendor assets. | `phpcs.xml` |
| Production files use space indentation and inconsistent formatting that would fail WPCS once scanning is widened. | ❌ | Reformat affected files to use tab-based indentation and satisfy core WordPress sniffs. | `bonus-hunt-guesser.php`, `admin/*.php`, `includes/*.php` (e.g. `includes/class-bhg-shortcodes.php`, `admin/views/*.php`) |

## Backend Requirements

| Requirement | Status | Notes | Files to Modify |
|-------------|:------:|-------|-----------------|
| Dashboard submenu renamed to “Dashboard” and lists latest hunts with up to 25 winners. | ⚠️ | Submenu label still reads “Bonushunt” and winners table shows a single row per hunt. | `admin/class-bhg-admin.php`, `admin/views/dashboard.php` |
| Bonus hunt list shows Final Balance column, configurable winners count, in-edit participant list with removal links and profile shortcuts. | ⚠️ | Column present, but participant management UI missing; winners count hard-coded. | `admin/views/bonus-hunts.php`, `admin/views/bonus-hunts-edit.php`, `includes/class-bhg-bonus-hunts.php` |
| Results button on closed hunts with ranked list and highlighted winners. | ⚠️ | Results view exists but does not highlight winners and lacks removal of empty state text. | `admin/views/bonus-hunts.php`, `admin/views/bonus-hunts-results.php` |
| Bonus hunt actions include separate “Admin Action” column with Delete button. | ❌ | Delete button still resides alongside other actions. | `admin/views/bonus-hunts.php` |
| Tournaments admin supports title/description, participants mode, close/results/delete actions, sorting/searching/pagination. | ⚠️ | Fields present, but participants mode not persisted and list lacks Admin Action column and close/results buttons. | `admin/views/tournaments.php`, `admin/views/tournaments-edit.php`, `includes/class-bhg-tournaments-controller.php` |
| Users admin supports search, sorting, 30-per-page pagination. | ⚠️ | Pagination defaults to 20 and search limited to username only. | `admin/views/users.php`, `includes/class-bhg-users-list-table.php` |
| Ads admin adds actions column with edit/remove and “None” placement option. | ⚠️ | Placement dropdown missing “None”; actions column merges edit/delete with other controls. | `admin/views/ads.php`, `admin/views/ads-edit.php` |
| Translations/tools screens populated with data. | ❌ | Both views render empty placeholders. | `admin/views/translations.php`, `admin/views/tools.php` |

## Prizes Module

| Requirement | Status | Notes | Files to Modify |
|-------------|:------:|-------|-----------------|
| Backend menu “Prizes” with add/edit/delete including image sizes, CSS options, active toggle. | ⚠️ | CRUD available, but CSS panel missing padding/margin fields and active toggle not persisted to frontend query. | `admin/views/prizes-edit.php`, `includes/class-bhg-prizes.php`, `includes/helpers.php` |
| Bonus hunts allow selecting multiple prizes. | ⚠️ | UI shows checklist but selections are not stored in linking table. | `admin/views/bonus-hunts-edit.php`, `includes/class-bhg-bonus-hunts.php`, `includes/class-bhg-db.php` (junction table) |
| Active hunts frontend supports grid/carousel toggle with dots/arrows. | ⚠️ | Grid works; carousel missing navigation controls and accessible focus handling. | `assets/js/bhg-shortcodes.js`, `assets/css/bhg-shortcodes.css`, `templates/frontend/prizes-carousel.php` |
| Prize shortcode `[bhg_prizes]` accepts `category`, `design`, `size`, `active`. | ⚠️ | `design="caroussel"` alias supported, but category filter ignored and size parameter not reflected in markup. | `includes/shortcodes/class-bhg-shortcode-prizes.php`, `includes/helpers.php` |

## New User Profile Shortcodes

| Shortcode | Requirement | Status | Files to Modify |
|-----------|-------------|:------:|-----------------|
| `[bhg_my_bonushunts]` | List hunts the user participated in with ranking. | ❌ | Not registered or implemented. | `includes/class-bhg-shortcodes.php`, new handler class under `includes/shortcodes/`, templates |
| `[bhg_my_tournaments]` | List tournaments participated with ranking. | ❌ | Not implemented. | Same as above |
| `[bhg_my_prizes]` | List prizes won. | ❌ | Not implemented. | Same |
| `[bhg_my_rankings]` | Aggregate bonus hunt/tournament rankings. | ❌ | Not implemented. | Same |
| Admin toggle to hide/show each shortcode output section. | ❌ | No settings present. | `admin/views/settings.php`, `includes/class-bhg-settings.php` |

## Frontend Verification Points

| Area | Status | Notes | Files to Modify |
|------|:------:|-------|-----------------|
| Sorting/search/pagination (30/page) across shortcode tables. | ⚠️ | Pagination defaults to 20 in several shortcodes; search filters inconsistent. | `includes/shortcodes/class-bhg-shortcode-user-guesses.php`, `class-bhg-shortcode-hunts.php`, `class-bhg-shortcode-tournaments.php`, `class-bhg-shortcode-leaderboards.php` |
| Timeline filters (This Week/Month/Year/Last Year/All-Time). | ✅ | Implemented in `BHG_Timeline`, ensure dropdowns match scope. | `includes/helpers/class-bhg-timeline.php` |
| Affiliate indicator lights and website display. | ⚠️ | Indicator helper exists but not rendered in all list templates. | `templates/frontend/*.php`, `includes/helpers/class-bhg-templates.php` |
| Profile improvements show user data. | ⚠️ | `[bhg_user_profile]` shows basics but omits affiliate websites per requirement. | `includes/shortcodes/class-bhg-shortcode-user-profile.php`, `templates/frontend/user-profile.php` |
| General shortcode registration. | ⚠️ | Core shortcodes registered, but duplicates (e.g. `[bhg_tournaments]`) without new filters documented. | `includes/class-bhg-shortcodes.php` |

## Summary

Significant development work remains to align the plugin with the customer scope. The table above
identifies the priority gaps and the corresponding files to update. Address coding standard coverage
first to prevent regressions, then tackle the missing backend/frontend features—especially the new
profile shortcodes and prize management expectations.

