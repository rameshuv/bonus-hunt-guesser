# Bonus Hunt Guesser – Customer Requirements Checklist

This checklist summarises each customer requirement and points to the primary implementation areas so future reviews can verify compliance quickly.

## 1. Prizes (bhg-prizes)
- [x] Admin menu entry and CRUD interface (`admin/class-bhg-admin.php`, `admin/views/prizes.php`, `includes/class-bhg-prizes.php`).
- [x] Prize metadata (title, description, category, images in three sizes, activation toggle) handled in `admin/views/prizes.php` and storage logic in `includes/class-bhg-prizes.php`.
- [x] Frontend design controls (border, colours, spacing) persisted via prize settings (`includes/class-bhg-prizes.php`, CSS tokens in `assets/css/bhg-shortcodes.css`).

## 2. Prize selection on Bonus Hunts
- [x] Bonus hunt editor supports selecting multiple prizes via multi-select control (`admin/views/bonus-hunts-edit.php`).
- [x] Persistence of hunt-prize relationships handled in `includes/class-bhg-prizes.php` and migrations in `includes/class-bhg-db.php`.

## 3. Prize frontend display
- [x] Active hunt output exposes grid and carousel renderers with navigation (`includes/class-bhg-shortcodes.php`, `assets/js/bhg-shortcodes.js`, `assets/css/bhg-shortcodes.css`).

## 4. Prize shortcode
- [x] `[bhg_prizes]` shortcode implemented with filters for category, design (grid/carousel), size, and active status (`includes/class-bhg-shortcodes.php`).

## 5. User dashboard shortcodes
- [x] Shortcodes `[bhg_my_bonushunts]`, `[bhg_my_tournaments]`, `[bhg_my_prizes]`, and `[bhg_my_rankings]` output personalised tables with admin visibility toggles (`includes/class-bhg-shortcodes.php`, settings in `admin/views/settings.php`).

## 6. CSS / Colour panel
- [x] Global design controls for title block, headings, descriptions, and standard text stored via settings UI (`admin/views/settings.php`) and consumed in shortcode styles (`assets/css/bhg-shortcodes.css`).

## 7. Shortcodes admin reference
- [x] “Shortcodes” admin screen lists shortcode catalogue with arguments and help copy (`admin/views/tools.php`, `admin/class-bhg-admin.php`).

## 8. Notifications (bhg-notifications)
- [x] Notifications submenu and configuration blocks for winner, tournament, and bonushunt emails with BCC fields and enable toggles (`admin/views/notifications.php`).
- [x] Email helper functions honour settings during hunt closure and tournament creation (`includes/helpers.php`).

## 9. Tournaments enhancements
- [x] Tournament admin form includes prize selection, affiliate website, and visibility controls (`admin/views/tournaments-edit.php`).
- [x] Frontend tournament details display prizes and affiliate information (`includes/class-bhg-shortcodes.php`).

## 10. Tournament ranking system
- [x] Points configuration stored via admin settings (`admin/views/settings.php`) and persisted in helpers.
- [x] Ranking calculations based on winners and configurable points in `includes/helpers.php` and tournament renderers in `includes/class-bhg-shortcodes.php`.
- [x] Highlighting of winners and top 3 positions applied in shortcode tables via `assets/css/bhg-shortcodes.css`.

## Additional Backend Adjustments
- [x] Dashboard “Latest Hunts” widget shows winners with guess difference (`admin/class-bhg-admin.php`).
- [x] Bonus hunts list includes Results action, guessing toggles, final balance column, and admin action column (`admin/views/bonus-hunts.php`).
- [x] Bonus hunt edit page lists guessers with management tools (`admin/views/bonus-hunts-edit.php`).
- [x] Tournaments list provides title/description fields, results/close actions, search, sort, and pagination support (`admin/views/tournaments.php`).
- [x] Users and ads admin tables now support search, sorting, pagination, and action buttons (`admin/views/users.php`, `admin/views/ads.php`).
- [x] Translations and tools pages expose stored data and front-end translation groups (`admin/views/translations.php`, `admin/views/tools.php`).

---

**Next Review Tip:** When validating changes, ensure WordPress coding standards (`vendor/bin/phpcs --standard=phpcs.xml`) remain clean and that new features stay within the documented requirements above.
