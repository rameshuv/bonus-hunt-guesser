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
- [ ] Shortcode `[bhg_leaderboards tournament="" bonushunt="" aff="" website="" ranking="1-10" timeline=""]` returns full user list (e.g., 26 users) not a single entry.
  - Attribute arrays are normalized inside `BHG_Shortcodes::leaderboards_shortcode()` so `array_key_exists()` is never executed on a bare string, clearing the PHP warnings reported at lines 4432/4488/4498 of `includes/class-bhg-shortcodes.php`.
- [ ] Avg Rank and Avg Tournament Pos display rounded integers (no decimals).
- [ ] Username outputs are capitalized in all frontend shortcodes; column header reads "username".
- [x] Prize box appears above table when a specific active tournament is selected.
- [ ] Affiliate column added after Avg Tournament Pos with green/red light indicator; Position column sortable.
- [x] Tournament/bonushunt titles rendered as H2 above table (tournament first when both set).
- [x] Bonushunt dropdown removed; leaderboard filters configurable via shortcode attributes to hide/show timeline, tournament, affiliate site, and affiliate status filters.
- [ ] Times Won counts only prize wins within timeline filter or selected tournament.
- [ ] Supports search, sorting, pagination (30 per page or global setting), timeline filters (Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year), affiliate lights, optional affiliate website display, and documentation on Info & Help page.

## 4. Tournament Admin and Shortcode
- [x] Admin add/edit includes Title, Description, Participants Mode (winners only/all guessers), Number of Winners, no legacy type field.
  - `admin/views/tournaments.php` renders the required fields plus Participants Mode select and Number of Winners inputs for both add and edit forms; the legacy `type` dropdown was removed in v8.0.18 so only supported settings remain.
- [x] List actions include Edit, Results, Close, Admin Action (Delete).
  - The tournaments list table (same view, rows 270–320) exposes inline buttons for Edit, Close (with nonce-protected form), Results (linking to the results screen), and Delete, satisfying the requested admin affordances.
- [ ] Tournament shortcode `[bhg_tournaments status="" tournament="" website="" timeline=""]` shows Name, Position column sortable, headers updated (Position, Times Won), pagination using global rows-per-page setting, and yellow closing notice for active tournaments with days remaining.
- [ ] Last Win column shows last bonushunt prize win tied to the tournament (not tournament win).
- [ ] Timeline filters limited to Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year.

## 5. Prize System
- [x] Admin CRUD supports title, description, category (cash money, casino money, coupons, merchandise, various), images (small/medium/big), CSS settings, active toggle, and prize link field.
  - The prize modal in `admin/views/prizes.php` includes inputs for metadata, CSS styling, active flag, media pickers for all three sizes, and optional prize/website links.
- [x] Fix big image (1200×800 PNG) uploads; label sizes (300×200, 600×400, 1200×800) in backend.
  - `bonus-hunt-guesser.php` registers the `bhg_prize_big` image size at 1200×800, and the admin form labels each picker accordingly so users know the expected resolution.
- [x] Prize categories manage name, optional link, show/hide link toggle; frontend category label clickable when enabled.
  - `admin/views/prizes.php` ships a dedicated categories panel with name/link/target inputs plus a toggle for showing the link, and `BHG_Prizes::get_category_label()` outputs clickable badges when `show_link` is set.
- [x] Image click behavior configurable: popup large image, open link same window, open link new window.
  - The display settings expose click-action defaults and per-prize overrides (`link`, `new`, `image`, `none`) along with target selectors that map to the frontend card renderer in `includes/class-bhg-shortcodes.php`.
- [x] Carousel/grid options: visible images, total images to load, autoscroll toggle, show/hide title/category/description; responsive sizing (1=big, 2–3=medium, 4–5=small).
  - Admin display settings manage carousel visible count, autoplay, headings, and per-field toggles; `includes/class-bhg-shortcodes.php` resolves the responsive size via `resolve_responsive_prize_size()` and `assets/js/bhg-prizes.js` enforces viewport-aware slide widths.
- [x] Frontend removes automatic "Prizes" heading; text added manually.
  - `render_prize_section()` only renders a heading when a custom label is supplied and `hide_heading` is false, so no forced "Prizes" title appears.
- [x] Bonus Hunt admin selects Regular and Premium prize sets; tournament/leaderboard shortcodes show tabbed regular vs premium carousels with optional summary list per place (1st, 2nd, etc.).
  - Bonus hunt forms include dual multi-selects for regular/premium prize sets, and the shortcode renderer outputs tabbed carousels with affiliate-aware prize selection plus per-place summary lists underneath.
- [x] Prize shortcodes support show/hide summary list; leaderboard/tournament shortcodes allow showing/hiding prizes and summary list. Prize summary list appears beneath prize boxes when specific tournament selected.
  - `[bhg_prizes]` uses the `show_summary` attribute to toggle summaries, while leaderboard/tournament shortcodes pass `show_summary`/`show_prizes` options through to `render_prize_section()`, ensuring the textual prize list is appended when requested.

## 6. Frontpage List Shortcodes
- [x] Implement `[bhg_latest_winners_list]` with toggles for date, username, prize won, bonushunt title, tournament title.
  - `BHG_Shortcodes::latest_winners_list_shortcode()` (includes/class-bhg-shortcodes.php) enforces `limit` (1–100) and parses the comma-delimited `fields` list so admins can hide/show each segment; shortcode registration uses the canonical `bhg_` prefix with a legacy alias for backwards compatibility.
- [x] Implement `[bhg_leaderboard_list]` supporting specific tournament/bonushunt IDs and hide/show toggles for position, username, times won, average hunt position, average tournament position.
  - The compact leaderboard list handler honours `timeline`, `tournament`, `bonushunt`, `website`, and `aff` filters plus `fields` toggles for every metric, and Info & Help now documents the `[bhg_leaderboard_list]` signature.
- [x] Implement `[bhg_tournament_list]` and `[bhg_bonushunt_list]` showing timeline/status with hide/show controls for listed fields; both support allowed timeline values and optional search block.
  - `BHG_Shortcodes::tournament_list_shortcode()` and `bonushunt_list_shortcode()` accept `status`, `timeline`, `limit`, `orderby`, and `fields` attributes so each column (name, start/end dates, winners/status/details) can be toggled per the runtime requirements.
- [x] Bonushunt shortcode includes timeline and status filters; all list/leaderboard/bonushunt/tournament shortcodes can hide/show search block.
  - `[bhg_bonushunt_list]` validates `timeline` keywords (Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year aliases) and the standard table shortcodes continue to expose `show_search` toggles, keeping the home-page widgets aligned with the trimmed filter set.

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
