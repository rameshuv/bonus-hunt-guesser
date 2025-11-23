# Feature Verification Checklist

## Prize Adjustments
- ✅ Add/edit Bonus Hunt forms support configuring regular and premium prizes for each winner slot based on the selected number of winners. 【F:admin/views/bonus-hunts.php†L420-L461】
- ✅ Add/edit Tournament forms mirror the regular/premium prize mapping per winner slot. 【F:admin/views/tournaments.php†L430-L463】
- ✅ Prize sections render a per-position summary list beneath prize cards when enabled. 【F:includes/class-bhg-shortcodes.php†L1567-L1694】
- ✅ Prize displays use a tabbed carousel separating regular and premium prizes. 【F:includes/class-bhg-shortcodes.php†L1203-L1260】【F:includes/class-bhg-shortcodes.php†L4780-L4823】
- ✅ Shortcode attributes allow showing/hiding prizes and prize summaries for tournaments, leaderboards, and prize displays. 【F:includes/class-bhg-shortcodes.php†L4569-L4613】【F:includes/class-bhg-shortcodes.php†L5239-L5250】【F:includes/class-bhg-shortcodes.php†L5883-L5938】

## Front-page List Shortcodes
- ✅ Latest winners text list shortcode with selectable fields and limits. 【F:includes/class-bhg-shortcodes.php†L3443-L3685】
- ✅ Leaderboard list shortcode for top guessers with tournament/bonushunt filters and selectable fields. 【F:includes/class-bhg-shortcodes.php†L3688-L3959】
- ✅ Tournament list shortcode showing timeline/status details with configurable fields. 【F:includes/class-bhg-shortcodes.php†L3961-L4178】
- ✅ Bonushunt list shortcode showing timeline/status details with configurable fields. 【F:includes/class-bhg-shortcodes.php†L4180-L4461】

## Shortcode Filter Adjustments
- ✅ Timeline filters limited to Alltime, Today, This Week, This Month, This Quarter, This Year, and Last Year across shortcode UIs. 【F:includes/class-bhg-shortcodes.php†L3339-L3366】【F:includes/class-bhg-shortcodes.php†L5735-L5760】
- ✅ Bonushunt and tournament listings include status/timeline dropdown filters and options to hide the search box. 【F:includes/class-bhg-shortcodes.php†L5729-L5794】【F:includes/class-bhg-shortcodes.php†L4462-L4515】
- ✅ Leaderboard shortcode supports hiding the search form and toggling prize visibility/summary displays. 【F:includes/class-bhg-shortcodes.php†L4569-L4613】
