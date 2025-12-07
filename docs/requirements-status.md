# Feature Requirement Mapping

This document confirms that version 8.0.23 of the plugin implements all requested functionality for PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+. Each bullet points to where the code satisfies the requirement so you can quickly verify coverage. Runtime targets are defined in the main plugin header (`bonus-hunt-guesser.php`).

## 1. Badges
- **Admin menu and CRUD**: The `Badges` submenu is registered in `BHG_Admin::register_menu_pages()` and renders the badges view for create/update/delete flows in `admin/views/badges.php` via `BHG_Badges::save()` / `BHG_Badges::delete()`.
- **Badge fields**: The badges form exposes Title, Badge Image/Icon, Affiliate Website selector, User Data selector, Set Data threshold selector, and Active toggle (see the form table in `admin/views/badges.php`).
- **Affiliate/user data logic**: Badge qualification checks affiliate activation dates, total wins (bonus hunts/tournaments), total guesses, registration days, and affiliate-active days inside `BHG_Badges::qualify_user_for_badge()` in `includes/class-bhg-badges.php`.
- **Frontend display**: Usernames render with earned badges appended through `bhg_format_user_with_badges()` (declared in `includes/helpers.php` and used in shortcode renderers in `includes/class-bhg-shortcodes.php`).

## 2. Buttons / `bhg_button` shortcode
- **Backend CRUD**: Buttons can be added/edited/deleted with placement, visibility, and style options in `admin/views/buttons.php`, orchestrated by `BHG_Admin` save handlers.
- **Visibility controls**: Placement targets (active bonushunt/tournament), audience filters, and conditional display for active hunts/tournaments are evaluated in the button shortcode rendering logic in `includes/class-bhg-shortcodes.php`.
- **Styling options**: Custom text, links/targets, colors, sizes, and border settings are stored and applied when composing the shortcode output in `includes/class-bhg-shortcodes.php`, styled by rules in `assets/css/bhg-shortcodes.css`.
- **Shortcode availability**: The `bhg_button` shortcode is registered in `BHG_Shortcodes::init()` within `includes/class-bhg-shortcodes.php`, and the shortcode list in `admin/views/shortcodes.php` documents its usage.

## 3. `bhg_active_hunt` and `bhg_user_guesses`
- **Empty-state messaging**: "No Guesses Yet" and "No Guesses Found" messages are wrapped in styled info blocks via helpers in `includes/helpers.php` with matching styles in `assets/css/bhg-shortcodes.css`.
- **Active bonus hunt layout**: Active hunt details use the card layout and gradients defined in `assets/css/bhg-shortcodes.css`, mirroring the tournament treatment.
- **Detail visibility controls**: Admin users can toggle whether the number of bonuses and affiliate website appear in the frontend hunt detail card via checkboxes in `admin/views/bonus-hunts.php`; `bhg_active_hunt` respects these flags in `includes/class-bhg-shortcodes.php`.

## 4. `bhg_active_hunt` detail controls
- **Description visibility**: Bonus hunt records carry visibility flags (number of bonuses, affiliate website) saved through the add/edit form in `admin/views/bonus-hunts.php` and honored during frontend rendering in `includes/class-bhg-shortcodes.php`.
- **Status output**: The frontend detail outputs a "Status" label showing "Active: Guess Now" (linking to the hunt guess page) or "Closed" as implemented in the `bhg_active_hunt` shortcode renderer in `includes/class-bhg-shortcodes.php`.

## 5. `bhg-bonus-hunts-results`
- **Winner color logic**: Backend results rows apply winner highlighting based on the configured winner counts for the selected hunt or tournament inside `admin/views/bonus-hunts-results.php`, with matching badge styles in `assets/css/admin.css`.
- **Hunt dropdown support**: The results admin includes dropdowns for both tournaments and bonus hunts, and navigation from bonus hunts into the results page uses the `bhg-bonus-hunts-results` target registered in `admin/class-bhg-admin.php` alongside the view logic in `admin/views/bonus-hunts-results.php`.

These references collectively confirm that the plugin implements the requested features in version 8.0.23.
