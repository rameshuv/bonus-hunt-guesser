# Customer Requirements Checklist (Bonus Hunt Guesser)

Statuses: **Met** (implemented and referenced in code), **Partial** (present but missing options/QA), **Gap** (not implemented), **Not Verified** (needs QA).

## 1. Runtime, Standards, Text Domain
- PHP 7.4 / WP 6.3.5 minimums declared in the plugin header. **Met.**【F:bonus-hunt-guesser.php†L3-L12】
- Plugin version must read **8.0.18**; header/constant still show **8.0.16**. **Gap.**【F:bonus-hunt-guesser.php†L3-L12】
- Text domain `bonus-hunt-guesser` loads on `plugins_loaded`. **Met.**【F:bonus-hunt-guesser.php†L399-L410】
- PHPCS conformance (WordPress-Core/Extra/Docs) not recently verified. **Not Verified.**

## 2. Plugin Header & Bootstrapping
- Required header fields (Name, URI, Description, Requires PHP, Requires at least, Text Domain, Domain Path, GPLv2+). **Met.**【F:bonus-hunt-guesser.php†L3-L12】
- Version mismatch vs. customer target (8.0.18). **Gap.**【F:bonus-hunt-guesser.php†L3-L12】
- Boot sequence loads admin/front components and text domain. **Met.**【F:bonus-hunt-guesser.php†L399-L419】

## 3. Leaderboards (Frontend Shortcode)
- Position column sortable. **Met.**【F:includes/class-bhg-shortcodes.php†L4913-L4917】
- Username label and capitalization (first letter uppercased). **Met.**【F:includes/class-bhg-shortcodes.php†L4917-L4919】【F:includes/class-bhg-shortcodes.php†L4951-L4958】
- Avg Rank / Avg Tournament Pos rendered with whole numbers. **Met.**【F:includes/class-bhg-shortcodes.php†L4970-L4973】
- Prize block shown when a specific active tournament is selected. **Met.**【F:includes/class-bhg-shortcodes.php†L4596-L4634】
- Affiliate status column/lights present. **Met.**【F:includes/class-bhg-shortcodes.php†L4927-L4935】【F:includes/class-bhg-shortcodes.php†L4974-L4975】
- H2 headings for selected tournament/hunt above the table. **Met.**【F:includes/class-bhg-shortcodes.php†L4896-L4905】
- Filters/search toggles exist for timeline/tournament/affiliate site/affiliate status. **Met.**【F:includes/class-bhg-shortcodes.php†L4855-L4884】
- Bonushunt filter should be removed; handler still parses `bhg_hunt` and paginates with it. **Gap.**【F:includes/class-bhg-shortcodes.php†L4520-L4527】【F:includes/class-bhg-shortcodes.php†L4999-L5008】
- Times Won calculation alignment with timeline/tournament scope not validated. **Not Verified.**
- Prize summary list toggle under leaderboard prizes not observed. **Gap.**【F:includes/class-bhg-shortcodes.php†L4596-L4634】

## 4. Tournament Shortcode / Admin
- “Number of Winners” field on add/edit with 1–25 range. **Met.**【F:admin/views/tournaments.php†L386-L395】
- Active tournament countdown banner (“closes in X days”). **Met.**【F:includes/class-bhg-shortcodes.php†L5229-L5253】
- Column headers changed to Position / Times Won with sortable Position. **Met.**【F:includes/class-bhg-shortcodes.php†L5329-L5337】【F:includes/class-bhg-shortcodes.php†L5342-L5348】
- Last Win column should surface last prize in linked hunts; data source not confirmed. **Not Verified.**
- Pagination meant to honor global rows-per-page setting; needs validation. **Not Verified.**

## 5. Prizes
- Tournament prize block renders but summary list and tabbed regular/premium carousels are absent. **Gap.**【F:includes/class-bhg-shortcodes.php†L4596-L4634】
- Prize admin UI lacks size labels/click-behavior/link options and carousel controls described by client. **Gap.**【F:admin/views/prizes.php†L1-L200】
- Regular vs. premium per-winner assignment and summary text beneath prizes not visible. **Gap.**【F:includes/class-bhg-prizes.php†L1025-L1080】

## 6. Frontpage “List” Shortcodes
- Shortcodes registered for latest winners, leaderboard list, tournament list, bonushunt list. **Met.**【F:includes/class-bhg-shortcodes.php†L72-L97】
- Visibility toggles and responsive behavior for these list blocks not validated. **Not Verified.**

## 7. General Frontend Adjustments
- Table header links styled white. **Met.**【F:assets/css/bhg-shortcodes.css†L512-L539】
- Hunts list includes Details column with context-aware links. **Met.**【F:includes/class-bhg-shortcodes.php†L4200-L4278】【F:includes/class-bhg-shortcodes.php†L4288-L4319】
- Mobile responsiveness across tables/carousels not validated. **Not Verified.**

## 8. Jackpot Module
- Jackpot service, admin wiring, and shortcodes exist. **Met (needs QA).**【F:includes/class-bhg-jackpots.php†L12-L520】【F:includes/class-bhg-shortcodes.php†L5647-L5894】
- Schema upgrades for jackpots assumed in DB class but require environment verification. **Not Verified.**【F:includes/class-bhg-db.php†L250-L344】

## 9. Winner Limits Per User
- Settings include hunt/tournament win limits with rolling periods. **Met.**【F:admin/views/settings.php†L118-L155】
- Helper text and notices exist; runtime enforcement should be QA’d. **Not Verified.**【F:includes/helpers.php†L1117-L1256】

## 10. Notifications System
- Notifications module with enable/BCC fields present. **Partial (needs validation).**【F:includes/notifications.php†L26-L192】
- BCC validation/sending behavior not QA’d. **Not Verified.**

## 11. Ranking & Points
- Ranking scope and points map configurable in tournaments admin. **Met.**【F:admin/views/tournaments.php†L360-L405】
- Centralized ranking recalculation tied to win limits mentioned in models; functional QA pending. **Not Verified.**【F:includes/class-bhg-models.php†L243-L355】

## 12. Global CSS / Color Panel
- Global styles collected from settings and injected inline on public pages. **Met (needs design QA).**【F:bonus-hunt-guesser.php†L360-L396】

## 13. Database & Migrations
- Core columns (`guessing_enabled`, `participants_mode`, `affiliate_id`) and junction tables defined. **Partial**; idempotent upgrades should be re-run/validated. 【F:bonus-hunt-guesser.php†L200-L234】【F:includes/class-bhg-db.php†L93-L216】
- Jackpot tables added but require deployment confirmation. **Not Verified.**【F:includes/class-bhg-db.php†L250-L344】

## 14. Release & Documentation
- Changelog/README not yet updated for version 8.0.18 or outstanding prize/leaderboard changes. **Gap.**【F:CHANGELOG.md†L1-L38】
- Info & Help shortcodes catalog includes jackpot entries. **Met.**【F:admin/views/shortcodes.php†L276-L306】

## 15. QA / Acceptance Tests
- Automated/PHPCS runs not recorded for current branch. **Not Verified.**
- End-to-end flows (hunts → winners → tournaments → jackpots, currency toggle, notifications) remain to be documented. **Not Verified.**
