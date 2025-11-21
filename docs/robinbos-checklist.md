# Delivery Checklist (robinbos Requests)

## Leaderboards Adjustments
1. [ ] Fix leaderboard shortcode to return all expected users (e.g., 26 users for tournament ID 3) instead of a single user.
2. [ ] Render Avg Rank and Avg Tournament Position as rounded integers (no decimals).
3. [ ] Capitalize the first letter of usernames across all frontend shortcode outputs.
4. [ ] When a specific active tournament is selected, display a prize box above the leaderboard table.
5. [ ] Add an Affiliate column after Avg Tournament Position with green/red status lights.
6. [ ] Make Position column sortable and update header label from "#"/"user" to "Position"/"Username" as appropriate.
7. [ ] Remove the bonushunt dropdown filter from the leaderboard and add shortcode options to hide/show dropdown filters individually (timeline, tournament, affiliate site, affiliate status) via `filters=""`.
8. [ ] Ensure Times Won counts only prize wins within the timeline filter or the selected tournament context.
9. [ ] Insert H2 titles above results when specific tournament/bonushunt filters are applied (tournament first if both selected).
10. [ ] Confirm pagination is visible at the bottom of the leaderboard output per global max rows setting.

## Tournament Adjustments
1. [ ] Add "Number of Winners" field to tournament add/edit screens; winners finalized on end date or manual close.
2. [ ] For active tournaments, show a yellow banner: "This tournament will close in x days" above the table.
3. [ ] Update table headers: "Wins" → "Times Won" and "#" → "Position"; make Position sortable with an icon.
4. [ ] Populate Last Win with the user’s last bonushunt prize win tied to the tournament (not last tournament win).
5. [ ] Add pagination to tournament shortcode output using global max rows per page setting.

## Prizes Adjustments
1. [ ] In bonushunt and tournament admin, configure regular and premium prizes per winner slot based on Number of Winners.
2. [ ] Generate prize summary text lists (one line per placement) beneath prize boxes in tournament shortcode and in leaderboard shortcode when a specific tournament is selected.
3. [ ] Add shortcode options to show/hide prize summary lists and prize boxes for prize, leaderboard, and tournament shortcodes.
4. [ ] Present prize carousels inside tabbed boxes: Regular Prizes and Premium Prizes.

## Frontpage Lists Add-on
1. [ ] Create latest-winners-list shortcode with hide/show controls for date, username, prize, bonushunt title, and tournament title.
2. [ ] Create leaderboard-list shortcode (with optional tournament/bonushunt ID) and hide/show controls for position, username, times won, average hunt position, and average tournament position.
3. [ ] Create tournament-list shortcode with timeline/status support and hide/show controls for name, start date, end date, status, and details.
4. [ ] Create bonushunt-list shortcode with timeline/status support and hide/show controls for title, start balance, final balance, winners, status, and details.
5. [ ] Restrict timeline filters for bonushunt, tournament, and leaderboard shortcodes to: Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year.
6. [ ] Add dropdown filters (timeline, status) to bonushunt shortcode and hide/show search block options for bonushunt, tournament, and leaderboard shortcodes.
