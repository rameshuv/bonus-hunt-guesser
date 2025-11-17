# Customer Requirements Checklist (Audit)

Statuses:
- **Met**: Confirmed in codebase with current version.
- **Partial**: Partially implemented or needs additional validation.
- **Gap**: Not implemented or conflicting with stated requirement.
- **Not verified**: Needs manual QA or deeper review beyond static scan.

## Runtime & Versioning
- **PHP 7.4 / WordPress 6.3.5 minimum**: **Met** — plugin header requires PHP 7.4 and WP 6.3.5.【F:bonus-hunt-guesser.php†L3-L8】
- **Plugin version 8.0.18 target**: **Gap** — header currently declares 8.0.16, below requested 8.0.18 and earlier release note reference.【F:bonus-hunt-guesser.php†L3-L8】

## Frontpage / List Shortcodes
- **latest-winners-list shortcode**: **Met** — registered in shortcode loader.【F:includes/class-bhg-shortcodes.php†L72-L97】
- **leaderboard-list shortcode**: **Met** — registered alongside other list views.【F:includes/class-bhg-shortcodes.php†L72-L97】
- **tournament-list shortcode**: **Met** — available for tournament text lists.【F:includes/class-bhg-shortcodes.php†L72-L97】
- **bonushunt-list shortcode**: **Met** — available for bonushunt text lists.【F:includes/class-bhg-shortcodes.php†L72-L97】
- **Frontpage block usability / 2-column layout**: **Not verified** — requires front-end QA for responsiveness and design fit.

## Jackpot Module
- **Admin jackpots CRUD & linking modes**: **Met** — dedicated jackpot class provides CRUD, link configurations, and event tracking tables.【F:includes/class-bhg-jackpots.php†L12-L119】
- **Frontend jackpot shortcodes**: **Partial** — shortcodes are registered, but UI/behavior needs QA against ticker/winners requirements.【F:includes/class-bhg-shortcodes.php†L72-L97】

## Prizes Enhancements
- **Prize image size support (1200×800 big)**: **Partial** — big size registered; need confirmation of medium/small and upload issue resolution.【F:bonus-hunt-guesser.php†L65-L69】
- **Dual prize sets (regular + premium)**: **Not verified** — code-level handling for premium prize set not confirmed in audit.
- **Prize links, categories, click behavior, carousel controls, responsive sizing**: **Not verified** — requires UI review in admin and front-end output.

## Leaderboards Adjustments
- **Leaderboard/tournament list shortcodes present**: **Met** — base leaderboards and list/tabs exist.【F:includes/class-bhg-shortcodes.php†L72-L97】
- **Avg Rank/Avg Tournament Pos rounding, username capitalization, affiliate light column, position sorting, prize box for selected tournament, heading placement, dropdown filter toggles/removal, timeline-aware “times won”, rename headers, etc.**: **Not verified** — static scan didn’t confirm these numerous UX tweaks; manual review and tests required in shortcode rendering templates.

## Tournament Adjustments
- **Basic tournament shortcodes and admin controller**: **Met** — tournament controller and shortcode present.【F:includes/class-bhg-tournaments-controller.php†L1-L120】
- **Number of winners field, closing countdown notice, header renames, position sorting, last-win logic, pagination setting**: **Not verified** — needs targeted UI/data validation beyond code scan.

## General Frontend Tweaks
- **Table header link color white; hunts details column with contextual links**: **Not verified** — CSS/layout adjustments not yet confirmed in `assets/css/public.css` or shortcode templates.

## Menus, Login Redirects, Profiles, Affiliates
- **Front-end menus and smart login redirect support**: **Partial** — login redirect helper exists; menu customization requires QA for role-based menus.【F:includes/class-bhg-login-redirect.php†L1-L140】
- **Affiliate lights and multi-site handling**: **Partial** — affiliate utilities exist, but per-requirement display rules need verification.【F:includes/class-bhg-utils.php†L1-L160】

## Notifications & Ads
- **Winner notifications shortcode**: **Met** — registered in shortcode loader.【F:includes/class-bhg-shortcodes.php†L72-L99】
- **Ads placement “none” option and actions**: **Not verified** — admin ads table/actions need UI check.

## Database & Migrations
- **Jackpot and prize tables present**: **Met** — jackpot tables referenced; prize handling in migrations requires review.【F:includes/class-bhg-jackpots.php†L37-L119】
- **Versioned migrations to align with requested schema updates**: **Partial** — migration helper exists, but alignment with all new columns (e.g., leaderboard/tournament filters) needs confirmation.【F:bonus-hunt-guesser.php†L200-L234】

## Documentation / Help
- **Shortcode catalog page**: **Partial** — shortcode registrations exist; need to verify admin help page completeness.【F:includes/class-bhg-shortcodes.php†L72-L104】

---

> This checklist highlights where the current codebase aligns with, partially addresses, or needs further work to satisfy the customer’s detailed requirements. Items marked “Not verified” should be validated through targeted functional testing or deeper code review before sign-off.
