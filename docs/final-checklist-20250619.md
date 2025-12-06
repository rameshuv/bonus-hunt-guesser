# Delivery readiness review — 2025-06-19

## Runtime/version
- Plugin header version remains **8.0.23** (matches requested runtime targets).

## Tests executed
- None in this review window; PHPUnit/PHPCS not run.

## Delivery blockers / items still needing verification
The following customer requirements remain unverified in the current branch and must be confirmed before delivery:

1. **Leaderboards**
   - "Times won" calculation accuracy across hunts and winners.
   - Prize block display options for grid vs. carousel, visible count, and total items per view.
   - Affiliate/tournament filters (e.g., moderators group) returning only matching users.
   - Pagination links functioning and clickable.
   - Accurate "Avg tournament position" calculation across closed/open tournaments.
   - Tournament dropdown showing all tournaments (plus "All") even when preselected.
   - `bhg_leaderboard_list` shortcode rendering without PHP errors/warnings.

2. **Tournaments**
   - Frontend tournament detail shortcode output loading correctly.
   - Prize block layout options (grid vs. carousel) honoring backend settings.
   - Pagination working across tournament, bonus hunt, and other paginated shortcodes.
   - Affiliate website filter limiting participants appropriately (e.g., moderators-only tournaments).
   - Winner highlighting tied to the configured number of winners.
   - Table header label updated from "Username" to "User".
   - Countdown block positioned below prizes with consistent content block layout.

3. **Global filters and UI/UX**
   - Uniform dropdown styling across bonus hunts, leaderboards, tournaments, and user guess shortcodes.
   - Dropdowns aligned in a single row; search bar on its own row spanning full width as specified.
   - Search/filter button alignment (height, spacing, padding) consistent across all shortcodes.
   - User guess form padding and button style matching global button design.

## Recommendation
Because the above functional and UX items are still unverified and no automated tests have been executed in this cycle, the plugin is **not yet ready for delivery**. Complete verification and fixes for these points, then rerun tests before shipping.
