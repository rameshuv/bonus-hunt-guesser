# Customer Requirements Checklist (snapshot)

## Runtime, Standards & Text Domain
- **Plugin header metadata**: Declares version 8.0.18, PHP 7.4, WordPress 6.3.5 minimum, MySQL 5.5.5 minimum, GPLv2+, and text domain `bonus-hunt-guesser`. **Met.**【F:bonus-hunt-guesser.php†L3-L13】
- **Text domain loading**: Loaded on `plugins_loaded` via `load_plugin_textdomain`. **Met.**【F:bonus-hunt-guesser.php†L400-L430】
- **Coding standards (PHPCS)**: Current run against `phpcs.xml` reports 4,134 errors and 1,648 warnings across 45 files. **Not met.**【41ce54†L1-L43】

## Leaderboard Shortcode (Frontend)
- **Timeline restriction**: Normalizes timeline to approved values (Today, This Week, This Month, This Quarter, This Year, Last Year, Alltime). **Met.**【F:includes/class-bhg-shortcodes.php†L4446-L4470】
- **Bonushunt attribute exposure**: Shortcode still accepts `bonushunt` attribute even though leaderboards should be tournament-only. **Not met.**【F:includes/class-bhg-shortcodes.php†L4386-L4400】
- **Active tournament prize box**: Prize display is gated on a selected, active tournament with prizes; markup is inserted above the table. **Met.**【F:includes/class-bhg-shortcodes.php†L4591-L4629】

## Immediate Remediation Targets (files to modify)
- Remove `bonushunt` handling and dropdown exposure from the leaderboard shortcode to comply with tournament-only scope. **File:** `includes/class-bhg-shortcodes.php`.【F:includes/class-bhg-shortcodes.php†L4386-L4400】
- Address PHPCS violations project-wide to satisfy WordPress-Core/Docs/Extra standards (see summary for affected files). **Files:** Multiple (`bonus-hunt-guesser.php`, admin/*, includes/*, tests/*).【41ce54†L1-L43】
- Verify and implement remaining customer requirements (jackpots, dual prize sets, list shortcodes, admin UX adjustments, notifications, etc.), as coverage is not yet validated in codebase. **Files:** admin/*, includes/*, shortcodes/views.
