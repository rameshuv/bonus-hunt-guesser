# Bonus Hunt Guesser – Code Review Findings (2024-09-09)

## WordPress Coding Standards

* `vendor/bin/phpcs --standard=phpcs.xml --report=summary` reports 5,069 errors and 1,095 warnings across 38 PHP files. The worst offenders are `bonus-hunt-guesser.php`, `admin/class-bhg-admin.php`, and `includes/class-bhg-shortcodes.php`. Address these first before iterating through the remaining files. 【2653fb†L1-L33】

## Requirement Alignment

* Plugin metadata and the `BHG_VERSION` constant both advertise version `8.0.13`, matching the agreed release identifier. 【F:bonus-hunt-guesser.php†L1-L16】【F:bonus-hunt-guesser.php†L141-L150】
* The Bonus Hunt results timeframe dropdown currently offers "This Month", "This Year", "Last Year", and "All Time". The customer asked for a reduced set (This Month default, This Year, All Time), so remove the "Last Year" option to stay within scope. 【F:admin/views/bonus-hunts-results.php†L171-L189】
* When "All Time" is selected, the results admin view runs two unprepared SQL queries (`SELECT id, title FROM {$hunts_table}` and `SELECT id, title FROM {$tour_table}`) that violate WordPress coding standards. Wrap these in `$wpdb->prepare()` or otherwise justify the direct query usage. 【F:admin/views/bonus-hunts-results.php†L162-L167】
* Currency selection and formatting features are present: the settings screen exposes an EUR/USD dropdown and helpers convert stored amounts into formatted strings. Verify values persist end-to-end before release. 【F:admin/views/settings.php†L16-L115】【F:includes/helpers.php†L780-L805】

## Recommended Remediation Checklist

| Task | Files |
|------|-------|
| Fix PHPCS violations, starting with the three highest-error files, then continue through the remaining offenders listed in the summary report. | `bonus-hunt-guesser.php`, `admin/class-bhg-admin.php`, `includes/class-bhg-shortcodes.php`, plus remaining files in the PHPCS summary. 【2653fb†L1-L33】|
| Align the advertised plugin version with the agreed release number. | `bonus-hunt-guesser.php`, `CHANGELOG.md` (once updated). 【F:bonus-hunt-guesser.php†L1-L16】|
| Limit the results timeframe dropdown to the requested options. | `admin/views/bonus-hunts-results.php`. 【F:admin/views/bonus-hunts-results.php†L171-L189】|
| Add prepared statements (or otherwise documented sanitisation) around the “All Time” queries in the results view. | `admin/views/bonus-hunts-results.php`. 【F:admin/views/bonus-hunts-results.php†L162-L167】|

