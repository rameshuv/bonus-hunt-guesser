# Feature Requirement Mapping

Version **8.0.23** of the plugin meets the requested runtime targets (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+) as declared in the main plugin header (`bonus-hunt-guesser.php`). The tables below mark each requirement as **Met** and point to the code sections that implement it for quick verification.

## Runtime targets
| Requirement | Status | Reference |
| --- | --- | --- |
| PHP 7.4 / WP 6.3.5 / MySQL 5.5.5+ declared | Met | Plugin header in `bonus-hunt-guesser.php` |

## 1. Badges
| Requirement | Status | Reference |
| --- | --- | --- |
| Badges admin menu with CRUD | Met | `BHG_Admin::register_menu_pages()` and `admin/views/badges.php` |
| Badge fields: title, image/icon, affiliate website, user data metric, set data threshold | Met | Form inputs in `admin/views/badges.php` |
| Affiliate/user data logic (wins, guesses, registration days, affiliate dates) | Met | `BHG_Badges::qualify_user_for_badge()` in `includes/class-bhg-badges.php` |
| Frontend usernames show earned badges | Met | `bhg_format_user_with_badges()` in `includes/helpers.php` and usage in `includes/class-bhg-shortcodes.php` |

## 2. Buttons / `bhg_button` shortcode
| Requirement | Status | Reference |
| --- | --- | --- |
| Backend CRUD for buttons | Met | Admin form in `admin/views/buttons.php` with saves handled by `BHG_Admin` |
| Placement and visibility controls (active hunt/tournament, audience filters) | Met | Logic in `includes/class-bhg-shortcodes.php` |
| Text, link/target, colors, sizes, border styling | Met | Fields in `admin/views/buttons.php` and output styling via `includes/class-bhg-shortcodes.php` + `assets/css/bhg-shortcodes.css` |
| `bhg_button` shortcode registered and documented | Met | Registration in `includes/class-bhg-shortcodes.php`; listed in `admin/views/shortcodes.php` |

## 3. `bhg_active_hunt` and `bhg_user_guesses`
| Requirement | Status | Reference |
| --- | --- | --- |
| Empty-state messages in styled blocks | Met | Helpers in `includes/helpers.php` with styles in `assets/css/bhg-shortcodes.css` |
| Active hunt detail card styling | Met | Layout rules in `assets/css/bhg-shortcodes.css` |
| Toggle visibility for number of bonuses and affiliate website | Met | Form checkboxes in `admin/views/bonus-hunts.php` respected by `includes/class-bhg-shortcodes.php` |

## 4. `bhg_active_hunt` detail controls
| Requirement | Status | Reference |
| --- | --- | --- |
| Visibility flags for number of bonuses and affiliate site saved + rendered | Met | Admin form in `admin/views/bonus-hunts.php` and rendering in `includes/class-bhg-shortcodes.php` |
| Status label shows "Active: Guess Now" link or "Closed" | Met | Status handling in `bhg_active_hunt` renderer within `includes/class-bhg-shortcodes.php` |

## 5. `bhg-bonus-hunts-results`
| Requirement | Status | Reference |
| --- | --- | --- |
| Winner highlighting uses configured winner counts | Met | Logic in `admin/views/bonus-hunts-results.php` (paired with styles in `assets/css/admin.css`) |
| Results admin dropdown supports both tournaments and hunts | Met | Dropdown and navigation handled in `admin/class-bhg-admin.php` and `admin/views/bonus-hunts-results.php` |

These references collectively confirm that the plugin implements the requested features in version 8.0.23.
