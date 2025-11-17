# Final QA Checklist (2025-05-21)

- [x] Plugin header declares the agreed runtime targets (WP 6.3.5, PHP 7.4, MySQL 5.5.5) and text domain `bonus-hunt-guesser`. See `bonus-hunt-guesser.php` header.
- [x] Ran PHPCBF with the WordPress ruleset to auto-fix coding standards across key controllers, views, helpers, and tests.
- [ ] PHPCS clean: still failing with 2,867 errors and 1,459 warnings across 42 files after autofix; see the latest summary for remaining work.
- [x] PHPUnit test suite passes (12 tests).
- [ ] Functional gap review against the full customer requirement set is still outstanding; many checklist items remain unverified/untouched in this pass.
