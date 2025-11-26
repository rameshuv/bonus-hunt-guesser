# Plugin Review & QA Checklist

This checklist consolidates the requested manual QA and data-verification steps for the Bonus Hunt Guesser plugin in a WordPress environment (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+).

## Environment preparation
- [ ] Install and activate the plugin on a clean WordPress 6.3.5 site running PHP 7.4 and MySQL 5.5.5+.
- [ ] Run the plugin setup to create all custom tables.
- [ ] Seed demo data for hunts, tournaments, prizes (including jackpots), users, and guesses so every shortcode has data to render.
- [ ] Confirm WP_DEBUG is enabled to surface notices during testing.

## Data completeness (demo data)
- [ ] Bonus Hunts: ensure each hunt has affiliate website, prizes (including jackpot/new prizes), start/final dates, status, winners, and related guesses.
- [ ] Tournaments: ensure tournaments reference hunts and have prize tiers populated; confirm at least one active and multiple closed tournaments exist.
- [ ] Leaderboards: populate wins so “Times won”, average hunt position, and average tournament position can be validated.
- [ ] Users: assign affiliate websites (e.g., Moderators) and affiliate status flags to several accounts.

## Shortcode coverage (admin list)
Validate that every shortcode registered in the admin area renders without PHP errors and shows data when demo content is present:
- [ ] `[bhg_bonus_hunt]` / `[bhg_hunts]`
- [ ] `[bhg_leaderboard]` / `[bhg_leaderboards]`
- [ ] `[bhg_tournaments]` / `[bhg_tournament_leaderboard]` / `[bhg_tournament_list]`
- [ ] `[bhg_latest_winners_list]` / `[bhg_leaderboard_list]`
- [ ] `[bhg_user_guess]` / `[bhg_user_guesses]`
- [ ] `[bhg_user_profile]` / `[bhg_active_hunt]` / `[bhg_guess_form]`
- [ ] `[bhg_winner_notifications]` / `[bhg_advertising]`

## Leaderboards
- [ ] Verify “Times won” aggregates correctly across all closed hunts/tournaments (sample page: `/leaderboards/leaderboards-all-participants-2025/`).
- [ ] Confirm filters (timeline, affiliate site, affiliate status) restrict results correctly; e.g., a Moderators-only leaderboard shows only users with the “Moderators” affiliate website.
- [ ] Check pagination links render via `paginate_links()` and are clickable.
- [ ] Validate average tournament position is computed from all open + closed tournaments and rounded/formatted as expected.
- [ ] Ensure tournament dropdowns include all tournaments even when a specific tournament is preselected via shortcode.

## Tournaments
- [ ] Load tournament detail view via query arg (e.g., `/tournaments/?bhg_tournament_id=1`) and confirm data blocks (header, prizes, leaderboard) populate.
- [ ] Confirm pagination works within tournament leaderboards and any other tabular outputs.
- [ ] Re-test prize summaries and prize boxes above leaderboards when tournaments have prizes configured.

## Bonus Hunts & User Guess
- [ ] Confirm bonus hunt outputs paginate and filter correctly (status, timeline, affiliate site, affiliate flag).
- [ ] Validate leaderboard and guess displays include affiliate indicators and correct guess/win metrics.
- [ ] For user guess form: align styling (padding left/right) with other buttons and verify submission works with demo data.

## UI/Design consistency
- [ ] Ensure all shortcode filter blocks use the same dropdown styling as tournaments, with filters aligned horizontally and search/filter buttons aligned with consistent padding.
- [ ] Confirm the dark-blue button style is applied consistently across guess forms and shortcode filter actions.

## Regression checks
- [ ] Sorting toggles (ASC/DESC) on leaderboard columns.
- [ ] Timeline tabs (Overall/Monthly/Yearly/All-Time) still switch views and preserve pagination.
- [ ] No PHP notices/warnings in error logs during interactions.

## Reporting
- [ ] Capture screenshots of each shortcode output (desktop + mobile width) after confirming data and filters.
- [ ] Document any failures with URL, shortcode attributes used, and expected vs actual output.
