# Bonus Hunt Guesser – Development Checklist

Use this checklist to plan, implement, and validate version **8.0.14**. Every task references the primary plugin areas/files to touch so the development team can align changes with the codebase layout. Mark each checkbox when the requirement has been satisfied and verified against customer expectations and WordPress coding standards.

---

## 0. Plugin Header & Compatibility

- [ ] Refresh plugin header metadata to match the proposed template (title, description, version, requirements, etc.). *(File: `bonus-hunt-guesser.php`)*
- [ ] Confirm compatibility baseline: PHP 7.4, WordPress 6.3.5, MySQL 5.5.5. *(Files: `composer.json`, `README.md`, `readme.txt`)*

## 1. Prizes Module (`bhg-prizes`)

- [ ] Add "Prizes" admin menu entry with list/table view. *(Files: `admin/class-bhg-admin-menu.php`, `admin/views/prizes/list.php`)*
- [ ] Implement CRUD for prizes with fields: title, description, category (`cash money`, `casino money`, `coupons`, `merchandise`, `various`), image (small/medium/big), CSS panel (border, border color, padding, margin, background color), active toggle. *(Files: `includes/class-bhg-prizes.php`, `admin/views/prizes/edit.php`)*

## 2. Bonus Hunts ↔ Prizes Integration (`bhg-bonus-hunts`)

- [ ] Allow selecting one or multiple prizes when adding/editing a bonus hunt. *(Files: `admin/views/bonus-hunts/edit.php`, `includes/class-bhg-bonus-hunts.php`)*

## 3. Frontend Prize Display

- [ ] Render prizes for active hunts in grid and carousel layouts with navigation controls. *(Files: `includes/shortcodes/class-bhg-shortcode-prizes.php`, `assets/css/frontend-prizes.css`, `assets/js/frontend-prizes.js`)*

## 4. Prize Shortcode

- [ ] Create shortcode to show available prizes with filters `category`, `design` (grid/carousel), `size` (small/medium/big), `active` (yes/no). *(Files: `includes/shortcodes/class-bhg-shortcode-prizes.php`)*

## 5. User Shortcodes & Visibility Controls

- [ ] Deliver user-facing shortcodes: `my_bonushunts`, `my_tournaments`, `my_prizes`, `my_rankings`. *(Files: `includes/shortcodes/`)*
- [ ] Add admin toggles to hide/show each shortcode section. *(Files: `admin/class-bhg-settings.php`, `admin/views/settings/shortcodes.php`)*

## 6. CSS/Color Control Panel

- [ ] Provide styling controls for title block, headings (`h2`, `h3`), description text, and general fields (font sizes, colors, padding, margins, border radius). *(Files: `admin/views/settings/design.php`, `assets/css/frontend-customizer.css`)*

## 7. Shortcodes Reference (`bhg-shortcodes`)

- [ ] Build admin Info & Help screen listing all shortcodes and options. *(Files: `admin/views/shortcodes/info.php`)*

## 8. Notifications Center (`bhg-notifications`)

### 8.1 Winner Notifications

- [ ] Add editable fields (title, HTML description, BCC, enable checkbox default off). *(Files: `admin/views/notifications/winners.php`, `includes/class-bhg-notifications.php`)*

### 8.2 Tournament Notifications

- [ ] Duplicate configuration for tournament announcements. *(Files: `admin/views/notifications/tournaments.php`)*

### 8.3 Bonus Hunt Notifications

- [ ] Provide same notification options for new hunts. *(Files: `admin/views/notifications/bonus-hunts.php`)*

## 9. Tournament Management (`bhg-tournaments`)

- [ ] Attach prizes to tournaments in admin and frontend detail views. *(Files: `admin/views/tournaments/edit.php`, `templates/tournament/single.php`)*
- [ ] Add affiliate website field and show/hide checkbox (default show). *(Files: `admin/views/tournaments/edit.php`, `includes/class-bhg-tournaments.php`)*

## 10. Tournament Ranking System

- [ ] Implement editable point scale (default `25,15,10,5,4,3,2,1`) applicable to active/closed/all hunts. *(Files: `admin/views/tournaments/settings-ranking.php`, `includes/class-bhg-ranking.php`)*
- [ ] Base tournament results on configured winners for linked bonus hunts; highlight winners with emphasis for top 3. *(Files: `includes/class-bhg-ranking.php`, `templates/tournament/results.php`)*

## 11. Core Bonus Hunt Functionality (`bhg-bonus-hunts`)

- [ ] Admin creation fields: title, starting balance, number of bonuses, prizes text. *(Files: `admin/views/bonus-hunts/edit.php`)*
- [ ] Logged-in users submit guesses (0–100,000); guesses stored and validated. *(Files: `includes/class-bhg-guesses.php`, `templates/bonus-hunt/guess-form.php`)*
- [ ] Leaderboard shows position, username, guess, and difference vs. final result. *(Files: `templates/bonus-hunt/leaderboard.php`)*
- [ ] Support configurable number of winners per hunt (1st–25th) and display all winners in admin dashboard. *(Files: `admin/views/dashboard/latest-hunts.php`, `includes/class-bhg-dashboard.php`)*
- [ ] Rename admin submenu `bonushunt` → `dashboard` and show "Latest Hunts" table (title, winners with guess + difference, start balance, final balance, closed at). *(Files: `admin/class-bhg-admin-menu.php`, `admin/views/dashboard/index.php`)*
- [ ] Provide "Results" action button per finished hunt listing all guesses ranked best→worst with winner highlighting. *(Files: `admin/views/bonus-hunts/results.php`)*
- [ ] Display participants list when editing a hunt; allow removing guesses; usernames clickable to open profile edit. *(Files: `admin/views/bonus-hunts/edit.php`, `admin/class-bhg-users.php`)*
- [ ] Add final balance column in admin list (show `-` while open). *(Files: `admin/views/bonus-hunts/list.php`)*

## 12. User Profiles & Enhancements

- [ ] Manage user profile fields (real name, username, email, affiliate status). *(Files: `admin/views/users/edit.php`, `includes/class-bhg-user-meta.php`)*
- [ ] Integrate Nextend Social Login (Google/Twitch/Kick); ensure hooks for compatibility. *(Files: `includes/integrations/class-bhg-nextend.php`)*
- [ ] Allow users to edit guesses before hunt closes. *(Files: `templates/bonus-hunt/guess-form.php`, `includes/class-bhg-guesses.php`)*
- [ ] Leaderboard affiliate indicator: green for affiliates, red otherwise. *(Files: `assets/css/frontend-leaderboard.css`, `templates/bonus-hunt/leaderboard.php`)*
- [ ] Leaderboard sortable + paginated (position, username, guess). *(Files: `assets/js/frontend-leaderboard.js`, `templates/bonus-hunt/leaderboard.php`)*
- [ ] `bhg-users` admin: add search, sort, pagination (30 per page). *(Files: `admin/views/users/list.php`)*

## 13. Tournaments & Leaderboards

- [ ] Support time-based tournaments (weekly, monthly, quarterly, yearly, all-time). *(Files: `includes/class-bhg-tournaments.php`)*
- [ ] Add missing title and description fields; ensure edit form saves correctly; remove redundant period field. *(Files: `admin/views/tournaments/edit.php`)*
- [ ] Provide leaderboard filters and sortable columns (position, username, wins) plus historical views. *(Files: `templates/tournament/leaderboard.php`, `assets/js/frontend-tournaments.js`)*

## 14. Frontend Leaderboard Enhancements

- [ ] Add tabs for best guessers (Overall, Monthly, Yearly, All-Time). *(Files: `templates/bonus-hunt/leaderboard-tabs.php`, `assets/js/frontend-leaderboard.js`)*
- [ ] Add tabs for viewing historical bonus hunt leaderboards. *(Files: `templates/bonus-hunt/history-tabs.php`)*

## 15. User Experience Improvements

- [ ] Implement smart login redirect back to originating page. *(Files: `includes/class-bhg-auth.php`)*
- [ ] Configure three WordPress menus (Admin/Moderator, Logged-in, Guest) with styling to match theme. *(Files: `admin/views/settings/menus.php`, `assets/css/frontend-navigation.css`)*
- [ ] Provide "Translations" admin tab for managing plugin text strings. *(Files: `admin/views/translations/index.php`, `includes/class-bhg-translations.php`)*

## 16. Affiliate Enhancements

- [ ] Manage multiple affiliate websites in admin (add/edit/delete). *(Files: `admin/views/affiliates/list.php`, `includes/class-bhg-affiliates.php`)*
- [ ] Select affiliate site during bonus hunt creation; show per-user assignments in profile (Affiliate Website 1, 2, 3…). *(Files: `admin/views/bonus-hunts/edit.php`, `admin/views/users/edit.php`)*
- [ ] Reflect affiliate data per hunt on frontend (indicator + ad targeting). *(Files: `templates/bonus-hunt/leaderboard.php`, `includes/class-bhg-ads.php`)*

## 17. Notifications & Communication

- [ ] Calculate winners based on closest guess to final balance. *(Files: `includes/class-bhg-winners.php`)*
- [ ] Trigger result and winner emails respecting enable/disable flags and BCCs. *(Files: `includes/class-bhg-notifications.php`)*

## 18. Advertising Module (`bhg-ads`)

- [ ] Admin can add ads with text, optional link, placement, and visibility controls (by login + affiliate status). *(Files: `admin/views/ads/edit.php`, `includes/class-bhg-ads.php`)*
- [ ] Table includes Actions column (edit/remove). *(Files: `admin/views/ads/list.php`)*
- [ ] Add `none` placement for shortcode-only ads. *(Files: `includes/class-bhg-ads.php`)*

## 19. Tools & Translations Screens

- [ ] Populate `bhg-translations` and `bhg-tools` admin screens with management interfaces instead of blank placeholders. *(Files: `admin/views/translations/index.php`, `admin/views/tools/index.php`)*

## 20. Quality, Testing & Standards

- [ ] Apply WordPress PHPCS standards (`WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`). *(Files: entire codebase)*
- [ ] Optimize performance and resolve outstanding bugs uncovered during QA. *(Files: as needed)*
- [ ] Add border styling to Bonus Hunt admin input fields. *(Files: `assets/css/admin-bonus-hunt.css`)*

---

**How to use this checklist**

1. Assign an owner to each unchecked item and keep status updates within sprint notes or GitHub issues.
2. Reference the suggested files when planning pull requests to keep modifications organized and reviewable.
3. Update the checklist alongside development to ensure all customer-mandated features ship in 8.0.14.
