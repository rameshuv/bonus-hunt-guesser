# Feature Requirement Mapping

Version **8.0.23** of the plugin meets the requested runtime targets (PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+) as declared in the main plugin header (`bonus-hunt-guesser.php`). The tables below mark each requirement as **Met** and point to the code sections that implement it for quick verification. The badge/button database tables (`bhg_badges`, `bhg_user_affiliate_dates`, `bhg_buttons`) are created by the installer, the admin menus are wired via `BHG_Admin`, and the rendering logic lives in `includes/class-bhg-shortcodes.php` plus its helpers, giving a single place to review runtime behavior.

## Runtime targets
| Requirement | Status | Reference |
| --- | --- | --- |
| PHP 7.4 / WP 6.3.5 / MySQL 5.5.5+ declared | Met | Plugin header in `bonus-hunt-guesser.php` |

## 1. Badges
| Requirement | Status | Reference |
| --- | --- | --- |
| Badges admin menu with CRUD | Met | Menu and handlers registered in `admin/class-bhg-admin.php` and rendered via `admin/views/badges.php` |
| Badge fields: title, image/icon, affiliate website, user data metric, set data threshold | Met | Form inputs (title/image/affiliate/user data/threshold/status) in `admin/views/badges.php` |
| Affiliate/user data logic (wins, guesses, registration days, affiliate dates) | Met | Badge qualification switch across wins/guesses/registration days/affiliate activation in `includes/class-bhg-badges.php` |
| Frontend usernames show earned badges | Met | `bhg_format_user_with_badges()` in `includes/helpers.php` appends rendered badges used by shortcodes |

## 2. Buttons / `bhg_button` shortcode
| Requirement | Status | Reference |
| --- | --- | --- |
| Backend CRUD for buttons | Met | Admin form in `admin/views/buttons.php` with saving wired in `admin/class-bhg-admin.php` |
| Placement and visibility controls (active hunt/tournament, audience filters) | Met | Placement/audience gating evaluated in `includes/class-bhg-shortcodes.php` and stored via `includes/class-bhg-buttons.php` |
| Text, link/target, colors, sizes, border styling | Met | Styling fields saved in `admin/views/buttons.php` and emitted by `includes/class-bhg-shortcodes.php` + `assets/css/bhg-shortcodes.css` |
| `bhg_button` shortcode registered and documented | Met | Shortcode registration in `includes/class-bhg-shortcodes.php`; listed in `admin/views/shortcodes.php` |

## 3. `bhg_active_hunt` and `bhg_user_guesses`
| Requirement | Status | Reference |
| --- | --- | --- |
| Empty-state messages in styled blocks | Met | Helpers in `includes/helpers.php` with styles in `assets/css/bhg-shortcodes.css` |
| Active hunt detail card styling | Met | Layout rules in `assets/css/bhg-shortcodes.css` |
| Toggle visibility for number of bonuses and affiliate website | Met | Form checkboxes in `admin/views/bonus-hunts.php` read when rendering in `includes/class-bhg-shortcodes.php` |

## 4. `bhg_active_hunt` detail controls
| Requirement | Status | Reference |
| --- | --- | --- |
| Visibility flags for number of bonuses and affiliate site saved + rendered | Met | Admin form in `admin/views/bonus-hunts.php` and rendering in `includes/class-bhg-shortcodes.php` |
| Status label shows "Active: Guess Now" link or "Closed" | Met | Status handling in the `bhg_active_hunt` card within `includes/class-bhg-shortcodes.php` |

## 5. `bhg-bonus-hunts-results`
| Requirement | Status | Reference |
| --- | --- | --- |
| Winner highlighting uses configured winner counts | Met | Logic in `admin/views/bonus-hunts-results.php` (paired with styles in `assets/css/admin.css`) |
| Results admin dropdown supports both tournaments and hunts | Met | Dropdown and navigation handled in `admin/class-bhg-admin.php` and `admin/views/bonus-hunts-results.php` |

These references collectively confirm that the plugin implements the requested features in version 8.0.23.

### Evidence pointers

- Runtime target declarations live in the plugin header of `bonus-hunt-guesser.php` (version/PHP/WP/MySQL requirements).
- Badge qualification logic covers wins, guesses, registration days, and affiliate activation in `includes/class-bhg-badges.php`.
- The `bhg_active_hunt` renderer outputs the "Status: Active: Guess Now" link and respects visibility toggles for bonus counts and affiliate site names in `includes/class-bhg-shortcodes.php`.
- Buttons CRUD + shortcode wiring flow through `admin/views/buttons.php`, `admin/class-bhg-admin.php`, `includes/class-bhg-buttons.php`, and the `bhg_button` registration in `includes/class-bhg-shortcodes.php`.

## Delivery confirmation

- Runtime targets: PHP 7.4 / WordPress 6.3.5 / MySQL 5.5.5+ ✅
- Feature coverage: badges, buttons (with shortcode), hunt/tournament detail updates, and results admin behavior ✅
- Checklists: [Order Delivery](order-delivery-checklist.md) and [Delivery Readiness](delivery-readiness.md) remain fully checked ✅

Result: **Ready for delivery on version 8.0.23.**
