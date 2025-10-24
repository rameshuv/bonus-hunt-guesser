# Prize and Bonus Hunt Feature Verification

This document summarizes where the plugin implements the customer requirements that relate to prizes, bonus hunts, front-end displays, and supporting tooling.

## 1. Prizes Admin (bhg-prizes)
- The top-level `Bonus Hunt` admin menu registers a dedicated **Prizes** submenu entry via `BHG_Admin::menu()` so administrators can reach the management screen directly. 【F:admin/class-bhg-admin.php†L52-L91】
- The prizes admin view lets staff create, edit, or delete prizes with fields for title, description, category, three image sizes, CSS styling controls, and the active toggle. Actions for editing and removing prizes are exposed alongside the listing. 【F:admin/views/prizes.php†L1-L210】【F:admin/views/prizes.php†L214-L285】
- `BHG_Prizes` provides the corresponding CRUD utilities, category list (`cash_money`, `casino_money`, `coupons`, `merchandise`, `various`), CSS sanitization, and multi-size image storage so the admin form persists the submitted data. 【F:includes/class-bhg-prizes.php†L15-L200】

## 2. Bonus Hunt Editor (bhg-bonus-hunts)
- Administrators can link multiple prizes to a hunt, configure up to 25 winners, review sortable and paginated hunts, view final balances, and access a dedicated **Results** button from the list screen. The edit view shows all participants with profile links and per-guess removal controls. 【F:admin/views/bonus-hunts.php†L1-L320】【F:admin/views/bonus-hunts.php†L321-L640】

## 3. Frontend Prize Display
- The active hunt presentation renders linked prizes as reusable card layouts (grid or carousel), shares styling with the shortcode output, and highlights winners within leaderboards and participant tables. 【F:includes/class-bhg-shortcodes.php†L1770-L2080】【F:includes/class-bhg-shortcodes.php†L2370-L2663】
- The standalone `[bhg_prizes]` shortcode supports `category`, `design` (grid or carousel aliases), `size` (small/medium/big with common synonyms), and `active` filters while reusing the same rendering pipeline. 【F:includes/class-bhg-shortcodes.php†L2672-L2715】

## 4. User Profile Shortcodes
- Logged-in users can list their hunts, tournaments, prizes, and rankings through dedicated profile shortcodes. Admins can enable or disable each section in settings, and winner styling carries across the tables. 【F:includes/class-bhg-profile-shortcodes.php†L1-L380】

## 5. Design Settings & Styling
- Global styling controls for title blocks, headings, descriptions, and standard text appear in the settings screen and are sanitized plus applied on the frontend. 【F:admin/views/settings.php†L150-L290】【F:includes/helpers.php†L1-L220】

## 6. Notifications & Shortcode Help
- Notifications and shortcode documentation each live in their own admin tabs so site owners can configure winner/tournament/bonushunt emails and review shortcode parameters. 【F:admin/views/notifications.php†L1-L220】【F:admin/views/shortcodes.php†L1-L200】

## 7. Tournament Integration
- Tournament administration covers title, description, type (including quarterly and all-time), affiliate visibility, prize selection, and the ability to attach hunts. The frontend tournament view and ranking recalculations incorporate winner points based on configured scales. 【F:admin/views/tournaments.php†L1-L280】【F:includes/class-bhg-models.php†L350-L560】

## 8. Dashboard & Results Visibility
- The dashboard “Latest Hunts” widget lists recent hunts with all winners, start and final balances, and closed timestamps, fulfilling the multi-winner requirement. Closed hunts expose a detailed results table with highlighted placements. 【F:admin/views/dashboard.php†L40-L160】【F:admin/views/bonus-hunts-results.php†L1-L200】

## 9. Testing
- Automated PHPUnit coverage exercises prize normalization, rendering, CSS sanitization, and related helpers to guard against regressions. 【F:tests/PrizesShortcodeNormalizationTest.php†L1-L200】【F:tests/PrizesCssSettingsTest.php†L1-L180】【F:tests/PrizesShortcodeRenderingTest.php†L1-L220】

