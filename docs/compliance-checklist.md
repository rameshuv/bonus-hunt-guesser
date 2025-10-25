# Bonus Hunt Guesser — Requirement Compliance Snapshot

Legend: ✅ implemented · ⚠️ partially implemented · ❌ missing / incorrect

## 0) Plugin Header
- ✅ Header comment and runtime constants match the requested version 8.0.13 and WordPress minimum 6.3.0. Ref: `bonus-hunt-guesser.php` L3-L15, L145-L146.

## Backend (Oct 20 spec)
1. **bhg dashboard**
   - ✅ Winners render on individual rows with bold usernames beneath each hunt. Ref: `admin/views/dashboard.php` L82-L127.

2. **bhg-bonus-hunts**
   - ✅ Closed hunts expose a “Results” button and include a participants table with removal controls and profile links. Ref: `admin/views/bonus-hunts.php` L240-L279, L600-L665.
   - ✅ Tournament multi-select shows active tournaments and preserves existing selections when editing. Ref: `admin/views/bonus-hunts.php` L392-L432, L544-L603.

3. **bhg-bonus-hunts-results**
   - ✅ Uses grey/white striping with green bold highlight for winners plus a “Price” column sourced from prizes. Ref: `admin/views/bonus-hunts-results.php` L200-L239; `assets/css/admin.css` L39-L55.

4. **bhg-tournaments**
   - ✅ Title/description/type fields restored (with quarterly/alltime options) and edit flow works; connected hunts filtered to current year plus existing links. Ref: `admin/views/tournaments.php` L288-L368, L404-L443.

5. **bhg-users**
   - ✅ Search, sortable columns, and 30-per-page pagination implemented; affiliate columns render per affiliate site with persistence. Ref: `admin/views/users.php` L9-L189; `admin/class-bhg-admin.php` L1167-L1206.

6. **bhg-affiliates**
   - ✅ Removing an affiliate purges related user meta, and per-site yes/no toggles are created when sites exist. Ref: `admin/class-bhg-admin.php` L1110-L1159, L1167-L1206.

## Core Functionality Summary
- ✅ Admin can create bonus hunts with title, starting balance, number of bonuses, and prizes. Ref: `admin/views/bonus-hunts.php` L360-L423.
- ✅ Logged-in users submit guesses (0–100k configurable) and may edit existing guesses while the hunt is open. Ref: `includes/class-bhg-shortcodes.php` L590-L700; `bonus-hunt-guesser.php` L538-L735.
- ✅ Frontend shortcodes expose active hunt details and leaderboards. Ref: `includes/class-bhg-shortcodes.php` L210-L552, L714-L1030.
- ✅ User profiles capture real name and affiliate status; affiliate dots display via CSS. Ref: `admin/views/users.php` L118-L173; `assets/css/admin.css` L22-L37.
- ✅ Social login integration hooks Nextend for redirects and profile meta. Ref: `includes/class-bhg-login-redirect.php` L17-L94.
- ✅ Affiliate tracking lights (green/red) supported via `.bhg-dot` classes on leaderboards. Ref: `assets/css/admin.css` L22-L37.
- ✅ Leaderboard tables provide sorting and pagination options. Ref: `includes/class-bhg-shortcodes.php` L714-L1030.
- ✅ Tournament leaderboards cover weekly/monthly/yearly/all-time filters. Ref: `includes/class-bhg-shortcodes.php` L1576-L2149.
- ✅ Frontend leaderboards include tabbed best guesser views. Ref: `includes/class-bhg-shortcodes.php` L2297-L2732.
- ✅ Smart login redirect implemented for core and Nextend flows. Ref: `includes/class-bhg-login-redirect.php` L31-L73.
- ✅ Distinct front-end menus registered for admin/user/guest with shortcode helper. Ref: `includes/class-bhg-front-menus.php` L17-L86.
- ✅ Translation management page seeded with pagination/search. Ref: `admin/views/translations.php` L1-L120.
- ✅ Affiliate websites CRUD updates user meta fields automatically. Ref: `admin/class-bhg-admin.php` L1110-L1206.
- ✅ Winner calculation based on closest guess when closing hunts, and emails issue on closure. Ref: `includes/class-bhg-models.php` L31-L160; `admin/class-bhg-admin.php` L440-L485.
- ✅ Admin ads UI supports placements (including “none”) and visibility logic. Ref: `admin/views/advertising.php` L40-L204; `assets/css/admin.css` L12-L21.
- ⚠️ Email template strings exist, but no dedicated helper ensures translations/HTML; review needed to confirm final content rendering in multiple locales. Ref: `includes/helpers.php` L559-L567; `admin/class-bhg-admin.php` L440-L483.
- ⚠️ Performance tuning largely relies on manual caching; further profiling may be required. Multiple direct queries marked with caching comments remain. Ref: `includes/class-bhg-shortcodes.php` L609-L648.

