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
- [ ] Main file is `bonus-hunt-guesser.php` with Name, URI, Description, Version (8.0.16–8.0.18), Author, Text Domain, Domain Path, Requires PHP 7.4, Requires at least WP 6.3.5, GPLv2+.
- [ ] Text domain is loaded on init; no PHPCS header issues.

## 3. Leaderboard Shortcode Requirements
- [ ] Shortcode `[bhg_leaderboards tournament="" bonushunt="" aff="" website="" ranking="1-10" timeline=""]` returns full user list (e.g., 26 users) not a single entry.
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
- [ ] Implement `[bhg_latest_winners_list]` with toggles for date, username, prize won, bonushunt title, tournament title.
- [ ] Implement `[bhg_leaderboard_list]` supporting specific tournament/bonushunt IDs and hide/show toggles for position, username, times won, average hunt position, average tournament position.
- [ ] Implement `[bhg_tournament_list]` and `[bhg_bonushunt_list]` showing timeline/status with hide/show controls for listed fields; both support allowed timeline values and optional search block.
- [ ] Bonushunt shortcode includes timeline and status filters; all list/leaderboard/bonushunt/tournament shortcodes can hide/show search block.

## 7. General Frontend Adjustments
- [ ] Table header links use white (#fff) text.
- [ ] Hunts list adds Details column next to Status: "Show Results" for closed hunts, "Guess Now" for open hunts; mobile-friendly tables across all shortcodes.

## 8. Jackpot Module
- [ ] Admin menu "Jackpots" with list (latest 10, title/start date/start amount/current amount/status), add/edit forms (title, start amount, bonushunt linking options, increment amount, currency via global setting), and CRUD for multiple jackpots.
- [ ] Logic updates on bonushunt close: mark hit on exact guess, otherwise increment amount; amounts use `bhg_currency` system.
- [ ] Shortcodes: `[bhg_jackpot_current id=""]`, `[bhg_jackpot_latest]` (filters: affiliate, date), `[bhg_jackpot_ticker mode="amount|winners"]`, `[bhg_jackpot_winners layout="list|table"]` with hide/show options for date, name, jackpot title, amount, affiliate website.

## 9. Winner Limits Per User
- [ ] Admin Settings → Bonus Hunt Limits: max wins per user and rolling period (days) for bonushunts and tournaments; 0 disables limits.
- [ ] Awarding logic skips users exceeding limits while keeping rankings; logs each award with user, type, context ID, timestamp; rolling window uses wins in last N days; concurrent awards respect current log.

## 10. Core Admin and Frontend Features
- [ ] Admin "Latest Hunts" dashboard widget/page lists latest 3 hunts with winners (user + guess + difference), balances, closed date; winners bolded.
- [ ] Bonus Hunts admin list shows Final Balance (or –), Affiliate column, actions Edit/Results/Admin Delete/Enable-Disable Guessing; results view defaults to latest closed hunt with filters (hunt, tournament, time) and empty state string.
- [ ] Edit Bonushunt shows only active tournaments in multi-select, configurable winners count, participant list with removal and profile links.
- [ ] Tournament DB includes `participants_mode` default "winners"; guessing-enabled column and affiliate_id exist; migrations idempotent with dbDelta and versioning.

## 11. User/Admin/Affiliate Management
- [ ] User admin list supports search, sorting, pagination (30 per page); profile shows affiliate toggles per website.
- [ ] Affiliate site CRUD auto-syncs fields to user profiles; frontend affiliate lights and optional website display supported.

## 12. Shortcode Catalog and Pages
- [ ] Shortcodes supported: `[bhg_user_profile]`, `[bhg_active_hunt]`, `[bhg_guess_form hunt_id=""]`, `[bhg_tournaments]`, `[bhg_winner_notifications]`, `[bhg_leaderboards]`, `[bhg_user_guesses id="" aff="" website=""]` (time-based ranking if no final balance; includes Difference column when final exists), `[bhg_hunts status="" bonushunt="" website="" timeline=""]` with Winners count, `[bhg_advertising status="" ad="" placement="none|…"]`, plus prize and advertising shortcodes.
- [ ] Info & Help admin page lists all shortcodes with options/examples; recommended frontend pages documented with per-page override metabox support.

## 13. Notifications System
- [ ] Notifications tab with blocks for Winners, Tournament, Bonushunt; each has title, HTML description, BCC field (validated), enable/disable toggle (default disabled).
- [ ] Emails sent via `wp_mail()` with filters for headers, subject, and message; honors BCC.

## 14. Ranking and Points System
- [ ] Centralized ranking service with editable default points mapping (1st–8th), scope toggle (active/closed/all hunts), and unit tests.
- [ ] Rankings available in backend and frontend; winners highlighted with top 3 extra styling.

## 15. Global Styling and Currency
- [ ] Global CSS panel controls typography for titles, headings, descriptions, paragraphs/spans across tables, widgets, and shortcodes.
- [ ] Currency helpers `bhg_currency_symbol()` and `bhg_format_money()` used for all money outputs; setting supports EUR or USD and propagates across plugin.

## 16. Security, i18n, and Backward Compatibility
- [ ] Capability checks, nonces, sanitized/validated input, escaped output throughout admin/front-end.
- [ ] Legacy data handled safely with defaults; deprecated options mapped to new settings; migrations are idempotent and version-based.

## 17. Release and Documentation
- [ ] Version bumped (8.0.16/8.0.18 per header) with changelog covering DB migrations and features.
- [ ] Readme and Info & Help updated; include optional screenshots/GIFs and QA notes.

## 18. QA and Acceptance
- [ ] E2E coverage: create/close hunts, award winners (1–25), verify highlights and points; currency switch EUR↔USD; guessing toggle blocks/unblocks forms; participants mode enforced.
- [ ] Prize CRUD and frontend displays verified; notifications tested for enable/disable and BCC; translation loading confirmed for all strings.
