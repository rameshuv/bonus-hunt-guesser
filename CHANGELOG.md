
## [Stage-1] Hygiene & Safety — 2025-09-03

- Standardized sanitization of `$_GET` / query args for leaderboard: `orderby`, `order`, `paged`, `hunt_id`.
- Added server-side pagination links to `[bhg_leaderboard]` when total > per_page (uses `paginate_links()`).
- Minor hardening in `[bhg_guess_form]` guest redirect URL construction (sanitizes host/URI).
- Note: For MySQL 5.5.5 in strict mode, `DATETIME` defaults of `0000-00-00 00:00:00` may require disabling NO_ZERO_DATE / NO_ZERO_IN_DATE or adjusting defaults.

# Changelog

## 8.0.14 — 2025-09-16
- Updated plugin header metadata (version 8.0.14, WordPress 6.3.0 minimum, MySQL requirement) and synced loader constants.
- Added dedicated Shortcodes and Notifications admin pages, including saved notification templates for hunts, winners, and tournaments.
- Implemented automated email dispatch when hunts and tournaments close with support for template tags and BCC recipients.
- Updated plugin header description to match the customer delivery specification.
- Normalized `load_plugin_textdomain()` path to match WordPress recommendations.
- Ensured the automatic “Bonus Hunt” submenu entry displays the translated Dashboard label in wp-admin.
- Limited the Bonus Hunt edit tournament selector to active tournaments (while preserving already-linked selections).
- Filtered the tournament edit Connected Bonus Hunts selector to current-year hunts while always including already-linked entries.
- Added per-affiliate website toggles to the Users admin list and user profile, syncing assignments when affiliate sites are removed.
- Added default prize layout and card size settings that drive active hunt prize rendering and `[bhg_prizes]` defaults so prizes surface automatically on active hunts.
- Introduced a global CSS/color panel with new inline styling that standardizes title blocks, headings, and description typography across frontend shortcodes.

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

