# Delivery Readiness Checklist (v8.0.22)

## Runtime and Version
- [x] Plugin version set to **8.0.22** in `bonus-hunt-guesser.php` (matches requested version).
- [ ] Runtime validation (PHP 7.4, WordPress 6.3.5, MySQL 5.5.5+) performed in staging environment.

## Leaderboard Adjustments
- [ ] "Times won" calculation verified against multiple closed hunts with distinct winners.
- [ ] Prize block display options (Grid/Carousel, items per view, total items) verified in backend settings and shortcode output.
- [ ] Affiliate website filter confirmed to limit leaderboard results appropriately.
- [ ] Pagination confirmed clickable and functioning across leaderboard pages.
- [ ] "Avg tournament position" formula validated against mixed open/closed tournaments.
- [ ] Tournament dropdown shows full list with correct default selection when preselected via shortcode.
- [ ] `bhg_leaderboard_list` shortcode renders without PHP errors and supports filters, pagination, prize blocks, dropdowns, and calculations above.

## Tournament Adjustments
- [ ] Tournament detail shortcode outputs correctly on frontend.
- [ ] Prize block options (Grid/Carousel, items per view, total items) functional in tournament settings and display.
- [ ] Pagination working for tournament and bonus hunt shortcodes.
- [ ] Affiliate website filter limits tournament participants correctly.
- [ ] Table winner highlighting respects configured winner count; non-winners use default styling.
- [ ] Column header updated to "User" in tournament tables to match leaderboard terminology.
- [ ] Countdown block positioned below prizes, with title/info/description in consistent content block.

## Global Filters & UI/UX
- [ ] Filter/search design consistent across bonus hunts, leaderboards, tournaments, and user guess shortcodes.
- [ ] Dropdown filters aligned in a single row with consistent spacing.
- [ ] Search/filter buttons aligned, equal height, and properly padded.
- [ ] User guess form padding symmetrical; submit button uses global dark blue style.

## Outstanding Risks / Actions
- Comprehensive functional testing across the above items is still required in a WordPress environment to confirm readiness for delivery.
- Align pagination and filter fixes across all shortcodes once core functionality is verified.
