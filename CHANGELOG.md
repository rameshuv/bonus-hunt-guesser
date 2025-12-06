
## [Stage-1] Hygiene & Safety — 2025-09-03

- Standardized sanitization of `$_GET` / query args for leaderboard: `orderby`, `order`, `paged`, `hunt_id`.
- Added server-side pagination links to `[bhg_leaderboard]` when total > per_page (uses `paginate_links()`).
- Minor hardening in `[bhg_guess_form]` guest redirect URL construction (sanitizes host/URI).
- Note: For MySQL 5.5.5 in strict mode, `DATETIME` defaults of `0000-00-00 00:00:00` may require disabling NO_ZERO_DATE / NO_ZERO_IN_DATE or adjusting defaults.

# Changelog

## 8.0.23 — 2025-02-21
- Updated plugin metadata and internal version constant to 8.0.23 for the
  requested runtime.
- Synced delivery and order checklists to reflect the 8.0.23 release details.
- Added a README delivery quick-answer note pointing to the 8.0.23 reply
  template and evidence expectations.
- Documented a rapid verification pass in the delivery approval guide to
  answer "can we deliver now?" with on-page badge, CTA, and results checks.

## 8.0.22 — 2025-02-20
- Updated plugin metadata and internal version constant to 8.0.22.
- Allowed desktop table wrappers to show horizontal overflow while retaining
  mobile scroll behaviour for leaderboard, tournament, and bonus hunt tables.

## 8.0.20 — 2025-02-10
- Bumped plugin metadata and internal version constant to 8.0.20 for the
  bug-fix-only release.
- Added explicit HTML comments when ad and jackpot shortcodes cannot render so
  editors can diagnose missing IDs, table data, or disabled settings directly
  from the page output.
- Fixed leaderboard win totals so duplicate tournament mappings no longer
  double-count "Times Won" and so affiliate website filters resolve site names
  reliably.
- Standardized filter layouts and button styling across leaderboards,
  tournaments, bonus hunts, and the user guess form, ensuring horizontal
  alignment and matching tournament dropdown visuals.
- Hardened pagination markup for shortcode listings to restore clickable page
  navigation links.

## 8.0.18 — 2025-02-06
- Bumped plugin metadata and internal version constant to 8.0.18 to match the
  current customer requirement baseline.
- Refreshed README highlights and acceptance checklist text to align with the
  8.0.18 release designation.

## 8.0.16 — 2025-01-24
- Added the Jackpot management module with admin CRUD, hunt-close integration, and the `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, and `[bhg_jackpot_winners]` shortcodes.
- Introduced jackpot database tables with idempotent `dbDelta()` migrations covering jackpots and jackpot event logs.
- Extended the admin “Info & Help” shortcodes catalog and translation strings to document the jackpot feature set.

## 8.0.14 — 2025-10-27
- Added `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, and `[my_rankings]` shortcodes that honour the new My Profile visibility toggles and reuse the global typography settings.
- Implemented the configurable tournament points system with ranking scope controls and ensured recalculations persist both points and wins for admin/front-end displays.
- Synced affiliate website CRUD with user profile metadata so new sites immediately expose checkboxes and deletions clean up user assignments.
- Extended the settings screen with global typography/color controls and wired inline CSS so front-end blocks reflect the configured styles.
- Protected uninstall by respecting the “remove data on uninstall” option and refreshed release notes to match the 8.0.14 requirements.

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

