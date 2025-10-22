# Bonus Hunt Guesser — Customer Requirements Checklist

_Status legend:_ `[ ]` pending verification, `[x]` complete, `[>]` in progress, `[!]` blocked, `[?]` clarification needed.

> Update the status token at the start of each line as work progresses. Leave implementation notes so the next engineer knows where to look.

## 0. Plugin Header
- [ ] Header metadata matches customer contract (`bonus-hunt-guesser.php`).
- [ ] Readmes and docs repeat the same header values (`README.md`, `README-Stage-*.txt`, `docs/`).

## 1. Security & Architecture
- [ ] Gate every privileged action behind `manage_options` or stricter caps (`admin/class-bhg-admin.php`, `admin/views/*`).
- [ ] Add nonces to create/edit/delete/toggle/close/results forms (`admin/views/*`).
- [ ] Sanitize inbound parameters and escape outbound strings (`includes/helpers.php`, `admin/views/*`).
- [ ] Ensure direct SQL always uses `$wpdb->prepare()` and key columns are indexed (`includes/class-bhg-db.php`, `includes/class-bhg-bonus-hunts.php`).
- [ ] Paginate large admin tables at 30 rows per page with `bhg_per_page` filter support (`admin/views/*`).

## 2. Database (MySQL 5.5.5 Safe)
- [ ] `wp_bhg_bonus_hunts` contains `guessing_enabled` TINYINT(1) default 1 and optional `affiliate_id` (`includes/class-bhg-db.php`).
- [ ] `wp_bhg_tournaments` uses `participants_mode` varchar(10) default `winners` and removes legacy `type` column (`includes/class-bhg-db.php`).
- [ ] Junction table `wp_bhg_tournaments_hunts` has PK, indexes, and UNIQUE pair (`includes/class-bhg-db.php`).
- [ ] `dbDelta()` migrations remain idempotent and 5.5.5-compatible (no unsupported features).

## 3. Settings — Currency
- [ ] `bhg_currency` option stores `EUR`/`USD` with default `EUR` (`includes/helpers.php`).
- [ ] Helpers `bhg_currency_symbol()` / `bhg_format_money()` implemented and reused globally (`includes/helpers.php`, `includes/class-bhg-shortcodes.php`).
- [ ] Settings screen saves and sanitizes currency selection (`admin/views/settings.php`).
- [ ] Currency change propagates to admin lists, results, and frontend output (`admin/views/*`, `includes/class-bhg-shortcodes.php`).

## 4. Admin — Global “bhg” Dashboard
- [ ] Rename submenu from "Bonushunt" to "Dashboard" (`admin/class-bhg-admin.php`).
- [ ] Latest Hunts card shows three hunts, each winner on its own row, username bold (`admin/views/dashboard.php`).
- [ ] Table columns: Bonushunt Title, Winners (guess + difference), Start Balance, Final Balance, Closed At (`admin/views/dashboard.php`).
- [ ] Ensure start/final balance left aligned and performance acceptable for large datasets (`admin/views/dashboard.php`).

## 5. Admin — Bonus Hunts (`bhg-bonus-hunts`)
- [ ] List table sortable by ID/Title/Start/Final Balance/Status with search and pagination (`admin/views/bonus-hunts.php`).
- [ ] Columns include Affiliate, Actions, Admin Action (Delete only) plus Final Balance with `-` for open hunts (`admin/views/bonus-hunts.php`).
- [ ] Row actions expose Edit, Results, Enable/Disable Guessing with nonce protection (`admin/views/bonus-hunts.php`).
- [ ] Add/Edit form: enable guessing checkbox default on, affiliate dropdown, winners count 1–25 default 3 (`admin/views/bonus-hunts.php`).
- [ ] Multi-select shows only active tournaments (`admin/views/bonus-hunts.php`).
- [ ] Edit screen lists participants with ability to remove guesses; usernames link to profile (`admin/views/bonus-hunts.php`, `admin/views/users.php`).
- [ ] Results button opens scoped Hunt Results view (`admin/views/bonus-hunts.php`).

## 6. Admin — Hunt Results (`bhg-bonus-hunts-results`)
- [ ] Default view loads latest closed hunt (`admin/views/bonus-hunts-results.php`).
- [ ] Dropdown filters: Hunt + Time Filter (This Month default, This Year, All Time) limiting hunt list (`admin/views/bonus-hunts-results.php`).
- [ ] Ranked table sorted by smallest absolute difference, highlighting winners with neutral grey/white rows (`admin/views/bonus-hunts-results.php`, `assets/css/admin.css`).
- [ ] Empty state message "There are no winners yet." shown when applicable (`admin/views/bonus-hunts-results.php`).
- [ ] Include Prize column showing prize title (`admin/views/bonus-hunts-results.php`).

## 7. Admin — Tournaments (`bhg-tournaments`)
- [ ] List table sortable by ID/Title/Start/End/Status with search + pagination (`admin/views/tournaments.php`).
- [ ] Row actions: Edit | Close | Results, each nonce-protected (`admin/views/tournaments.php`).
- [ ] Add/Edit contains Title, Description, Participants mode (`winners|all`), removes legacy type (`admin/views/tournaments.php`).
- [ ] Connected Bonus Hunts: Mode A "All in period" and Mode B "Manual select" with current-year limit (`admin/views/tournaments.php`).
- [ ] Affiliate fields: website URL + "Show in frontend?" checkbox default checked (`admin/views/tournaments.php`).
- [ ] Close/Results flows confirmed post-schema change (`admin/class-bhg-admin.php`).

## 8. Admin — Users (`bhg-users`)
- [ ] Search by email/username, sortable columns, pagination 30/pg (`admin/views/users.php`).
- [ ] Usernames link to profile editing view (`admin/views/users.php`).
- [ ] Profile shows Real Name, Username, Email, affiliate yes/no per site with ability to remove guesses (`admin/views/users.php`, `admin/views/bonus-hunts.php`).

## 9. Admin — Ads (`bhg-ads`)
- [ ] Table includes Actions column with Edit and Remove (nonce-protected) (`admin/views/advertising.php`).
- [ ] Placement dropdown includes `none` for shortcode-only ads (`admin/views/advertising.php`).
- [ ] Ads support text + link, visibility rules by login and affiliate status (`admin/views/advertising.php`).

## 10. Admin — Translations (`bhg-translations`)
- [ ] Admin screen lists all frontend display strings as editable keys (`admin/views/translations.php`).
- [ ] Saved translations override defaults via `.mo`, falling back appropriately (`includes/helpers.php`, `languages/`).

## 11. Frontend Shortcodes (Unified Rules)
- [ ] All list shortcodes implement sorting, search, pagination (30/pg), timeline filters (`includes/class-bhg-shortcodes.php`).
- [ ] Affiliate lights show green/red with optional website link respecting settings (`includes/class-bhg-shortcodes.php`, `assets/css/admin.css`).
- [ ] `[bhg_user_profile]` displays profile block with affiliate statuses (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_active_hunt]` handles multiple active hunts, guessing state, paginated guesses (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_guess_form]` supports optional `hunt_id`, button toggles Submit/Edit, honors `guessing_enabled`, redirect configurable (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_tournaments]` list excludes legacy Type column and includes Title (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_winner_notifications]` renders notification center (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_leaderboard]` maintained for back-compat (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_user_guesses]` exposes id/aff/website filters and Difference column, sorts by time if hunt open (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_hunts]` handles status/timeline filters with Winners count (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_tournaments]` extended filters (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_leaderboards]` advanced filters + metrics (times won, average positions) (`includes/class-bhg-shortcodes.php`).
- [ ] `[bhg_advertising]` respects status/ad parameters (`includes/class-bhg-shortcodes.php`).

## 12. Pages to Create (Shortcode Wiring)
- [ ] Document creation of required pages with provided shortcodes (`README.md` or onboarding doc).
- [ ] Confirm setup script or documentation explains attribute overrides (`docs/`).

## 13. UX & Glue
- [ ] Implement smart login redirect returning users to attempted page (`includes/helpers.php`, `includes/class-bhg-login-redirect.php`).
- [ ] Register three menu locations (Admins/Mods, Logged-in, Guests) with WordPress menu system (`includes/helpers.php`).
- [ ] Document menu usage and styling guidance (`README.md`, `assets/css/`).
- [ ] Translation loading order: admin overrides → `.mo` → defaults (`includes/helpers.php`).
- [ ] Ads respect placement `none` for shortcode-only scenarios (`admin/views/advertising.php`).

## 14. Acceptance Test Checklist
- [ ] Currency change reflected globally (manual QA script in `docs/QA.md` or similar).
- [ ] Hunts list sorting/search/pagination + guessing toggle verified.
- [ ] Results admin default hunt selection + filters + Prize column validated.
- [ ] Tournament participants mode, Close/Results actions re-tested.
- [ ] Users admin search/sort/pagination + affiliate fields confirmed.
- [ ] Ads edit/remove + placement `none` tested.
- [ ] Translations admin end-to-end save + frontend override tested.
- [ ] Frontend shortcode suite smoke-tested for sorting, filters, affiliate lights, currency format.

## 15. Versioning & Delivery
- [ ] Version constant bumped to 8.0.14 and CHANGELOG updated (`bonus-hunt-guesser.php`, `CHANGELOG.md`).
- [ ] Migration guard + rollback instructions documented (`UPGRADE_NOTES.txt`, `docs/`).

## 16. Third-Party Integrations
- [ ] Detect Nextend Social Login plugin and enable related options without hard coupling (`includes/class-bhg-login-redirect.php`).
- [ ] Verify license messaging/documentation for customer-owned plugin (`README.md`).

## 17. Developer Notes
- [ ] Use `WP_List_Table` for admin grids (hunts, tournaments, users, ads, prizes, notifications) (`admin/views/*`).
- [ ] Shared partials organized under `/admin/views/partials/` where applicable (`admin/views/partials/`).
- [ ] Admin JS/CSS enqueued only on plugin pages (`admin/class-bhg-admin.php`).
- [ ] Pagination constant `BHG_PER_PAGE` = 30 with `bhg_per_page` filter (`includes/helpers.php`).
- [ ] Provide hooks/filters for theme overrides without modifying core plugin files (`includes/helpers.php`).

## 18. Add-On: Prizes (`bhg-prizes`)
- [ ] Admin CRUD for prizes with Title, Description, Category, Image, CSS panel, Active flag (`admin/views/prizes.php`, `includes/class-bhg-prizes.php`).
- [ ] Register image sizes small/medium/big (`includes/class-bhg-prizes.php`).
- [ ] Bonus Hunt edit screen allows selecting one or multiple prizes (`admin/views/bonus-hunts.php`).
- [ ] Tournament admin/frontend display associated prizes (`admin/views/tournaments.php`, `includes/class-bhg-shortcodes.php`).
- [ ] Frontend prizes render as grid/carousel with dots/arrows and respect active filter (`includes/class-bhg-shortcodes.php`, assets).
- [ ] `[bhg_prizes]` shortcode supports category/design/size/active attributes (`includes/class-bhg-shortcodes.php`).

## 19. Add-On: Notifications (`bhg-notifications`)
- [ ] Notifications admin tab with Winner/Tournament/Bonushunt sections, each with Title, HTML Description, BCC, enable checkbox (`admin/views/notifications.php`).
- [ ] Emails send only when enabled and sanitize HTML via `wp_kses_post()` (`includes/class-bhg-notifications.php`).
- [ ] Respect BCC field when dispatching notifications (`includes/class-bhg-notifications.php`).

## 20. User Shortcodes (Profile Widgets)
- [ ] Provide admin toggles to hide/show each profile block (`admin/views/settings.php`).
- [ ] Implement `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, `[my_rankings]` shortcodes (`includes/class-bhg-shortcodes.php`).
- [ ] Ensure profile page wiring instructions updated (`README.md`).

## 21. Tournament Ranking System (Points)
- [ ] Editable points table (default 25/15/10/5/4/3/2/1) scoped by hunt state (`admin/views/settings.php` or dedicated view).
- [ ] Tournament results respect configured winners per hunt when assigning points (`includes/class-bhg-bonus-hunts-helpers.php`).
- [ ] Leaderboards highlight winners and top 3 extra emphasis (`includes/class-bhg-shortcodes.php`, CSS).

## 22. Admin Refinements (Recap)
- [ ] Dashboard, Bonus Hunts, Tournaments, Users, Affiliates reflect specified refinements (`admin/views/*`).
- [ ] Affiliate site CRUD syncs user meta when sites added/removed (`admin/views/affiliate-websites.php`, `includes/helpers.php`).

## 23. Shortcodes Admin (`bhg-shortcodes` Menu)
- [ ] Create menu item showing all shortcode options, attributes, and examples (`admin/views/shortcodes.php`).

## 24. PHPCS & Tooling
- [ ] Enforce `vendor/bin/phpcs --standard=phpcs.xml` on commit or CI (`phpcs.xml`).
- [ ] Maintain temporary baseline exclusions and schedule cleanup (`phpcs.xml`).

## Customer Addendum – User Profiles & Guessing Enhancements
- [ ] Admin can manage Real Name, Username, Email, Affiliate Status fields (`admin/views/users.php`).
- [ ] Affiliate light indicator (green/red) on frontend leaderboard (`includes/class-bhg-shortcodes.php`, CSS).
- [ ] Integrate Nextend Social Login (Google/Twitch/Kick) detection hooks (`includes/class-bhg-login-redirect.php`).
- [ ] Allow users to alter guesses while hunt open (`includes/class-bhg-shortcodes.php`, `includes/class-bhg-bonus-hunts.php`).
- [ ] Guess table sortable (position, username, balance) with pagination (`includes/class-bhg-shortcodes.php`).

## Customer Addendum – Tournament & Leaderboard System
- [ ] Time-based tournaments (monthly/yearly) with ranking filters (`includes/class-bhg-shortcodes.php`).
- [ ] Leaderboards sortable columns: position, username, wins (`includes/class-bhg-shortcodes.php`).
- [ ] Filters for week/month/year plus history access (`includes/class-bhg-shortcodes.php`).
- [ ] Display current tournament results and historical data (`includes/class-bhg-shortcodes.php`, `admin/views/tournaments.php`).

## Customer Addendum – Frontend Leaderboard Enhancements
- [ ] Tabs for best guessers (Overall, Monthly, Yearly, All-Time) (`includes/class-bhg-shortcodes.php`, frontend templates).
- [ ] Tabs for leaderboard history across previous bonus hunts (`includes/class-bhg-shortcodes.php`).

## Customer Addendum – User Experience Improvements
- [ ] Smart redirect after login back to originating page (`includes/class-bhg-login-redirect.php`).
- [ ] Three custom menu locations (Admin/Mods, Logged-in, Guests) using WP menus (`includes/helpers.php`).
- [ ] Menu styling matches site design (document CSS hooks) (`assets/css/`, `docs/`).
- [ ] Translations tab enables editing of all plugin text strings (`admin/views/translations.php`).

## Customer Addendum – Affiliate Adjustment/Upgrade
- [ ] Admin manages multiple affiliate websites (add/edit/delete) (`admin/views/affiliate-websites.php`).
- [ ] Bonus Hunt creation includes affiliate dropdown (`admin/views/bonus-hunts.php`).
- [ ] User profile shows affiliate yes/no per site line item (`admin/views/users.php`).
- [ ] Frontend guesser display respects per-affiliate status and ad targeting (`includes/class-bhg-shortcodes.php`, `admin/views/advertising.php`).

## Customer Addendum – Final Enhancements & Polish
- [ ] Winner calculation uses proximity to final balance (`includes/class-bhg-bonus-hunts-helpers.php`).
- [ ] Basic email notifications for results and wins (`includes/class-bhg-notifications.php`).
- [ ] Performance tuning and bug fixes documented (`CHANGELOG.md`).
- [ ] Apply border styling to Bonus Hunt admin inputs (`assets/css/admin.css`).
- [ ] Advertising module placement/visibility per requirement (`admin/views/advertising.php`).

## Customer Questions (bhg-translations & bhg-tools)
- [ ] Populate Translations tab with meaningful data or usage instructions (`admin/views/translations.php`).
- [ ] Populate Tools tab with diagnostics/relevant utilities (`admin/views/tools.php`).

