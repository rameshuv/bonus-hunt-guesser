# Customer Requirements Checklist (snapshot)

## Runtime, Standards & Text Domain
- **Plugin header versions**: Declares PHP 7.4, WordPress 6.3.5, MySQL 5.5.5, and text domain `bonus-hunt-guesser`. **Met.**【F:bonus-hunt-guesser.php†L3-L13】
- **Text domain loading**: Loaded on `plugins_loaded` via `load_plugin_textdomain`. **Met.**【F:bonus-hunt-guesser.php†L400-L430】
- **Coding standards (PHPCS)**: Latest run shows 4,134 errors and 1,648 warnings across 45 files under the configured WordPress standards. **Not met.**【524f9e†L2-L57】

## Leaderboard Shortcode (Frontend)
- **Timeline options**: Normalizes and restricts to the approved set (Today, This Week, This Month, This Quarter, This Year, Last Year, Alltime). **Met.**【F:includes/class-bhg-shortcodes.php†L4446-L4470】
- **Bonushunt attribute**: Shortcode still accepts a `bonushunt` attribute even though only tournament filtering should be exposed. **Not met.**【F:includes/class-bhg-shortcodes.php†L4386-L4404】
- **Active tournament prize box**: Prize box rendering is gated on an active selected tournament with prizes. **Met.**【F:includes/class-bhg-shortcodes.php†L4592-L4630】

## Outstanding / Unverified
- **Functional coverage**: Requirements such as multi-winner admin UX, jackpot module behavior, dual prize sets, notification templates, search/pagination defaults, and other checklist items remain unverified in this snapshot.
- **PHPCS remediation**: Full standards compliance work is pending to satisfy acceptance criteria.
