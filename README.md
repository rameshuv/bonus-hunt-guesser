# Bonus Hunt Guesser

Requires at least: WordPress 5.5.5

## Shortcodes

### `[bhg_user_guesses]`
Display guesses submitted by a user.

- `timeline`: limit results to `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_hunts]`
List bonus hunts.

- `timeline`: filter hunts created within `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_leaderboards]`
Display overall wins leaderboard.

- `fields`: comma-separated list of columns to render. Allowed values: `pos`, `user`, `wins`, `aff`, `site`, `hunt`, `tournament`.
- `per_page`: number of rows to display (default `50`).
- `timeline`: limit results to `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_tournaments]`
List tournaments or show details.

- `timeline`: limit tournaments by `day`, `week`, `month`, `year`, the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`, or by type keywords `all_time`, `weekly`, `monthly`, `yearly`, `quarterly`, `alltime`.

