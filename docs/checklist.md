# Bonus Hunt Guesser v8.0.14 – Implementation Checklist

This checklist captures the mandatory deliverables agreed with the customer for the v8.0.14 release. Tick off each item only after verifying it inside the plugin.

## 1. General & Release
- [ ] Plugin header matches customer contract (version 8.0.14, WP ≥ 6.3.0, PHP ≥ 7.4, MySQL ≥ 5.5.5, GPLv2+).
- [ ] Text domain `bonus-hunt-guesser` is used for all translatable strings and loaded from `/languages`.
- [ ] PHPCS (`phpcs.xml`) passes without introducing new violations.
- [ ] Database schema migrations run through `dbDelta()` and remain MySQL 5.5.5 compatible.

## 2. Security & Architecture
- [ ] All privileged actions gated by `manage_options` (or the dedicated capability).
- [ ] Nonces implemented for create/edit/delete/toggle/close/result operations.
- [ ] Inputs sanitized (e.g., `sanitize_text_field`, `absint`, `floatval`) and outputs escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- [ ] `$wpdb->prepare()` used for all raw SQL statements.
- [x] Admin tables paginated with 30 items per page (`BHG_PER_PAGE` constant or filter).

## 3. Database Tables
- [ ] `wp_bhg_bonus_hunts` includes `guessing_enabled` and optional `affiliate_id` columns.
- [x] `wp_bhg_tournaments` stores `participants_mode` (`winners`/`all`) and legacy `type` column removed.
- [x] Junction table `wp_bhg_tournaments_hunts` created with unique (`tournament_id`, `hunt_id`).
- [ ] Database migrations are idempotent and re-runnable.

## 4. Settings & Currency
- [ ] Option `bhg_currency` stored and defaults to `EUR`.
- [ ] Helper functions `bhg_currency_symbol()` and `bhg_format_money()` available and used in admin/frontend outputs.
- [ ] Changing currency updates all displayed monetary values (hunts, guesses, results, leaderboards).

## 5. Admin Dashboard (`bhg`)
- [ ] Menu label changed from “Bonushunt” to “Dashboard”.
- [ ] “Latest Hunts” widget shows three most recent hunts with columns: Title, Winners (username, guess, difference), Start Balance, Final Balance, Closed At.
- [ ] Multiple winners per hunt (up to 25) displayed with bold usernames and each winner on a separate row.

## 6. Bonus Hunts Admin (`bhg-bonus-hunts`)
- [ ] List table sortable by ID, Title, Start Balance, Final Balance, Status; searchable by ID/Title; paginated (30/pg).
- [ ] Columns include Affiliate, Start Balance, Final Balance (show “-” when open), Status, Actions, Admin Action (Delete only).
- [ ] Row actions: Edit, Results, Enable/Disable Guessing (reflects `guessing_enabled`).
- [x] Add/Edit form contains: Enable guessing checkbox (default on), Affiliate dropdown (optional), winners count selector (1–25, default 3), multi-select of active tournaments only, and prize selection.
- [ ] Edit screen shows participant guesses list with ability to remove guesses; usernames link to profile.
- [ ] Results button opens Hunt Results screen scoped to selected hunt.

## 7. Hunt Results (`bhg-bonus-hunts-results`)
- [ ] Defaults to latest closed hunt with grey/white alternating rows.
- [ ] Dropdown filters for hunt and time range (`This Month`, `This Year`, `All Time`).
- [ ] Winners highlighted consistently irrespective of winner count.
- [x] Includes “Prize” column (prize title) and “There are no winners yet” empty state.

## 8. Tournaments Admin (`bhg-tournaments`)
- [ ] List table sortable (ID, Title, Start, End, Status) with search and pagination.
- [ ] Row actions: Edit, Close, Results (nonce-protected).
- [ ] Add/Edit form contains Title, Description, Participants Mode (`winners`/`all`), affiliate URL + visibility toggle, and connection settings.
- [x] Bonus hunt connection modes: “All in period” (by date overlap) and “Manual select” (multi-select limited to hunts from the current year plus already linked ones).
- [x] Type field removed; participants mode respected in calculations; edit/close actions functioning.

## 9. Users Admin (`bhg-users`)
- [ ] Table supports search (email/username), sorting, pagination (30/pg).
- [x] Profile includes Real Name, Username, Email, affiliate yes/no fields per affiliate site, with links to user profile from hunts.
- [ ] Admin can remove user guesses from the hunt edit screen.

## 10. Ads Admin (`bhg-ads`)
- [ ] List table includes Actions column with Edit and Remove (nonce protected).
- [ ] Placement dropdown includes `none` option for shortcode-only ads.
- [ ] Visibility rules (logged-in/affiliate) honored.

## 11. Translations & Tools
- [ ] `bhg-translations` screen lists all frontend strings and allows saving overrides.
- [ ] Saved translations override defaults and `.mo` files, with sanitization.
- [ ] `bhg-tools` (if active) displays expected utilities or otherwise hidden.

## 12. Frontend Shortcodes
- [ ] All list shortcodes support sorting, searching, pagination (30/pg), and timeline filters (`this_week`, `this_month`, `this_year`, `last_year`, `all_time`).
- [ ] Affiliate indicators (green/red lights) shown consistently with optional affiliate website links (when allowed).
- [ ] Currency formatting uses `bhg_format_money()`.
- [ ] Shortcodes implemented:
  - `[bhg_user_profile]`, `[bhg_active_hunt]`, `[bhg_guess_form]` (with hunt selector, guessing toggle, submit/edit states, redirect option).
  - `[bhg_tournaments]` without legacy Type column.
  - `[bhg_user_guesses]`, `[bhg_hunts]`, `[bhg_tournaments]`, `[bhg_leaderboards]`, `[bhg_winner_notifications]`, `[bhg_leaderboard]` (legacy), `[bhg_advertising]`.
  - `[bhg_prizes]` with design/size/active filters.
- [ ] Pages created for required shortcodes (`/bonus-hunt/active`, `/bonus-hunt/guess`, etc.).

## 13. Prizes Module (`bhg-prizes`)
- [ ] Admin can add/edit/delete prizes (title, description, category, image, CSS panel, active flag).
- [ ] Image sizes registered (small, medium, big).
- [ ] Bonus hunts allow selecting multiple prizes; tournaments display associated prizes.
- [ ] Frontend displays prizes via grid/carousel with active filter and navigation controls.

## 14. Notifications Module (`bhg-notifications`)
- [ ] Admin screens for Winner, Tournament, and Bonushunt notifications with title, HTML description (sanitized), BCC, and enable/disable switches (default disabled).
- [ ] Emails sent only when enabled; BCC honored.

## 15. User Profile Widgets
- [ ] Admin can toggle visibility of `my_bonushunts`, `my_tournaments`, `my_prizes`, `my_rankings` blocks.
- [ ] Frontend renders each widget with accurate data for the logged-in user.

## 16. Tournament Ranking System
- [ ] Points table configurable for positions 1–8 (default 25/15/10/5/4/3/2/1).
- [ ] Tournament leaderboards use configured points, highlight winners and top-3 rows.
- [ ] Calculations respect winner counts per hunt and tournament scope filters (active/closed/all).

## 17. UX Enhancements
- [ ] Smart login redirect returns users to the originally requested page.
- [ ] Three menu locations (Admins/Mods, Logged-in, Guests) registered and conditionally rendered.
- [ ] Translation load order: admin overrides → `.mo` → defaults.
- [ ] Ads module supports text/link placements with placement = `none` for shortcode-only usage.

## 18. Notifications & Emails
- [ ] Winner calculation based on proximity to final balance.
- [ ] Result and win notifications triggered appropriately, respecting enable flags.

## 19. Social Login
- [ ] Detect Nextend Social Login plugin and expose configuration options without hard dependencies.
- [ ] Social login buttons visible where required when plugin active.

## 20. Performance & QA
- [ ] Pagination and queries optimized to avoid full table scans.
- [ ] Caching and hooks provided so themes can customize output.
- [ ] Manual QA run through acceptance scenarios listed in the spec.

> **Note:** If an item does not apply to the current deployment, document the reason in release notes.
