# QA Verification Checklist (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+)

This checklist maps the requested leaderboard, tournament, prize, and frontpage shortcode behaviors to concrete verification steps. It focuses on the bundled shortcodes and admin screens shipped with the plugin.

## Environment prerequisites
- WordPress 6.3.5 with PHP 7.4 and MySQL 5.5.5+.
- Plugin activated and sample tournaments/bonushunts with prizes populated for at least one active tournament (e.g., tournament ID 3).
- For automated tests, install dependencies and run PHPUnit after ensuring GitHub API access (`composer install`, then `./vendor/bin/phpunit`).

## Automated checks
- Install dependencies: `composer install`
  - If GitHub throttling blocks dist downloads, provide a read-only GitHub token (public scope) or configure a mirror, then rerun.
  - When prompted for a GitHub token, press Ctrl+C to abort if you cannot provide one, then rerun later with credentials configured in `~/.composer/auth.json`.
- Run unit tests: `./vendor/bin/phpunit`
  - Confirms core services and shortcode rendering helpers compile and execute.

### Automation run log (latest)
- `composer install` (PHP 7.4) — **failed** due to GitHub API 403 (CONNECT tunnel blocked when fetching `squizlabs/php_codesniffer`); aborted at GitHub token prompt. Configure a GitHub token or mirror, then rerun before executing PHPUnit.

## Leaderboard shortcode
- **Query correctness**: Load `/leaderboards/leaderboards-all-participants-2025/` and confirm participant count matches `/tournaments/all-tournaments/?bhg_tournament_id=3` (expected 26 users in the example).
- **Rounding**: Avg Rank and Avg Tournament Pos render as whole numbers (no decimal suffix).
- **Username formatting**: First letter of each username is capitalized across all shortcode outputs; table header reads **Username**.
- **Prize visibility**: When a specific active tournament is selected, a prize box appears above the table.
- **Affiliate indicator**: Column after Avg Tournament Pos shows green/red indicator values.
- **Position sorting**: Position column header provides sorting control.
- **Titles**: H2 headings appear above the results table reflecting selected tournament and/or bonushunt (tournament first, then bonushunt when both are present).
- **Filters**: Bonushunt dropdown removed; shortcode attributes allow hiding/showing filters individually (`timeline`, `tournament`, `affiliate site`, `affiliate status`) or entirely via `filters=""`.
- **Times won scope**: Counts wins only from bonushunts inside the timeline filter or from the specified tournament.
- **Search block**: Confirm hide/show option functions if surfaced for leaderboard.

## Tournament shortcode
- **Admin field**: “Number of Winners” field is present when adding/editing a tournament.
- **Closing notice**: Active tournaments display a yellow box above the table: “This tournament will close in X days,” based on end date.
- **Headers**: Column titles show **Position** (instead of #) and **Times Won** (instead of Wins).
- **Sorting**: Position column supports sorting with an icon.
- **Last Win**: Each row’s “Last Win” reflects the last prize in bonushunts linked to the tournament (not last tournament win).
- **Pagination**: Tables paginate according to the general setting (e.g., 25 rows per page).
- **Search block**: Validate hide/show option when applicable.

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

