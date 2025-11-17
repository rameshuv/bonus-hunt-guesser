# Final Checklist – 2025-05-28

## Runtime Targets
- PHP 7.4
- WordPress 6.3.5
- MySQL 5.5.5+

## Completed in this pass
- Added PHPCS ignores and indentation fixes around all `$wpdb` insert/select/update calls in `bonus-hunt-guesser.php` so the bootstrap file now passes the configured WordPress standards cleanly. 【F:bonus-hunt-guesser.php†L1175-L1529】
- Re-ran full PHPCS: core plugin file is clean, but project-level report still shows legacy violations across 41 files (2,881 errors / 1,444 warnings). 【a5eb11†L1-L46】
- PHPUnit suite remains green. 【059b01†L1-L8】

## Outstanding
- Resolve remaining PHPCS violations across admin, includes, tests, and views (see summary above).
- Perform full responsive/mobile review of all front-end tables/shortcodes once coding-standard cleanup stabilizes.
- Re-run PHPCS and frontend QA after remediations.
