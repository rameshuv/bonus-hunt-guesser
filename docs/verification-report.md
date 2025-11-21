# Verification Report (PHP 7.4 / WordPress 6.3.5)

## Environment & Versioning
- Plugin header declares compatibility with WordPress 6.3.5+, PHP 7.4, and MySQL 5.5.5 alongside version 8.0.18, matching the requested stack for verification.【F:bonus-hunt-guesser.php†L3-L13】
- Global setting **Shortcode rows per page** remains available in the admin Settings screen to control pagination defaults for leaderboard and tournament tables.【F:admin/views/settings.php†L102-L107】

## Leaderboards & Tournaments
- The shared leaderboard query aggregates all eligible participants (including tournament-specific union rows) with timeline, tournament, affiliate-site/status, and pagination handling; it exposes `per_page`/`paged` arguments for slicing while keeping totals for pagination controls.【F:includes/class-bhg-shortcodes.php†L257-L520】
- Leaderboard output supports sortable Position/Username/Times Won/Avg columns, heading blocks for selected tournament/bonushunt, affiliate indicator cells, integer-only averages, and capitalized usernames to meet display expectations.【F:includes/class-bhg-shortcodes.php†L4862-L4950】
- Prize sections for selected tournaments can auto-render above the leaderboard table, with `show_prize_summary` controlling whether the summary list appears when a tournament context exists.【F:includes/class-bhg-shortcodes.php†L4497-L4600】
- Leaderboard filters are attribute-driven (`filters="timeline,tournament,affiliate_site,affiliate_status"`) with normalization of the allowed keys and support for disabling all filters via empty/none values.【F:includes/class-bhg-shortcodes.php†L1092-L1168】

## Shortcodes & Frontend Lists
- Compact/alternative views exist for the requested lists: latest winners (`latest-winners-list`), leaderboard list, tournament list, and bonushunt list shortcodes all include attribute-controlled field visibility, ordering, and limits consistent with the specification.【F:includes/class-bhg-shortcodes.php†L3215-L3953】

## Pagination & Search Controls
- Leaderboard shortcode attributes include `per_page`, `paged`, and `show_search`, with default rows-per-page derived from the global setting and filter-aware query args preserved when paginating or sorting.【F:includes/class-bhg-shortcodes.php†L4358-L4680】

## Testing & Tooling Notes
- Composer install (to fetch PHPUnit/PHPCS tooling) could not complete because GitHub downloads require credentials through the restricted network tunnel; the process halted before dependencies were installed.【a4e2a7†L1-L51】
