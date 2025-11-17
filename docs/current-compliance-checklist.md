# Customer Requirements Checklist (snapshot)

## Runtime, Standards & Text Domain
- **Plugin header versions**: Declares PHP 7.4, WordPress 6.3.5, MySQL 5.5.5, and text domain `bonus-hunt-guesser`. **Met.**【F:bonus-hunt-guesser.php†L3-L13】
- **Text domain loading**: Loaded on `plugins_loaded` via `load_plugin_textdomain`. **Met.**【F:bonus-hunt-guesser.php†L400-L431】
- **Coding standards (PHPCS)**: Latest run with project ruleset reports 4,134 errors and 1,648 warnings across 45 files. **Not met.**【05db44†L2-L48】

## Leaderboard Shortcode (Frontend)
- **Timeline options**: Normalizes and restricts to approved set (Today, This Week, This Month, This Quarter, This Year, Last Year, Alltime). **Met.**【F:includes/class-bhg-shortcodes.php†L4446-L4470】
- **Bonushunt attribute exposure**: Shortcode still accepts a `bonushunt` attribute even though leaderboards should be tournament-only. **Not met.**【F:includes/class-bhg-shortcodes.php†L4386-L4404】
- **Active tournament prize box**: Prize box rendering gated on an active, selected tournament with prizes. **Met.**【F:includes/class-bhg-shortcodes.php†L4574-L4635】

## Outstanding / Unverified
- **Functional coverage**: Many checklist items (multi-winner admin UX, jackpot module, dual prize sets, notification templates, list shortcodes, search/pagination defaults, etc.) remain unverified in this snapshot.
- **PHPCS remediation**: Full standards compliance work is pending to satisfy acceptance criteria.
