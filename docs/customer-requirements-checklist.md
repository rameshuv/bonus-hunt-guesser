# Bonus Hunt Guesser — Customer Acceptance Checklist

_Status legend:_ `[ ]` pending verification, `[x]` complete, `[>]` in progress, `[!]` blocked, `[?]` clarification needed.

> Update the status token at the start of each line as work progresses. Each item lists the primary file(s) to inspect when implementing or validating the requirement.

## A. Core Functionality (Bonus Hunts & Guessing)
- [ ] Admin can create a bonus hunt with title, starting balance, number of bonuses, and prize description. (`admin/views/bonus-hunts.php`, `includes/class-bhg-bonus-hunts.php`)
- [ ] Logged-in users can submit a final balance guess between €0 and €100,000 for the active hunt. (`includes/class-bhg-shortcodes.php`, `includes/class-bhg-bonus-hunts.php`)
- [ ] Frontend displays active hunt details (title, starting balance, bonuses count). (`includes/class-bhg-shortcodes.php`, templates)
- [ ] Leaderboard lists all guesses with position, username, and guessed balance. (`includes/class-bhg-shortcodes.php`, frontend assets)

## B. User Profiles & Guessing Enhancements
- [ ] Admin user management includes real name, username, email, and affiliate status fields. (`admin/views/users.php`, `includes/class-bhg-users.php`)
- [ ] Integration detects Nextend Social Login (Google/Twitch/Kick) without hard coupling. (`includes/class-bhg-login-redirect.php`, `includes/helpers.php`)
- [ ] Users may edit their guesses while the hunt remains open. (`includes/class-bhg-bonus-hunts.php`, `includes/class-bhg-shortcodes.php`)
- [ ] Leaderboard shows affiliate indicator (green for affiliates, red for non-affiliates). (`includes/class-bhg-shortcodes.php`, `assets/css/frontend.css`)
- [ ] Guess table supports sorting by position, username, and guess amount with pagination. (`includes/class-bhg-shortcodes.php`, JS helpers)

## C. Tournament & Leaderboard System
- [ ] Admin can create tournaments with title, description, schedule (monthly/yearly/quarterly/all-time). (`admin/views/tournaments.php`, `includes/class-bhg-tournaments.php`)
- [ ] Tournament leaderboard exposes sortable columns (position, username, wins) and filters (week/month/year). (`includes/class-bhg-shortcodes.php`)
- [ ] Historical tournament data accessible alongside current standings. (`includes/class-bhg-shortcodes.php`, `admin/views/tournaments.php`)

## D. Frontend Leaderboard Enhancements
- [ ] Leaderboard interface provides tabs for Overall, Monthly, Yearly, and All-Time best guessers. (`includes/class-bhg-shortcodes.php`, frontend scripts)
- [ ] Tabs expose history across previous bonus hunts. (`includes/class-bhg-shortcodes.php`, templates)

## E. User Experience Improvements
- [ ] Smart login redirect returns users to the page that required authentication. (`includes/class-bhg-login-redirect.php`)
- [ ] Three WordPress menu locations registered (Admins/Mods, Logged-in, Guests) with styling guidance. (`includes/helpers.php`, `assets/css/frontend.css`, `docs/`)
- [ ] Translations admin tab lists all plugin strings with override capability. (`admin/views/translations.php`, `includes/helpers.php`)

## F. Affiliate Adjustment / Upgrade
- [ ] Admin CRUD for multiple affiliate websites (add/edit/delete). (`admin/views/affiliate-websites.php`, `includes/class-bhg-affiliates.php`)
- [ ] Bonus hunt edit form includes affiliate dropdown selection. (`admin/views/bonus-hunts.php`)
- [ ] User profile shows affiliate yes/no per affiliate site. (`admin/views/users.php`)
- [ ] Frontend guess tables and ad targeting respect per-affiliate status. (`includes/class-bhg-shortcodes.php`, `admin/views/advertising.php`)

## G. Final Enhancements & Polish
- [ ] Winner ranking uses closest final-balance difference. (`includes/class-bhg-bonus-hunts-helpers.php`)
- [ ] Email notifications announce results and wins when enabled. (`includes/class-bhg-notifications.php`, `admin/views/notifications.php`)
- [ ] Performance fixes and bug resolutions documented in changelog. (`CHANGELOG.md`)
- [ ] Bonus Hunt admin inputs use required border styling. (`assets/css/admin.css`)
- [ ] Advertising module allows text/link ads, placement control (including footer), login/affiliate visibility, and `none` placement for shortcode use. (`admin/views/advertising.php`)

## H. Backend Admin Adjustments (Customer Addendum)
- [ ] Main submenu renamed from "Bonushunt" to "Dashboard". (`admin/class-bhg-admin.php`)
- [ ] Dashboard shows "Latest Hunts" table with three hunts, all winners (up to 25) with guesses and differences, plus start/final balance and closed date columns. (`admin/views/dashboard.php`)
- [ ] Bonus Hunts list adds Final Balance column ("-" if open), Results action, participant list with removal controls, and winner count configuration (1–25). (`admin/views/bonus-hunts.php`)
- [ ] Hunt Results admin ranks guesses best-to-worst, highlights winners, includes Prize column, and defaults to most recent closed hunt with filters. (`admin/views/bonus-hunts-results.php`)
- [ ] Tournaments admin restores title/description fields, supports type choices (monthly/quarterly/yearly/all-time), removes redundant period field, and fixes edit flow. (`admin/views/tournaments.php`, `includes/class-bhg-tournaments.php`)
- [ ] Users admin supports search, sorting, and pagination (30 per page). (`admin/views/users.php`)
- [ ] Ads admin includes Actions column (Edit/Remove) with nonce protection and `none` placement option. (`admin/views/advertising.php`)
- [ ] Translations and Tools admin screens display meaningful data per attachments. (`admin/views/translations.php`, `admin/views/tools.php`)

## I. Versioning, Tooling & Compliance
- [ ] Plugin header metadata updated to final contract values in `bonus-hunt-guesser.php` (Requires PHP 7.4, WordPress 6.3.0+, MySQL 5.5.5, version 8.0.14).
- [ ] CHANGELOG.md and docs reflect version 8.0.14 release scope. (`CHANGELOG.md`, `README.md`)
- [ ] Database migrations remain MySQL 5.5.5 compatible via `dbDelta()` and helper guards. (`includes/class-bhg-db.php`)
- [ ] PHPCS WordPress Core/Docs/Extra standard enforced via `vendor/bin/phpcs --standard=phpcs.xml`. (`phpcs.xml`)
- [ ] PHPUnit bootstrap with WordPress stubs executes `vendor/bin/phpunit` suite. (`tests/bootstrap.php`, `tests/*`)

## J. Documentation & Onboarding
- [ ] Required WordPress pages and associated shortcodes documented for site setup. (`README.md`, `docs/`)
- [ ] Onboarding guide covers menu assignments, translations workflow, affiliate usage, and notifications configuration. (`docs/`, `README.md`)

