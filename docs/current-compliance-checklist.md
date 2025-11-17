# Customer Requirements Checklist (snapshot)

## Runtime, Standards & Text Domain
- **Plugin header versions**: Declares PHP 7.4, WordPress 6.3.5, MySQL 5.5.5, and text domain `bonus-hunt-guesser`. **Met.**【F:bonus-hunt-guesser.php†L3-L13】
- **Text domain loading**: Loaded on `plugins_loaded` via `load_plugin_textdomain`. **Met.**【F:bonus-hunt-guesser.php†L400-L431】
- **Coding standards (PHPCS)**: Latest run with project ruleset reports 4,134 errors and 1,648 warnings across 45 files. **Not met.**【25f70d†L2-L58】

## Leaderboard Shortcode (Frontend)
- **Timeline options**: Normalizes and restricts to the approved set (Today, This Week, This Month, This Quarter, This Year, Last Year, Alltime). **Met.**【F:includes/class-bhg-shortcodes.php†L4446-L4470】
- **Bonushunt attribute exposure**: Shortcode still accepts a `bonushunt` attribute even though leaderboards should be tournament-only. **Not met.**【F:includes/class-bhg-shortcodes.php†L4386-L4404】
- **Active tournament prize box**: Prize box rendering is gated on an active, selected tournament with prizes. **Met.**【F:includes/class-bhg-shortcodes.php†L4574-L4635】

## Outstanding / Unverified
- **Functional coverage**: Numerous agreed requirements (e.g., multi-winner admin UX, jackpot module, dual prize sets, list shortcodes, search/pagination defaults, tournament edits, translations tab content) remain unverified in this snapshot; further implementation review is required to confirm compliance.
- **PHPCS remediation**: Full standards compliance work remains outstanding to satisfy acceptance criteria.
