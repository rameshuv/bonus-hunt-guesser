# Bonus Hunt Guesser shortcode review checklist

Use this checklist when validating shortcode behavior on PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+ sites.

## Shortcode inventory
- Core: `bhg_active_hunt`, `bhg_guess_form`, `bhg_leaderboard`, `bhg_tournaments`, `bhg_winner_notifications`, `bhg_user_profile`.
- Add-ons: `bhg_best_guessers`, `bhg_user_guesses`, `bhg_hunts`, `bhg_leaderboards`, `bhg_prizes`, `my_bonushunts`, `my_tournaments`, `my_prizes`, `my_rankings`, `bhg_jackpot_current`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`, `bhg_latest_winners_list`, `bhg_leaderboard_list`, `bhg_tournament_list`, `bhg_bonushunt_list`.
- Aliases/back-compat: `latest-winners-list`, `leaderboard-list`, `tournament-list`, `bonushunt-list`, `bonus_hunt_leaderboard`, `bonus_hunt_login`, `bhg_active`.

## Data preparedness
1. Confirm demo fixtures exist for all tables used by shortcodes (hunts, guesses, tournaments, results, affiliate websites, hunt winners, tournament-hunt links, prizes, hunt prizes, jackpots, jackpot events, users, and usermeta). Populate sample rows for each so dropdowns and counts are non-empty.
2. Ensure prize assets enqueue only once per request by toggling multiple prize shortcodes on a page and watching the network tab.

## Functional test cases
- **Leaderboard shortcodes** (`bhg_leaderboard`, `bhg_leaderboards`, `bhg_leaderboard_list` and aliases)
  - Verify filters for timeline, tournament, site, and affiliate all populate with demo data and return filtered rows.
  - Validate "times won" reflects distinct winner counts across closed hunts; cross-check against demo winners.
  - Confirm pagination links render and navigate between pages.
  - Check average tournament position by comparing aggregated rankings across open/closed tournaments against manual calculations.
  - When a tournament is preselected via shortcode attributes, dropdown still lists all tournaments plus the selected one.

- **Tournaments shortcodes** (`bhg_tournaments`, `tournament-list`, `my_tournaments`)
  - Load detail view via `bhg_tournaments` with a `bhg_tournament_id` parameter and verify match with stored data.
  - Test pagination under filtered lists.
  - Ensure dropdown and search controls share the same horizontal alignment and styling used on tournaments filters.

- **Bonus hunts shortcodes** (`bhg_hunts`, `bonushunt-list`, `my_bonushunts`, `bhg_active_hunt`)
  - Check filter layout matches tournaments filter styling and sits on one row.
  - Confirm demo hunts and prizes render with jackpots and new prize data populated.

- **Prizes and jackpots** (`bhg_prizes`, `my_prizes`, `bhg_jackpot_current`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`)
  - Validate jackpot totals, ticker events, and winners display populated demo data; refresh to ensure ticker rotates entries.

- **User interactions** (`bhg_guess_form`, `bhg_user_guesses`, `bhg_best_guessers`, `bhg_user_profile`, `my_rankings`, `bhg_winner_notifications`)
  - Submit a guess via the form, verify validation, and confirm the entry appears in user guesses and best guessers.
  - Confirm user profile sections and notifications populate from demo user meta.

- **Navigation and menus** (`bhg_nav`, `bhg_menu` from front menu class)
  - Place menu shortcodes in widgets/pages to ensure navigation renders and uses demo data.

## Styling consistency
- Align dropdown filters horizontally for all shortcode UIs (bonus hunts, leaderboards, tournaments, user guess input).
- Match button styling to the dark blue shortcode buttons; add consistent padding for search/filter controls.
- Adjust user guess input padding so the text field aligns with labels and buttons.

## Acceptance notes
- Re-run checklist after any shortcode or template change.
- Document any discrepancies with URLs, filters applied, and expected vs. actual output to aid debugging.
