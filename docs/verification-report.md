# Detailed Verification Checklist (PHP 7.4 / WordPress 6.3.5)

## Environment & Tooling
- [ ] Confirm the plugin header declares version **8.0.18** with minimums **WordPress 6.3.5**, **PHP 7.4**, and **MySQL 5.5.5** before running compatibility checks.【F:bonus-hunt-guesser.php†L3-L15】
- [ ] Set or validate the default **Shortcode rows per page** value in the Settings screen so leaderboard/tournament pagination inherits the expected row count.【F:admin/views/settings.php†L102-L107】
- [ ] When provisioning tests, prefer the built-in pagination defaults from shortcode attributes (`per_page`, `paged`) rather than custom limits to mirror production behavior.【F:includes/class-bhg-shortcodes.php†L273-L340】【F:includes/class-bhg-shortcodes.php†L4460-L4700】

## Leaderboard Verification
- [ ] Run the shared leaderboard query with tournament/timeline/affiliate/site filters to verify it returns all matching participants (not a single row) while supplying total counts and pagination metadata.【F:includes/class-bhg-shortcodes.php†L257-L360】
- [ ] Confirm the leaderboard filter bar only exposes **Timeline**, **Tournament**, **Affiliate Site**, and **Affiliate Status** dropdowns (bonushunt selector removed) and that `show_search="no"` suppresses the search input.【F:includes/class-bhg-shortcodes.php†L4755-L4857】
- [ ] Click column headers for **Position**, **Username**, **Times Won**, **Avg Hunt Pos**, and **Avg Tournament Pos** to validate ascending/descending sorting controls and icons.【F:includes/class-bhg-shortcodes.php†L4719-L4782】【F:includes/class-bhg-shortcodes.php†L4878-L4893】
- [ ] Verify headings above the table reflect selected tournament/bonushunt IDs and that active tournament prizes render in a box above the grid when prize data exists and `show_prize_summary` permits summaries.【F:includes/class-bhg-shortcodes.php†L4497-L4600】【F:includes/class-bhg-shortcodes.php†L4861-L4874】
- [ ] Check table cells to ensure usernames are capitalized, average ranks are integers (no decimals), and affiliate status shows the colored indicator column directly after average tournament position.【F:includes/class-bhg-shortcodes.php†L4914-L4941】
- [ ] Confirm the **Username** header replaces any legacy "User" label across the leaderboard columns.【F:includes/class-bhg-shortcodes.php†L4882-L4885】
- [ ] Inspect pagination links to ensure they preserve filters/sorting (`bhg_orderby`, `bhg_order`, `bhg_timeline`, `bhg_tournament`, `bhg_aff`, `bhg_site`) and use the default rows-per-page setting when `per_page` is omitted.【F:includes/class-bhg-shortcodes.php†L4957-L4994】

## Tournament View Checks
- [ ] In the add/edit tournament form, set **Number of Winners** and confirm the value persists after save with helper text describing the limit.【F:admin/views/tournaments.php†L360-L370】
- [ ] For active tournaments, verify the notice "This tournament will close in x days" appears between the dates and description, counting whole days until end-of-day.【F:includes/class-bhg-shortcodes.php†L5194-L5218】
- [ ] Validate tournament tables show headers **Position**, **Username**, **Times Won**, and **Last win**, with sortable position support similar to the leaderboard view.【F:includes/class-bhg-shortcodes.php†L5267-L5300】
- [ ] Confirm **Last win** values derive from the latest prize within connected bonushunts (including legacy tournament relations) rather than earlier tournament wins alone.【F:includes/class-bhg-shortcodes.php†L5302-L5335】
- [ ] Ensure pagination on tournament results follows the configured per-page default and exposes navigation when total rows exceed one page.【F:includes/class-bhg-shortcodes.php†L5345-L5360】

## Prize Display
- [ ] When multiple winner slots exist, confirm regular and premium prize sets render in tabbed boxes, with summary headings for each prize type when summaries are enabled.【F:includes/class-bhg-shortcodes.php†L2267-L2319】
- [ ] Validate prize summaries/prize boxes can be toggled on shortcodes through `show_prize_summary` / `show_prizes` options and inherit defaults for tournament-context leaderboards.【F:includes/class-bhg-shortcodes.php†L4497-L4600】【F:includes/class-bhg-shortcodes.php†L2250-L2312】

## Frontend List Shortcodes
- [ ] `[latest-winners-list]`: verify attribute-controlled fields (date, username, prize, bonushunt, tournament, position), order by most recent wins, and username capitalization in the condensed list output.【F:includes/class-bhg-shortcodes.php†L3215-L3372】
- [ ] `[leaderboard-list]`: confirm tournament/bonushunt ID filters, per-field visibility toggles, and ranking logic mirror the main leaderboard results.【F:includes/class-bhg-shortcodes.php†L3380-L3640】
- [ ] `[tournament-list]`: test timeline and status filters (Alltime/Today/This Week/This Month/This Quarter/This Year/Last Year) plus show/hide controls for name, dates, status, and details link.【F:includes/class-bhg-shortcodes.php†L3643-L3788】
- [ ] `[bonushunt-list]`: check timeline/status filters and field toggles for balances, winners, status, and details to ensure list output respects requested visibility.【F:includes/class-bhg-shortcodes.php†L3791-L3960】

## Timeline & Search Controls
- [ ] Confirm all leaderboard/tournament filters only present the allowed timeline options: **Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year**.【F:includes/class-bhg-shortcodes.php†L4801-L4809】【F:includes/class-bhg-shortcodes.php†L3695-L3708】
- [ ] Toggle `show_search="no"` on leaderboard/tournament shortcodes to ensure the search block is hidden while preserving other GET parameters via hidden inputs.【F:includes/class-bhg-shortcodes.php†L4784-L4857】

## Pagination Behavior
- [ ] Set `per_page` and `bhg_paged` query args on leaderboards to validate slice sizes and ensure pagination retains filter/sort query parameters.【F:includes/class-bhg-shortcodes.php†L4460-L4700】【F:includes/class-bhg-shortcodes.php†L4957-L4994】
- [ ] For tournament views, verify calculated `total_pages` uses `(total/per_page)` and renders navigation when applicable.【F:includes/class-bhg-shortcodes.php†L5345-L5360】

## Outstanding Tooling (local environment)
- [ ] Re-run `composer install` to fetch PHPUnit/PHPCS once network credentials are available; previous attempts in this environment could not reach GitHub-hosted packages.【F:composer.json†L1-L96】

## Testing Matrix (manual + automated)
- [ ] **Static analysis**: run `php -d memory_limit=-1 vendor/bin/phpcs -ps` to confirm WordPress ruleset compliance and capture any new violations introduced by shortcode or admin changes.【F:phpcs.xml.dist†L1-L20】
- [ ] **Unit/integration**: execute `php -d memory_limit=-1 vendor/bin/phpunit` against the bundled wp-phpunit scaffold once credentials are configured; record failures by shortcode area (leaderboard, tournament, list shortcodes, prizes).【F:phpunit.xml.dist†L1-L39】
- [ ] **Sample data seeding**: generate at least 30 users and 3 tournaments/bonushunts with wins across timelines to validate pagination, sorting, and timeline filters end-to-end; ensure tournament ID 3 has ≥26 participants for leaderboard cross-checks.【F:includes/class-bhg-shortcodes.php†L257-L360】
- [ ] **Filter persistence**: while paginating and sorting, confirm query strings keep `bhg_orderby`, `bhg_order`, `bhg_timeline`, `bhg_tournament`, `bhg_aff`, `bhg_site`, and search terms intact between requests.【F:includes/class-bhg-shortcodes.php†L4957-L4994】
- [ ] **Prize visibility toggles**: test combinations of `show_prizes`, `show_prize_summary`, and `show_search` attributes (yes/no) on leaderboard and tournament shortcodes to ensure prize boxes, summaries, and search bars appear or hide as configured, including prize tabs for regular vs premium rewards.【F:includes/class-bhg-shortcodes.php†L2250-L2319】【F:includes/class-bhg-shortcodes.php†L4497-L4874】
- [ ] **Rendering regression sweep**: load each shortcode (`leaderboard`, `tournament`, `latest-winners-list`, `leaderboard-list`, `tournament-list`, `bonushunt-list`) with and without timeline/status filters to confirm username capitalization, integer averages, affiliate indicators, and header labels render consistently with the checklist expectations.【F:includes/class-bhg-shortcodes.php†L3215-L3960】【F:includes/class-bhg-shortcodes.php†L4719-L4941】

## Test Execution Log (this run)
- Composer install (dev deps) is still blocked by GitHub CONNECT 403s while cloning PHPCS standards; tooling remains unavailable until credentials are provided.【5831d6†L1-L34】
- PHPUnit was not executed because the Composer install failure prevented vendor/bin from being created; rerun after resolving the dependency download issue.【5831d6†L1-L34】
