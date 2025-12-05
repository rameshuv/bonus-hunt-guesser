# Feature Implementation Checklist (Runtime: PHP 7.4 · WordPress 6.3.5 · MySQL 5.5.5+)

## Badges
- [ ] Admin menu entry for **Badges** under Bonushunt
- [ ] CRUD: add, edit, delete badges
- [ ] Fields: Badge Title, Badge Image/Icon
- [ ] Field: Affiliate Website (options: none, all affiliate websites; applies activation date tracking per user/site)
- [ ] Field: User Data (none; total bonushunt wins; total tournament wins; total guesses; days of registration; days of affiliate active)
- [ ] Field: Set Data threshold (5, 10, 25, 50, 100, 250, 500, 1000)
- [ ] Show earned badges after usernames on frontend

## Buttons
- [ ] Admin Buttons CRUD
- [ ] Placement options: none; active bonushunt details (below description); active tournament details (below description)
- [ ] Visibility options: all, guests, logged in, affiliates, non affiliates
- [ ] Conditional visibility: active bonushunt; active tournament
- [ ] Button text (default: Guess Now)
- [ ] Custom link + target (same window default)
- [ ] Colors: background + hover; text + hover; border
- [ ] Text size control
- [ ] Button size: small, medium, big
- [ ] Responsive tablet/mobile/desktop styles
- [ ] `bhg_button` shortcode documented in backend

## Active Hunt & User Guesses
- [ ] Empty states wrapped in info block ("No Guesses Yet" / "No Guesses Found")
- [ ] Active bonus hunt details displayed in styled block

## Active Hunt Details
- [ ] Toggle to hide/show description details: number of bonuses, affiliate website
- [ ] Detail label changed to **Status:** with link "Active: Guess Now" or "Closed"

## Bonus Hunts Results (Backend)
- [ ] Winner row highlighting matches selected winners count
- [ ] Results admin dropdown includes bonushunts and tournaments
- [ ] Clicking "results" in bonushunt list opens correct data in results screen
