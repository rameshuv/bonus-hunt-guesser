
## [Stage-1] Hygiene & Safety — 2025-09-03

- Standardized sanitization of `$_GET` / query args for leaderboard: `orderby`, `order`, `paged`, `hunt_id`.
- Added server-side pagination links to `[bhg_leaderboard]` when total > per_page (uses `paginate_links()`).
- Minor hardening in `[bhg_guess_form]` guest redirect URL construction (sanitizes host/URI).
- Note: For MySQL 5.5.5 in strict mode, `DATETIME` defaults of `0000-00-00 00:00:00` may require disabling NO_ZERO_DATE / NO_ZERO_IN_DATE or adjusting defaults.

# Changelog

## 8.0.14 — 2025-09-20
- Added dedicated `bhg_currency` option with updated helpers to standardize currency formatting across admin and frontend views.
- Refreshed admin dashboard “Latest Hunts” widget to list each winner on its own row with bold usernames and consistent money formatting.
- Extended hunt results with a Prize column and neutral winner highlighting that aligns with the grey/white row standard.
- Simplified the Latest Hunts data fetch to rely on existing winner helpers while keeping all rendering WPCS-compliant and avoiding duplicate queries per row.
- Hardened settings and guess submission handlers by sanitizing toggle inputs and redirect targets while aligning the bootstrap file with WordPress coding standards.

## 8.0.11 — 2025-09-14
- Version bump.

## 8.0.10 — 2025-09-12
- Bump version to 8.0.10.
- Set minimum WordPress version to 5.5.5.

## 8.0.06 — 2025-09-05
- Removed legacy deprecated database layer (deprecated/ directory and includes/deprecated-db.php).

## 8.0.05 — 2025-09-03
- Fix: Affiliate Websites edit query querying wrong table (now selects by `id`).
- Security: Server-side enforcement — guesses can only be added/edited while a hunt is `open`.
- Feature: New `[bhg_best_guessers]` shortcode with tabs (Overall, Monthly, Yearly, All-Time).
- UX: Ensure leaderboard affiliate indicators (green/red) always render via CSS.
- Admin: Minor coding-standards cleanups and nonces/cap checks verified.

