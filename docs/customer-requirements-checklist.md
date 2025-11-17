# Customer Requirements Checklist (Bonus Hunt Guesser)

Statuses: **Met** (verified in code), **Partial** (implemented but missing options/QA), **Gap** (not present), **Not Verified** (needs functional QA).

## Runtime & Versioning
- **PHP 7.4 / WP 6.3.5 minimums enforced** — **Met**. Plugin header declares the required versions.【F:bonus-hunt-guesser.php†L3-L12】
- **Plugin version 8.0.18** — **Gap**. Header still shows **8.0.16**; bump header/constant to 8.0.18.【F:bonus-hunt-guesser.php†L6-L12】

## Admin Dashboard & Navigation
- **Menu label + Dashboard submenu** — **Met**. Bonus Hunt menu opens to the dashboard sub-item.【F:admin/class-bhg-admin.php†L53-L99】
- **Latest Hunts widget (3 hunts, all winners, start/final/closed)** — **Met**.【F:admin/views/dashboard.php†L111-L205】

## Bonus Hunts (List/Edit/Results)
- **List shows Final Balance (– if open) + guessing toggle** — **Met**.【F:admin/views/bonus-hunts.php†L168-L236】【F:admin/views/bonus-hunts.php†L264-L287】
- **Edit form supports winners count + participant list with remove/profile links** — **Met**.【F:admin/views/bonus-hunts-edit.php†L152-L251】
- **Results page ranks guesses and highlights winners** — **Partial**. Styling exists, but confirm/remove controls may be missing in `admin/views/bonus-hunts-results.php`.

## Leaderboards (Nov 13 set)
1) **Query returns full roster (not just 1 user)** — **Not Verified**. Aggregation uses `run_leaderboard_query()`; needs QA to confirm multi-user output.【F:includes/class-bhg-shortcodes.php†L4703-L4744】
2) **Avg Rank / Avg Tournament Pos rounded** — **Met** (0 decimals via `number_format_i18n`).【F:includes/class-bhg-shortcodes.php†L4970-L4973】
3) **Capitalize first letter of usernames** — **Met** (mbstring-aware fallback to `ucfirst`).【F:includes/class-bhg-shortcodes.php†L4949-L4958】
4) **Prize box when specific active tournament selected** — **Met** (renders tournament prizes block when status is active).【F:includes/class-bhg-shortcodes.php†L4605-L4633】
5) **Affiliate column + green/red lights + header placement** — **Met** (affiliate column added and rendered).【F:includes/class-bhg-shortcodes.php†L4927-L4935】【F:includes/class-bhg-shortcodes.php†L4974-L4978】
6) **Position column sortable** — **Met** (sortable header/link for Position).【F:includes/class-bhg-shortcodes.php†L4913-L4917】
7) **H2 tournament/hunt titles above table** — **Met** (renders selected tournament then hunt heading).【F:includes/class-bhg-shortcodes.php†L4896-L4905】
8) **Remove bonushunt dropdown filter** — **Gap**. Hunt filter plumbing remains; remove hunt dropdown for leaderboard use in `class-bhg-shortcodes.php`.
9) **Shortcode option to hide/show filters (timeline, tournament, affiliate site/status) & search block** — **Met** (attributes control filter/search visibility).【F:includes/class-bhg-shortcodes.php†L4865-L4894】
10) **Times Won respects timeline/tournament scope** — **Not Verified**. Needs QA against leaderboard query to ensure wins are filtered by current timeline/tournament.
11) **Header text “user” → “username”** — **Met** (label uses “Username”).【F:includes/class-bhg-shortcodes.php†L4916-L4919】

## Tournament Adjustments (Nov 13)
1) **Number of Winners field in add/edit** — **Met** (numeric field with default 3, max 25).【F:admin/views/tournaments.php†L386-L395】
2) **Active tournament countdown banner** — **Met** (“closes in X days” notice).【F:includes/class-bhg-shortcodes.php†L5229-L5253】
3) **Header text Wins → Times Won** — **Met**.【F:includes/class-bhg-shortcodes.php†L5342-L5348】
4) **Header text # → Position** — **Met**.【F:includes/class-bhg-shortcodes.php†L5329-L5337】
5) **Sortable Position column** — **Met** (sortable header for Position).【F:includes/class-bhg-shortcodes.php†L5329-L5337】
6) **Last Win shows last prize across linked hunts (not tournament win)** — **Not Verified**. Review tournament results query to confirm source of last win date.【F:includes/class-bhg-shortcodes.php†L5361-L5416】
7) **Pagination with global rows-per-page setting** — **Not Verified**. Confirm shortcode tables honor the backend rows-per-page option when totals exceed the limit.

## Prizes Adjustments
1) **Regular + Premium prize per winner (hunt/tournament)** — **Partial**. Premium and regular sets exist in models, but admin UI for per-place assignment and summary output need confirmation/expansion.【F:includes/class-bhg-prizes.php†L1025-L1080】
2) **Prize summary list under prize boxes (tournament + leaderboard) with toggle** — **Gap**. Add summary rendering/toggles to prize, tournament, and leaderboard shortcodes.【F:includes/class-bhg-shortcodes.php†L4605-L4633】
3) **Prize shortcode show/hide summary option** — **Gap**. No summary toggle exposed on `[bhg_prizes]`.
4) **Leaderboard/tournament shortcodes show/hide summary & prizes** — **Partial**. Prize block appears for tournaments; add explicit summary/prize visibility switches.
5) **Tabbed regular vs premium carousel** — **Gap**. Current rendering is single-section; needs tabbed UI with both prize sets.
6) **Big image upload (1200×800) + size labels in admin** — **Partial**. Size registered, but admin form lacks size hint text for small/medium/big.【F:bonus-hunt-guesser.php†L139-L149】
7) **Prize link + click behavior (same/new window/popup)** — **Gap**. No prize link field or click-behavior toggle in admin/frontend templates.
8) **Prize categories with optional link + show/hide link flag** — **Gap**. Category link visibility controls absent in prizes admin.
9) **Carousel/grid controls (visible count, total loaded, auto-scroll, hide title/category/description)** — **Gap**. Settings not exposed for `[bhg_prizes]` rendering.
10) **Responsive sizing (1=big, 2–3=medium, 4–5=small) & remove automatic “Prizes” heading** — **Not Verified**. Needs UI/CSS check and heading suppression confirmation.

## Frontpage/List Shortcodes (Nov 13 add-on)
- **latest-winners-list, leaderboard-list, tournament-list, bonushunt-list shortcodes registered** — **Met** (handlers present).【F:includes/class-bhg-shortcodes.php†L72-L97】
- **Visibility toggles + mobile friendliness for these list blocks** — **Not Verified**. Review renderer options and CSS responsiveness around the list sections.

## General Frontend Adjustments
- **Header links in tables forced to white** — **Met** (global table header link styles set to #fff).【F:assets/css/bhg-shortcodes.css†L512-L539】
- **Bonus hunt list adds Details column with Guess Now/Show Results** — **Met** (Details column with contextual links).【F:includes/class-bhg-shortcodes.php†L4200-L4278】【F:includes/class-bhg-shortcodes.php†L4288-L4319】
- **Mobile responsiveness across shortcodes** — **Not Verified**. Needs responsive QA for tables/carousels.

## Jackpot Feature
- **Admin CRUD + balance growth logic** — **Met**.【F:includes/class-bhg-jackpots.php†L12-L137】
- **Shortcodes (current/latest/ticker/winners) match requested options** — **Partial**. Handlers exist but need option-level QA for affiliate/date filters and ticker modes.【F:includes/class-bhg-shortcodes.php†L5647-L5894】

## Settings, Login, Currency, Limits
- **Currency helpers and EUR/USD setting** — **Met**.【F:bonus-hunt-guesser.php†L668-L713】
- **Rows-per-page, login redirect, menus, win limits, style panel** — **Partial**. Settings exposed, but verify front-end tables honor page-size/win-limit controls and role-based menus/redirects during QA.【F:bonus-hunt-guesser.php†L714-L878】【F:admin/views/settings.php†L101-L155】
- **Notifications tab with enable + BCC** — **Partial**. Email hooks exist; confirm UI exposes enable/BCC fields with validation in notifications module.【F:includes/notifications.php†L26-L192】

## Database & Migrations
- **Columns (guessing_enabled, participants_mode, affiliate_id) + hunt↔tournament junction** — **Partial**. Schema declarations exist; confirm dbDelta/upgrade paths apply changes idempotently.【F:bonus-hunt-guesser.php†L200-L234】【F:includes/class-bhg-db.php†L93-L216】

## Global UX & QA
- **Sorting/search/pagination on public tables** — **Met** via shared helpers; ensure rows-per-page ties to settings during QA.【F:includes/class-bhg-shortcodes.php†L4294-L4350】【F:includes/class-bhg-shortcodes.php†L4896-L4990】
- **Translation hooks** — **Partial**. Strings use `bhg_t()`/text domain, but translation admin tab content should be validated.
- **E2E acceptance (hunts → winners → rankings, currency switch, winner limits)** — **Not Verified**. Full flow testing still required.
