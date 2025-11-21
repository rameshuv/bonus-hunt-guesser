# QA Verification Checklist (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+)

This checklist maps the requested leaderboard, tournament, prize, and frontpage shortcode behaviors to concrete verification steps. It focuses on the bundled shortcodes and admin screens shipped with the plugin.

## Environment prerequisites
- WordPress 6.3.5 with PHP 7.4 and MySQL 5.5.5+.
- Plugin activated and sample tournaments/bonushunts with prizes populated for at least one active tournament (e.g., tournament ID 3).
- Admin user capable of editing tournaments/bonushunts to validate form fields and prize setup.
- For automated tests, install dependencies and run PHPUnit after ensuring GitHub API access (`composer install`, then `./vendor/bin/phpunit`).

## Automated checks
- Install dependencies: `composer install`
  - If GitHub throttling blocks dist downloads, provide a read-only GitHub token (public scope) or configure a mirror, then rerun.
  - When prompted for a GitHub token, press Ctrl+C to abort if you cannot provide one, then rerun later with credentials configured in `~/.composer/auth.json`.
- Run unit tests: `./vendor/bin/phpunit`
  - Confirms core services and shortcode rendering helpers compile and execute.

### Automation run log (latest)
- `composer install` (PHP 7.4) — **failed**: GitHub API 403 (CONNECT tunnel blocked while fetching `sebastian/version`), then Composer prompted for a GitHub token; aborted with Ctrl+C. Configure a GitHub token or mirror, then rerun before executing PHPUnit.

## Fast automated test sequence (post-dependency install)
1) `composer install`
2) `./vendor/bin/phpunit`
3) *(optional)* `./vendor/bin/phpcs` to ensure coding standards still pass after shortcode changes.

> Note: If Composer prompts for a GitHub token, abort with Ctrl+C, add the token to `~/.composer/auth.json`, then rerun step 1.

## Leaderboard shortcode manual checks
1) **Query correctness**: Load `/leaderboards/leaderboards-all-participants-2025/`; participant count should equal `/tournaments/all-tournaments/?bhg_tournament_id=3` (26 users in the example). Cross-check pagination is not truncating results.
2) **Rounding**: Avg Rank and Avg Tournament Pos show whole numbers (no decimals) across all rows.
3) **Username formatting**: First letter of each username capitalized everywhere; column header reads **Username**.
4) **Prize visibility**: Selecting an active tournament shows a prize box above the table (hidden when no tournament or inactive tournament is selected).
5) **Affiliate indicator**: Column after Avg Tournament Pos appears with green/red indicator per row; header present after Avg Tournament Pos.
6) **Position sorting**: Position header offers sorting; toggling adjusts row order accordingly.
7) **Titles**: H2 headings above the table display tournament title (if selected) and bonushunt title beneath when both are set.
8) **Filters**: Bonushunt dropdown removed. Shortcode attributes support hiding filters (`timeline`, `tournament`, `affiliate site`, `affiliate status`) individually or all via `filters=""`; verify UI hides as configured.
9) **Times won scope**: Counts wins only for bonushunts inside the timeline filter or for the specified tournament; change filters to ensure the value responds.
10) **Search block**: Hide/show option works if exposed for the leaderboard.

## Tournament shortcode manual checks
1) **Admin field**: Add/Edit tournament shows a “Number of Winners” input; saving persists the value.
2) **Closing notice**: Active tournaments display a yellow banner above the table: “This tournament will close in X days,” computed from end date.
3) **Headers**: Columns show **Position** (replacing #) and **Times Won** (replacing Wins).
4) **Sorting**: Position column supports clickable sorting with icon feedback.
5) **Last Win**: Each row’s “Last Win” matches the user’s most recent prize in linked bonushunts (not the last tournament win); verify by comparing bonushunt history.
6) **Pagination**: Table paginates according to the general setting (e.g., 25 rows per page) with controls visible when threshold exceeded.
7) **Search block**: Hide/show option works when configured.

## Prize management and display
- **Per-place prizes**: Add/Edit bonushunt and tournament forms allow setting regular and premium prizes for each winner based on the configured number of winners.
- **Prize summary**: Text list of prizes (1st place on top) appears below prize boxes in tournament and leaderboard shortcodes (leaderboard only when a tournament is selected). Options exist to hide/show the summary in prize, leaderboard, and tournament shortcodes.
- **Tabbed carousel**: Prize carousel uses tabs: Regular Prizes and Premium Prizes.

## Frontpage shortcodes
- **latest-winners-list**: Shows Date, Username, Prize won, Bonushunt Title, Tournament Title with hide/show options.
- **leaderboard-list**: Lists best guessers; can target specific tournament ID or bonushunt ID; supports hide/show for Position, Username, Times Won, Average Hunt Position, Average Tournament Position.
- **tournament-list**: Shows tournaments with Timeline/Status filters; hide/show Name, Start date, End date, Status, Details.
- **bonushunt-list**: Shows bonushunts with Timeline/Status filters; hide/show Title, Start Balance, Final Balance, Winners, Status, Details.
- **Timeline simplification**: Tournament and bonushunt shortcodes expose only: Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year.
- **Filters**: Bonushunt shortcode includes Timeline and Status dropdowns.
- **Search block**: Hide/show option for bonushunt, tournament, and leaderboard shortcodes.

## Manual data setup tips
- Seed at least one active tournament with linked bonushunts and prizes to exercise the active-state UI (closing notice and prize box).
- Use multiple users with varying win histories to confirm Times Won, Last Win, and average metrics behave under timeline filters.

