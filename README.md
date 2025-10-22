# Bonus Hunt Guesser

Requires at least: WordPress 5.5.5

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

### Onboarding Checklist

1. **Create required pages** under *Pages → Add New* and place the documented shortcodes:
   - `/bonus-hunt/active` → `[[bhg_active_hunt]]`
   - `/bonus-hunt/guess` → `[[bhg_guess_form]]`
   - `/bonus-hunt/guesses` → `[[bhg_user_guesses]]`
   - `/bonus-hunt/hunts` → `[[bhg_hunts status="active" timeline="month"]]`
   - `/tournaments` → `[[bhg_tournaments status="active" timeline="year"]]`
   - `/leaderboards` → `[[bhg_leaderboards ranking="10" timeline="year" fields="pos,user,wins,avg_rank,hunt_title,tournament_title,aff,aff_site"]]`
   - `/profile` → `[[bhg_user_profile]]`
   - `/ads` → `[[bhg_advertising status="active" ad="1"]]`
2. **Assign menus** via *Appearance → Menus*. Create dedicated menus for admins/moderators, logged-in users, and guests, then assign them to the plugin’s menu locations.
3. **Configure translations** from *Bonus Hunt → Translations*. Override any customer-facing labels directly in the UI without editing code.
4. **Manage affiliate websites** from *Bonus Hunt → Affiliates* and set per-user affiliate access on *Bonus Hunt → Users* by toggling the site checkboxes.
5. **Set up notifications** under *Bonus Hunt → Notifications* to enable and customize hunt, winner, or tournament email templates.


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

