# Bonus Hunt Guesser v8.0.14 – Compliance Review

*Reviewed on 2025-10-21 (UTC).* This document captures the current code-level compliance status against the v8.0.14 customer specification. Each gap lists the files that require updates.

## General & Versioning

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Plugin header must advertise v8.0.14 with the agreed runtime guard (WP ≥ 6.3.0, MySQL 5.5.5). | ❌ | Header still reports `Version: 8.0.12` and `Requires at least: 5.5.5`; update the block in `bonus-hunt-guesser.php`. |
| Constants should match header (e.g., `BHG_VERSION` = 8.0.14, `BHG_MIN_WP` = 6.3.0). | ❌ | `bonus-hunt-guesser.php` defines `BHG_VERSION` as `8.0.12` and `BHG_MIN_WP` as `5.5.5`; align them with the spec. |

## Database Schema

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Remove the legacy `type` column from tournaments and rely on `participants_mode` + date logic. | ❌ | `includes/class-bhg-db.php` still creates and back-fills a `type` column for `bhg_tournaments`; drop the column and associated index handling. |
| Junction table must be named `wp_bhg_tournaments_hunts` with unique (`tournament_id`,`hunt_id`). | ❌ | Schema currently provisions `bhg_hunt_tournaments`; rename/migrate to the specified table name and uniqueness. |

## Settings & Currency

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Store currency in `bhg_currency` option with helpers `bhg_currency_symbol()` / `bhg_format_money()` using that option. | ❌ | Currency helpers read `bhg_plugin_settings['currency']`; adjust helpers, settings admin, and install logic (`includes/helpers.php`, `admin/views/settings.php`, `bonus-hunt-guesser.php`) to use the standalone option. |

## Admin Dashboard

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| “Latest Hunts” widget lists winners one per row with bold usernames (up to 25). | ❌ | `admin/views/dashboard.php` collapses all winners into a single bullet-separated string with no bold styling; refactor rendering to output one `<tr>` per winner with `<strong>` usernames. |

## Bonus Hunts Admin (`bhg-bonus-hunts`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Multi-select should show only active tournaments to keep the list short. | ❌ | `admin/views/bonus-hunts.php` loads every tournament (`SELECT ... ORDER BY title ASC`); add filtering for active tournaments and consider limiting to current period. |

## Hunt Results (`bhg-bonus-hunts-results`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Include a “Prize” column showing the assigned prize title for winners. | ❌ | `admin/views/bonus-hunts-results.php` builds the column set without any prize data; extend query/output to join prize selections and show the label. |
| Standardize row colors to grey/white instead of green highlight. | ❌ | CSS `assets/css/admin.css` defines `.bhg-winner-row { background-color: #d1fae5; }`; adjust styling to comply with the grey/white requirement while still differentiating winners (e.g., bold text). |

## Tournaments Admin (`bhg-tournaments`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Connected bonus hunts list limited to hunts from the current year plus already linked ones. | ❌ | `admin/views/tournaments.php` fetches all hunts with `SELECT id, title FROM ... ORDER BY title ASC`; update the query/filter logic accordingly. |

## Users Admin (`bhg-users`)

| Requirement | Status | Notes / Required Changes |
| --- | --- | --- |
| Profile rows must expose affiliate yes/no toggles per affiliate website. | ❌ | `admin/views/users.php` only handles a single `bhg_is_affiliate` flag; extend data model and UI to iterate over sites from `bhg_affiliate_websites` (and adjust helper output where affiliate lights are rendered). |

## Additional Observations

* The review did not cover every shortcode/front-end rule in depth. Re-run functional QA after addressing the blockers above.
* Keep PHPCS (`phpcs.xml`) checks enabled before committing changes.
