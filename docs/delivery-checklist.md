# Delivery Readiness Checklist (v8.0.22)

## Delivery Verdict
- **Current status:** **NOT READY FOR DELIVERY** — no end-to-end validation has been executed yet in a WordPress 6.3.5 / PHP 7.4 / MySQL 5.5.5+ stack. All checklist items below must be confirmed on staging before greenlighting release.
- **Version:** Plugin header already set to **8.0.22** in `bonus-hunt-guesser.php`.
- **Verification log:** Record tester, date, environment (PHP/WP/MySQL), and pass/fail for every section. Do not ship without a fully completed log.

## Runtime and Environment
- [ ] Confirm runtime matches **PHP 7.4**, **WordPress 6.3.5**, **MySQL 5.5.5+** (staging) and that caches/CDN are disabled during validation.
- [ ] Run smoke test for fatal errors/notices across all shortcodes after enabling `WP_DEBUG` and capture logs/screenshots.

## Leaderboard Adjustments
- [ ] **Times won:** Validate per-user counts across at least two closed bonus hunts with distinct winners (e.g., sample URL provided) and confirm counts differ by user as expected (no global constant values).
- [ ] **Prize block options:** In general settings, confirm Grid/Carousel options save; verify prizes honor items-per-view and total items in shortcode output.
- [ ] **Affiliate filter:** Apply affiliate website filter (e.g., moderators) and confirm only matching users render.
- [ ] **Pagination:** Confirm page links are clickable and change results across leaderboard pages; verify page-size respects settings and that URL parameters persist filters.
- [ ] **Avg tournament position:** Recompute average using current rankings across open/closed tournaments and verify against manual calculation for at least two users.
- [ ] **Tournament dropdown:** Ensure dropdown always lists all tournaments plus “All tournaments,” with shortcode-preselected tournament selected by default; confirm keyboard navigation works.
- [ ] **`bhg_leaderboard_list` stability:** Render shortcode with filters, pagination, prize blocks, dropdowns, and metrics above without PHP warnings/notices; validate template output matches agreed UI.

## Tournament Adjustments
- [ ] **Tournament detail shortcode:** Confirm frontend renders tournament details (title, info, description, prizes, countdown) at `/tournaments/?bhg_tournament_id=ID`.
- [ ] **Prize block options:** Validate Grid/Carousel settings save and render correctly with items-per-view and total item limits.
- [ ] **Pagination:** Verify working pagination for tournament and bonus hunt shortcodes after data loads.
- [ ] **Affiliate filter:** Confirm tournament participant list respects affiliate website group (e.g., moderators-only tournament shows only moderators).
- [ ] **Winner highlighting:** Check that number of highlighted rows matches configured winner count; non-winners use default styling.
- [ ] **Column header:** Table header reads **“User”** (not “Username”).
- [ ] **Countdown placement:** Tournament title/info/description shown in a cohesive block; countdown positioned below prizes.

## Global Filters & UI/UX
- [ ] **Filter styling:** Filters for bonus hunts, leaderboards, tournaments, and user guess use consistent dropdown styling (matching tournaments style).
- [ ] **Dropdown layout:** All dropdowns aligned in a single row with even spacing.
- [ ] **Search/filter alignment:** Search bar on its own row (full width) aligned with table width; buttons equal height with proper padding.
- [ ] **User guess form:** Input uses reduced left padding and symmetrical right padding; submit button uses global dark blue style.

## Release Gate
- [ ] All checks above validated in staging with screenshots where applicable.
- [ ] No unresolved PHP notices/warnings in logs after exercising all shortcodes.
- [ ] Sign-off recorded: “Ready for delivery” once every checkbox is confirmed.
