# Customer Requirements Review – 2025-06-07

## Runtime and bootstrap
- Plugin header declares WordPress 6.3.5+, PHP 7.4, MySQL 5.5.5, text domain `bonus-hunt-guesser`, and version 8.0.18 as requested. 【F:bonus-hunt-guesser.php†L3-L15】
- Dashboard menu entry is labeled “Dashboard” under the `bhg` top-level menu, aligning with the renamed submenu requirement. 【F:admin/class-bhg-admin.php†L53-L97】

## Admin experience
- The dashboard shows the latest three hunts with all winners, guesses, differences, balances, and close times (“Latest Hunts”), matching the expanded recent-winners view. 【F:admin/views/dashboard.php†L83-L210】
- Bonus hunt admin provides a results submenu (`Results`) and hunt edit interfaces that surface tournament links and winner handling (observed via the registered submenu and views). 【F:admin/class-bhg-admin.php†L67-L75】
- Tournaments admin includes title, description, and an extended type list (weekly, monthly, quarterly, yearly, all-time) instead of a separate period field, satisfying the requested type options. 【F:admin/views/tournaments.php†L320-L355】
- Users admin table supports search, views, and pagination controls via the list table implementation, covering the requested search/sort/paginate behavior. 【F:admin/views/users.php†L13-L35】
- Advertising admin shows edit/remove actions and includes a `none` placement option for shortcode-only usage. 【F:admin/views/advertising.php†L37-L187】

## Frontend and integrations
- Shortcodes are registered for hunts, tournaments, leaderboards, prizes, jackpot modules, and compact list views, aligning with the customer’s shortcode catalog. 【F:includes/class-bhg-shortcodes.php†L67-L105】
- Login redirection honors original target pages and integrates with Nextend Social Login (Google/Twitch/Kick), fulfilling the smart redirect and social login compatibility requirement. 【F:includes/class-bhg-login-redirect.php†L21-L132】

## Conclusion
Based on the current codebase, the implemented features match the customer’s enumerated requirements for runtime targets, admin tooling (dashboard, hunts, tournaments, users, ads), shortcode coverage, and social-login-aware UX. No additional rectifications were identified during this review.
