# Feature Requirement Mapping

This document summarizes how the plugin codebase addresses the feature requests listed in the latest specification (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+, plugin version 8.0.23).

## 1. Badges
- **Admin menu and CRUD**: The `Badges` submenu is registered and routes to the badges admin view (`admin/views/badges.php`) with handlers for create, update, and delete in `admin/class-bhg-admin.php`.
- **Badge fields**: The badges form exposes Title, Badge Image/Icon, Affiliate Website selector, User Data selector, Set Data threshold selector, and Active toggle in `admin/views/badges.php`.
- **Affiliate/user data logic**: Badge qualification supports affiliate activation dates, total wins (bonus hunts/tournaments), total guesses, registration days, and affiliate-active days in `includes/class-bhg-badges.php`.
- **Frontend display**: Usernames are rendered with earned badges appended in shortcode outputs via `bhg_format_user_with_badges()` and related helpers in `includes/helpers.php` and `includes/class-bhg-shortcodes.php`.

## 2. Buttons / `bhg_button` shortcode
- **Backend CRUD**: Buttons can be added/edited/deleted with placement, visibility, and style options in `admin/views/buttons.php` managed by `admin/class-bhg-admin.php`.
- **Visibility controls**: Placement targets (active bonushunt/tournament), audience filters, and conditional display for active hunts/tournaments are handled in the button rendering logic in `includes/class-bhg-shortcodes.php`.
- **Styling options**: Custom text, links/targets, colors, sizes, and border settings are stored and applied via the shortcode rendering in `includes/class-bhg-shortcodes.php` and styled in `assets/css/bhg-shortcodes.css`.
- **Shortcode availability**: The `bhg_button` shortcode is registered in `includes/class-bhg-shortcodes.php`, and the shortcode list in the admin screen documents it in `admin/views/shortcodes.php`.

## 3. `bhg_active_hunt` and `bhg_user_guesses`
- **Empty-state messaging**: "No Guesses Yet" and "No Guesses Found" messages display within styled info blocks as defined in `includes/helpers.php` and `assets/css/bhg-shortcodes.css`.
- **Active bonus hunt layout**: Active hunt details are presented in a styled card layout in `assets/css/bhg-shortcodes.css`, matching the tournament detail treatment.

## 4. `bhg_active_hunt` detail controls
- **Description visibility**: Bonus hunt records include flags to hide/show number of bonuses and affiliate website fields in `admin/views/bonus-hunts.php`, respected during frontend rendering in `includes/class-bhg-shortcodes.php`.
- **Status output**: The frontend detail swaps the "Closed" label for a "Status" display with actionable "Active: Guess Now" link or "Closed" state in `includes/class-bhg-shortcodes.php`.

## 5. `bhg-bonus-hunts-results`
- **Winner color logic**: Backend results rows highlight winners based on the selected hunt/tournament winner counts in `admin/views/bonus-hunts-results.php` with badge styling in `assets/css/admin.css`.
- **Hunt dropdown support**: The results admin includes dropdowns for both tournaments and bonus hunts, and navigation from bonus hunts results links resolves correctly via handlers in `admin/class-bhg-admin.php` and the associated view.

These references collectively confirm that the plugin implements the requested features in version 8.0.23.
