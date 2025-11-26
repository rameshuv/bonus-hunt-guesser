# Plugin Review & QA Checklist

This checklist consolidates the requested manual QA and data-verification steps for the Bonus Hunt Guesser plugin in a WordPress environment (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+).

## Environment preparation
- [ ] Install and activate the plugin on a clean WordPress 6.3.5 site running PHP 7.4 and MySQL 5.5.5+.
- [ ] Run the plugin setup to create all custom tables.
- [ ] Seed demo data for hunts, tournaments, prizes (including jackpots), users, and guesses so every shortcode has data to render.
- [ ] Confirm WP_DEBUG is enabled to surface notices during testing.

### Demo data reset + seed (full coverage)
- [ ] From wp-admin, use the demo tools to run `bhg_reset_demo_and_seed()` so every table (hunts, winners, guesses, tournaments, tournament results, jackpots/prizes, ads, affiliate sites) is repopulated. The helper wipes tables (except translations/affiliate sites) and reseeds hunts, winners, tournaments, and ads for realistic leaderboard math.【F:includes/helpers.php†L1794-L2060】
- [ ] After seeding, verify at least one open and one closed hunt exist and that closed hunts have winners assigned so “Times won” and average position fields return values.【F:includes/helpers.php†L1856-L1982】
- [ ] Confirm tournament results are generated from closed hunts so leaderboards and tournament detail pages have data.【F:includes/helpers.php†L1984-L2060】

## Data completeness (demo data)
- [ ] Bonus Hunts: ensure each hunt has affiliate website, prizes (including jackpot/new prizes), start/final dates, status, winners, and related guesses.
- [ ] Tournaments: ensure tournaments reference hunts and have prize tiers populated; confirm at least one active and multiple closed tournaments exist.
- [ ] Leaderboards: populate wins so “Times won”, average hunt position, and average tournament position can be validated.
- [ ] Users: assign affiliate websites (e.g., Moderators) and affiliate status flags to several accounts.

## Shortcode coverage (admin list)
Validate that every shortcode registered in the admin area renders without PHP errors and shows data when demo content is present. Use the shortcode registration block for a definitive list (aliases included) so nothing is skipped.【F:includes/class-bhg-shortcodes.php†L62-L109】
- [ ] `[bhg_bonus_hunt]` / `[bhg_hunts]` / `[bhg_bonushunt_list]`
- [ ] `[bhg_leaderboard]` / `[bhg_leaderboards]` / `[bhg_leaderboard_list]` / `[leaderboard-list]`
- [ ] `[bhg_tournaments]` / `[bhg_tournament_leaderboard]` / `[bhg_tournament_list]` / `[tournament-list]`
- [ ] `[bhg_latest_winners_list]`
- [ ] `[bhg_user_guess]` / `[bhg_user_guesses]` / `[bhg_guess_form]`
- [ ] `[bhg_user_profile]` / `[bhg_active_hunt]` / `[bhg_active]`
- [ ] `[bhg_winner_notifications]` / `[bhg_advertising]`
- [ ] Jackpot surfaces: `[bhg_jackpot_current]` / `[bhg_jackpot_ticker]` / `[bhg_jackpot_winners]`

### Attribute + pagination matrix (per shortcode)
- [ ] Exercise pagination links wherever `paginate_links()` is used (leaderboards, tournaments, bonus hunts, list variants) and confirm links are clickable + preserve filters across pages.【F:includes/class-bhg-shortcodes.php†L2719-L5723】
- [ ] For every shortcode with filters (timeline, affiliate website, affiliate yes/no, tournament selection, search), test combinations including empty/“all” values, preselected tournament attributes, and edge cases like zero results.

## Leaderboards
- [ ] Verify “Times won” comes from aggregated hunt wins per user (`COUNT(hw.id)`) and respects hunt/tournament filters so users with multiple distinct wins show distinct totals rather than duplicated values.【F:includes/class-bhg-shortcodes.php†L446-L520】
- [ ] Confirm filters (timeline ranges, affiliate website, affiliate status) restrict results; check queries honor meta joins for affiliate yes/no and hunt affiliate_site_id selections.【F:includes/class-bhg-shortcodes.php†L430-L504】
- [ ] Validate pagination links are clickable and reflect filtered totals (e.g., Moderators-only leaderboard) so pages beyond page 1 still respect filters.【F:includes/class-bhg-shortcodes.php†L2719-L3137】
- [ ] Validate average tournament position column is populated when requested (`AVG(fw.position)` in aggregate) and reflects all wins within the timeline selection.【F:includes/class-bhg-shortcodes.php†L463-L520】
- [ ] Ensure tournament dropdowns include all tournaments even when shortcode attributes preselect one; the union query pulls participant rows for the selected tournament while leaving other tournaments available in the filter dropdown.【F:includes/class-bhg-shortcodes.php†L473-L520】

## Tournaments
- [ ] Load tournament detail view via query arg (e.g., `/tournaments/?bhg_tournament_id=1`) and confirm data blocks (header, prizes, leaderboard) populate using seeded tournament results.
- [ ] Confirm pagination works within tournament leaderboards and any other tabular outputs, and verify filter/search retention when moving between pages.【F:includes/class-bhg-shortcodes.php†L2719-L3548】
- [ ] Re-test prize summaries and prize boxes above leaderboards when tournaments have prizes configured.

## Bonus Hunts & User Guess
- [ ] Confirm bonus hunt outputs paginate and filter correctly (status, timeline, affiliate site, affiliate flag) and keep filters intact when navigating pages.【F:includes/class-bhg-shortcodes.php†L4680-L5723】
- [ ] Validate leaderboard and guess displays include affiliate indicators and correct guess/win metrics.
- [ ] For user guess form: align styling (padding left/right) with other buttons and verify submission works with demo data; the default CSS shows horizontal padding and dark-blue buttons for consistency.【F:assets/css/public.css†L1-L35】

## UI/Design consistency
- [ ] Ensure all shortcode filter blocks use the same dropdown styling as tournaments, with filters aligned horizontally and search/filter buttons aligned with consistent padding. Compare against the shared `.bhg-filter-controls` / `.bhg-filter-select` / `.bhg-filter-button` styles to keep spacing uniform across leaderboards, tournaments, bonus hunts, and user guess.【F:assets/css/public.css†L27-L35】
- [ ] Confirm the dark-blue button style is applied consistently across guess forms and shortcode filter actions so the user-guess submit button matches shortcode buttons.【F:assets/css/public.css†L27-L35】

## Regression checks
- [ ] Sorting toggles (ASC/DESC) on leaderboard columns.
- [ ] Timeline tabs (Overall/Monthly/Yearly/All-Time) still switch views and preserve pagination.
- [ ] No PHP notices/warnings in error logs during interactions.

## Reporting
- [ ] Capture screenshots of each shortcode output (desktop + mobile width) after confirming data and filters.
- [ ] Document any failures with URL, shortcode attributes used, and expected vs actual output.
