# Bonus Hunt Guesser – Customer Requirements Checklist

This checklist consolidates all customer requirements provided for the Bonus Hunt Guesser plugin. Each item includes a checkbox to track implementation status.

## Runtime Compatibility
- [ ] PHP 7.4 compatibility
- [ ] WordPress 6.3.5 compatibility
- [ ] MySQL 5.5.5+ compatibility

## General Frontend
- [ ] Change clickable links in table headers to white on all frontend outputs
- [ ] `bg-hunts`: Add "Details" column next to "Status"
  - [ ] Closed hunts show "Show Results" link to hunt result page
  - [ ] Open hunts show "Guess Now" link to hunt guess page

## Prizes Module
- [ ] Big image (1200x800 PNG) uploads correctly in backend
- [ ] Display image sizes in backend add/edit form: Small (300x200), Medium (600x400), Big (1200x800)
- [ ] Allow adding product/prize link in backend; make frontend image clickable when link is set
- [ ] Add "Category" management with custom link for prizes
- [ ] Category menu option to show/hide link in frontend
- [ ] Backend option for big image interaction: popup, direct link, or new window
- [ ] Carousel mode option to set number of images visible simultaneously
- [ ] Carousel/Grid mode option to set total images loaded
- [ ] Carousel mode auto animation toggle
- [ ] Carousel/Grid mode toggles to hide/show title, category, description
- [ ] Responsive sizing: display big image when showing 1 prize, medium for 2–3, small for 4–5
- [ ] Remove "Prizes" heading above grid/carousel
- [ ] Support separate prize sets for regular vs. premium users per bonus hunt

## Jackpot Feature
- [ ] Jackpot admin page lists latest 10 jackpots with title, start date, start amount, current amount, and status
- [ ] Admin menu entry for jackpots exists
- [ ] Jackpot add/edit includes title and start amount
- [ ] Assign jackpots to: all hunts, specific hunts, hunts by affiliate, hunts within date range (month, year, all time)
- [ ] Configure rollover amount to add when jackpot is not hit; currency pulled from general settings
- [ ] Support multiple jackpots with CRUD operations
- [ ] Shortcode to display current jackpot amount (filterable by jackpot ID)
- [ ] Shortcode to display latest jackpot hit with toggle for date, amount, winner, and filters by affiliate/date
- [ ] Shortcode ticker for jackpot amount or latest winners with auto-scrolling text
- [ ] Shortcode to list latest jackpot winners with view mode (list/table) and column toggles (date, name, title, amount, affiliate)

## Core Bonus Hunt & Guessing System
- [ ] Admin can create bonus hunts with title, starting balance, number of bonuses, prizes
- [ ] Frontend displays active hunt details and leaderboard of guesses
- [ ] Logged-in users can submit guesses between €0 and €100,000
- [ ] Leaderboard shows position, username, and guess

## User Profiles & Guessing Enhancements
- [ ] Admin can manage user profiles with real name, username, email, affiliate status
- [ ] Social login integration (Google, Twitch, Kick) via Nextend plugin
- [ ] Users can alter guess while hunt is open
- [ ] Leaderboard shows affiliate indicator (green for affiliates, red for non-affiliates)
- [ ] Guess table supports sorting (position, username, balance) and pagination

## Tournament & Leaderboard System
- [ ] Support time-based tournaments (weekly, monthly, yearly)
- [ ] Leaderboard columns sortable by position, username, wins
- [ ] Rankings filterable by week, month, year
- [ ] Display current tournament results and historical data

## Frontend Leaderboard Enhancements
- [ ] Tabs for best guessers: Overall, Monthly, Yearly, All-Time
- [ ] Tabs for viewing leaderboard history across previous hunts

## User Experience Improvements
- [ ] Smart post-login redirect to originally requested page
- [ ] Three menu configurations (Admin/Moderators, Logged-in Users, Guests) using WP menu system
- [ ] Menu styling aligns with site borders/tabs design
- [ ] Translation management tab to edit all plugin text fields

## Affiliate Adjustment/Upgrade
- [ ] Admin can manage multiple affiliate websites (CRUD)
- [ ] Bonus Hunt creation allows selecting affiliate site
- [ ] User profiles show affiliate site assignments (multiple)
- [ ] Frontend reflects affiliate status per hunt and influences display/ad targeting

## Final Enhancements and Polish
- [ ] Automatic winner calculation based on proximity to final balance
- [ ] Email notifications for results and wins
- [ ] Performance optimizations and bug fixes
- [ ] Styled input borders in Bonus Hunt admin
- [ ] Advertising module: admin can add text with optional links, choose placement, control visibility by login/affiliate status

## Backend Updates Requested (Robinbos – Sep 04)
### `bhg` Dashboard
- [ ] Rename sub-menu item from "Bonus Hunt" to "Dashboard"
- [ ] Recent winners list displays multiple winners per hunt (up to 25)
- [ ] Rename "Recent Winners" to "Latest Hunts"
- [ ] Latest Hunts table includes title, winners (with guess & difference), start balance, final balance, closed date

### `bhg-bonus-hunts`
- [ ] "Results" action button for finished hunts showing ranked guesses with winner highlighting
- [ ] Configurable number of winners per hunt
- [ ] Hunt edit view lists all guesses with ability to remove entries; usernames clickable for profile edit
- [ ] Admin list table shows final balance column ("-" for open hunts)

### `bhg-tournaments`
- [ ] Title field in admin
- [ ] Description field in admin
- [ ] Type field includes quarterly and all-time options
- [ ] Remove redundant period field
- [ ] Editing tournaments functions correctly

### `bhg-users`
- [ ] Search functionality for users/email
- [ ] Sort options for table
- [ ] Pagination (30 per page)

### `bhg-ads`
- [ ] Actions column with edit/remove buttons
- [ ] Placement dropdown includes "None" option for shortcode-only ads

### `bhg-translations` & `bhg-tools`
- [ ] Populate pages with relevant data per attachments (currently empty)

## Additional Notes
- [ ] All features adhere to WordPress coding standards
- [ ] All requirements implemented without extra enhancements beyond customer specifications

