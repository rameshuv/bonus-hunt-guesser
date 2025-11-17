# Customer Requirements Checklist (Bonus Hunt Guesser)

Statuses: **Met** (verified in code), **Partial** (implemented but missing options/QA), **Gap** (not present), **Not Verified** (needs functional QA).

## Runtime & Versioning
- **Plugin header enforces PHP 7.4 / WP 6.3.5 minimums** — **Met**.【F:bonus-hunt-guesser.php†L3-L12】
- **Version set to 8.0.18** — **Gap**. Header and constant remain **8.0.16**; bump `bonus-hunt-guesser.php` to 8.0.18.【F:bonus-hunt-guesser.php†L6-L12】【F:bonus-hunt-guesser.php†L154-L158】

## Admin Dashboard & Navigation
- **Menu label + Dashboard submenu** — **Met**. Dashboard is the first submenu under the Bonus Hunt menu.【F:admin/class-bhg-admin.php†L53-L99】
- **“Latest Hunts” shows last 3 hunts with all winners/start/final/closed-at** — **Met**.【F:admin/views/dashboard.php†L111-L205】

## Bonus Hunts (List/Edit/Results)
- **List shows Final Balance + guessing toggle** — **Met**.【F:admin/views/bonus-hunts.php†L168-L236】【F:admin/views/bonus-hunts.php†L264-L287】
- **Edit: winners count + participant list with remove/profile links** — **Met**.【F:admin/views/bonus-hunts-edit.php†L152-L251】
- **Results: ranked table with highlighted winners** — **Partial**. Winners are styled, but confirm/remove controls on the results screen; adjust `admin/views/bonus-hunts-results.php` if missing.

## Leaderboards (Nov 13 set)
- **Headings (tournament/hunt), prize box, rounded averages, capitalized usernames** — **Met**.【F:includes/class-bhg-shortcodes.php†L4896-L4975】
- **Affiliate column/lights + filter toggles (timeline/tournament/affiliate site/status) + search control** — **Met** in UI; ensure shortcode attributes expose the requested hide/show switches.【F:includes/class-bhg-shortcodes.php†L4865-L4894】
- **Bonushunt filter removed for leaderboard, position sorting, Times Won scope, query returns full roster** — **Not Verified**. QA leaderboard query section to confirm more than one entrant is returned and unwanted filters are hidden (`includes/class-bhg-shortcodes.php`).

## Tournament Adjustments (Nov 13)
- **Title/description fields + quarterly/alltime types + participants mode + actions (results/close/delete)** — **Met**.【F:admin/views/tournaments.php†L44-L303】【F:admin/views/tournaments.php†L329-L420】
- **Countdown (“This tournament will close in X days”), Position/Times Won headers sortable** — **Met**.【F:includes/class-bhg-shortcodes.php†L5229-L5253】【F:includes/class-bhg-shortcodes.php†L5342-L5416】
- **Last Win column source + global rows-per-page pagination** — **Not Verified**. Validate tournament query logic and ensure settings rows-per-page applies to the table.
- **Controller hook wiring** — **Gap**. `BHG_Tournaments_Controller::init()` is empty; wire required hooks in `includes/class-bhg-tournaments-controller.php`.【F:includes/class-bhg-tournaments-controller.php†L18-L26】

## Frontpage/List Shortcodes (Nov 13 add-on)
- **latest-winners-list, leaderboard-list, tournament-list, bonushunt-list shortcodes registered** — **Met**.【F:includes/class-bhg-shortcodes.php†L72-L97】
- **Visibility toggles & mobile styling for these list blocks** — **Not Verified**. Review list renderers around the shortcode handlers for option coverage and responsive CSS.

## Prizes
- **Dual regular/premium sets + big image size (1200×800) registered** — **Partial**. Image size exists and prize models handle premium sets, but admin UI still lacks size hints, prize-link field, category/link visibility toggles, click-behavior options, and carousel/grid controls (visible count, total loaded, auto-scroll, hide title/category/description, responsive sizing, tabbed regular/premium view).【F:bonus-hunt-guesser.php†L139-L149】【F:includes/class-bhg-prizes.php†L1025-L1080】 Update `admin/class-bhg-prizes-controller.php` and shortcode templates.
- **Prize summary list under tournament/leaderboard prize boxes + shortcode opt-in/out** — **Gap**. Add summary rendering and visibility toggles to prize, leaderboard, and tournament shortcodes in `includes/class-bhg-shortcodes.php`.
- **Heading removal above grid/carousel** — **Not Verified**. Confirm the “Prizes” label is suppressed in prize templates.

## Jackpot Module
- **Jackpot CRUD/linkage + balance growth** — **Met**.【F:includes/class-bhg-jackpots.php†L12-L137】
- **Shortcodes (current/latest/ticker/winners) fully match options** — **Partial**. Handlers exist but need option-level QA in `includes/class-bhg-shortcodes.php` to ensure affiliate/date filters and ticker modes behave as requested.

## Settings, Login, Currency, Limits
- **Currency helper, guess limits, win limits, shortcode rows-per-page, style panel** — **Met/Partial**. Settings page exposes these controls, but confirm wording matches customer settings and that pagination and win-limit settings drive the front-end tables during QA.【F:bonus-hunt-guesser.php†L668-L878】【F:admin/views/settings.php†L101-L155】
- **Smart redirect after login + menu role variants** — **Partial**. Redirect helper exists; verify role-based menus in `includes/class-bhg-front-menus.php` and related templates.
- **Notifications tab with enable/disable + BCC** — **Partial**. Email hooks exist; confirm admin UI shows enable and BCC fields with validation in `includes/notifications.php`.

## Database & Migrations
- **Schema includes guessing_enabled, participants_mode, affiliate_id, hunt↔tournament junction** — **Partial**. `bhg_create_tables()` and `BHG_DB::create_tables()` scaffold these, but double-check version gating and dbDelta statements for upgrades.【F:bonus-hunt-guesser.php†L200-L234】【F:includes/class-bhg-db.php†L93-L216】

## Global UX & Styling
- **Table header links forced to white** — **Met**.【F:assets/css/bhg-shortcodes.css†L512-L539】
- **Sorting/search/pagination applied to public tables** — **Met** for hunts/tournaments/leaderboards via shared helpers; ensure page-size follows settings during QA.【F:includes/class-bhg-shortcodes.php†L4294-L4350】【F:includes/class-bhg-shortcodes.php†L4896-L4975】
- **Mobile responsiveness** — **Not Verified**. Run responsive checks for shortcode tables and list blocks (`assets/css/bhg-shortcodes.css`).
