# Bonus Hunt Guesser – Development Checklist

Use this checklist to track implementation status for version 8.0.14 based on the consolidated customer requirements.

## 0. Plugin Header

- [ ] Update plugin header metadata to match the proposed format (title, description, version, requirements, etc.).

## 1. Prizes (Admin: `bhg-prizes`)

- [ ] Add a "Prizes" menu item in the WordPress admin.
- [ ] Implement CRUD (create, read, update, delete) for prizes with the following fields:
  - [ ] Title
  - [ ] Description
  - [ ] Category selector (`cash money`, `casino money`, `coupons`, `merchandise`, `various`)
  - [ ] Image handling with three sizes (`small`, `medium`, `big`)
  - [ ] CSS customization panel (border, border color, padding, margin, background color)
  - [ ] Active toggle (yes/no)

## 2. Bonus Hunts ↔ Prizes Integration (`bhg-bonus-hunts`)

- [ ] Allow selecting one or more prizes when adding or editing a bonus hunt.

## 3. Frontend Prize Display

- [ ] Provide frontend display for prizes attached to active bonus hunts.
- [ ] Support grid list and horizontal carousel layouts with dots and/or navigation arrows.

## 4. Prize Shortcode

- [ ] Implement shortcode to display available prizes with filters:
  - [ ] `category=""`
  - [ ] `design=""` (grid list or carousel)
  - [ ] `size=""` (small, medium, big)
  - [ ] `active=""` (yes/no)

## 5. User Shortcodes & Admin Visibility Controls

- [ ] Add shortcodes for user dashboards:
  - [ ] `my_bonushunts`
  - [ ] `my_tournaments`
  - [ ] `my_prizes`
  - [ ] `my_rankings`
- [ ] Add admin settings to toggle visibility of each shortcode output.

## 6. CSS/Color Panel (Frontend Styling)

- [ ] Provide configurable styling options for:
  - [ ] Title block (background color, border radius, padding, margin)
  - [ ] `h2` (font size, font weight, color, padding, margin)
  - [ ] `h3` (font size, font weight, color, padding, margin)
  - [ ] Descriptions (font size, font weight, color, padding, margin)
  - [ ] Paragraph/span/standard fields (font size, padding, margin)

## 7. Shortcodes Admin (`bhg-shortcodes`)

- [ ] Add admin menu section listing all shortcodes with options and usage guidance (block title: "Info & Help").

## 8. Notifications (`bhg-notifications`)

### 8.1 Winner Notifications

- [ ] Add admin block to customize winner notification emails:
  - [ ] Title
  - [ ] Description (HTML)
  - [ ] BCC field
  - [ ] Enable/disable checkbox (default disabled)

### 8.2 Tournament Notifications

- [ ] Add admin block to customize tournament announcement emails with the same fields as winner notifications.

### 8.3 Bonus Hunt Notifications

- [ ] Add admin block to customize bonus hunt announcement emails with the same fields as winner notifications.

## 9. Tournaments

- [ ] Attach prizes to tournament admin and frontend details.
- [ ] Add affiliate website field to tournament admin (create/edit).
- [ ] Add affiliate website show/hide checkbox (default show) to control frontend display.

## 10. Tournament Ranking System

- [ ] Implement editable points system for rankings (default: 25, 15, 10, 5, 4, 3, 2, 1).
- [ ] Allow editing the points scale for active, closed, or all hunts.
- [ ] Ensure tournament results (admin & frontend) are based on winners from selected bonus hunts.
- [ ] Highlight winners in bonus hunt rankings (admin & frontend) with extra emphasis for top 3.

## 11. Core Bonus Hunt Functionality

- [ ] Admin can create bonus hunts with title, starting balance, number of bonuses, and prizes text.
- [ ] Logged-in users can submit guesses (0–100,000) for final balance.
- [ ] Display guesses in leaderboard with position, username, guess, and difference from result.
- [ ] Allow multiple winners (1st through 25th place) per hunt and show in admin dashboard.
- [ ] Provide hunt "Results" view listing all guesses ranked best to worst with winner highlighting.
- [ ] Configure number of winners per bonus hunt in admin.
- [ ] Show final balance column in admin tables ("-" while open).
- [ ] Display participants list in bonus hunt edit view with removal capability and clickable usernames linking to profile.

## 12. User Profiles & Enhancements

- [ ] Admin-manageable user profile fields (real name, username, email, affiliate status).
- [ ] Integrate social login via Nextend plugin (Google, Twitch, Kick).
- [ ] Allow users to alter guesses while hunt is open.
- [ ] Track affiliate status with green/red indicators on leaderboard.
- [ ] Provide sortable/paginated guess table (position, username, guess).
- [ ] Add search, sort, and pagination (30 per page) to `bhg-users` admin view.

## 13. Tournaments & Leaderboards

- [ ] Support time-based tournaments (weekly, monthly, quarterly, yearly, all-time).
- [ ] Add title and description fields to tournament admin (create/edit); ensure editing works.
- [ ] Remove redundant period field if type covers the same information.
- [ ] Provide leaderboards with sortable columns (position, username, wins) and filters by week, month, year.
- [ ] Display current tournament standings and historical data.

## 14. Frontend Leaderboard Enhancements

- [ ] Add tabs for best guessers (Overall, Monthly, Yearly, All-Time).
- [ ] Add tabs for viewing leaderboard history of previous bonus hunts.

## 15. User Experience Improvements

- [ ] Implement smart post-login redirect to the originally requested page.
- [ ] Configure three navigational menus (Admins/Moderators, Logged-in Users, Guests) using WordPress menus.
- [ ] Style menus to match site theme (borders, tab-like appearance).
- [ ] Provide "Translations" admin tab for managing plugin text strings.

## 16. Affiliate Enhancements

- [ ] Admin can manage multiple affiliate websites (add/edit/delete).
- [ ] Dropdown selector for affiliate site when creating a bonus hunt.
- [ ] Display affiliate site assignments per user profile (Affiliate Website 1, 2, 3, ...).
- [ ] Reflect affiliate data per hunt on frontend, influencing guesser display and ad targeting.

## 17. Notifications & Communication

- [ ] Calculate winners based on closest guess to final balance.
- [ ] Send result and winner notifications via email (respecting enable/disable settings and BCCs).

## 18. Advertising Module (`bhg-ads`)

- [ ] Admin can create ads with text, optional links, placement controls, and visibility rules.
- [ ] Add "Actions" column with edit and remove buttons in admin table.
- [ ] Add `none` placement option for shortcode-only ads.

## 19. Tools & Translations Screens

- [ ] Populate `bhg-translations` and `bhg-tools` admin pages with the expected management interfaces (currently empty).

## 20. General Polish

- [ ] Optimize performance and fix outstanding bugs.
- [ ] Apply border styling to Bonus Hunt admin input fields.
- [ ] Ensure compatibility with PHP 7.4, WordPress 6.3.5, and MySQL 5.5.5.
- [ ] Adhere to WordPress PHPCS standards (`WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`).

---

**Notes:**
- Track progress by checking off items as they are implemented and validated.
- Update this checklist as requirements evolve for future versions.
