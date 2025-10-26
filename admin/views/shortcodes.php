<?php
/**
 * Shortcodes reference admin view.
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
		'tag'         => 'bhg_active_hunt',
		'aliases'     => array( 'bhg_active' ),
		'description' => bhg_t( 'shortcode_active_hunt_desc', 'Displays the currently active bonus hunt with prize information.' ),
		'example'     => '[bhg_active_hunt prize_layout="carousel" prize_size="small"]',
		'attributes'  => array(
			'prize_layout' => bhg_t( 'shortcode_attr_prize_layout', 'Prize layout (grid or carousel).' ),
			'prize_size'   => bhg_t( 'shortcode_attr_prize_size', 'Prize size (small, medium, big).' ),
		),
	),
	array(
		'tag'         => 'bhg_guess_form',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_guess_form_desc', 'Outputs the guess submission form for a selected hunt.' ),
		'example'     => '[bhg_guess_form hunt_id="42"]',
		'attributes'  => array(
			'hunt_id' => bhg_t( 'shortcode_attr_hunt_id', 'Optional hunt ID override (defaults to the newest open hunt).' ),
		),
	),
	array(
		'tag'         => 'bhg_leaderboard',
		'aliases'     => array( 'bonus_hunt_leaderboard' ),
		'description' => bhg_t( 'shortcode_leaderboard_desc', 'Shows the leaderboard for a bonus hunt.' ),
		'example'     => '[bhg_leaderboard hunt_id="42" orderby="user" order="DESC"]',
		'attributes'  => array(
			'hunt_id'  => bhg_t( 'shortcode_attr_hunt_id', 'Optional hunt ID override (defaults to the newest closed hunt).' ),
			'orderby'  => bhg_t( 'shortcode_attr_leaderboard_orderby', 'Sort by guess, user, or position.' ),
			'order'    => bhg_t( 'shortcode_attr_leaderboard_order', 'Sort direction (ASC or DESC).' ),
			'fields'   => bhg_t( 'shortcode_attr_leaderboard_fields', 'Comma-separated columns to display (position,user,guess,difference,price).' ),
			'per_page' => bhg_t( 'shortcode_attr_per_page', 'Rows per page (defaults to 30).' ),
			'paged'    => bhg_t( 'shortcode_attr_paged', 'Starting page number; query vars also supported.' ),
			'search'   => bhg_t( 'shortcode_attr_search', 'Preset search term (users can override with query vars).' ),
		),
	),
	array(
		'tag'         => 'bhg_leaderboards',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_leaderboards_desc', 'Displays aggregate leaderboards with timeframe and tournament filters.' ),
		'example'     => '[bhg_leaderboards timeline="monthly" ranking="5"]',
		'attributes'  => array(
			'fields'     => bhg_t( 'shortcode_attr_leaderboards_fields', 'Comma-separated columns (pos,user,wins,avg_hunt,avg_tournament,aff,site).' ),
			'ranking'    => bhg_t( 'shortcode_attr_ranking', 'Number of entries to display.' ),
			'timeline'   => bhg_t( 'shortcode_attr_timeline', 'Timeline filter (weekly, monthly, quarterly, yearly, alltime).' ),
			'orderby'    => bhg_t( 'shortcode_attr_orderby_generic', 'Column used for sorting.' ),
			'order'      => bhg_t( 'shortcode_attr_order_generic', 'Sort direction (ASC or DESC).' ),
			'search'     => bhg_t( 'shortcode_attr_search', 'Preset search term (users can override with query vars).' ),
			'tournament' => bhg_t( 'shortcode_attr_tournament_filter', 'Filter results to a tournament ID.' ),
			'bonushunt'  => bhg_t( 'shortcode_attr_bonushunt_filter', 'Filter results to a bonus hunt ID.' ),
			'website'    => bhg_t( 'shortcode_attr_website', 'Filter by affiliate website ID.' ),
			'aff'        => bhg_t( 'shortcode_attr_aff', 'Filter by affiliate status (yes or no).' ),
		),
	),
	array(
		'tag'         => 'bhg_prizes',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_prizes_desc', 'Lists prizes with optional layout and category filters.' ),
		'example'     => '[bhg_prizes category="cash-money" design="carousel" size="small" active="yes"]',
		'attributes'  => array(
			'category' => bhg_t( 'shortcode_attr_category', 'Limit prizes to a category slug.' ),
			'design'   => bhg_t( 'shortcode_attr_design', 'Layout type (grid or carousel).' ),
			'size'     => bhg_t( 'shortcode_attr_size', 'Prize image size (small, medium, big).' ),
			'active'   => bhg_t( 'shortcode_attr_active', 'Limit to active prizes (yes or no).' ),
		),
	),
	array(
		'tag'         => 'bhg_tournaments',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_tournaments_desc', 'Outputs tournaments with filters for status, type, and affiliate site.' ),
		'example'     => '[bhg_tournaments status="active" timeline="quarterly"]',
		'attributes'  => array(
			'status'     => bhg_t( 'shortcode_attr_status', 'Status filter (active or closed).' ),
			'tournament' => bhg_t( 'shortcode_attr_tournament_filter', 'Limit to a specific tournament ID.' ),
			'website'    => bhg_t( 'shortcode_attr_website', 'Filter by affiliate website ID.' ),
			'timeline'   => bhg_t( 'shortcode_attr_timeline', 'Timeline or type filter (weekly, monthly, quarterly, yearly, alltime).' ),
			'orderby'    => bhg_t( 'shortcode_attr_orderby_generic', 'Column used for sorting.' ),
			'order'      => bhg_t( 'shortcode_attr_order_generic', 'Sort direction (ASC or DESC).' ),
			'paged'      => bhg_t( 'shortcode_attr_paged', 'Starting page number; query vars also supported.' ),
			'search'     => bhg_t( 'shortcode_attr_search', 'Preset search term (users can override with query vars).' ),
		),
	),
	array(
		'tag'         => 'bhg_hunts',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_hunts_desc', 'Shows hunts with optional affiliate and timeline filters.' ),
		'example'     => '[bhg_hunts status="closed" fields="title,start,final,winners"]',
		'attributes'  => array(
			'id'       => bhg_t( 'shortcode_attr_hunt_id', 'Target a single hunt ID.' ),
			'aff'      => bhg_t( 'shortcode_attr_aff', 'Filter by affiliate status (yes or no).' ),
			'website'  => bhg_t( 'shortcode_attr_website', 'Filter by affiliate website ID.' ),
			'status'   => bhg_t( 'shortcode_attr_status', 'Status filter (open or closed).' ),
			'timeline' => bhg_t( 'shortcode_attr_timeline', 'Timeline filter (weekly, monthly, quarterly, yearly, alltime).' ),
			'fields'   => bhg_t( 'shortcode_attr_fields_generic', 'Comma-separated columns to show (title,start,final,status,winners,site).' ),
			'orderby'  => bhg_t( 'shortcode_attr_orderby_generic', 'Column used for sorting.' ),
			'order'    => bhg_t( 'shortcode_attr_order_generic', 'Sort direction (ASC or DESC).' ),
			'paged'    => bhg_t( 'shortcode_attr_paged', 'Starting page number; query vars also supported.' ),
			'search'   => bhg_t( 'shortcode_attr_search', 'Preset search term (users can override with query vars).' ),
		),
	),
	array(
		'tag'         => 'bhg_user_guesses',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_user_guesses_desc', 'Lists user guesses with sorting and affiliate filters.' ),
		'example'     => '[bhg_user_guesses aff="yes" fields="hunt,user,guess,final"]',
		'attributes'  => array(
			'id'       => bhg_t( 'shortcode_attr_user_id', 'User ID to inspect (defaults to current user when logged in).' ),
			'aff'      => bhg_t( 'shortcode_attr_aff', 'Filter by affiliate status (yes or no).' ),
			'website'  => bhg_t( 'shortcode_attr_website', 'Filter by affiliate website ID.' ),
			'status'   => bhg_t( 'shortcode_attr_status', 'Hunt status filter (open or closed).' ),
			'timeline' => bhg_t( 'shortcode_attr_timeline', 'Timeline filter (weekly, monthly, quarterly, yearly, alltime).' ),
			'fields'   => bhg_t( 'shortcode_attr_fields_generic', 'Comma-separated columns to show (hunt,user,guess,final,site).' ),
			'orderby'  => bhg_t( 'shortcode_attr_orderby_generic', 'Column used for sorting.' ),
			'order'    => bhg_t( 'shortcode_attr_order_generic', 'Sort direction (ASC or DESC).' ),
			'paged'    => bhg_t( 'shortcode_attr_paged', 'Starting page number; query vars also supported.' ),
			'search'   => bhg_t( 'shortcode_attr_search', 'Preset search term (users can override with query vars).' ),
		),
	),
	array(
		'tag'         => 'bhg_best_guessers',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_best_guessers_desc', 'Tabs for overall, monthly, and yearly best guessers.' ),
		'example'     => '[bhg_best_guessers]',
		'attributes'  => array(),
	),
	array(
		'tag'         => 'bhg_winner_notifications',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_winner_notifications_desc', 'Lists latest winners with final balances.' ),
		'example'     => '[bhg_winner_notifications limit="5"]',
		'attributes'  => array(
			'limit' => bhg_t( 'shortcode_attr_limit', 'Number of hunts to show.' ),
		),
	),
	array(
		'tag'         => 'bhg_user_profile',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_user_profile_desc', 'Displays the logged-in user profile with affiliate badges.' ),
		'example'     => '[bhg_user_profile]',
		'attributes'  => array(),
	),
	array(
		'tag'         => 'bonus_hunt_login',
		'aliases'     => array(),
		'description' => bhg_t( 'shortcode_login_hint_desc', 'Shows a login prompt that respects smart redirects.' ),
		'example'     => '[bonus_hunt_login]',
		'attributes'  => array(),
	),
);
?>
<div class="wrap bhg-wrap">
	<h1><?php echo esc_html( bhg_t( 'shortcodes_reference', 'Shortcodes Reference' ) ); ?></h1>
	<p class="description"><?php echo esc_html( bhg_t( 'shortcodes_reference_help', 'Use the following tags to embed Bonus Hunt features in your pages.' ) ); ?></p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php echo esc_html( bhg_t( 'shortcode_tag', 'Shortcode' ) ); ?></th>
				<th><?php echo esc_html( bhg_t( 'shortcode_aliases', 'Aliases' ) ); ?></th>
				<th><?php echo esc_html( bhg_t( 'shortcode_attributes', 'Attributes' ) ); ?></th>
				<th><?php echo esc_html( bhg_t( 'shortcode_description', 'Description' ) ); ?></th>
				<th><?php echo esc_html( bhg_t( 'shortcode_example', 'Example' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $shortcodes as $shortcode ) : ?>
			<tr>
				<td><code>[<?php echo esc_html( $shortcode['tag'] ); ?>]</code></td>
				<td>
					<?php
					if ( empty( $shortcode['aliases'] ) ) {
						echo esc_html( bhg_t( 'shortcode_attr_none', 'None' ) );
					} else {
						$alias_tags = array();
						foreach ( $shortcode['aliases'] as $alias ) {
							$alias_tags[] = '<code>[' . esc_html( $alias ) . ']</code>';
						}
						echo wp_kses_post( implode( '<br>', $alias_tags ) );
					}
					?>
				</td>
				<td>
					<?php
					if ( empty( $shortcode['attributes'] ) ) {
						echo esc_html( bhg_t( 'shortcode_attr_none', 'None' ) );
					} else {
						$attr_lines = array();
						foreach ( $shortcode['attributes'] as $attr_name => $attr_desc ) {
							$attr_lines[] = '<code>' . esc_html( $attr_name ) . '</code> — ' . esc_html( $attr_desc );
						}
						echo wp_kses_post( implode( '<br>', $attr_lines ) );
					}
					?>
				</td>
				<td><?php echo esc_html( $shortcode['description'] ); ?></td>
				<td><code><?php echo esc_html( $shortcode['example'] ); ?></code></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
