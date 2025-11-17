# Customer Requirements Checklist (Bonus Hunt Guesser)

Statuses: **Met** (verified in code), **Partial** (implemented but missing options/QA), **Gap** (not present), **Not Verified** (needs functional QA).

## Runtime & Versioning
- **Plugin header declares PHP 7.4 / WP 6.3.5 minimum** — **Met**. Evidence: plugin header requires PHP 7.4 and WordPress 6.3.5.【F:bonus-hunt-guesser.php†L3-L12】 _Adjust version field to customer target 8.0.18 in `bonus-hunt-guesser.php` to close the gap._
- **Version bumped to 8.0.18** — **Gap**. Header shows 8.0.16.【F:bonus-hunt-guesser.php†L3-L12】 _Update `bonus-hunt-guesser.php` and release docs._

## Admin Dashboard & Navigation
- **Top-level menu label + Dashboard submenu present** — **Met**. Dashboard registered under `bhg` menu and submenu keeps the “Dashboard” label.【F:admin/class-bhg-admin.php†L53-L98】 _No change needed unless labels differ from customer copy._
- **Dashboard shows “Latest Hunts” with winners, start/final balance, closed at (latest 3)** — **Met**. Template outputs Bonushunt title, all winners (multi-row), start/final balances, and closed timestamp with fallbacks.【F:admin/views/dashboard.php†L111-L205】 _If any column text needs renaming, edit `admin/views/dashboard.php`._

## Bonus Hunts (List/Edit/Results)
- **Results button + multi-winner highlight** — **Partial**. Results view renders winners list and supports highlighting via template, but audit did not confirm per-requirement sorting/removal UX or admin-side guess removal controls (check `admin/views` for results template). _Likely files: `admin/class-bhg-bonus-hunts-controller.php`, `admin/views/bonus-hunts/*.php`._
- **Final balance column in admin list** — **Not Verified**. Review `admin/class-bhg-bonus-hunts-list-table.php` for column definitions.
- **Editable winners count per hunt** — **Not Verified**. Confirm field presence in hunt edit form (`admin/class-bhg-bonus-hunts-controller.php`).

## Tournaments
- **Controller exists but logic empty** — **Gap**. `BHG_Tournaments_Controller::init()` is empty and does not expose title/description fields, participants mode, or edit handling requested by customer.【F:includes/class-bhg-tournaments-controller.php†L18-L26】 _Implement required fields and saving in `includes/class-bhg-tournaments-controller.php` plus admin views._

## Users (Admin)
- **Custom table class present** — **Partial**. `class-bhg-users-table.php` provides table mechanics, but search/sort/pagination at 30 per page need QA. _Review `admin/class-bhg-users-table.php` to confirm search args and per-page sizing._

## Ads / Translations / Tools
- **Menus wired** — **Met**. Submenus for ads/translations/tools exist.【F:admin/class-bhg-admin.php†L53-L88】
- **Ads actions + placement “none”** — **Not Verified**. Inspect `admin/class-bhg-admin.php` handlers and ads templates to confirm edit/delete buttons and placement option.

## Shortcodes & Frontpage Lists
- **Core and list shortcodes registered** — **Met**. Includes leaderboard, tournaments, prizes, jackpot, and text list shortcodes (`latest-winners-list`, `leaderboard-list`, `tournament-list`, `bonushunt-list`).【F:includes/class-bhg-shortcodes.php†L72-L104】
- **Leaderboard/tournament/bonushunt list feature set (filters, pagination, mobile styling)** — **Not Verified**. Rendering templates need review to confirm column headers, filters, and mobile responsiveness. _Likely files: `includes/class-bhg-shortcodes.php`, `assets/css/public.css`, template partials under `includes/templates/`._

## Leaderboard Adjustments (Customer Nov 13 list)
- **Times-won timeline logic, position sort, affiliate light column, rounded averages, username capitalization, prize box, header renames, filter toggles, remove bonushunt dropdown** — **Gap/Not Verified**. Current shortcode registration exists but code scan didn’t surface these UI/logic tweaks. _Implement or validate in leaderboard shortcode renderer (`includes/class-bhg-shortcodes.php` and related templates/scripts)._ 

## Tournament Adjustments (Customer Nov 13 list)
- **Number of winners, closing countdown box, header renames, position sorting, last-win calculation, pagination setting** — **Gap/Not Verified**. Tournament controller lacks active logic.【F:includes/class-bhg-tournaments-controller.php†L18-L26】 _Add fields and front-end rendering in tournament shortcode templates._

## Prizes & Prize Shortcodes
- **Prize CRUD and carousel/grid shortcode base** — **Partial**. Prizes menu is registered.【F:admin/class-bhg-admin.php†L68-L71】 Big-image upload, category links, dual prize sets (regular/premium), click behaviors, tabbed carousel, responsive sizing, and summary list toggles are not confirmed. _Review `admin/class-bhg-prizes-controller.php`, `includes/class-bhg-shortcodes.php`, and prize templates for gaps._

## Jackpot Module
- **Jackpot CRUD and retrieval** — **Met**. Dedicated class provides CRUD, linkage modes, and query helpers for jackpots and events.【F:includes/class-bhg-jackpots.php†L12-L137】
- **Frontend jackpot shortcodes and ticker behaviors** — **Partial**. Shortcodes are registered but ticker/winner display options need QA in render callbacks.【F:includes/class-bhg-shortcodes.php†L90-L104】

## Settings, Login, Currency, Limits
- **Currency (EUR/USD), shortcode rows-per-page, guess min/max, allow guess changes, win-limit settings, and global style controls** — **Met/Partial**. Settings handler processes currency, pagination, guess limits, hunt/tournament win limits, ads toggle, profile sections, and style blocks.【F:bonus-hunt-guesser.php†L668-L878】 QA needed to ensure UI matches customer wording.
- **Smart login redirect & menu customization** — **Partial**. Login redirect helper exists; menu-role separation not confirmed. _Check `includes/class-bhg-login-redirect.php` and theme/menu integration._
- **Notifications** — **Partial**. Winner notifications shortcode registered; verify admin tabs and BCC handling. _Files: `includes/class-bhg-shortcodes.php`, `bonus-hunt-guesser.php` mail helpers._

## Database & Migrations
- **Migrations scaffold present** — **Partial**. Installer handles schema via `dbDelta`, but coverage for all requested columns (guessing_enabled, participants_mode, affiliate_id, junction tables) needs confirmation.【F:bonus-hunt-guesser.php†L200-L234】 _Review installer/migrations to ensure fields exist._

## Remaining Frontend Polish
- **Table header link color white; hunts “Details” column** — **Not Verified**. Inspect `assets/css/public.css` and hunts template for color/column changes. _Likely files: `assets/css/public.css`, `includes/class-bhg-shortcodes.php`, hunts template._
- **Mobile responsiveness across shortcodes** — **Not Verified**. Requires CSS/markup review and device testing.

## Actions to Reach Full Compliance
- Align plugin version to 8.0.18 and audit header/readme files (`bonus-hunt-guesser.php`, docs).
- Implement tournament logic (fields, save/display) and leaderboard/tournament UI tweaks per customer notes.
- Extend prizes module for big image upload, categories/links, dual prize sets, tabbed carousel, responsive sizing, and summary toggles.
- Add pagination config & filters to shortcodes; verify timelines, search blocks, and header naming.
- Validate ads placement options and actions; confirm translation tab content.
- Confirm DB schema covers new columns/tables; add migrations where missing.
