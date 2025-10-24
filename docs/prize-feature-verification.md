# Bonus Hunt Guesser Requirement Verification

This document maps each agreed customer requirement to the implementing code so reviewers can quickly confirm coverage without browsing the full codebase. The checklist is intentionally limited to the ten numbered requirements supplied by the customer and does not introduce or assess any additional enhancements.

## Quick Reference by Requirement

| Requirement | Summary | Primary Implementation |
|-------------|---------|------------------------|
| 1. Prizes admin | Dedicated submenu, CRUD form (title, description, category, three image slots), CSS panel, active toggle | `admin/class-bhg-admin.php`, `admin/views/prizes.php`, `includes/class-bhg-prizes.php` |
| 2. Bonus hunt editor | Attach multiple prizes, control winners (1–25), review/removal of guesses, final balance column & results button | `admin/views/bonus-hunts.php`, `admin/views/bonus-hunts-results.php`, `admin/class-bhg-admin.php`, `includes/class-bhg-bonus-hunts-helpers.php` |
| 3. Frontend prizes | Active hunt cards plus standalone `[bhg_prizes]` shortcode with grid/carousel layouts and placeholder handling | `includes/class-bhg-shortcodes.php`, `assets/css/bhg-shortcodes.css` |
| 4. Prize shortcode filters | Attribute filters for category/design/size/active backed by alias normalization and rendering tests | `includes/class-bhg-shortcodes.php`, `tests/PrizesShortcodeNormalizationTest.php`, `tests/PrizesShortcodeRenderingTest.php` |
| 5. User shortcodes | `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, `[my_rankings]` with admin toggles | `includes/class-bhg-profile-shortcodes.php`, `admin/views/settings.php` |
| 6. CSS/color panel | Design settings for title blocks, headings, description, and standard text with sanitizers | `admin/views/settings.php`, `includes/helpers.php` |
| 7. Shortcodes info | “Info & Help” page documenting every shortcode and option | `admin/views/shortcodes.php` |
| 8. Notifications | Configurable Winner/Tournament/Bonushunt email blocks (subject, HTML body, BCC, enable toggle) | `admin/views/notifications.php`, `includes/helpers.php` |
| 9. Tournaments | Admin form (title, description, type incl. quarterly/alltime, affiliate visibility, prizes) & frontend display | `admin/views/tournaments.php`, `includes/class-bhg-shortcodes.php`, `includes/class-bhg-models.php` |
| 10. Tournament ranking | Editable point system driving standings and winner highlighting backend/frontend | `includes/helpers.php`, `includes/class-bhg-models.php`, `admin/views/bonus-hunts-results.php`, `includes/class-bhg-shortcodes.php` |

---

## 1. Prizes Admin (bhg-prizes)
- **Menu entry**: The main `Bonus Hunt` admin menu registers a dedicated **Prizes** submenu item so administrators can reach the prize list directly. 【F:admin/class-bhg-admin.php†L78-L118】
- **CRUD capabilities**: The prizes admin view renders the list table with Edit/Delete actions and the creation form with inputs for title, description, category, image IDs for three sizes (small, medium, large), CSS panel fields (border, border color, padding, margin, background color), and an Active toggle. 【F:admin/views/prizes.php†L1-L290】
- **Data layer**: `BHG_Prizes` provides category validation, CSS sanitization, CRUD helpers, and image retrieval for the three stored sizes. 【F:includes/class-bhg-prizes.php†L15-L210】

## 2. Bonus Hunt Editor (bhg-bonus-hunts)
- **Prize selection**: The hunt editor exposes a multi-select control that pulls from saved prizes so admins can associate one or more prizes with each hunt. 【F:admin/views/bonus-hunts.php†L120-L212】
- **Participants overview**: Editing a hunt shows all submitted guesses with sortable columns, pagination, profile links, and removal buttons that call the admin handler. 【F:admin/views/bonus-hunts.php†L320-L512】【F:admin/class-bhg-admin.php†L420-L548】
- **Final balance/results access**: The hunt list table shows the Final Balance column (or `-` while open) and adds a **Results** button that links to the ranked guesses view with highlighted winners. 【F:admin/views/bonus-hunts.php†L40-L118】【F:admin/views/bonus-hunts-results.php†L1-L180】
- **Winner count configuration**: Admins can define 1–25 winners per hunt, and the setting is enforced when closing hunts and generating rankings. 【F:admin/views/bonus-hunts.php†L214-L252】【F:includes/class-bhg-bonus-hunts-helpers.php†L120-L228】

## 3. Frontend Prize Display
- **Active hunt prizes**: The active hunt shortcode renders linked prizes in reusable card templates that support grid or carousel layouts, navigation controls, and winner highlighting across hunt tables. 【F:includes/class-bhg-shortcodes.php†L1740-L2112】【F:assets/css/bhg-shortcodes.css†L1-L220】
- **Standalone prize shortcode**: `[bhg_prizes]` accepts `category`, `design` (grid, carousel, plus aliases like list/caroussel/horizontal), `size` (small/medium/big with common synonyms), and `active` filters; it reuses the same rendering pipeline and placeholder handling. 【F:includes/class-bhg-shortcodes.php†L2590-L2854】

## 4. Prize Shortcode Filters
- **Attribute support**: The shortcode parser normalizes requested categories, layouts, sizes, and active flags before querying prizes. 【F:includes/class-bhg-shortcodes.php†L2590-L2754】
- **Rendering verification**: PHPUnit coverage confirms grid and carousel markup plus alias handling so regressions are caught automatically. 【F:tests/PrizesShortcodeNormalizationTest.php†L1-L210】【F:tests/PrizesShortcodeRenderingTest.php†L1-L220】

## 5. User Profile Shortcodes
- **Profile sections**: Logged-in users can embed `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, and `[my_rankings]`, each producing tables with ranking data, prize summaries, and winner highlighting. 【F:includes/class-bhg-profile-shortcodes.php†L1-L410】
- **Admin visibility controls**: The settings view exposes toggles so administrators can hide/show each profile shortcode section in the frontend. 【F:admin/views/settings.php†L150-L242】

## 6. CSS / Color Panel
- **Design controls**: The settings page offers inputs for title block background, border radius, padding, margin, and typography options for `h2`, `h3`, description, and standard text. Saved values are sanitized and transformed into inline CSS for frontend usage. 【F:admin/views/settings.php†L244-L332】【F:includes/helpers.php†L40-L236】

## 7. Shortcodes Help (bhg-shortcodes)
- The Shortcodes admin page lists every available shortcode with option tables (block title: “Info & Help”), covering prizes, hunts, tournaments, user dashboards, and filters. 【F:admin/views/shortcodes.php†L1-L200】

## 8. Notifications (bhg-notifications)
- The Notifications admin tab provides configurable sections for Winner, Tournament, and Bonus Hunt emails with subject, HTML body, BCC field, and enable checkbox (disabled by default). 【F:admin/views/notifications.php†L1-L232】【F:includes/helpers.php†L420-L548】

## 9. Tournaments
- **Admin form**: Tournament creation/editing includes title, description, type selector (weekly, monthly, quarterly, yearly, alltime), hunt linkage mode, affiliate website dropdown plus visibility toggle, and prize editor. 【F:admin/views/tournaments.php†L1-L248】
- **Frontend output**: Tournament detail shortcodes render prizes, affiliate links (respecting visibility flag), and standings aggregated from winner points. 【F:includes/class-bhg-shortcodes.php†L1200-L1738】

## 10. Tournament Ranking Enhancements
- **Point system**: Default points (1st–8th) are stored in options, editable in the admin, and applied when hunts are closed. Aggregation recalculates tournaments based on selected hunt winners and highlights the top three in both admin and frontend tables. 【F:includes/helpers.php†L238-L418】【F:includes/class-bhg-models.php†L350-L612】【F:admin/views/bonus-hunts-results.php†L90-L170】
- **Frontend standings**: Public tournament views display the accumulated points with sortable columns and consistent winner highlighting so players can track rankings over time. 【F:includes/class-bhg-shortcodes.php†L1200-L1738】

## Testing References
- PHPUnit coverage guards prize normalization, CSS sanitization, shortcode rendering, and tournament recalculations to prevent regressions within the documented requirements. 【F:tests/PrizesShortcodeNormalizationTest.php†L1-L210】【F:tests/PrizesCssSettingsTest.php†L1-L190】【F:tests/PrizesShortcodeRenderingTest.php†L1-L220】

## WordPress Coding Standards Checklist
- Core admin controllers and views follow WordPress tab-based indentation, ABSPATH guards, and translation-ready strings as demonstrated in `BHG_Admin` and the prizes view. 【F:admin/class-bhg-admin.php†L1-L210】【F:admin/views/prizes.php†L1-L60】
- Frontend helpers sanitize URLs, attributes, and CSS fragments before output, mirroring WordPress escaping guidance. 【F:includes/class-bhg-prizes.php†L120-L210】【F:includes/class-bhg-shortcodes.php†L2590-L2854】
- PHPUnit bootstrap supplies shims for `sanitize_text_field()` and `sanitize_hex_color()` so automated checks emulate the WordPress environment, keeping tests PHP 7.4 compatible. 【F:tests/bootstrap.php†L1-L220】
