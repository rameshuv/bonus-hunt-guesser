# Bonus Hunt Guesser – Compliance Review

## Prizes module

* Admin screen allows creating, editing, and listing prizes with category, description, multi-size images, CSS controls, and active toggle. 【F:admin/views/prizes.php†L1-L199】
* Database layer exposes CRUD helpers used by the admin UI and hunt associations. 【F:includes/class-bhg-prizes.php†L1-L225】
* Bonus hunt editor supports selecting prizes alongside tournament linkage and winner limits. 【F:admin/views/bonus-hunts-edit.php†L41-L196】
* Front-end `[bhg_prizes]` shortcode renders active prizes with layout options. 【F:includes/class-bhg-shortcodes.php†L1250-L1389】

## User dashboard shortcodes

* Shortcodes for `my_bonushunts`, `my_tournaments`, `my_prizes`, and `my_rankings` are registered and implemented for logged-in users with visibility checks. 【F:includes/class-bhg-shortcodes.php†L41-L58】【F:includes/class-bhg-shortcodes.php†L588-L866】

## Notifications module

* Notifications submenu renders winner, tournament, and bonushunt templates with enable flags, BCC field, and rich text body. 【F:admin/views/notifications.php†L1-L209】
* Handler persists notification settings with nonce/permission checks. 【F:admin/class-bhg-admin.php†L1083-L1129】

## Tournament enhancements

* Tournament editor includes title, description, affiliate website controls, prize assignments, participant modes, and results links. 【F:admin/views/tournaments.php†L1-L255】
* Tournament ranking logic awards configurable points based on winner placement, supports highlight styling, and integrates with hunt selections. 【F:includes/helpers.php†L2223-L2471】

## Shortcode catalogue & tools

* Tools menu documents available shortcodes and options for reference. 【F:admin/views/tools.php†L1-L189】

## Coding-standard & security findings

These areas require follow-up to meet WordPress coding standards and harden input handling:

1. **Sanitize numeric and text inputs in hunt save handler.** `$starting_raw`, `$tournament_ids_input`, `$prize_ids_input`, and `$final_balance_raw` pull directly from `$_POST` without `sanitize_text_field()` or array sanitization before use. 【F:admin/class-bhg-admin.php†L303-L356】
2. **Sanitize CSS settings from prize form before persistence.** The current handler passes raw `$_POST['css_*']` values into the settings array. 【F:admin/class-bhg-admin.php†L748-L754】
3. **Use `$wpdb->prepare()` for dynamic SQL** where table names and variables are concatenated, e.g., retrieving existing hunts and writing results. 【F:admin/class-bhg-admin.php†L401-L415】【F:admin/class-bhg-admin.php†L510-L567】
4. **Avoid overriding `$submenu` global directly** when renaming menu labels; prefer `add_filter( 'parent_file', ... )` or `add_filter( 'submenu_file', ... )`. 【F:admin/class-bhg-admin.php†L65-L70】
5. **Eliminate `error_log()` debug calls** left in tournament recalculation flows. 【F:admin/class-bhg-admin.php†L908-L909】
6. **Add nonce checks for batch actions** such as tournament deletion and hunt association updates which currently process `$_POST` without verification. 【F:admin/class-bhg-admin.php†L808-L921】
7. **Document mock database test harness** to satisfy coding standards (missing file/class docblocks). 【F:tests/support/class-mock-wpdb.php†L1-L350】

## Recommended next steps

* Implement the sanitization and nonce updates noted above, rerun `vendor/bin/phpcs` with the bundled `phpcs.xml`, and iteratively address remaining sniffs (particularly tab indentation and prepared statements).
* Add regression tests covering prize creation, hunt-prize associations, and notification toggles to ensure future changes respect the agreed requirements.
