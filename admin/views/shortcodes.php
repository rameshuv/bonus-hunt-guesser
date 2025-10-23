<?php
/**
 * Shortcodes help and reference list.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$shortcodes = array(
array(
'tag'         => '[bhg_active_hunt]',
'description' => bhg_t( 'sc_desc_active_hunt', 'Displays the currently active bonus hunt with leaderboard and optional prize gallery.' ),
'attributes'  => array(
'prize_layout' => bhg_t( 'sc_attr_prize_layout', 'grid (default) or carousel' ),
'prize_size'   => bhg_t( 'sc_attr_prize_size', 'small, medium (default), big' ),
),
),
array(
'tag'         => '[bhg_guess_form]',
'description' => bhg_t( 'sc_desc_guess_form', 'Shows the guess submission form for logged-in users.' ),
'attributes'  => array(),
),
array(
'tag'         => '[bhg_leaderboard]',
'description' => bhg_t( 'sc_desc_leaderboard', 'Outputs a paginated leaderboard of guesses.' ),
'attributes'  => array(
'hunt_id' => bhg_t( 'sc_attr_hunt_id', 'Optional hunt ID; defaults to active hunt.' ),
),
),
array(
'tag'         => '[bhg_tournaments]',
'description' => bhg_t( 'sc_desc_tournaments', 'Lists tournaments with filters or shows a single tournament when bhg_tournament_id is set in the URL.' ),
'attributes'  => array(
'timeline' => bhg_t( 'sc_attr_timeline', 'Filter by time period (e.g. month, year, all_time).' ),
'status'   => bhg_t( 'sc_attr_status', 'Filter by active, closed, or all.' ),
),
),
array(
'tag'         => '[bhg_best_guessers]',
'description' => bhg_t( 'sc_desc_best_guessers', 'Tabbed leaderboard highlighting top performers overall, monthly, yearly, and hunt history.' ),
'attributes'  => array(),
),
array(
'tag'         => '[bhg_user_guesses]',
'description' => bhg_t( 'sc_desc_user_guesses', 'Table of guesses for a specific hunt with sorting and filtering.' ),
'attributes'  => array(
'id'       => bhg_t( 'sc_attr_hunt_id', 'Optional hunt ID; defaults to the latest hunt.' ),
'fields'   => bhg_t( 'sc_attr_fields', 'Comma separated columns to show (hunt,user,guess,final,site).' ),
'timeline' => bhg_t( 'sc_attr_timeline', 'Limit guesses to a specific time window.' ),
),
),
array(
'tag'         => '[bhg_hunts]',
'description' => bhg_t( 'sc_desc_hunts', 'Archive list of bonus hunts with search, ordering, and pagination.' ),
'attributes'  => array(
'status'  => bhg_t( 'sc_attr_status', 'Filter hunts by status (open, closed, all).' ),
'order'   => bhg_t( 'sc_attr_order', 'asc or desc ordering.' ),
'orderby' => bhg_t( 'sc_attr_orderby_hunt', 'title, start, final, winners, status, created.' ),
),
),
array(
'tag'         => '[bhg_leaderboards]',
'description' => bhg_t( 'sc_desc_leaderboards', 'Flexible leaderboard widget supporting filters for hunts, tournaments, affiliate sites, and timeframes.' ),
'attributes'  => array(
'type'    => bhg_t( 'sc_attr_type', 'hunts or tournaments view.' ),
'timeline' => bhg_t( 'sc_attr_timeline', 'Limit results to a time period.' ),
'order'   => bhg_t( 'sc_attr_order', 'asc or desc ordering.' ),
'orderby' => bhg_t( 'sc_attr_orderby_leaderboard', 'wins, points, avg_hunt, avg_tournament, user.' ),
),
),
array(
'tag'         => '[bhg_prizes]',
'description' => bhg_t( 'sc_desc_prizes', 'Standalone prize gallery filtered by category, status, or layout.' ),
'attributes'  => array(
'category' => bhg_t( 'sc_attr_category', 'cash_money, casino_money, coupons, merchandise, various.' ),
'design'   => bhg_t( 'sc_attr_prize_layout', 'grid or carousel.' ),
'size'     => bhg_t( 'sc_attr_prize_size', 'small, medium, big.' ),
'active'   => bhg_t( 'sc_attr_active', 'yes, no, or blank for all.' ),
),
),
array(
'tag'         => '[bhg_winner_notifications]',
'description' => bhg_t( 'sc_desc_winner_notifications', 'Compact list of latest winners suitable for sidebars.' ),
'attributes'  => array(
'limit' => bhg_t( 'sc_attr_limit', 'Number of hunts to include (default 5).' ),
),
),
array(
'tag'         => '[bhg_user_profile]',
'description' => bhg_t( 'sc_desc_user_profile', 'Displays the logged-in user profile overview with affiliate status.' ),
'attributes'  => array(),
),
array(
'tag'         => '[my_bonushunts]',
'description' => bhg_t( 'sc_desc_my_bonushunts', 'Shows all bonus hunts the current user participated in with ranking details.' ),
'attributes'  => array(),
),
array(
'tag'         => '[my_tournaments]',
'description' => bhg_t( 'sc_desc_my_tournaments', 'Lists tournaments the current user has results in along with ranking and points.' ),
'attributes'  => array(),
),
array(
'tag'         => '[my_prizes]',
'description' => bhg_t( 'sc_desc_my_prizes', 'Summarises prizes earned from winning bonus hunts.' ),
'attributes'  => array(),
),
array(
'tag'         => '[my_rankings]',
'description' => bhg_t( 'sc_desc_my_rankings', 'Combined overview of the user’s bonus hunt placements and tournament standings.' ),
'attributes'  => array(),
),
);
?>
<div class="wrap bhg-wrap">
<h1><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>
<p class="description"><?php echo esc_html( bhg_t( 'shortcodes_overview_help', 'Use the following shortcodes inside posts, pages, or widgets. Attributes are optional unless noted.' ) ); ?></p>

<table class="widefat striped">
<thead>
<tr>
<th><?php echo esc_html( bhg_t( 'label_shortcode', 'Shortcode' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_description', 'Description' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_attributes', 'Attributes' ) ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $shortcodes as $shortcode ) : ?>
<tr>
<td><code><?php echo esc_html( $shortcode['tag'] ); ?></code></td>
<td><?php echo esc_html( $shortcode['description'] ); ?></td>
<td>
<?php
if ( empty( $shortcode['attributes'] ) ) {
echo '<span class="description">' . esc_html( bhg_t( 'label_no_attributes', 'No additional attributes.' ) ) . '</span>';
} else {
echo '<ul class="bhg-shortcode-attributes">';
foreach ( $shortcode['attributes'] as $attribute => $explanation ) {
echo '<li><code>' . esc_html( $attribute ) . '</code> — ' . esc_html( $explanation ) . '</li>';
}
echo '</ul>';
}
?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
