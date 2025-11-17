# Final checklist – 2025-11-17

## Runtime targets
- PHP 7.4
- WordPress 6.3.5
- MySQL 5.5.5+
- Text domain: `bonus-hunt-guesser`

## Tests executed
- `composer install --no-interaction`
- `./vendor/bin/phpunit`
- `./vendor/bin/phpcs --standard=phpcs.xml --report=summary` *(still failing; see remediation plan below)*

## Conflict check
- `git status` shows a clean working tree before changes and no merge conflicts were detected.

## Outstanding findings and remediation map
- **WordPress Coding Standards**: `phpcs` still reports violations. Highest-volume files to prioritize:
  - `includes/class-bhg-shortcodes.php` (formatting/i18n/sanitization)
  - `admin/class-bhg-admin.php` (admin output escaping)
  - `includes/helpers.php` (debug calls and unused parameters)
  - `tests/bootstrap.php` (test helpers not matching standards)
- **Responsiveness review**: Frontend tables rely on `.bhg-table-wrapper` and mobile stacking rules in `assets/css/public.css`. Revalidate leaderboard/tournament/bonushunt outputs on sub-782px widths and tighten any overflow edge cases if found.
- **Security/i18n**: Ensure future edits keep capability checks, nonce verification, and `esc_html__`/`esc_attr__` translations intact throughout the admin views listed above.

## Next steps requested by customer
- Finish PHPCS cleanup for the files listed above until `phpcs --standard=phpcs.xml` passes cleanly.
- Re-test frontend responsiveness after any template or CSS change.
- Re-run PHPUnit and PHPCS after fixes.
