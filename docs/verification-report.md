# Bonus Hunt Guesser – Feature Verification

This document captures code-level evidence that the current plugin implementation satisfies the
customer requirements enumerated in Robin's latest checklist. Each subsection lists the core
requirement, the primary implementation touchpoints, and any follow-up action if changes are
required. When a rectification is necessary, update the files listed in the "Modify" column.

## Sorting, Search, and Pagination (30 per page)

| Area | Evidence | Modify |
|------|----------|--------|
| `[bhg_user_guesses]` | `includes/shortcodes/class-bhg-shortcode-user-guesses.php` builds a `WP_Query` wrapper that enforces a 30 item `LIMIT`, handles `paged` args, and inspects `$_GET['bhg_search']` / `$_GET['bhg_orderby']`. | Same file and `includes/class-bhg-data-access.php` if query adjustments are required. |
| `[bhg_hunts]` | `includes/shortcodes/class-bhg-shortcode-hunts.php` registers sortable headers, forwards search to `BHG_Query_Helper::apply_search_filters()`, and paginates via `bhg_paginate_links()` (30 rows). | Modify shortcode class; shared helpers in `includes/helpers/class-bhg-query-helper.php`. |
| `[bhg_tournaments]` | `includes/shortcodes/class-bhg-shortcode-tournaments.php` mirrors the hunts implementation for ordering, searching, and pagination. | Same shortcode class and shared query helper. |
| `[bhg_leaderboards]` | `includes/shortcodes/class-bhg-shortcode-leaderboards.php` exposes sortable headings, accepts `bhg_search`, and slices results in 30-row pages using `bhg_build_pagination()`. | Update shortcode class or pagination helper `includes/helpers/class-bhg-pagination.php`. |
| Admin Lists | `admin/views/bonus-hunts.php`, `admin/views/tournaments.php`, `admin/views/users.php`, and related `WP_List_Table` subclasses enforce 30 rows per page while honouring `orderby`, `order`, and `s` query parameters. | Adjust respective `class-bhg-*-list-table.php` files if behaviour needs changes. |

## Timeline Filters

| Area | Evidence | Modify |
|------|----------|--------|
| Timeline helper | `includes/helpers/class-bhg-timeline.php` resolves keywords (`this-week`, `this-month`, `this-year`, `last-year`, `all-time`) to start/end dates. | Update helper to add/adjust ranges. |
| Shortcodes | `class-bhg-shortcode-user-guesses.php`, `class-bhg-shortcode-hunts.php`, `class-bhg-shortcode-tournaments.php`, and `class-bhg-shortcode-leaderboards.php` call `BHG_Timeline::resolve()` before composing SQL WHERE clauses. | Modify respective shortcode files to alter timeline handling. |
| Admin reports | `admin/views/hunt-results.php` uses the same helper to trim dropdowns and result sets. | Adjust view/controller if additional filters are required. |

## Affiliate Indicators & Websites

| Requirement | Evidence | Modify |
|-------------|----------|--------|
| Indicator lights | `includes/helpers/class-bhg-templates.php::render_affiliate_indicator()` outputs green/red SVG badges. Invoked by guess, hunt, and leaderboard templates. | Update helper for styling tweaks. |
| Template usage | `templates/frontend/guess-row.php`, `templates/frontend/leaderboard-row.php`, and `templates/frontend/tournament-row.php` call the helper so each row shows the indicator. | Modify individual template partials to change placement. |
| Affiliate website name | `includes/helpers/class-bhg-templates.php::render_affiliate_site_name()` fetches the linked site label and appends it to table cells when shortcode attribute `show_website` is true. | Update helper or templates that call it. |

## Profile Output

| Requirement | Evidence | Modify |
|-------------|----------|--------|
| Extended profile data | `includes/shortcodes/class-bhg-shortcode-user-profile.php` collects real name, username, email, affiliate flag, and connected affiliate sites before rendering `templates/frontend/user-profile.php`. | Update shortcode class or template file for additional fields. |
| Admin profile linkage | `admin/views/users.php` provides quick links to edit each user profile, exposing the same fields for administrators. | Modify view/controller to add further metadata. |

## Shortcode Inventory & Registration

| Requirement | Evidence | Modify |
|-------------|----------|--------|
| Registration | `includes/class-bhg-shortcodes.php::__construct()` registers all customer-requested shortcodes: `bhg_user_profile`, `bhg_active_hunt`, `bhg_guess_form`, `bhg_tournaments`, `bhg_winner_notifications`, `bhg_leaderboard`, `bhg_user_guesses`, `bhg_hunts`, `bhg_leaderboards`, and `bhg_advertising`. | Update constructor to add or remove shortcode bindings. |
| Handler locations | Each shortcode has a dedicated class inside `includes/shortcodes/` matching the naming convention `class-bhg-shortcode-*.php`. | Modify the relevant handler class when adjusting behaviour. |

## Additional Notes

* If any verification step fails during QA, refer to the "Modify" column for the exact file to update.
* Coding standards: run `composer lint` (PHPCS) after changes that touch PHP templates or helpers.
* Database migrations live in `includes/class-bhg-db.php`; adjust there if schema updates are required.
