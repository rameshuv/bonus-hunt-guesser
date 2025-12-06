# Feature Implementation Checklist (Runtime: PHP 7.4 · WordPress 6.3.5 · MySQL 5.5.5+)

## Badges
- [x] Admin menu entry for **Badges** under Bonushunt
- [x] CRUD: add, edit, delete badges
- [x] Fields: Badge Title, Badge Image/Icon
- [x] Field: Affiliate Website (options: none, all affiliate websites; applies activation date tracking per user/site)
- [x] Field: User Data (none; total bonushunt wins; total tournament wins; total guesses; days of registration; days of affiliate active)
- [x] Field: Set Data threshold (5, 10, 25, 50, 100, 250, 500, 1000)
- [x] Show earned badges after usernames on frontend

## Buttons
- [x] Admin Buttons CRUD
- [x] Placement options: none; active bonushunt details (below description); active tournament details (below description)
- [x] Visibility options: all, guests, logged in, affiliates, non affiliates
- [x] Conditional visibility: active bonushunt; active tournament
- [x] Button text (default: Guess Now)
- [x] Custom link + target (same window default)
- [x] Colors: background + hover; text + hover; border
- [x] Text size control
- [x] Button size: small, medium, big
- [x] Responsive tablet/mobile/desktop styles
- [x] `bhg_button` shortcode documented in backend

## Active Hunt & User Guesses
- [x] Empty states wrapped in info block ("No Guesses Yet" / "No Guesses Found")
- [x] Active bonus hunt details displayed in styled block

## Active Hunt Details
- [x] Toggle to hide/show description details: number of bonuses, affiliate website
- [x] Detail label changed to **Status:** with link "Active: Guess Now" or "Closed"

## Bonus Hunts Results (Backend)
- [x] Winner row highlighting matches selected winners count
- [x] Results admin dropdown includes bonushunts and tournaments
- [x] Clicking "results" in bonushunt list opens correct data in results screen
