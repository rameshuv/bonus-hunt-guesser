# Bonus Hunt Guesser

Requires at least: WordPress 6.3.0

## Shortcodes

### `[bhg_user_guesses]`
Display guesses submitted for a specific bonus hunt. Pass `id` to target a hunt; omit it to use the most recent active hunt.

- `timeline`: limit results to `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_hunts]`
List bonus hunts.

- `timeline`: filter hunts created within `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_leaderboards]`
Display overall wins leaderboard.

- `fields`: comma-separated list of columns to render. Allowed values: `pos`, `user`, `wins`, `avg_hunt`, `avg_tournament`, `aff`, `site`, `hunt`, `tournament`. Defaults to `pos`, `user`, `wins`, `avg_hunt`, `avg_tournament`.
- `ranking`: number of top rows to display. Accepts values from `1` to `10` (default `1`).
- `timeline`: limit results to `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_tournaments]`
List tournaments or show details.

- `timeline`: limit tournaments by `day`, `week`, `month`, `year`, the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`, or by type keywords `all_time`, `weekly`, `monthly`, `yearly`, `quarterly`, `alltime`.

### `[my_bonushunts]`
Displays the logged-in user's guesses across recent bonus hunts. Results highlight winning placements, show the recorded guess, and display the final balance once a hunt closes. Output honours the My Profile visibility toggles defined under **Settings → Bonus Hunt Guesser → My Profile Blocks**.

### `[my_tournaments]`
Shows each tournament a logged-in user has participated in along with accumulated points, wins, rank, and their most recent scoring event. Only renders when the corresponding profile block is enabled in settings.

### `[my_prizes]`
Lists prizes won by the current user, including the source bonus hunt, prize category, placement, and closed date. The shortcode respects the My Profile visibility toggle and uses the global typography/color overrides configured in plugin settings.

### `[my_rankings]`
Summarises the logged-in user's performance across hunts and tournaments. The block surfaces total hunt wins, tournament points, best rank, and provides tables for individual hunt wins and tournament standings when available.

## Settings highlights

- **My Profile Blocks** — Toggle each of the `[my_*]` shortcodes on or off without editing theme templates.
- **Global Typography & Colors** — Configure shared styles for profile sections, headings, descriptions, and body text. The plugin injects inline CSS so front-end shortcodes immediately reflect the chosen palette.
- **Uninstall safety** — A new "Remove plugin data on uninstall" checkbox controls whether tables/options are removed when the plugin is uninstalled.

## Manual Testing

- **Hunt deletion updates tournaments**
  1. Create a tournament and associate a hunt with winners.
  2. Note the wins recorded for those users on the tournament leaderboard.
  3. Delete the hunt from the admin panel and confirm no SQL errors are displayed.
  4. Reload the tournament leaderboard and verify the affected users have their win counts reduced or removed accordingly.
- **Tournament type persistence**
  1. Create a tournament with start and end dates that span roughly one month.
  2. Visit the `[bhg_tournaments timeline="monthly"]` shortcode output and confirm the tournament appears.
  3. Edit the same tournament (leaving the type selector untouched) and change another field such as the title or description.
  4. Save the changes, refresh the shortcode output, and verify the tournament still appears in the monthly view (demonstrating the stored type was preserved).
  5. Repeat with a weekly-length tournament to confirm the shortcode timeline tabs continue filtering correctly after edits.

