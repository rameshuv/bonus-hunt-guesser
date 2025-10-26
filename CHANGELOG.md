
## [Stage-1] Hygiene & Safety — 2025-09-03

- Standardized sanitization of `$_GET` / query args for leaderboard: `orderby`, `order`, `paged`, `hunt_id`.
- Added server-side pagination links to `[bhg_leaderboard]` when total > per_page (uses `paginate_links()`).
- Minor hardening in `[bhg_guess_form]` guest redirect URL construction (sanitizes host/URI).
- Note: For MySQL 5.5.5 in strict mode, `DATETIME` defaults of `0000-00-00 00:00:00` may require disabling NO_ZERO_DATE / NO_ZERO_IN_DATE or adjusting defaults.

# Changelog

## 8.0.14 — 2025-09-20
- Bump plugin version constant and header metadata to 8.0.14.
- Raise minimum supported WordPress version to 6.3.0 to match customer runtime target.
- Refresh the Bonus Hunt dashboard with a "Latest Hunts" summary that lists every winner per hunt alongside balances and closure timestamps.
- Extend bonus hunt admin tools with configurable winner counts, results exports that highlight winners, and a Price column populated from linked prizes.
- Restore the tournaments admin form (title/description/type), add quarterly/all-time periods, and filter connected hunts to the current year while keeping existing links selectable.
- Expand the Users admin to include search, sortable columns, pagination, and per-affiliate website toggles that stay in sync with the Affiliates module.
- Add edit/remove actions to the Advertising table and support a "None" placement for shortcode-only banners.

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

