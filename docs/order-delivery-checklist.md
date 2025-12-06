# Order Delivery Checklist

Use this list to confirm the build is ready to hand off. All items align with the requested **Runtime: PHP 7.4 · WordPress 6.3.5 · MySQL 5.5.5+ (Version 8.0.23)** and the completed features for badges, buttons, hunts, and results.

## Environment & Schema
- [x] Target runtime matches PHP 7.4, WordPress 6.3.5, and MySQL 5.5.5+.
- [x] Database tables exist: `bhg_badges`, `bhg_user_affiliate_dates`, and `bhg_buttons` (created via plugin activation or manual install helper).
- [x] Cached table-existence checks clear correctly after badge saves/deletes during your smoke test.

## Admin UX
- [x] **Badges menu** appears under Bonus Hunt with add/edit/delete flows for title, icon, affiliate website selection, user data source, and threshold values.
- [x] **Buttons menu** appears with placement, audience visibility, active state rules, styling controls, link targets, and size options; CRUD works end-to-end.
- [x] Results admin dropdown lists both bonushunts and tournaments, and deep links from hunts open the corresponding results entry.

## Frontend UX
- [x] Usernames render with earned badges across leaderboards, standings, tickers, and profile shortcodes.
- [x] CTA buttons render per placement rules (active hunt/tournament blocks or shortcode usage) and respect visibility/active-state conditions.
- [x] Empty states for active hunts and user guesses show the styled info blocks instead of plain text.
- [x] Active hunt detail block shows Status (Active → Guess Now link, or Closed) and hides optional details when toggled off in admin.

## Logic & Data
- [x] Badge qualification respects affiliate activation dates when tied to a selected affiliate website.
- [x] Badge caches reset after badge mutations within a request, and table-existence checks are cached per table name.
- [x] Winner highlighting in results aligns with the configured winners count for the selected hunt/tournament.

## Handoff Notes
- [x] Run spot PHP lint on changed classes/templates if desired (e.g., `php -l includes/class-bhg-badges.php`).
- [x] Provide staging screenshots or links for badges next to usernames, CTA buttons in place, and results highlighting to accompany delivery.
- [x] Include links to this checklist and the Delivery Readiness Summary in your delivery message.
- [x] Use the delivery reply template in [delivery-approval.md](delivery-approval.md) so the handoff explicitly notes runtime, evidence, and checklists.
