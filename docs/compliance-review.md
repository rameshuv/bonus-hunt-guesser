# Bonus Hunt Guesser v8.0.14 – Compliance Review

*Reviewed on 2025-10-21 (UTC).* This document captures the current code-level compliance status against the v8.0.14 customer specification. Each gap lists the files that require updates.

## General & Versioning

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Plugin header must advertise v8.0.14 with the agreed runtime guard (WP ≥ 6.3.0, MySQL 5.5.5). | ✅ | `bonus-hunt-guesser.php` now declares Version 8.0.14, Requires at least 6.3.0, and Requires MySQL 5.5.5. |
| Constants should match header (e.g., `BHG_VERSION` = 8.0.14, `BHG_MIN_WP` = 6.3.0). | ✅ | Core constants in `bonus-hunt-guesser.php` updated to 8.0.14 / 6.3.0 for consistency. |

## Database Schema

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Remove the legacy `type` column from tournaments and rely on `participants_mode` + date logic. | ✅ | `includes/class-bhg-db.php` drops the legacy `type` column/index during migration and no longer creates it for new installs. |
| Junction table must be named `wp_bhg_tournaments_hunts` with unique (`tournament_id`,`hunt_id`). | ✅ | Schema now provisions `bhg_tournaments_hunts`, migrates rows from `bhg_hunt_tournaments`, and enforces the (`tournament_id`,`hunt_id`) uniqueness constraint. |

## Settings & Currency

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Store currency in `bhg_currency` option with helpers `bhg_currency_symbol()` / `bhg_format_money()` using that option. | ✅ | Currency now persists via the `bhg_currency` option with migration/back-compat in `bonus-hunt-guesser.php`, `includes/helpers.php`, and `admin/views/settings.php`; outputs use `bhg_format_money()`. |

## Admin Dashboard

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| “Latest Hunts” widget lists winners one per row with bold usernames (up to 25). | ✅ | `admin/views/dashboard.php` now renders each winner inside a `<ul class="bhg-dashboard-winners-list">` with bold usernames and separate list rows. |

## Bonus Hunts Admin (`bhg-bonus-hunts`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Multi-select should show only active tournaments to keep the list short. | ✅ | `admin/views/bonus-hunts.php` and `admin/views/bonus-hunts-edit.php` now query only active tournaments while always preserving already-linked IDs. |

## Hunt Results (`bhg-bonus-hunts-results`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Include a “Prize” column showing the assigned prize title for winners. | ✅ | `admin/views/bonus-hunts-results.php` maps hunt prize selections to each winner row and renders a dedicated “Prize” column. |
| Standardize row colors to grey/white instead of green highlight. | ✅ | `assets/css/admin.css` keeps the core striped grey/white rows and highlights winners with bold green text only (no background overrides). |

## Tournaments Admin (`bhg-tournaments`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Connected bonus hunts list limited to hunts from the current year plus already linked ones. | ✅ | `admin/views/tournaments.php` now limits the manual selector to hunts from the current calendar year and any already-associated hunt IDs. |

## Users Admin (`bhg-users`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Profile rows must expose affiliate yes/no toggles per affiliate website. | ✅ | `admin/views/users.php` renders a checkbox list sourced from `bhg_affiliate_websites`, persisting selections via `bhg_affiliate_websites` user meta. |

## Additional Observations

* The review did not cover every shortcode/front-end rule in depth. Re-run functional QA after addressing the blockers above.
* Keep PHPCS (`phpcs.xml`) checks enabled before committing changes.
