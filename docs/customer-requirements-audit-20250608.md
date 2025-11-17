# Customer Requirements Audit (2025-06-08)

## Confirmed coverage
- **Runtime & text domain:** Plugin header declares PHP 7.4, WordPress 6.3.5 minimums, MySQL 5.5.5 compatibility, GPL license, and correct text domain path.【F:bonus-hunt-guesser.php†L3-L15】
- **Admin navigation:** The primary BHG menu now uses a Dashboard label for the root submenu entry, with dedicated entries for bonus hunts, prizes, jackpots, tournaments, users, affiliates, advertising, translations, shortcodes, database, and settings as requested.【F:admin/class-bhg-admin.php†L53-L96】
- **Latest Hunts widget:** Dashboard shows the latest hunts with all winners, starting/final balances, and closed timestamps, matching the “Latest Hunts” requirement and multi-winner visibility.【F:admin/views/dashboard.php†L111-L180】
- **Bonus hunts admin list:** List view supports search, pagination, sortable columns (including final balance), and exposes a “Results” action per hunt so closed hunts can be reviewed with highlighted winners.【F:admin/views/bonus-hunts.php†L33-L144】【F:admin/views/bonus-hunts.php†L265-L276】
- **Tournament admin form:** Title, description, participant mode, type options (weekly/monthly/quarterly/yearly/all time), number of winners, ranking scope, prizes, and affiliate site selection are present in the add/edit form, addressing the admin-field omissions noted by the customer.【F:admin/views/tournaments.php†L300-L373】【F:admin/views/tournaments.php†L382-L430】
- **Advertising placements:** Placement dropdown includes a `none` option for shortcode-only ads alongside footer/bottom/sidebar choices, aligning with the customer’s request for a “none” placement and edit/remove flows.【F:admin/views/advertising.php†L150-L190】
- **Translations/tooling:** Translations screen initializes the translations table, supports search/pagination, and provides save handling, addressing the previously empty view concern for translations/tools.【F:admin/views/translations.php†L21-L80】

## Outstanding verification / gaps
- **PHPCS compliance and unit coverage** were not exercised in this review; the customer requires WordPress-Core/Docs/Extra rules to pass, so a full PHPCS run is still needed to confirm coding-standard compliance.
- **Frontend leaderboard/tournament behaviors** (e.g., username capitalization, rounding of averages, prize box display, dropdown filter hiding, and correct user counts) rely on runtime data paths not covered by automated tests; targeted functional tests or manual validation against sample tournaments/bonushunts are required to conclusively confirm those requirements. Key logic lives in `includes/class-bhg-shortcodes.php` for future adjustments.
- **Version/changelog alignment** with the requested 8.0.16/8.0.18 bump is not documented outside the plugin header; ensure changelog/readme entries match the shipped version when finalizing release notes.

## Next steps if rectifications are needed
- Run PHPCS with the WordPress-Core, WordPress-Docs, and WordPress-Extra standards and address any reported issues (repository-wide, especially `bonus-hunt-guesser.php` and `includes/` classes).
- Add focused functional tests or manual QA scripts for leaderboard/tournament shortcode outputs to validate the customer’s specific formatting and filtering behaviors in `includes/class-bhg-shortcodes.php`.
- Update `CHANGELOG.md` and `README.md` with the final version number and any migration notes once verification is complete.
