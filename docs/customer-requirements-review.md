# Customer Requirements & Coding Standards Review

This audit covers Bonus Hunt Guesser commit `3110069` against the latest customer specification. Each section lists the implementation status, key file locations, and any follow-up work needed (with exact file paths).

> **Legend**
> * ✅ — Fully implemented & aligned with expectations
> * ⚠️ — Works functionally but needs refinement (see notes)
> * ❌ — Requirement missing or blocked

## 1. Prizes (bhg-prizes)

Status | Details
-------|--------
✅ | Admin CRUD is available via `admin/views/prizes.php`, backed by `includes/class-bhg-prizes.php` and helper assets `assets/js/admin-prizes.js`, `assets/css/bhg-shortcodes.css`. Supports title, description, category, three image sizes, CSS styling, and active flag.
⚠️ | Coding standards: the PHP in `admin/views/prizes.php` and `includes/class-bhg-prizes.php` mixes tabs/spaces and omits consistent inline documentation. Recommend running `phpcbf --standard=WordPress` on these files to align with WP guidelines.

## 2. Prize selection on Bonus Hunts

Status | Details
-------|--------
✅ | `admin/views/bonus-hunts-edit.php` exposes multi-select control populated from prizes (`BHG_Prizes::get_all_prizes()`), storing associations through `includes/class-bhg-prizes.php`.
⚠️ | Admin list column styles in `admin/views/bonus-hunts.php` still use mixed indentation; format with WordPress rules.

## 3. Prize Frontend Display

Status | Details
-------|--------
✅ | Active hunt shortcode renders attached prizes in grid/carousel via `includes/class-bhg-shortcodes.php` (`render_prize_section`) and JS carousel behaviours in `assets/js/bhg-shortcodes.js`.
⚠️ | Carousel alias fix (`design="caroussel"`) handled in `includes/class-bhg-shortcodes.php` but surrounding file violates WP indentation conventions. Run formatter or normalize manually.

## 4. Prize Shortcode `[bhg_prizes]`

Status | Details
-------|--------
✅ | Implemented in `includes/class-bhg-shortcodes.php::prizes_shortcode()`, honoring filters `category`, `design`, `size`, `active`. Styling handled in `assets/css/bhg-shortcodes.css`.
⚠️ | Shortcode attribute documentation missing in admin help. Consider updating `admin/views/tools.php`/`admin/views/translations.php` to list all options as per spec (see §7).

## 5. User Profile Shortcodes

Status | Details
-------|--------
❌ | Required shortcodes `my_bonushunts`, `my_tournaments`, `my_prizes`, `my_rankings` are not registered. `includes/class-bhg-shortcodes.php` only provides `bhg_user_profile` and general listing shortcodes. Implementers should add the four shortcodes (and admin visibility toggles) inside this file and expose settings in `admin/views/settings.php` (or dedicated visibility screen).

## 6. CSS/Color Panel

Status | Details
-------|--------
⚠️ | Prize CSS controls exist (`admin/views/prizes.php`). Global typography/block controls requested (title block, h2, h3, description, paragraph) are not surfaced in settings. Add a panel to `admin/views/settings.php` (or new design screen) that stores options in `bhg_plugin_settings`, then apply styles within frontend templates (e.g., `assets/css/bhg-shortcodes.css`).

## 7. Shortcode Info Screen (`bhg-shortcodes`)

Status | Details
-------|--------
⚠️ | `admin/views/tools.php` and `admin/views/translations.php` currently provide structural placeholders but do not enumerate shortcode usage/options. Populate `admin/views/tools.php` with a table describing each shortcode and its attributes per spec.

## 8. Notifications (`bhg-notifications`)

Status | Details
-------|--------
✅ | Notifications menu registered via `admin/class-bhg-admin.php`. Blocks for winner/tournament/hunt emails with enable toggles and BCC fields live in `admin/views/settings.php` (notifications section) and persist via `admin_post_bhg_save_settings` handler.
⚠️ | Double-check defaults: requirements say checkboxes disabled by default. Confirm `get_option( 'bhg_plugin_settings' )` respects zero values; adjust `admin/class-bhg-admin.php::handle_save_settings()` if needed.

## 9. Tournaments Enhancements

Status | Details
-------|--------
✅ | Admin prize linkage and affiliate fields: see `admin/views/tournaments.php`, `admin/views/tournaments-edit.php`, and persistence in `includes/class-bhg-tournaments-controller.php`. Frontend output includes prizes (`includes/class-bhg-shortcodes.php::tournaments_shortcode()`).
⚠️ | Ensure affiliate URL hide/show checkbox wired on frontend. Verify markup in `includes/class-bhg-shortcodes.php` around tournament cards (search for `affiliate_site_url`).

## 10. Tournament Ranking System

Status | Details
-------|--------
⚠️ | Points system stored in settings (`admin/views/settings.php`) and processed in `includes/class-bhg-tournaments-controller.php`. Highlighted winners exist in admin results view `admin/views/bonus-hunts-results.php`. Need to confirm editable points per hunt scope (active/closed/all) — `includes/class-bhg-tournaments-controller.php::recalculate_rankings()` currently applies single config globally. Consider extending schema to support per-status overrides if customer insists.

## Legacy Improvements & Feedback Loop (Sep 04)

Requirement | Status | File references / actions
-------------|--------|-------------------------
Dashboard renaming | ✅ | `admin/class-bhg-admin.php` uses "Dashboard" label. Ensure translation string `menu_dashboard` is localized in `languages/`.
Latest Hunts widget | ⚠️ | `admin/views/dashboard.php` lists hunts but verify it shows all winners + differences. Adjust query in `includes/class-bhg-bonus-hunts-helpers.php` if partial.
Bonus Hunt admin enhancements | ⚠️ | Buttons available (`admin/views/bonus-hunts.php`), but audit removal of guesses via `admin/views/bonus-hunts-edit.php` and `admin_post_bhg_delete_guess`. Confirm user links go to edit-profile screen.
Tournaments admin issues | ⚠️ | Title/description fields exist in `admin/views/tournaments-edit.php`, but confirm edit flow (`admin_post_bhg_tournament_save`) updates records (test once). Remove legacy "period" column if still shown.
Users table search/sort/pagination | ✅ | Implemented with custom list table `admin/views/users.php` (30 items per page). Ensure `includes/class-bhg-bonus-hunts-helpers.php` search sanitization uses `sanitize_text_field`.
Ads actions & placement | ✅ | `admin/views/advertising.php` includes edit/remove actions; placement includes `none` option.
Translations front-end defaults | ⚠️ | `admin/views/translations.php` loads keys but front-end strings may need initial population. Ensure `BHG_Translations::get_default_strings()` covers display text.

## WordPress Coding Standards Snapshot

File | Key Issues | Suggested Action
-----|------------|-----------------
`admin/class-bhg-admin.php` | Mixed tabs/spaces, trailing whitespace, inline comments not aligned | Run `phpcbf --standard=WordPress admin/class-bhg-admin.php`
`includes/class-bhg-shortcodes.php` | Extensive spacing inconsistencies, missing docblocks for many methods, direct `echo` inside logic | Gradually refactor per section; start with functions touched above to avoid giant diff
`includes/helpers.php` | Some helpers lack inline documentation and use snake_case inconsistent with WP naming | Add docblocks, ensure translation helpers use `esc_html__` where output is immediate
`assets/js/*` | JS follows ES5 but lacks linting; consider running `eslint` with WP config (future task)

## Next Steps Summary

1. Implement user dashboard shortcodes in `includes/class-bhg-shortcodes.php` (with visibility settings in `admin/views/settings.php`).
2. Add global CSS/color panel fields to `admin/views/settings.php` and apply in frontend templates (`assets/css/bhg-shortcodes.css`).
3. Populate shortcode help in `admin/views/tools.php` to satisfy documentation requirement.
4. Normalize coding standards across touched PHP files using WordPress rules.
5. Verify notification defaults and tournament point overrides as noted.

This document should guide further fine-tuning while keeping changes scoped to explicit customer requirements.
