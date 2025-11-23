# Tournament Adjustments Review

Checklist of requested behaviors (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+):

- [x] Admin add/edit tournament screen exposes a **Number of Winners** field persisted with sane defaults (1–25).【F:admin/views/tournaments.php†L360-L371】
- [x] Active tournament views show a yellow countdown banner above the standings: “This tournament will close in _x_ days.”【F:includes/class-bhg-shortcodes.php†L5249-L5273】【F:assets/css/bhg-shortcodes.css†L972-L980】
- [x] Tournament tables label the wins column as **Times Won** to match leaderboard phrasing.【F:includes/class-bhg-shortcodes.php†L5347-L5354】
- [x] Position column header matches the leaderboard wording instead of “#” and is sortable with an icon toggle.【F:includes/class-bhg-shortcodes.php†L5346-L5355】
- [x] “Last win” values come from the user’s most recent eligible prize win in hunts tied to the tournament (not just their last tournament result).【F:includes/class-bhg-shortcodes.php†L5195-L5219】【F:includes/class-bhg-shortcodes.php†L5376-L5389】
- [x] Tournament result tables paginate using the global shortcode rows-per-page setting, and the setting is configurable in General Settings.【F:includes/class-bhg-shortcodes.php†L5306-L5414】【F:admin/views/settings.php†L105-L111】【F:includes/helpers.php†L1155-L1171】

No further code changes were required beyond documentation of the verification above.
