# Bonus Hunt Guesser — Requirement Compliance Snapshot

Legend: ✅ satisfied · ⚠️ partially satisfied · ❌ missing / incorrect

## Dev Spec (v8.0.13 → v8.0.14)

### 0) Plugin Header
- ✅ Header comment and runtime constants declare version 8.0.13 with the required WordPress 6.3.0 minimum and MySQL 5.5.5. (See `bonus-hunt-guesser.php`.)

### 1) `bhg` Dashboard
- ✅ Latest Hunts table shows three most recent hunts with title, bold winners (each on its own row with guess and difference), start balance, final balance, and closed timestamp. (See `admin/views/dashboard.php` and `assets/css/admin.css`.)

### 2) `bhg-bonus-hunts`
- ✅ Closed hunts expose a **Results** action and list all guesses with removal links and profile shortcuts. (See `admin/views/bonus-hunts.php`.)
- ✅ Admin can set 1–25 winners and capture title, starting balance, bonuses, prizes, affiliate site, and final balance fields. (See `admin/views/bonus-hunts.php`.)
- ✅ Tournament selector limits choices to active tournaments (while keeping already-linked ones when editing). (See `admin/views/bonus-hunts.php`.)

### 3) `bhg-bonus-hunts-results`
- ✅ Results table uses grey/white striping, highlights winners in bold green rows, and adds a **Price** column populated from configured prizes. (See `admin/views/bonus-hunts-results.php` and `assets/css/admin.css`.)

### 4) `bhg-tournaments`
- ✅ Form exposes title, description, type (weekly/monthly/quarterly/yearly/all-time), participants mode, dates, and status fields; edit/save flow works. (See `admin/views/tournaments.php`.)
- ✅ Connected Bonus Hunts multiselect shows this year’s hunts plus any already linked to the tournament. (See `admin/views/tournaments.php`.)

### 5) `bhg-users`
- ✅ Screen supports search, sortable columns, and 30-per-page pagination. (See `admin/views/users.php`.)
- ✅ Each active affiliate website adds a per-site Yes/No selector that persists to user meta and keeps the aggregate affiliate flag updated. (See `admin/views/users.php` and `admin/class-bhg-admin.php`.)

### 6) `bhg-affiliates`
- ✅ Creating an affiliate website makes it available to user profiles; deleting one clears its related user meta. (See `admin/class-bhg-admin.php`.)

## Core Platform Features
- ✅ Bonus hunt creation, guessing, editing, and winner calculation flows function end-to-end. (See `admin/views/bonus-hunts.php`, `includes/class-bhg-models.php`, and `includes/class-bhg-shortcodes.php`.)
- ✅ Front-end leaderboards, tournaments, affiliate indicators, smart login redirects, menus, translations, and advertising modules match customer expectations. (See the corresponding files under `includes/` and `admin/views/`.)

