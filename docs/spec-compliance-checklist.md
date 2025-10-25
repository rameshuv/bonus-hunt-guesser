# Bonus Hunt Guesser v8.0.14 vs. Dev Spec v8.0.13 → v8.0.14

This checklist records whether the current codebase satisfies each requirement in the customer specification. Items marked ✅ meet the spec, ⚠️ are partially met, and ❌ are missing.

## 0. Plugin Header
- ❌ Header metadata does not match the requested snippet (version, description).【F:bonus-hunt-guesser.php†L3-L13】

## 1. Prizes Admin (`bhg-prizes`)
- ✅ Dedicated admin screen supports creating, editing, deleting prizes with title, description, category, image fields, CSS controls, and active toggle.【F:admin/views/prizes.php†L16-L200】

## 2. Prizes on Bonus Hunt Editor
- ✅ Bonus hunt create/edit forms include multi-select linking to prizes and winners count configuration.【F:admin/views/bonus-hunts.php†L418-L574】

## 3. Prizes on Active Hunt Frontend
- ✅ Active hunt shortcode renders hunt metadata plus prize grids/carousels depending on layout selection.【F:includes/class-bhg-shortcodes.php†L475-L518】【F:includes/class-bhg-shortcodes.php†L189-L236】

## 4. Prizes Shortcode
- ✅ `[bhg_prizes]` shortcode exposes category, design, size, and active filters and reuses the shared renderer.【F:includes/class-bhg-shortcodes.php†L2548-L2592】

## 5. User Profile Shortcodes
- ❌ No `my_bonushunts`, `my_tournaments`, `my_prizes`, or `my_rankings` shortcodes or visibility toggles are registered; only the existing shortcode list is available.【F:includes/class-bhg-shortcodes.php†L25-L45】

## 6. CSS / Color Panel
- ✅ Settings page offers design token controls for title blocks, headings, descriptions, and body text, covering the requested styling fields.【F:admin/views/settings.php†L128-L226】

## 7. Shortcodes Admin Menu (`bhg-shortcodes`)
- ✅ “Shortcode Reference” admin page lists all available shortcodes with attribute documentation.【F:admin/views/shortcodes.php†L16-L138】

## 8. Notifications Admin (`bhg-notifications`)
- ✅ Notifications view provides enable toggles, BCC fields, subject/body editors, and token hints for winner, tournament, and hunt emails.【F:admin/views/notifications.php†L16-L82】

## 9. Tournaments Enhancements
- ❌ Tournament admin lacks prize selection and affiliate website fields/toggles, and the detail shortcode does not surface such data (only existing fields are present).【F:admin/views/tournaments.php†L262-L332】【F:includes/class-bhg-shortcodes.php†L2235-L2270】

## 10. Tournament Ranking Points System
- ✅ Settings expose editable placement points with recalculation scope, and winner/tournament calculations persist point totals and highlight podium placements.【F:admin/views/settings.php†L128-L156】【F:includes/class-bhg-models.php†L235-L299】【F:admin/views/bonus-hunts-results.php†L232-L255】

---

### Summary of Outstanding Gaps
1. Update plugin header metadata to match the spec snippet (`bonus-hunt-guesser.php`).
2. Implement the four “my_*” profile shortcodes plus admin visibility controls (likely `includes/class-bhg-shortcodes.php` and an admin settings view).
3. Extend tournaments admin/detail flows to manage and display prizes and affiliate website fields (`admin/views/tournaments.php`, related handlers, and corresponding shortcodes/templates).

All other items in the spec are satisfied by the current implementation.
