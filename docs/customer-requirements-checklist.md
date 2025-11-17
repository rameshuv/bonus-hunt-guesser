# Customer Requirements Checklist (Bonus Hunt Guesser)

Statuses: **Met** (verified in code), **Partial** (implemented but missing options/QA), **Gap** (not present), **Not Verified** (needs functional QA).

## Runtime & Versioning
- **Plugin header declares PHP 7.4 / WP 6.3.5 minimum** — **Met**. Header enforces the required runtime targets.【F:bonus-hunt-guesser.php†L3-L12】
- **Version bumped to 8.0.18** — **Gap**. Codebase still defines **8.0.16** in the plugin header and constant; update `bonus-hunt-guesser.php` to 8.0.18 to match the spec.【F:bonus-hunt-guesser.php†L6-L12】【F:bonus-hunt-guesser.php†L154-L158】

## Admin Dashboard & Navigation
- **Top-level menu label + Dashboard submenu present** — **Met**. Dashboard remains the first submenu under the Bonus Hunt menu.【F:admin/class-bhg-admin.php†L53-L99】
- **Dashboard shows “Latest Hunts” (latest 3) with all winners, start/final balance, closed at** — **Met**. The dashboard template lists the latest hunts with multi-row winners, balances, and closed timestamps.【F:admin/views/dashboard.php†L111-L205】

## Bonus Hunts (List/Edit/Results)
- **List columns include Final Balance and guessing toggle** — **Met**. The admin list shows a Final Balance column plus enable/disable guessing controls.【F:admin/views/bonus-hunts.php†L168-L236】【F:admin/views/bonus-hunts.php†L264-L287】
- **Winners count configurable + participant list with removal + profile links** — **Met**. Hunt edit surfaces winners count, a participants table with remove buttons, and username profile links.【F:admin/views/bonus-hunts-edit.php†L152-L251】
- **Results page with ranking and highlighted winners** — **Partial**. Results view lists guesses with winner highlighting, but the checklist still lacks explicit confirmation of the “remove guess” control on the results screen; verify/extend in `admin/views/bonus-hunts-results.php`.

## Tournaments
- **Admin list + edit form (title, description, type, participants mode, winners count, prizes, actions)** — **Met**. The tournaments screen supports search/sort/pagination, edit/close/delete/results actions, title/description fields, type options (including quarterly/alltime), participants mode, winners count, prize selection, and affiliate fields.【F:admin/views/tournaments.php†L44-L303】【F:admin/views/tournaments.php†L329-L420】
- **Frontend controller hook coverage** — **Gap**. `BHG_Tournaments_Controller::init()` is empty, so tournament logic is not wired into WordPress hooks; implement needed behaviors in `includes/class-bhg-tournaments-controller.php`.【F:includes/class-bhg-tournaments-controller.php†L18-L26】

## Users (Admin)
- **Search, sort, and 30-per-page pagination** — **Met**. The custom `WP_List_Table` implements search, sortable columns, and a fixed 30 rows per page with pagination arguments applied.【F:admin/class-bhg-users-table.php†L21-L256】

## Ads / Translations / Tools
- **Menus wired** — **Met**. Ads, translations, and tools submenus are registered.【F:admin/class-bhg-admin.php†L68-L88】
- **Ads actions + placement “none”** — **Met**. The Advertising screen offers bulk delete, per-row Edit/Delete, and a placement selector that includes **None** for shortcode-only usage.【F:admin/views/advertising.php†L37-L219】

## Shortcodes & Frontpage Lists
- **Core shortcodes (leaderboard, tournaments, prizes, jackpot, list views) registered** — **Met**.【F:includes/class-bhg-shortcodes.php†L72-L104】
- **Hunts shortcode details column** — **Met**. Details column outputs context-aware Guess Now / Show Results links plus guessing-disabled text.【F:includes/class-bhg-shortcodes.php†L4292-L4338】
- **Leaderboard heading/prize box + rounded averages + capitalized usernames** — **Met**. Leaderboard renderer adds selected tournament/hunt headings, injects prize markup, capitalizes usernames, and rounds averages to whole numbers.【F:includes/class-bhg-shortcodes.php†L4896-L4975】
- **Remaining Nov 13 leaderboard UI tweaks (remove bonushunt filter, affiliate column lights, filter toggles, prize box placement) and list shortcode mobile styles** — **Not Verified**. Validate in `includes/class-bhg-shortcodes.php` and `assets/css/bhg-shortcodes.css`.

## Tournament Adjustments (Nov 13)
- **Closing countdown banner** — **Met**. Active tournaments show “This tournament will close in X days” above the table.【F:includes/class-bhg-shortcodes.php†L5229-L5253】
- **Position header/Times Won labels & sorting** — **Met**. Tournament tables render sortable Position and Times Won headers; confirm pagination setting exposure in settings UI if required.【F:includes/class-bhg-shortcodes.php†L5342-L5416】
- **Last Win calculation + global rows-per-page setting** — **Not Verified**. Check tournament query logic and settings panel to ensure last-win source and pagination control match customer request.

## Prizes & Prize Shortcodes
- **Prize CRUD, CSS settings, dual regular/premium sets, and image sizes (small/medium/big)** — **Partial**. Prizes include regular/premium handling and register a 1200×800 big size, but admin UI lacks explicit size hints, link/category management, tabbed regular vs premium carousel, and click-behavior controls.【F:bonus-hunt-guesser.php†L139-L149】【F:includes/class-bhg-prizes.php†L1025-L1080】 Add these options in `admin/class-bhg-prizes-controller.php` and related templates.
- **Prize summary list + carousel/grid controls (visible count, total loaded, auto-scroll, hide title/category/description, responsive sizing)** — **Gap**. Shortcode rendering does not expose these toggles; extend `includes/class-bhg-shortcodes.php` and prize templates accordingly.

## Jackpot Module
- **Jackpot CRUD/linkage** — **Met**. Jackpot manager supports creation, linking, and balance updates.【F:includes/class-bhg-jackpots.php†L12-L137】
- **Ticker/winner shortcode options** — **Partial**. Shortcodes exist but need QA to ensure ticker modes and latest-winner filters match the spec (`includes/class-bhg-shortcodes.php`).

## Settings, Login, Currency, Limits
- **Currency helper, guess limits, win limits, pagination controls, styles panel** — **Met/Partial**. Settings page wires these options, but verify labels match customer wording and that rows-per-page setting drives tournament/leaderboard pagination.【F:bonus-hunt-guesser.php†L668-L878】
- **Smart login redirect** — **Partial**. Redirect helper exists; confirm menu role-based visibility is implemented (`includes/class-bhg-login-redirect.php`, `includes/class-bhg-front-menus.php`).
- **Notifications with BCC toggle** — **Partial**. Notification code present; confirm admin tab exposes enable/disable and BCC fields with validation (`includes/notifications.php`).

## Database & Migrations
- **Schema creation with guessing_enabled, participants_mode, affiliate linkage, and hunt↔tournament junction** — **Partial**. Migrator scaffolding exists but needs a pass to ensure all required columns/tables are version-gated for upgrades.【F:bonus-hunt-guesser.php†L200-L234】【F:includes/class-bhg-db.php†L93-L216】

## Remaining Frontend Polish
- **Table header links forced to white** — **Met**. Shared shortcode stylesheet sets header link color to white across tables.【F:assets/css/bhg-shortcodes.css†L520-L539】
- **Mobile responsiveness across shortcodes** — **Not Verified**. Requires CSS/markup QA on common viewports (`assets/css/bhg-shortcodes.css`).
