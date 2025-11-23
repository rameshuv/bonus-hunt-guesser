# robinbos Feature Verification (PHP 7.4 / WP 6.3.5)

## Prize Adjustments
- [x] Admin add/edit Bonus Hunt form exposes Number of Winners plus per-position Regular and Premium prize selectors so each winner slot (1..N) can map to distinct rewards. 【F:admin/views/bonus-hunts-edit.php†L123-L165】
- [x] Prize rendering builds carousel/tab layouts for Regular vs Premium prize sets and appends per-place summary lists beneath the prize cards when summaries are enabled. 【F:includes/class-bhg-shortcodes.php†L1600-L1693】
- [x] Leaderboard shortcode pulls tournament prize data when a tournament filter is active, shows tabbed regular/premium prizes above the table, and respects the `show_prize_summary` toggle (auto/default shows when tournament selected). 【F:includes/class-bhg-shortcodes.php†L4702-L5096】

## Frontpage List Shortcodes
- [x] `latest-winners-list` shortcode outputs a text list of recent winners with per-field visibility controls (date, username, prize, bonushunt, tournament, position) and timeline/status filters. 【F:includes/class-bhg-shortcodes.php†L3210-L3372】
- [x] `leaderboard-list`, `tournament-list`, and `bonushunt-list` shortcodes provide condensed text lists with tournament/bonushunt filters, timeline/status dropdowns limited to the approved ranges, and per-field show/hide options for the requested columns. 【F:includes/class-bhg-shortcodes.php†L3380-L3960】

## Shortcode Visibility Controls
- [x] Prize/tournament/leaderboard shortcodes support `show_prize_summary`, `show_prizes`, and `show_search` attributes to toggle summary lists, prize boxes, and search blocks; bonushunt/tournament filters expose only the approved timeline/status options. 【F:includes/class-bhg-shortcodes.php†L4702-L5096】【F:includes/class-bhg-shortcodes.php†L5032-L5079】【F:includes/class-bhg-shortcodes.php†L5250-L5460】

## Testing
- [ ] `composer install --no-interaction` *(fails: GitHub CONNECT tunnel 403 while cloning dependencies; vendor bin tools unavailable in this environment).* 【cd6ff6†L1-L36】【289172†L1-L38】
- [ ] `vendor/bin/phpunit` *(not run; depends on Composer install success).* 
