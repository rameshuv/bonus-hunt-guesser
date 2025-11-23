# Customer Requirements Checklist (Feb 2025)

Statuses: **Met** (implemented and visible in code), **Partial** (present but missing options/QA), **Gap** (not implemented), **Not Verified** (needs testing).

## Jackpot Adjustments
- `[bhg_jackpot_ticker]` supports `status` (active/closed/all) and `design` (fade/scroll) plus interval, scroll speed, separator, and padding options from general settings. **Met.**【F:includes/class-bhg-shortcodes.php†L6364-L6407】【F:admin/views/settings.php†L27-L136】
- Ticker output uses either fade (interval) or scroll (speed, separator, padding) with no forced margins beyond inline reset. **Met.**【F:includes/class-bhg-shortcodes.php†L6407-L6424】【F:assets/css/public.css†L335-L357】
- `[bhg_jackpot_winners]` offers hide/show toggles for title, amount, username, affiliate, date and optional `<strong>` wrapping per field. **Met.**【F:includes/class-bhg-shortcodes.php†L6498-L6612】
- Output order for jackpot winners matches username → amount → title → affiliate → date in both list and table layouts. **Met.**【F:includes/class-bhg-shortcodes.php†L6527-L6558】【F:includes/class-bhg-shortcodes.php†L6584-L6611】
- `[bhg_jackpot_latest]` shortcode no longer registered (removed as duplicate of winners). **Met.**【F:includes/class-bhg-shortcodes.php†L72-L105】
- Bonus hunt edit screen includes "Jackpot Increase" checkbox to control connected jackpots per hunt. **Met.**【F:admin/views/bonus-hunts-edit.php†L130-L151】

## Leaderboards Adjustments
- Leaderboard query renders multiple users with pagination and sorting; column headers include sortable Position/Username/Times Won/Avg Hunt Pos/Avg Tournament Pos. **Met.**【F:includes/class-bhg-shortcodes.php†L4921-L4993】
- Avg Rank / Avg Tournament Pos displayed as whole numbers (no decimals). **Met.**【F:includes/class-bhg-shortcodes.php†L5025-L5033】
- Usernames formatted via `format_username_label` to capitalize the first letter. **Met.**【F:includes/class-bhg-shortcodes.php†L5020-L5028】
- Prize box shown above table when a specific tournament is selected. **Met.**【F:includes/class-bhg-shortcodes.php†L4917-L4919】
- Affiliate column and status indicator included after Avg Tournament Pos. **Met.**【F:includes/class-bhg-shortcodes.php†L4933-L4942】【F:includes/class-bhg-shortcodes.php†L5012-L5018】
- Position column sortable via header link. **Met.**【F:includes/class-bhg-shortcodes.php†L4923-L4927】
- H2 headings render tournament/bonushunt titles above the leaderboard results. **Met.**【F:includes/class-bhg-shortcodes.php†L4906-L4915】
- Bonushunt filter still parsed in request/URL; customer asked to remove. **Gap.**【F:includes/class-bhg-shortcodes.php†L4965-L4969】
- Times Won calculation alignment with timeline/tournament scopes not validated. **Not Verified.**
- Dropdown hide/show controls for filters exist (`bhg_filters` param) covering timeline, tournament, affiliate site, affiliate status. **Met.**【F:includes/class-bhg-shortcodes.php†L4855-L4895】
- Search block rendering controlled by shortcode flag but not yet a hide/show setting per customer. **Gap.**【F:includes/class-bhg-shortcodes.php†L4897-L4902】

## Tournament Adjustments
- Add/edit tournament includes "Number of Winners" field (1–25). **Met.**【F:admin/views/tournaments.php†L360-L369】
- Active tournament banner displays "This tournament will close in X days" above table. **Met.**【F:includes/class-bhg-shortcodes.php†L5229-L5253】
- Column headers changed to Position / Times Won with sortable Position. **Met.**【F:includes/class-bhg-shortcodes.php†L5329-L5348】
- "Last Win" value expected to show last prize from linked hunts; data source not explicitly verified. **Not Verified.**
- Pagination exists, but global rows-per-page backend control not confirmed. **Not Verified.**【F:includes/class-bhg-shortcodes.php†L5349-L5378】

## Prizes Adjustments
- Dual prize sets for regular and premium winners selectable per bonus hunt. **Met.**【F:admin/views/bonus-hunts-edit.php†L130-L138】【F:includes/class-bhg-prizes.php†L1044-L1080】
- Frontend prize carousel renders but lacks tabbed regular/premium toggle and summary text list. **Gap.**【F:includes/class-bhg-shortcodes.php†L4596-L4634】
- Admin prize form lacks explicit size labels, product link field, category management with link toggles, image click behaviors, carousel controls (visible count/total/load/auto-scroll), and per-field hide/show toggles. **Gap.**【F:admin/views/prizes.php†L1-L200】
- Responsive image sizing by count (big/medium/small) not implemented. **Gap.**【F:assets/css/bhg-shortcodes.css†L1-L180】
- Automatic "Prizes" heading removal not yet applied. **Gap.**【F:includes/class-bhg-shortcodes.php†L4596-L4634】

## Frontpage List Shortcodes
- Shortcodes registered for latest winners list, leaderboard list, tournament list, bonushunt list with hide/show options. **Met.**【F:includes/class-bhg-shortcodes.php†L72-L105】【F:admin/views/shortcodes.php†L140-L240】
- Timeline/status filters for bonushunt/tournament list outputs include the requested presets; search-block hide/show control not exposed. **Gap.**【F:includes/class-bhg-shortcodes.php†L4076-L4195】【F:includes/class-bhg-shortcodes.php†L4320-L4457】
- Mobile styling not validated; needs responsive QA. **Not Verified.**

## General Frontend
- Table header links styled white across shortcode tables. **Met.**【F:assets/css/bhg-shortcodes.css†L512-L539】
- Bonus hunts list includes Details column with "Show Results" for closed and "Guess Now" for open hunts. **Met.**【F:includes/class-bhg-shortcodes.php†L4200-L4319】

## Jackpot Feature (Core)
- Admin CRUD, linkage filters, and hunt-close integration implemented in jackpot service. **Met (needs QA).**【F:includes/class-bhg-jackpots.php†L12-L520】【F:includes/class-bhg-models.php†L243-L355】
- Shortcodes `[bhg_jackpot_current]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` live; ticker interval/speed/padding settings exposed in General tab. **Met.**【F:includes/class-bhg-shortcodes.php†L6330-L6612】【F:admin/views/settings.php†L27-L136】
- Latest jackpot hit shortcode with filters is covered by `[bhg_jackpot_winners]` list/table output. **Met.**【F:includes/class-bhg-shortcodes.php†L6498-L6612】

## Winner Limits Per User
- Settings page exposes per-type max wins and rolling period inputs. **Met.**【F:admin/views/settings.php†L118-L155】
- Enforcement logic referenced in helpers but not recently QA’d. **Not Verified.**【F:includes/helpers.php†L1117-L1256】

## Database & Migrations
- Jackpot tables and hunt/tournament limit columns defined in DB migrations; deployment state not validated. **Not Verified.**【F:includes/class-bhg-db.php†L93-L344】

## Runtime / Docs / Standards
- Plugin header declares PHP 7.4, WP 6.3.5, version 8.0.18, and loads text domain. **Met.**【F:bonus-hunt-guesser.php†L3-L19】【F:bonus-hunt-guesser.php†L399-L419】
- Documentation includes shortcodes catalog entries for jackpot/list blocks; changelog lists jackpot rollout. **Met.**【F:admin/views/shortcodes.php†L276-L306】【F:CHANGELOG.md†L1-L20】
- PHPCS/automated QA not run in this review. **Not Verified.**
