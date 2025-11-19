# Bonus Hunt Guesser Compliance Checklist

This checklist consolidates runtime expectations, coding standards, customer feature requirements, and QA coverage for the Bonus Hunt Guesser plugin. Use it to verify each release against WordPress 6.3.5 and PHP 7.4.

## 1. Runtime, Standards, and Localization
- [x] Confirm runtime stack: PHP 7.4, WordPress 6.3.5, MySQL 5.5.5+.
  - `bonus-hunt-guesser.php` header (lines 4–15) documents the required versions plus GPLv2+ license, so QA can point to the canonical source.
- [ ] Ensure PHPCS passes with WordPress-Core, WordPress-Docs, and WordPress-Extra rulesets.
  - `phpcs.xml` still references the aggregate `WordPress` ruleset only; PHPCS must be re-run after enabling the Core/Docs/Extra standards explicitly.
- [x] Plugin text domain is `bonus-hunt-guesser`; all strings use translation functions and `load_plugin_textdomain()` runs.
  - The bootstrap loads translations during `plugins_loaded` (`bonus-hunt-guesser.php` line 411) and every admin/frontend template wraps UI strings with `bhg_t()`/`__()` helpers.

## 2. Plugin Header and Bootstrapping
- [x] Main file is `bonus-hunt-guesser.php` with Name, URI, Description, Version (8.0.18), Author, Text Domain, Domain Path, Requires PHP 7.4, Requires at least WP 6.3.5, GPLv2+.
  - Header block (lines 4–20) declares the required runtime values plus the GPL notice, and the bootstrap wires up activation hooks and helpers immediately after the header so QA can point to the canonical metadata.
- [x] Text domain is loaded on init; no PHPCS header issues.
  - `bonus-hunt-guesser.php` loads translations via `load_plugin_textdomain( 'bonus-hunt-guesser', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );` during `plugins_loaded`, matching the runtime contract.

## 3. Leaderboard Shortcode Requirements
### 3.1 Frontend display adjustments
- [x] `[bhg_leaderboards tournament="" bonushunt="" aff="" website="" ranking="1-10" timeline=""]` now renders the full result set (e.g., 26 users for the sample tournament) because `leaderboards_shortcode()` normalizes `$atts` before routing to `run_leaderboard_query()` so pagination, offsets, and limits are honored instead of returning only the first row. (`includes/class-bhg-shortcodes.php`, lines 270–700.)
- [x] Avg Rank and Avg Tournament Pos are rounded via `number_format_i18n( $value, 0 )`, so integers such as `1` or `7` are displayed instead of `1.00`. (`includes/class-bhg-shortcodes.php`, lines 3400–3450.)
- [x] Username strings are capitalized across leaderboard, hunts, and compact list shortcodes using the `mb_substr`/`mb_strtoupper` helpers with ASCII fallback; the main leaderboard header is labeled "Username". (`includes/class-bhg-shortcodes.php`, lines 3375–3388, 3614–3625, 4972–4982.)
- [x] When a specific active tournament is selected the shortcode fetches its attached prizes and outputs a prize box (regular vs. premium tabs) above the table along with an optional summary list. (`includes/class-bhg-shortcodes.php`, lines 4630–4705.)
- [x] Affiliate status column appears immediately after Avg Tournament Pos with green/red indicators produced by `bhg_render_affiliate_dot()`, and the Position column header is wired into the sortable header helper so visitors can sort ascending/descending. (`includes/class-bhg-shortcodes.php`, lines 2100–2190.)
- [x] Tournament and bonushunt `<h2>` elements display above the table whenever specific IDs are passed; tournaments always render first, followed by bonushunts when both filters are active. (`includes/class-bhg-shortcodes.php`, lines 2205–2265.)
- [x] The bonushunt dropdown was removed from the leaderboard filters. Instead, admins can hide/show `timeline`, `tournament`, `site`, and `affiliate` dropdowns by passing a comma-separated `filters=""` attribute, matching the backend setting documented on the Info & Help page. (`includes/class-bhg-shortcodes.php`, lines 104–132 and 1100–1150.)
- [x] Times Won counts only prize wins that fall within the selected timeline window (Today, Week, Month, Quarter, Year, Alltime) or belong to the explicitly selected tournament because `run_leaderboard_query()` limits the `bhg_hunt_winners` rows via `get_timeline_range()` and overrides the range when `$tournament_id > 0`. (`includes/class-bhg-shortcodes.php`, lines 330–420.)
### 3.2 Core behavior expectations
- [x] Sorting, search box, and pagination use the shared query args (`bhg_orderby`, `bhg_order`, `bhg_search`, `bhg_paged`) and honor the global rows-per-page option so the table shows 30 rows (or the configured value) per page. (`includes/class-bhg-shortcodes.php`, lines 2100–2400.)
- [x] Timeline filters are restricted to Alltime, Today, This Week, This Month, This Quarter, This Year, and Last Year by normalizing attribute aliases before calling `get_timeline_range()`. (`includes/class-bhg-shortcodes.php`, lines 350–380.)
- [x] Affiliate "lights" and optional affiliate website text come from `bhg_render_affiliate_dot()` and the site join that runs when `need_site` is true, satisfying the spec for visual indicators. (`includes/class-bhg-shortcodes.php`, lines 280–520.)
- [x] The Info & Help admin page lists `[bhg_leaderboards]` usage, filter toggles, prize display flags, and the trimmed timeline values so editors can configure the shortcode without guessing. (`admin/views/shortcodes.php`, section "Leaderboards").

## 4. Tournament Admin and Shortcode
### 4.1 Admin experience
- [x] Add/Edit forms include Title, Description, Participants Mode (`winners` or `all guessers`), Number of Winners, and prize selectors with the legacy `type` dropdown removed. (`admin/views/tournaments.php`, lines 40–210.)
- [x] List rows expose Edit, Results, Close (with nonce), and Admin Delete buttons exactly as specified. (`admin/views/tournaments.php`, lines 260–340.)
- [x] Closing logic awards winners automatically when end dates pass or admins click Close, because the table actions call `BHG_Tournaments::close()` which respects Participants Mode. (`includes/class-bhg-tournaments.php`, lines 180–260.)
### 4.2 Frontend shortcode `[bhg_tournaments]`
- [x] Active tournaments display a yellow infobox reading "This tournament will close in X days" using the difference between `end_date` and `current_time('timestamp')`. (`includes/class-bhg-shortcodes.php`, lines 5060–5085.)
- [x] Headers read Position, Username, Times Won, Avg Tournament Position, and Last Win; the Position header is sortable via the same request params used on leaderboards. (`includes/class-bhg-shortcodes.php`, lines 5110–5205.)
- [x] The Last Win column pulls the most recent bonushunt prize win tied to the tournament by joining `bhg_tournament_results` and `bhg_hunt_winners`. (`includes/class-bhg-shortcodes.php`, lines 5230–5290.)
- [x] Pagination leverages `bhg_get_shortcode_rows_per_page()` so the global "max rows per page" setting applies to both leaderboard and tournament outputs. (`includes/class-bhg-shortcodes.php`, lines 5300–5335.)
- [x] Timeline filters are normalized to Alltime/Today/Week/Month/Quarter/Year/Last Year and exposed through dropdown controls beneath the shortcode filters. (`includes/class-bhg-shortcodes.php`, lines 4980–5040.)

## 5. Prize System
### 5.1 Baseline CRUD
- [x] Admin CRUD offers title, description, category (cash money/casino money/coupons/merchandise/various), image pickers for small (300×200), medium (600×400), and big (1200×800) sizes, CSS controls, active toggle, and optional prize link field. (`admin/views/prizes.php`, lines 40–320.)
### 5.2 Adjustments and per-winner rules
- [x] Bonus Hunt and Tournament admin screens store per-place regular and premium prize assignments tied to the Number of Winners field so each winner (1st–N) has a deterministic reward. (`admin/views/bonus-hunts-edit.php` & `admin/views/tournaments.php`, prize panels.)
- [x] Prize summary lists render beneath prize boxes in the tournament shortcode and in the leaderboard shortcode when a specific tournament is selected, with numbered lines matching the per-place assignments. (`includes/class-bhg-shortcodes.php`, lines 2280–2335 & 4630–4705.)
- [x] `[bhg_prizes]`, `[bhg_leaderboards]`, and `[bhg_tournaments]` expose `show_prizes`/`show_summary` attributes so site owners can hide prize displays or the textual summary independently. (`admin/views/shortcodes.php`, prize sections.)
- [x] Regular vs. premium prize tabs use the `render_prize_tabs()` helper to show carousel content in two tabs labeled "Regular Prizes" and "Premium Prizes". (`includes/class-bhg-shortcodes.php`, lines 1497–1515 & 2260–2330.)
### 5.3 Enhancements and display controls
- [x] Big image uploads (1200×800 PNGs) succeed because custom image sizes are registered during plugin bootstrap, and backend labels remind admins of the expected resolutions. (`bonus-hunt-guesser.php`, lines 430–460.)
- [x] Prize categories have CRUD with optional links and "Show link" toggle; frontend badges become clickable when `show_link` is enabled. (`admin/views/prizes.php`, category table; `includes/class-bhg-shortcodes.php`, `render_prize_card()`.)
- [x] Image click behavior can open a popup, same-window link, or new-window link depending on the prize settings. (`includes/class-bhg-shortcodes.php`, lines 1800–1950.)
- [x] Carousel/grid settings include visible count, total items, autoscroll, and toggles for title/category/description; responsive sizing logic switches between big/medium/small classes based on how many prizes are visible. (`assets/js/bhg-prizes.js` and `includes/class-bhg-shortcodes.php`, lines 1368–1505.)
- [x] The autogenerated "Prizes" heading has been removed; only admin-supplied headings render. (`includes/class-bhg-shortcodes.php`, lines 1368–1405.)
### 5.4 Dual prize sets
- [x] Bonus Hunt admin exposes Regular and Premium prize selectors, and the frontend shows premium prizes to affiliate winners (premium users) while everyone sees the regular set. (`admin/views/bonus-hunts-edit.php`; `includes/class-bhg-shortcodes.php`, lines 2220–2315.)

## 6. Frontpage List Shortcodes
- [x] Canonical shortcode tags use the `bhg_` prefix (`[bhg_latest_winners_list]`, `[bhg_leaderboard_list]`, `[bhg_tournament_list]`, `[bhg_bonushunt_list]`) and legacy aliases remain for backward compatibility. `tests/ShortcodesRegistrationTest.php` asserts that the new prefixed names are registered. (`includes/class-bhg-shortcodes.php`, lines 90–110.)
- [x] `[bhg_latest_winners_list]` supports `fields` toggles for date, username, prize, bonushunt, tournament, and optional position plus an `empty` message override. (`includes/class-bhg-shortcodes.php`, lines 3230–3485.)
- [x] `[bhg_leaderboard_list]` accepts tournament/bonushunt IDs, timeline/affiliate/site filters, and hide/show toggles for position, username, times won, Avg Hunt Position, and Avg Tournament Position. (`includes/class-bhg-shortcodes.php`, lines 3485–3660.)
- [x] `[bhg_tournament_list]` and `[bhg_bonushunt_list]` expose `timeline`, `status`, `limit`, and `fields` attributes so Name, Start Date, End Date, Status, Details (tournaments) plus Title, Start Balance, Final Balance, Winners, Status, Details (bonushunts) can be toggled. (`includes/class-bhg-shortcodes.php`, lines 3665–3880.)
- [x] All list shortcodes restrict timeline filters to Alltime, Today, This Week, This Month, This Quarter, This Year, or Last Year, matching the trimmed filter set. (`includes/class-bhg-shortcodes.php`, lines 3530–3555 & 3720–3750.)
- [x] Search blocks can be hidden by passing `show_search="no"` (or equivalent) because the list renderers wrap their search forms in conditionals tied to that attribute. (`includes/class-bhg-shortcodes.php`, lines 3700–3745.)

## 7. General Frontend Adjustments
- [x] Table header links use white (#fff) text.
  - `assets/css/bhg-shortcodes.css` forces `.bhg-table thead a` to `color: #fff`, matching the customer request across leaderboards, tournaments, hunts, and user guesses.
- [x] Hunts list adds Details column next to Status: "Show Results" for closed hunts, "Guess Now" for open hunts; mobile-friendly tables across all shortcodes.
  - `BHG_Shortcodes::hunts_shortcode()` renders the Details column with context-sensitive links and seeds `$status_filter` before building the query (resolving the log notice from line 2935), while `assets/css/public.css` keeps `.bhg-table-wrapper` scrollable on narrow screens for consistent responsive behavior.

## 8. Jackpot Module
- [x] Admin menu "Jackpots" with list (latest 10, title/start date/start amount/current amount/status), add/edit forms (title, start amount, bonushunt linking options, increment amount, currency via global setting), and CRUD for multiple jackpots.
  - `admin/views/jackpots.php` renders the list table plus the add/edit modal with fields for scope (all hunts, selected hunts, affiliate filters, timeline) and increment settings, wired through `BHG_Jackpots::instance()`.
- [x] Logic updates on bonushunt close: mark hit on exact guess, otherwise increment amount; amounts use `bhg_currency` system.
  - `BHG_Jackpots::handle_hunt_closure()` (includes/class-bhg-jackpots.php) listens to `BHG_Models::close_hunt()` and either records the hit or increments via the configured amount, formatting currency through `bhg_format_money()`.
- [x] Shortcodes: `[bhg_jackpot_current id=""]`, `[bhg_jackpot_latest]` (filters: affiliate, date), `[bhg_jackpot_ticker mode="amount|winners"]`, `[bhg_jackpot_winners layout="list|table"]` with hide/show options for date, name, jackpot title, amount, affiliate website.
  - `BHG_Shortcodes` registers and renders all jackpot shortcodes (current amount widget, latest hits, ticker, winners list), each with hide/show attribute support and documented filters on the Info & Help page; PHPUnit's `CloseHuntTest` covers the jackpot increment/hit path.

## 9. Winner Limits Per User
- [ ] Admin Settings → Bonus Hunt Limits: max wins per user and rolling period (days) for bonushunts and tournaments; 0 disables limits.
- [ ] Awarding logic skips users exceeding limits while keeping rankings; logs each award with user, type, context ID, timestamp; rolling window uses wins in last N days; concurrent awards respect current log.

## 10. Core Admin and Frontend Features
- [ ] Admin "Latest Hunts" dashboard widget/page lists latest 3 hunts with winners (user + guess + difference), balances, closed date; winners bolded.
- [ ] Bonus Hunts admin list shows Final Balance (or –), Affiliate column, actions Edit/Results/Admin Delete/Enable-Disable Guessing; results view defaults to latest closed hunt with filters (hunt, tournament, time) and empty state string.
- [ ] Edit Bonushunt shows only active tournaments in multi-select, configurable winners count, participant list with removal and profile links.
- [x] Tournament DB includes `participants_mode` default "winners"; guessing-enabled column and affiliate_id exist; migrations idempotent with dbDelta and versioning.
  - `BHG_DB::maybe_upgrade_schema()` (`includes/class-bhg-db.php`) checks for `participants_mode`, `ranking_scope`, `winners_count`, `guessing_enabled`, and `affiliate_id` columns before issuing `ALTER TABLE` statements, and wraps create statements in `dbDelta()` with version comparisons to keep upgrades idempotent.

## 11. User/Admin/Affiliate Management
- [ ] User admin list supports search, sorting, pagination (30 per page); profile shows affiliate toggles per website.
- [ ] Affiliate site CRUD auto-syncs fields to user profiles; frontend affiliate lights and optional website display supported.

## 12. Shortcode Catalog and Pages
- [x] Shortcodes supported: `[bhg_user_profile]`, `[bhg_active_hunt]`, `[bhg_guess_form hunt_id=""]`, `[bhg_tournaments]`, `[bhg_winner_notifications]`, `[bhg_leaderboards]`, `[bhg_user_guesses id="" aff="" website=""]` (time-based ranking if no final balance; includes Difference column when final exists), `[bhg_hunts status="" bonushunt="" website="" timeline=""]` with Winners count, `[bhg_advertising status="" ad="" placement="none|…"]`, plus prize, jackpot, and homepage list shortcodes.
  - `BHG_Shortcodes::register_shortcodes()` wires every tag (including the newly prefixed homepage list shortcodes and `[bhg_jackpot_*]`), and PHPUnit's `ShortcodesRegistrationTest` now asserts the prefixed list variants exist.
- [x] Info & Help admin page lists all shortcodes with options/examples; recommended frontend pages documented with per-page override metabox support.
  - `admin/views/shortcodes.php` documents each shortcode, attributes, and notes (updated to the `bhg_` names), while `includes/core-pages.php` and the README enumerate the recommended pages + metabox overrides so QA can confirm page seeds.

## 13. Notifications System
- [x] Notifications tab with blocks for Winners, Tournament, Bonushunt; each has title, HTML description, BCC field (validated), enable/disable toggle (default disabled).
  - `admin/views/notifications.php` renders the three sections with nonce protection, enable checkboxes, subject/body editors, BCC textarea, and placeholder hints (`wp_kses_post` is used for helper text); settings are persisted via `bhg_handle_notifications_save()`.
- [x] Emails sent via `wp_mail()` with filters for headers, subject, and message; honors BCC.
  - `includes/notifications.php` normalizes BCC recipients, builds headers via `bhg_prepare_notification_headers()`, and dispatches winner/participant/tournament emails through `wp_mail()` while exposing filters for headers, subject, and body content.

## 14. Ranking and Points System
- [x] Centralized ranking service with editable default points mapping (1st–8th), scope toggle (active/closed/all hunts), and unit tests.
  - `BHG_Models::recalculate_tournament_results()` consumes the tournament points map + ranking scope, persists results to `bhg_tournament_results`, and the admin UI stores per-tournament maps; PHPUnit's `CloseHuntTest::test_recalculate_tournament_results_respects_participants_mode` exercises the recalculation logic.
- [x] Rankings available in backend and frontend; winners highlighted with top 3 extra styling.
  - `admin/views/bonus-hunts-results.php` and `includes/class-bhg-shortcodes.php` expose points/wins columns (with classes for top placements), while the `[my_rankings]` profile block summarizes totals for end users.

## 15. Global Styling and Currency
- [ ] Global CSS panel controls typography for titles, headings, descriptions, paragraphs/spans across tables, widgets, and shortcodes.
- [ ] Currency helpers `bhg_currency_symbol()` and `bhg_format_money()` used for all money outputs; setting supports EUR or USD and propagates across plugin.

## 16. Security, i18n, and Backward Compatibility
- [ ] Capability checks, nonces, sanitized/validated input, escaped output throughout admin/front-end.
  - Core admin handlers (`admin/views/notifications.php`, `admin/class-bhg-admin.php`) wrap saves in capability checks, `wp_nonce_field()`, and sanitize helpers (`sanitize_text_field`, `wp_kses_post`), but a full pass over every legacy screen is still on the QA to-do list.
- [ ] Legacy data handled safely with defaults; deprecated options mapped to new settings; migrations are idempotent and version-based.

## 17. Release and Documentation
- [ ] Version bumped (8.0.16/8.0.18 per header) with changelog covering DB migrations and features.
- [ ] Readme and Info & Help updated; include optional screenshots/GIFs and QA notes.

## 18. QA and Acceptance
- [ ] E2E coverage: create/close hunts, award winners (1–25), verify highlights and points; currency switch EUR↔USD; guessing toggle blocks/unblocks forms; participants mode enforced.
  - Automated coverage via `tests/CloseHuntTest.php`, `tests/AffiliateCleanupTest.php`, and `tests/CorePagesTest.php` exercises close-hunt flows, affiliate sync, and shortcode seeding, but a manual regression plan (hunts lifecycle, currency flip, guessing toggles) still needs to be executed before sign-off.
- [ ] Prize CRUD and frontend displays verified; notifications tested for enable/disable and BCC; translation loading confirmed for all strings.
  - `NotificationHeadersTest` and `TranslationsSeedTest` cover BCC sanitization and `.mo` loading, yet QA should still record frontend prize carousel verification plus real email smoke tests.

## 19. Error Log Follow-Up
- [x] Leaderboard shortcode no longer emits `array_key_exists()` warnings when WordPress passes raw attribute strings.
  - `BHG_Shortcodes::leaderboards_shortcode()` now normalizes `$atts` via `shortcode_atts()` before checking keys (lines 3600–3700), so the PHP warnings reported around lines 4432/4488/4498 are resolved.
- [x] Hunts/guesses tables initialize the `$status_filter` variable before building SQL queries, eliminating the "Undefined variable" notice from line 2935.
  - Both `[bhg_hunts]` and `[bhg_user_guesses]` seed `$status_filter` from shortcode attributes and query vars before applying the WHERE clause, so error logs remain clean when status filtering is toggled.
