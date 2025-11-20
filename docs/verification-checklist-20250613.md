# Verification Checklist — 13 Jun 2025

**Legend:** ✅ complete · ⚠️ partial/in-progress · ❌ missing/not implemented · 🔍 needs QA/validation

## 1. Runtime, Standards & Text Domain
- ✅ Plugin header advertises PHP 7.4, WordPress 6.3.5, MySQL 5.5.5+, GPLv2+, text domain `bonus-hunt-guesser`, and version 8.0.18 per current requirement. 【F:bonus-hunt-guesser.php†L3-L16】
- ✅ Text domain loads during `plugins_loaded` so translations continue to resolve. 【F:bonus-hunt-guesser.php†L400-L430】
- ⚠️ Coding standards tooling is present (`phpcs.xml` + Composer scripts) but uses the umbrella `WordPress` ruleset and has not been run for this verification; customer explicitly asked for WordPress-Core/Docs/Extra compliance. 【F:phpcs.xml†L1-L14】【F:composer.json†L1-L17】

## 2. Plugin Header, Bootstrapping & Localization
- ✅ `BHG_VERSION` constant and header metadata already set to 8.0.18, with changelog entries covering both 8.0.16 and 8.0.18 deliverables. 【F:bonus-hunt-guesser.php†L96-L105】【F:CHANGELOG.md†L1-L28】
- ✅ `bhg_register_prize_image_size()` registers the big (1200×800) image size, addressing the upload issue for larger PNGs. 【F:bonus-hunt-guesser.php†L92-L101】

## 3. Leaderboard Shortcode Enhancements
- ✅ Average rank/tournament numbers are formatted with zero decimal places for both the compact `[bhg_leaderboard_list]` and full table views. 【F:includes/class-bhg-shortcodes.php†L3610-L3643】【F:includes/class-bhg-shortcodes.php†L4992-L5004】
- ✅ Usernames are capitalized in leaderboard/tournament outputs (multibyte safe). 【F:includes/class-bhg-shortcodes.php†L3610-L3627】【F:includes/class-bhg-shortcodes.php†L4978-L4985】
- ✅ Prize panel surfaces above the leaderboard when an active tournament with prizes is selected, and the `show_prize_summary` attribute controls whether summary lines render. 【F:includes/class-bhg-shortcodes.php†L4552-L4664】
- ✅ Affiliate status column and green/red “lights” are rendered via `bhg_render_affiliate_dot()`; headers display “Affiliate” and the dots output inside the table body. 【F:includes/class-bhg-shortcodes.php†L4948-L5004】【F:includes/helpers.php†L1594-L1608】
- ✅ Position column is now sortable with screen-reader-friendly indicators. 【F:includes/class-bhg-shortcodes.php†L4929-L4947】
- ✅ Selected tournament/bonushunt names appear as stacked `<h2>` headings directly above the table results. 【F:includes/class-bhg-shortcodes.php†L4922-L4932】
- ✅ Dropdown filters default to `timeline`, `tournament`, `site`, and `affiliate`; the legacy bonushunt filter has been removed from defaults. Per-shortcode `filters=""` (or granular lists) toggles the filter controls. 【F:includes/class-bhg-shortcodes.php†L28-L31】【F:includes/class-bhg-shortcodes.php†L4421-L4480】【F:includes/class-bhg-shortcodes.php†L1096-L1155】
- ✅ `Times Won` aggregates only eligible prize wins that satisfy the active timeline or the explicit tournament constraint, thanks to the `hw.eligible = 1` filter and timeline range logic that is bypassed when a tournament is fixed. 【F:includes/class-bhg-shortcodes.php†L357-L466】
- ✅ Column header now reads “Username.” 【F:includes/class-bhg-shortcodes.php†L4929-L4937】
- 🔍 Open QA items: the previously reported “only one user displayed” bug has not been re-tested in this pass, so functional validation is still required even though the SQL now honors pagination/limits.

## 4. Tournament Admin & Shortcode Adjustments
- ✅ “Number of Winners” field exists on the tournament form; the legacy `type` field has been removed with an inline comment for posterity. 【F:admin/views/tournaments.php†L330-L371】
- ✅ Action buttons (Edit, Results, Close, Delete) render per tournament row. 【F:admin/views/tournaments.php†L248-L276】
- ✅ Front-end countdown box shows the “This tournament will close in x days” notice when still active. 【F:includes/class-bhg-shortcodes.php†L5269-L5277】
- ✅ Column headers renamed to “Position” and “Times Won,” with sortable icons on Position. 【F:includes/class-bhg-shortcodes.php†L5332-L5358】
- ✅ “Last win” column resolves to the user’s last bonushunt prize win within the tournament via the helper queries near the results table render. 【F:includes/class-bhg-shortcodes.php†L5358-L5395】
- ✅ Pagination obeys the global rows-per-page default, with `paginate_links()` output at the bottom of tournament, leaderboard, and hunt tables. 【F:includes/class-bhg-shortcodes.php†L5040-L5052】【F:includes/class-bhg-shortcodes.php†L5306-L5333】
- ⚠️ Timeline filter options on tournament/bonushunt list shortcodes still accept helper aliases (day/week/month/year) but omit `last_year`; customer asked for a limited set (Alltime, Today, This Week, This Month, This Quarter, This Year, Last Year). Additional alias coverage for “last year” should be added. 【F:includes/class-bhg-shortcodes.php†L3714-L3732】【F:includes/class-bhg-shortcodes.php†L3870-L3886】

## 5. Prize System & Dual Prize Sets
- ✅ Admin prize modal includes all required fields (title, description, category, multi-size images, CSS, active toggle, category link/target, click behavior). 【F:admin/views/prizes.php†L430-L481】【F:admin/views/prizes.php†L31-L44】
- ✅ Prize sizes labeled 300×200, 600×400, 1200×800 in the UI to guide uploads. 【F:admin/views/prizes.php†L460-L479】
- ✅ Prize link + click-behavior defaults (popup, same tab, new tab, none) and category link toggles are available. 【F:admin/views/prizes.php†L31-L44】【F:includes/class-bhg-prizes.php†L828-L938】
- ✅ Carousel controls (visible count, total loaded, autoplay, interval, show/hide headings) and display toggles (title/category/description) flow through `BHG_Prizes::prepare_section_options()` and the global display settings UI. 【F:includes/class-bhg-prizes.php†L640-L780】【F:includes/class-bhg-shortcodes.php†L1390-L1489】
- ✅ Responsive sizing automatically falls back to big/medium/small based on visible images before rendering the section. 【F:includes/class-bhg-shortcodes.php†L1390-L1400】
- ✅ Prize summary lists (regular & premium) display beneath the prize carousels, and admins can suppress them via shortcode attributes (`show_prize_summary`). 【F:includes/class-bhg-shortcodes.php†L4552-L4564】【F:includes/class-bhg-shortcodes.php†L1368-L1490】【F:includes/class-bhg-shortcodes.php†L5074-L5076】
- ✅ Bonus hunt admin exposes both regular and premium prize selectors; front-end logic shows premium tabs to affiliates and falls back to regular prizes otherwise. 【F:admin/views/bonus-hunts.php†L435-L603】【F:includes/class-bhg-shortcodes.php†L2265-L2317】
- ✅ Prize tabbed UI (Regular vs Premium) uses accessible buttons and panels. 【F:includes/class-bhg-shortcodes.php†L2291-L2305】【F:includes/class-bhg-shortcodes.php†L1497-L1523】
- ✅ Carousel/tab assets enqueue automatically, and no automatic “Prizes” heading is rendered unless configured (`hide_heading` + `heading_text`). 【F:includes/class-bhg-shortcodes.php†L1390-L1445】

## 6. Frontpage “List” Shortcodes
- ✅ `[bhg_latest_winners_list]`, `[bhg_leaderboard_list]`, `[bhg_tournament_list]`, and `[bhg_bonushunt_list]` exist with limit/field toggles, hide/show controls, status/timeline filters, and empty-state overrides. 【F:includes/class-bhg-shortcodes.php†L3483-L3977】
- ✅ Admin “Info & Help → Shortcodes” documents the new tags and their arguments for editors. 【F:admin/views/shortcodes.php†L90-L150】
- ✅ Bonushunt list adds timeline/status dropdown filters and a details column linking to Guess Now / Show Results. 【F:includes/class-bhg-shortcodes.php†L3865-L3967】
- ✅ `show_search="no"` attributes are wired for hunts/tournaments/leaderboards to hide the search block when desired. 【F:includes/class-bhg-shortcodes.php†L3998-L4275】【F:includes/class-bhg-shortcodes.php†L4421-L4932】
- ⚠️ Timeline alias lists for the “list” shortcodes still expose synonyms like `weekly`/`monthly` rather than the strictly requested set; documentation flags will need alignment once product confirms the allowed values. 【F:includes/class-bhg-shortcodes.php†L3527-L3543】【F:includes/class-bhg-shortcodes.php†L3714-L3732】

## 7. General Frontend Adjustments
- ✅ Table header links switched to white (`#fff`) with lighter hover state. 【F:assets/css/public.css†L7-L16】
- ✅ Hunts list now displays a “Details” column that points to Show Results (closed) or Guess Now (open) depending on `guessing_enabled`. 【F:includes/class-bhg-shortcodes.php†L3930-L3967】
- ✅ Mobile responsiveness handled via `.bhg-table-wrapper`, scroll containers, and responsive paddings/breakpoints for tables and forms. 【F:assets/css/public.css†L18-L74】

## 8. Jackpot Module
- ✅ Admin menu includes “Jackpots” with create/edit/delete/reset forms covering title, start/current amounts, increment per miss, link modes (all/selected/affiliate/time period), and status fields. 【F:admin/views/jackpots.php†L84-L230】
- ✅ Jackpot schema + event log tables ship via `dbDelta()`. 【F:includes/class-bhg-db.php†L283-L337】
- ✅ Service class handles CRUD, hunt-close integration, increments on misses, and formatting via `bhg_format_money()`. 【F:includes/class-bhg-jackpots.php†L12-L120】【F:includes/class-bhg-models.php†L357-L366】
- ✅ Front-end shortcodes `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` support filters and layouts. 【F:includes/class-bhg-shortcodes.php†L6330-L6564】
- 🔍 Multiple currency QA (EUR/USD) still needs to be run after DB migrations execute in staging.

## 9. Winner Limits Per User
- ✅ Settings → Bonus Hunt Limits defines per-type (hunt/tournament) max wins and rolling periods, with 0 = disabled. 【F:admin/views/settings.php†L118-L155】
- ✅ Awarding logic respects limits, tracks eligibility, and logs entries even when users are skipped because of rolling windows. 【F:includes/class-bhg-models.php†L210-L320】

## 10. Core Admin & Frontend Features
- ✅ Dashboard “Latest Hunts” widget lists the three latest hunts with per-winner rows, bold usernames, start/final balances, and closed timestamps. 【F:admin/views/dashboard.php†L83-L210】
- ✅ Bonus hunts list shows final balance and affiliate columns; actions include Edit, Results, Delete, and Guessing toggle (per `admin/views/bonus-hunts.php`, not repeated here). Pagination/search supported via shared helper. 
- ✅ Tournament participants mode, hunt-to-tournament mapping, and `participants_mode` column exist per migrations and admin UI. 【F:includes/class-bhg-db.php†L325-L337】【F:admin/views/tournaments.php†L248-L356】
- ✅ User admin includes affiliate toggles; affiliates sync into usermeta and front-end lights via helper functions. 【F:includes/helpers.php†L1580-L1608】

## 11. Shortcode Catalog & Recommended Pages
- ✅ All legacy shortcodes (`[bhg_user_profile]`, `[bhg_active_hunt]`, `[bhg_guess_form]`, `[bhg_tournaments]`, `[bhg_winner_notifications]`, `[bhg_leaderboards]`, `[bhg_user_guesses]`, `[bhg_hunts]`, `[bhg_advertising]`) remain registered inside `BHG_Shortcodes`. Doc coverage confirmed via Info & Help screen. 【F:admin/views/shortcodes.php†L90-L150】【F:includes/class-bhg-shortcodes.php†L80-L120】
- ✅ Recommended front-end pages and shortcode usage patterns documented in admin Info & Help files (no code change needed here).

## 12. Notifications System
- ✅ Notifications tab exposes Winner, Tournament, and Bonus Hunt sections with enable toggles, HTML subject/body, and BCC textarea. Nonces and capability checks wrap the save handler. 【F:admin/views/notifications.php†L16-L101】
- ✅ Backend helpers normalize BCC lists, build headers (including Bcc), and dispatch mail through `wp_mail()` with filters available. 【F:includes/notifications.php†L12-L132】

## 13. Ranking & Points System
- ✅ Default points map (1st–8th) plus sanitizers exist; admin UI lets operators edit the distribution. 【F:includes/helpers.php†L1346-L1404】【F:admin/views/tournaments.php†L346-L356】
- ✅ Tournament closing logic awards points, respects participants mode, and sorts winners via a centralized model class. 【F:includes/class-bhg-models.php†L428-L690】
- ⚠️ Dedicated unit tests for the ranking service were not located in `tests/`; manual QA still required. 【F:tests/ShortcodesRegistrationTest.php†L1-L40】

## 14. Global CSS / Color Panel
- ✅ Settings screen exposes typography/colour controls for headings, descriptions, and body text. 【F:admin/views/settings.php†L60-L108】
- ✅ `bhg_build_global_styles_css()` converts the stored settings into inline CSS enqueued with the public assets. 【F:bonus-hunt-guesser.php†L468-L580】

## 15. Currency System
- ✅ `bhg_currency` option (EUR/USD) is configurable in settings and referenced by helpers. 【F:admin/views/settings.php†L76-L90】
- ✅ `bhg_currency_symbol()` / `bhg_format_money()` centralize formatting and are reused across admin/front-end tables. 【F:includes/helpers.php†L1081-L1102】

## 16. Database & Migrations
- ✅ Migrations include required columns (`guessing_enabled`, `participants_mode`, `affiliate_id`) plus the hunt↔tournament junction and jackpot tables, all wrapped with `dbDelta()`. 【F:includes/class-bhg-db.php†L250-L337】

## 17. Security, i18n, Compatibility
- ✅ Settings/notifications/jackpot forms use `wp_nonce_field()` and capability checks; outputs escaped via helpers. 【F:admin/views/notifications.php†L16-L101】【F:admin/views/jackpots.php†L98-L225】
- ✅ Strings pass through `bhg_t()` and the loaded text domain; `bhg_render_notification_template()` offers placeholder substitutions while keeping HTML contexts intact. 【F:includes/notifications.php†L12-L151】

## 18. Global UX Guarantees
- ✅ `bhg_get_shortcode_rows_per_page()` centralizes pagination defaults; shortcodes honor search/pagination/sorting controls. 【F:includes/helpers.php†L1152-L1169】【F:includes/class-bhg-shortcodes.php†L4460-L5055】
- ✅ Affiliate lights and optional website display propagate through leaderboard/hunt/tournament shortcodes. 【F:includes/class-bhg-shortcodes.php†L4948-L5004】【F:includes/class-bhg-shortcodes.php†L3930-L3967】

## 19. Release & Documentation
- ✅ Changelog documents 8.0.16 (jackpot module) and 8.0.18 (metadata sync) releases; README and Info & Help already reflect the latest shortcodes/features. 【F:CHANGELOG.md†L1-L28】【F:admin/views/shortcodes.php†L90-L150】
- 🔍 No fresh screenshots/GIFs captured in this pass.

## 20. QA / Acceptance Tests – Outstanding
- ⚠️ Manual QA still required for PHPCS compliance, front-end pagination, search toggles, affiliate prize gating, jackpot increments, winner limits, currency toggle (EUR↔USD), guessing toggle, and translation coverage. Automated verification is not part of this run.

## Known Log Warnings (from customer-provided 17 Nov 2025 log)
```
[17-Nov-2025 16:36:58 UTC] PHP Warning:  array_key_exists() expects parameter 2 to be array, string given in ...class-bhg-shortcodes.php on line 4432
[17-Nov-2025 16:36:58 UTC] PHP Warning:  array_key_exists() expects parameter 2 to be array, string given in ...class-bhg-shortcodes.php on line 4488
[17-Nov-2025 16:36:58 UTC] PHP Warning:  array_key_exists() expects parameter 2 to be array, string given in ...class-bhg-shortcodes.php on line 4498
[17-Nov-2025 16:36:58 UTC] PHP Notice:  Undefined variable: status_filter in ...class-bhg-shortcodes.php on line 2935
```
These should be reproduced locally (likely caused by `array_key_exists()` calls when `$atts` is passed as a string) and fixed before release.
