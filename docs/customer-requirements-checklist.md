# Bonus Hunt Guesser — Customer Acceptance Checklist

_Last reviewed:_ 2025-02-17 (UTC)
_Status legend:_ `[x]` complete, `[!]` needs attention, `[?]` requires manual verification.

## Quick Reference Matrix

| Requirement Block | Key Implementation Files |
| --- | --- |
| Core Bonus Hunt & Guessing | `bonus-hunt-guesser.php`, `admin/views/bonus-hunts.php`, `includes/class-bhg-shortcodes.php` |
| User Profiles & Guessing Enhancements | `admin/views/users.php`, `includes/helpers.php`, `includes/class-bhg-login-redirect.php` |
| Tournament & Leaderboard System | `admin/views/tournaments.php`, `includes/class-bhg-db.php`, `includes/class-bhg-shortcodes.php` |
| Frontend Leaderboard Enhancements | `includes/class-bhg-shortcodes.php`, `assets/css/bhg-shortcodes.css` |
| UX Improvements (Menus, Redirects, Translations) | `includes/class-bhg-login-redirect.php`, `includes/class-bhg-front-menus.php`, `admin/views/translations.php` |
| Affiliate Adjustments | `admin/views/affiliate-websites.php`, `admin/views/bonus-hunts.php`, `admin/views/users.php` |
| Notifications & Final Polish | `includes/class-bhg-notifications.php`, `admin/views/notifications.php`, `assets/css/admin.css` |
| Backend Admin Addendum | `admin/views/dashboard.php`, `admin/views/bonus-hunts-results.php`, `admin/views/advertising.php` |
| Tooling & Compliance | `phpcs.xml`, `tests/bootstrap.php`, `tests/HelpersTest.php`, `CHANGELOG.md`, `README.md` |

## Add-On Verification — Customer Checklist

The customer requested an explicit validation of the prizes, shortcode, notification, and tournament enhancements. The table below records their status and the primary files involved when remediation is required.

| # | Requirement | Status | Notes / Files to modify |
| --- | --- | --- | --- |
| 1 | Prizes menu in admin with CRUD, category, multi-size images, CSS panel (border/border-color/padding/margin/background), active toggle | ✅ | Implemented via `admin/class-bhg-admin.php`, `admin/views/prizes.php`, `includes/class-bhg-prizes.php`, and enqueue script/styles in `assets/js/admin-prizes.js`, `assets/css/admin.css`. |
| 2 | Bonus hunt add/edit supports selecting one or multiple prizes | ✅ | Multi-select rendered in `admin/views/bonus-hunts.php` and persisted through `admin/class-bhg-admin.php` → `BHG_Prizes::set_hunt_prizes()`. |
| 3 | Active Hunt shortcode renders prizes as grid or carousel with dots/arrows | ✅ | `[bhg_active_hunt]` accepts `prize_layout`/`prize_size`; output handled in `includes/class-bhg-shortcodes.php` with behaviour scripts in `assets/js/bhg-shortcodes.js`. |
| 4 | `[bhg_prizes]` shortcode supports `category`, `design` (grid/carousel), `size` (small/medium/big), `active` (yes/no) filters | ✅ | Implemented in `includes/class-bhg-shortcodes.php` leveraging `BHG_Prizes::get_prizes()`. |
| 5 | Front-end “My Profile” shortcodes (`my_bonushunts`, `my_tournaments`, `my_prizes`, `my_rankings`) with admin hide/show toggles | ❌ | Not present; add shortcode handlers and visibility settings in `includes/class-bhg-shortcodes.php`, profile rendering helpers, and admin toggles (e.g. `admin/views/users.php` or dedicated settings). |
| 6 | Extended CSS/color panel covering title block, H2/H3, description, and standard text styling | ❌ | Current form only captures border/background basics. Enhance `admin/views/prizes.php`, `includes/class-bhg-prizes.php`, and front-end styles in `assets/css/bhg-shortcodes.css` to store/apply per-element styles. |
| 7 | “Info & Help” Shortcodes admin screen listing all shortcodes and attributes | ❌ | Menu item/view missing. Introduce submenu in `admin/class-bhg-admin.php` and template under `admin/views/shortcodes.php`. |
| 8 | Notifications tab with winner/tournament/bonus-hunt blocks (subject, HTML body, BCC, enable checkbox) | ✅ | Available via `admin/views/notifications.php` and handled by `includes/class-bhg-notifications.php`. |
| 9 | Tournaments: attach prizes (admin & frontend), affiliate website URL field, show/hide checkbox for frontend exposure | ❌ | Admin form lacks these controls and front-end ignores prize display. Update `admin/views/tournaments.php`, persist via `admin/class-bhg-admin.php`/`includes/class-bhg-db.php`, and render in `includes/class-bhg-shortcodes.php`. |
| 10 | Tournament ranking point system (editable points per placement, scoped to active/closed/all hunts; results highlight top winners) | ❌ | No point-allocation logic exists. Requires schema/storage in `includes/class-bhg-db.php`, calculations in hunt close handlers, admin UI for point tables, and leaderboard updates in `includes/class-bhg-shortcodes.php`. |

## Verification Summary

- ✅ Functional tests: `vendor/bin/phpunit`
- ✅ Coding standards: `vendor/bin/phpcs --standard=phpcs.xml`

Each requirement below links back to the main implementation areas for quick spot checks.

## A. Core Functionality (Bonus Hunts & Guessing)
- [x] Admin can create a bonus hunt with title, starting balance, number of bonuses, and prize description. (`admin/views/bonus-hunts.php`)  
  *Verified form inputs for title, starting balance, bonus count, winners, and prizes.*
- [x] Logged-in users can submit a final balance guess between €0 and €100,000 for the active hunt. (`bonus-hunt-guesser.php`)  
  *`bhg_handle_submit_guess()` enforces min/max limits and login checks.*
- [x] Frontend displays active hunt details (title, starting balance, bonuses count). (`includes/class-bhg-shortcodes.php`)  
  *`active_hunt_shortcode()` prints the selected hunt card with balances and bonus count.*
- [x] Leaderboard lists all guesses with position, username, and guessed balance. (`includes/class-bhg-shortcodes.php`)  
  *Rendered in both `[bhg_active_hunt]` and `[bhg_leaderboard]` outputs with pagination support.*

## B. User Profiles & Guessing Enhancements
- [x] Admin user management includes real name, username, email, and affiliate status fields. (`admin/views/users.php`)  
  *Inline edit controls allow updating real name, global affiliate flag, and per-site toggles.*
- [x] Integration detects Nextend Social Login (Google/Twitch/Kick) without hard coupling. (`includes/class-bhg-login-redirect.php`)  
  *Checks for `NextendSocialLogin()` and maps profile data when available.*
- [x] Users may edit their guesses while the hunt remains open. (`bonus-hunt-guesser.php`)  
  *When `allow_guess_changes` is enabled the handler updates the latest guess instead of blocking.*
- [x] Leaderboard shows affiliate indicator (green for affiliates, red for non-affiliates). (`includes/helpers.php`, `includes/class-bhg-shortcodes.php`)  
  *`bhg_render_affiliate_dot()` emits colour-coded markers appended to usernames.*
- [x] Guess table supports sorting by position, username, and guess amount with pagination. (`includes/class-bhg-shortcodes.php`)  
  *`bhg_leaderboard` shortcode sanitises `orderby`/`order` inputs and paginates results.*

## C. Tournament & Leaderboard System
- [x] Admin can create tournaments with title, description, schedule (monthly/quarterly/yearly/all-time). (`admin/views/tournaments.php`, `admin/class-bhg-admin.php`, `includes/class-bhg-db.php`)
  *Schema now retains the `type` column and the save handler persists the selected value without errors.*
- [x] Tournament leaderboard exposes sortable columns (position, username, wins) and filters (week/month/year). (`includes/class-bhg-shortcodes.php`)  
  *`[bhg_leaderboards]` builds CASE expressions for period filters and applies sort toggles.*
- [x] Historical tournament data accessible alongside current standings. (`includes/class-bhg-shortcodes.php`)  
  *Timeline filters (`this_month`, `this_year`, etc.) are translated into date ranges for archived data.*

## D. Frontend Leaderboard Enhancements
- [x] Leaderboard interface provides tabs for Overall, Monthly, Yearly, and All-Time best guessers. (`includes/class-bhg-shortcodes.php`, `assets/css/bhg-shortcodes.css`)  
  *`bhg_render_leaderboard_tabs()` outputs the tabbed navigation with matching styles.*
- [x] Tabs expose history across previous bonus hunts. (`includes/class-bhg-shortcodes.php`)  
  *Historic closed hunts are listed under the “Bonus Hunts” tab with deep links.*

## E. User Experience Improvements
- [x] Smart login redirect returns users to the page that required authentication. (`includes/class-bhg-login-redirect.php`)  
  *Filters `login_redirect` and Nextend callbacks to honour the attempted URL.*
- [x] Three WordPress menu locations registered (Admins/Mods, Logged-in, Guests) with styling guidance. (`includes/class-bhg-front-menus.php`, `assets/css/bhg-shortcodes.css`)  
  *`register_nav_menus()` registers the three slots; shortcode helpers pick the appropriate menu.*
- [x] Translations admin tab lists all plugin strings with override capability. (`admin/views/translations.php`, `includes/helpers.php`)  
  *Interface seeds defaults, supports search, pagination, and updates the translations table.*

## F. Affiliate Adjustment / Upgrade
- [x] Admin CRUD for multiple affiliate websites (add/edit/delete). (`admin/views/affiliate-websites.php`, `admin/class-bhg-admin.php`)  
  *Listing, edit, and delete actions are nonce-protected.*
- [x] Bonus hunt edit form includes affiliate dropdown selection. (`admin/views/bonus-hunts.php`)  
  *Affiliate site select appears on create/edit screens.*
- [x] User profile shows affiliate yes/no per affiliate site. (`admin/views/users.php`)  
  *Each affiliate renders as a checkbox within the profile row.*
- [x] Frontend guess tables and ad targeting respect per-affiliate status. (`includes/class-bhg-shortcodes.php`, `admin/views/advertising.php`)  
  *Affiliate filters propagate through leaderboard queries and ad placement rules.*

## G. Final Enhancements & Polish
- [x] Winner ranking uses closest final-balance difference. (`admin/views/bonus-hunts-results.php`, `includes/class-bhg-shortcodes.php`)  
  *Queries order by `ABS(final_balance - guess)` and display formatted differences.*
- [x] Email notifications announce results and wins when enabled. (`includes/class-bhg-notifications.php`, `admin/views/notifications.php`)  
  *Notifications service stores templates, enables toggles, and dispatches emails via hooks.*
- [x] Performance fixes and bug resolutions documented in changelog. (`CHANGELOG.md`)  
  *8.0.14 entry notes caching, dashboard unions, and schema clean-up items.*
- [x] Bonus Hunt admin inputs use required border styling. (`assets/css/admin.css`)  
  *Admin stylesheet adds consistent border/padding for hunt forms.*
- [x] Advertising module allows text/link ads, placement control (including footer), login/affiliate visibility, and `none` placement for shortcode use. (`admin/views/advertising.php`)  
  *Placement map includes `none`, and visibility filters cover login/affiliate conditions.*

## H. Backend Admin Adjustments (Customer Addendum)
- [x] Main submenu renamed from "Bonushunt" to "Dashboard". (`admin/class-bhg-admin.php`)  
  *Top-level menu now registers a dedicated Dashboard submenu.*
- [x] Dashboard shows "Latest Hunts" table with three hunts, all winners, balances, and closed date columns. (`admin/views/dashboard.php`)  
  *Row-spanned table lists each winner row with bold usernames and money columns.*
- [x] Bonus Hunts list adds Final Balance column ("-" if open), Results action, participant list with removal controls, and winner count configuration (1–25). (`admin/views/bonus-hunts.php`)  
  *List table includes final balance and actions; edit view shows participant overview and winner count select.*
- [x] Hunt Results admin ranks guesses best-to-worst, highlights winners, includes Prize column, and defaults to most recent closed hunt with filters. (`admin/views/bonus-hunts-results.php`)  
  *Selector defaults to latest closed hunt; table highlights winners and shows prize titles.*
- [x] Tournaments admin restores title/description fields, supports type choices (monthly/quarterly/yearly/all-time), removes redundant period field, and fixes edit flow. (`admin/views/tournaments.php`, `includes/class-bhg-db.php`)
  *Database keeps the `type` column so edit and create actions save and reload the configured schedule.*
- [x] Users admin supports search, sorting, and pagination (30 per page). (`admin/views/users.php`)  
  *Implements `WP_User_Query` with search box, sortable headers, and paginated results.*
- [x] Ads admin includes Actions column (Edit/Remove) with nonce protection and `none` placement option. (`admin/views/advertising.php`)  
  *Action buttons include nonces; placement selector lists `none` for shortcode-only ads.*
- [x] Translations and Tools admin screens display meaningful data. (`admin/views/translations.php`, `admin/views/tools.php`)  
  *Translations list populates keys; Tools screen reports diagnostics counts.*

## I. Versioning, Tooling & Compliance
- [x] Plugin header metadata updated to final contract values in `bonus-hunt-guesser.php` (Requires PHP 7.4, WordPress 6.3.5+, MySQL 5.5.5, version 8.0.14). (`bonus-hunt-guesser.php`)
  *Header block reflects agreed metadata.*
- [x] CHANGELOG.md and docs reflect version 8.0.14 release scope. (`CHANGELOG.md`, `README.md`)  
  *Changelog entry and README onboarding steps align with 8.0.14 features.*
- [x] Database migrations remain MySQL 5.5.5 compatible via `dbDelta()` and helper guards. (`includes/class-bhg-db.php`)  
  *`create_tables()` builds `CREATE TABLE` statements passed to `dbDelta()` with 5.5-safe SQL.*
- [x] PHPCS WordPress Core/Docs/Extra standard enforced via `vendor/bin/phpcs --standard=phpcs.xml`. (`phpcs.xml`)
  *Ruleset now scans the admin, includes, tests, and bootstrap files (excluding vendor) so coding standards cover the full plugin.*
- [x] PHPUnit bootstrap with WordPress stubs executes `vendor/bin/phpunit` suite. (`tests/bootstrap.php`, `tests/HelpersTest.php`)  
  *Bootstrap seeds fake `$wpdb` and helper tests cover parsing/sanitisation.*

## J. Documentation & Onboarding
- [x] Required WordPress pages and associated shortcodes documented for site setup. (`README.md`)
  *Onboarding checklist enumerates the eight required pages and matching shortcodes.*
- [x] Onboarding guide covers menu assignments, translations workflow, affiliate usage, and notifications configuration. (`README.md`)
  *Steps 2–5 outline menus, translations, affiliates, and notifications setup.*

## Verification

- `vendor/bin/phpunit`
- `vendor/bin/phpcs --standard=phpcs.xml`
