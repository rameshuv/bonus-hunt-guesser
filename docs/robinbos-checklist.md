# Delivery Checklist (robinbos Requests)

## Leaderboards Adjustments
1. [ ] Restore leaderboard shortcode query to return all eligible users (e.g., 26 users for tournament ID 3) instead of a single row.
2. [ ] Render Avg Rank and Avg Tournament Position as rounded integers (no decimals) in table output.
3. [ ] Capitalize the first letter of usernames across all frontend shortcode outputs.
4. [ ] When a specific active tournament is selected, display a prize box above the leaderboard table showing the available prizes.
5. [ ] Add an Affiliate column after Avg Tournament Position with green/red status lights for affiliate status.
6. [ ] Make the Position column sortable and update table headers from "#"/"user" to "Position"/"Username" as appropriate.
7. [ ] Remove the bonushunt dropdown filter from the leaderboard. Provide shortcode options to hide/show dropdown filters individually (timeline, tournament, affiliate site, affiliate status) via `filters=""`.
8. [ ] Ensure Times Won counts only prize wins within the timeline filter or within the selected tournament context.
9. [ ] Insert H2 titles above results when specific tournament/bonushunt filters are applied (tournament title first when both are selected).
10. [ ] Confirm pagination appears at the bottom of the leaderboard output according to the global max-rows setting.

## Tournament Adjustments
1. [ ] Add a "Number of Winners" field to tournament add/edit screens; winners finalize on end date or manual close.
2. [ ] For active tournaments, show a yellow banner above the table reading: "This tournament will close in x days" with the correct day count.
3. [ ] Update table headers: "Wins" → "Times Won" and "#" → "Position"; make Position sortable with an icon.
4. [ ] Populate Last Win with the user’s last bonushunt prize win tied to the tournament (not last tournament win).
5. [ ] Add pagination to the tournament shortcode output using the global max-rows-per-page setting.

## Prizes Adjustments
1. [ ] In bonushunt and tournament admin, configure regular and premium prizes per winner slot based on the Number of Winners field.
2. [ ] Generate prize summary text lists (one line per placement) beneath prize boxes in the tournament shortcode and in the leaderboard shortcode when a specific tournament is selected.
3. [ ] Add shortcode options to show/hide prize summary lists and prize boxes for prize, leaderboard, and tournament shortcodes.
4. [ ] Present prize carousels inside tabbed boxes with Regular Prizes and Premium Prizes tabs.

## Frontpage Lists Add-on
1. [ ] Create a `latest-winners-list` shortcode with hide/show controls for date, username, prize, bonushunt title, and tournament title.
2. [ ] Create a `leaderboard-list` shortcode (with optional tournament/bonushunt ID) and hide/show controls for position, username, times won, average hunt position, and average tournament position.
3. [ ] Create a `tournament-list` shortcode with timeline/status support and hide/show controls for name, start date, end date, status, and details.
4. [ ] Create a `bonushunt-list` shortcode with timeline/status support and hide/show controls for title, start balance, final balance, winners, status, and details.
5. [ ] Restrict timeline filters for bonushunt, tournament, and leaderboard shortcodes to the approved set: Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year.
6. [ ] Add dropdown filters (timeline, status) to the bonushunt shortcode and hide/show search-block options for bonushunt, tournament, and leaderboard shortcodes.
