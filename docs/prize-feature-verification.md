# Bonus Hunt Guesser Requirement Verification

This document maps each agreed customer requirement to the implementing code so reviewers can quickly confirm coverage without browsing the full codebase.

## Quick Reference by Requirement

| Requirement | Summary | Primary Implementation |
|-------------|---------|------------------------|
| 1. Prizes admin | Dedicated submenu, CRUD form (title, description, category, three image slots), CSS panel, active toggle | `admin/class-bhg-admin.php`, `admin/views/prizes.php`, `includes/class-bhg-prizes.php` |
| 2. Bonus hunt editor | Attach multiple prizes, control winners (1–25), review/removal of guesses, final balance column & results button | `admin/views/bonus-hunts.php`, `admin/views/bonus-hunts-results.php`, `admin/class-bhg-admin.php`, `includes/class-bhg-bonus-hunts-helpers.php` |
| 3. Frontend prizes | Active hunt cards plus standalone `[bhg_prizes]` shortcode with grid/carousel layouts and placeholder handling | `includes/class-bhg-shortcodes.php`, `assets/css/bhg-shortcodes.css` |
| 4. Prize shortcode | Attribute filters for category/design/size/active backed by alias normalization and rendering tests | `includes/class-bhg-shortcodes.php`, `tests/PrizesShortcodeNormalizationTest.php`, `tests/PrizesShortcodeRenderingTest.php` |
| 5. User shortcodes | `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, `[my_rankings]` with admin toggles | `includes/class-bhg-profile-shortcodes.php`, `admin/views/settings.php` |
| 6. CSS/color panel | Design settings for title blocks, headings, description, and standard text with sanitizers | `admin/views/settings.php`, `includes/helpers.php` |
| 7. Shortcodes info | “Info & Help” page documenting every shortcode and option | `admin/views/shortcodes.php` |
| 8. Notifications | Configurable Winner/Tournament/Bonushunt email blocks (subject, HTML body, BCC, enable toggle) | `admin/views/notifications.php`, `includes/helpers.php` |
| 9. Tournaments | Admin form (title, description, type incl. quarterly/alltime, affiliate visibility, prizes) & frontend display | `admin/views/tournaments.php`, `includes/class-bhg-shortcodes.php`, `includes/class-bhg-models.php` |
| 10. Tournament ranking | Editable point system driving standings and winner highlighting backend/frontend | `includes/helpers.php`, `includes/class-bhg-models.php`, `admin/views/bonus-hunts-results.php`, `includes/class-bhg-shortcodes.php` |

---

## 1. Prizes Admin (bhg-prizes)
- **Menu entry**: The main `Bonus Hunt` admin menu registers a dedicated **Prizes** submenu item so administrators can reach the prize list directly. 【F:admin/class-bhg-admin.php†L78-L118】
- **CRUD capabilities**: The prizes admin view renders the list table with Edit/Delete actions and the creation form with inputs for title, description, category, image IDs for three sizes (small, medium, large), CSS panel fields (border, border color, padding, margin, background color), and an Active toggle. 【F:admin/views/prizes.php†L1-L210】【F:admin/views/prizes.php†L214-L290】
- **Data layer**: `BHG_Prizes` provides category validation, CSS sanitization, CRUD helpers, and image retrieval for the three stored sizes. 【F:includes/class-bhg-prizes.php†L15-L210】

## 2. Bonus Hunt Editor (bhg-bonus-hunts)
- **Prize selection**: The hunt editor exposes a multi-select control that pulls from saved prizes so admins can associate one or more prizes with each hunt. 【F:admin/views/bonus-hunts.php†L120-L212】
- **Participants overview**: Editing a hunt shows all submitted guesses with sortable columns, pagination, profile links, and removal buttons that call the admin handler. 【F:admin/views/bonus-hunts.php†L320-L512】【F:admin/class-bhg-admin.php†L420-L548】
- **Final balance/results access**: The hunt list table shows the Final Balance column (or `-` while open) and adds a **Results** button that links to the ranked guesses view with highlighted winners. 【F:admin/views/bonus-hunts.php†L40-L118】【F:admin/views/bonus-hunts-results.php†L1-L180】
- **Winner count configuration**: Admins can define 1–25 winners per hunt, and the setting is enforced when closing hunts and generating rankings. 【F:admin/views/bonus-hunts.php†L214-L252】【F:includes/class-bhg-bonus-hunts-helpers.php†L120-L228】

## 3. Frontend Prize Display
- **Active hunt prizes**: The active hunt shortcode renders linked prizes in reusable card templates that support grid or carousel layouts, navigation controls, and winner highlighting across hunt tables. 【F:includes/class-bhg-shortcodes.php†L1740-L2112】【F:assets/css/bhg-shortcodes.css†L1-L220】
- **Standalone prize shortcode**: `[bhg_prizes]` accepts `category`, `design` (grid, carousel, plus aliases like list/caroussel/horizontal), `size` (small/medium/big with common synonyms), and `active` filters; it reuses the same rendering pipeline and placeholder handling. 【F:includes/class-bhg-shortcodes.php†L2676-L2854】

## 4. User Profile Shortcodes
- **Profile sections**: Logged-in users can embed `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, and `[my_rankings]`, each producing tables with ranking data, prize summaries, and winner highlighting. 【F:includes/class-bhg-profile-shortcodes.php†L1-L410】
- **Admin visibility controls**: The settings view exposes toggles so administrators can hide/show each profile shortcode section in the frontend. 【F:admin/views/settings.php†L150-L242】

## 5. CSS / Color Panel
- **Design controls**: The settings page offers inputs for title block background, border radius, padding, margin, and typography options for `h2`, `h3`, description, and standard text. Saved values are sanitized and transformed into inline CSS for frontend usage. 【F:admin/views/settings.php†L244-L332】【F:includes/helpers.php†L40-L236】

## 6. Shortcodes Help (bhg-shortcodes)
- The Shortcodes admin page lists every available shortcode with option tables (block title: “Info & Help”), covering prizes, hunts, tournaments, user dashboards, and filters. 【F:admin/views/shortcodes.php†L1-L200】

## 7. Notifications (bhg-notifications)
- The Notifications admin tab provides configurable sections for Winner, Tournament, and Bonus Hunt emails with subject, HTML body, BCC field, and enable checkbox (disabled by default). 【F:admin/views/notifications.php†L1-L232】【F:includes/helpers.php†L420-L548】

## 8. Tournaments
- **Admin form**: Tournament creation/editing includes title, description, type selector (weekly, monthly, quarterly, yearly, alltime), hunt linkage mode, affiliate website dropdown plus visibility toggle, and prize editor. 【F:admin/views/tournaments.php†L1-L248】
- **Frontend output**: Tournament detail shortcodes render prizes, affiliate links (respecting visibility flag), and standings aggregated from winner points. 【F:includes/class-bhg-shortcodes.php†L1200-L1738】

## 9. Tournament Ranking
- **Point system**: Default points (1st–8th) are stored in options, editable in the admin, and applied when hunts are closed. Aggregation recalculates tournaments based on selected hunt winners and highlights the top three in both admin and frontend tables. 【F:includes/helpers.php†L238-L418】【F:includes/class-bhg-models.php†L350-L612】【F:admin/views/bonus-hunts-results.php†L90-L170】

## 10. Additional Back-End Improvements
- **Dashboard rename & widget**: The primary submenu label is “Dashboard,” and the widget titled “Latest Hunts” lists the three most recent hunts with all winners, balances, and timestamps. 【F:admin/class-bhg-admin.php†L64-L118】【F:admin/views/dashboard.php†L32-L150】
- **Users tooling**: The Users admin view supports searching (with nonce), sortable username/name/email columns, in-place affiliate toggles, and 30-per-page pagination. 【F:admin/views/users.php†L9-L148】
- **Ads administration**: Advertising entries expose bulk delete, edit, and remove controls with a placement dropdown that includes “none” for shortcode-only spots and visibility filters for guests, logged-in users, affiliates, and non-affiliates. 【F:admin/views/advertising.php†L37-L200】
- **Translations & Tools**: Both admin pages load search, pagination, and grouped translation data plus import/export utilities, ensuring the tabs no longer appear empty. 【F:admin/views/translations.php†L1-L200】【F:admin/views/tools.php†L1-L190】

## 11. Guess Submission & Leaderboards
- **Guess form & editing**: Logged-in users can submit or edit guesses while hunts are open, with range validation (0–100,000 by default), AJAX support, and redirect handling. 【F:bonus-hunt-guesser.php†L590-L715】【F:includes/class-bhg-shortcodes.php†L637-L780】
- **Leaderboard tables**: Active hunt leaderboards provide sortable/paginated listings, affiliate indicators, and winner highlighting. 【F:includes/class-bhg-shortcodes.php†L420-L940】
- **Best guesser tabs**: The `[bhg_best_guessers]` shortcode renders overall/monthly/yearly/all-time tabs plus hunt history links. 【F:includes/class-bhg-shortcodes.php†L2936-L3007】
- **User history**: `[bhg_user_guesses]` and profile shortcodes list per-user guesses with sorting, final balances, and winner badges. 【F:includes/class-bhg-shortcodes.php†L985-L1400】【F:includes/class-bhg-profile-shortcodes.php†L430-L760】

## 12. Login & Social Integration
- **Smart redirect**: Core and Nextend Social Login flows validate `redirect_to` parameters or fall back to referers so users return to their original destination after logging in. 【F:includes/class-bhg-login-redirect.php†L21-L81】
- **Social profile capture**: When Nextend is active the plugin sanitizes provider data (Google, Twitch, Kick) and stores avatar/profile metadata on registration. 【F:includes/class-bhg-login-redirect.php†L24-L128】【F:includes/class-bhg-nextend-profile.php†L12-L40】

## 13. Menu Customization
- **Role-specific menus**: Three menu locations (Admin/Moderator, Logged-in, Guest) are registered and rendered via shortcodes or automatic selection. 【F:includes/class-bhg-front-menus.php†L20-L115】
- **Admin reminder**: Backend notices prompt administrators to assign the menus from Appearance → Menus, ensuring visibility controls are configured. 【F:includes/class-bhg-front-menus.php†L118-L131】

## 14. Affiliate Management & Indicators
- **Affiliate websites**: Administrators can add/edit/delete affiliate sites with nonce protection, and hunts/tournaments present dropdown selectors plus visibility toggles. 【F:admin/views/affiliate-websites.php†L40-L180】【F:admin/views/bonus-hunts.php†L370-L530】【F:admin/views/tournaments.php†L70-L248】
- **User tracking**: The Users view toggles affiliate status, and helpers expose per-site affiliation plus indicator dots for leaderboards and profiles. 【F:admin/views/users.php†L94-L148】【F:includes/helpers.php†L1226-L1408】
- **Frontend usage**: Shortcodes render affiliate badges next to guesses and respect hunt-specific affiliate associations. 【F:includes/class-bhg-shortcodes.php†L870-L1050】【F:includes/class-bhg-profile-shortcodes.php†L540-L760】

## 15. Advertising Module
- **Admin tooling**: Ads can be targeted by placement (`none`, `footer`, `bottom`, `sidebar`, `shortcode`), visibility (guests/logged-in/affiliates), and page slugs. 【F:admin/views/advertising.php†L117-L200】
- **Frontend delivery**: Ads respect enablement settings, visitor visibility, affiliate status, and placement hooks; shortcode rendering is also available. 【F:includes/class-bhg-ads.php†L22-L200】

## 16. Translation & Localization
- **Translation manager**: Administrators can search, paginate, and edit translation strings with nonce-protected forms and highlighted overrides. 【F:admin/views/translations.php†L26-L200】
- **String registry**: `bhg_t()` exposes all labels (menus, shortcodes, notifications) so the translations UI surfaces every customer-facing phrase. 【F:includes/helpers.php†L470-L1108】

## 17. Notifications & Email Templates
- **Configurable emails**: Winner, Tournament, and Bonus Hunt notifications include editable subject/body, BCC recipients, and enable toggles (disabled by default). 【F:admin/views/notifications.php†L1-L232】【F:includes/helpers.php†L420-L548】
- **Delivery hooks**: Notification helpers integrate with hunt/tournament lifecycle events so configured emails dispatch when hunts close or tournaments are created. 【F:includes/helpers.php†L548-L692】

## 18. Platform Compatibility & Bootstrapping
- **Version requirements**: Plugin headers declare PHP 7.4, WordPress 5.5.5, and MySQL 5.5.5 compatibility, aligning with customer constraints. 【F:bonus-hunt-guesser.php†L1-L16】
- **Database migrations**: Table creation and migration routines cover hunts, guesses, prizes, tournaments, affiliates, and ads with MySQL-safe schema definitions. 【F:bonus-hunt-guesser.php†L141-L210】【F:includes/class-bhg-db.php†L55-L340】

## Testing
- PHPUnit coverage guards prize normalization, CSS sanitization, shortcode rendering, and tournament recalculations to prevent regressions. 【F:tests/PrizesShortcodeNormalizationTest.php†L1-L210】【F:tests/PrizesCssSettingsTest.php†L1-L190】【F:tests/PrizesShortcodeRenderingTest.php†L1-L220】
