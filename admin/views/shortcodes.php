<?php
/**
 * Shortcodes reference screen.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die(
		esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) )
	);
}

$shortcodes = array(
	array(
		'tag'         => '[bhg_active_hunt]',
		'description' => bhg_t( 'shortcodes_display_currently_open_bonus_hunts_with_the_live_guess_table_and_associated_prizes', 'Display currently open bonus hunts with the live guess table and associated prizes.' ),
		'aliases'     => array( '[bhg_active]' ),
		'attributes'  => array(
			array(
				'name'        => 'prize_layout',
				'values'      => 'grid | carousel',
				'default'     => 'grid',
				'description' => bhg_t( 'shortcodes_controls_how_the_selected_hunt_s_prizes_are_rendered', 'Controls how the selected hunt\'s prizes are rendered.' ),
			),
			array(
				'name'        => 'prize_size',
				'values'      => 'small | medium | big',
				'default'     => 'medium',
				'description' => bhg_t( 'shortcodes_selects_the_registered_image_size_used_for_prize_artwork', 'Selects the registered image size used for prize artwork.' ),
			),
		),
		'example'     => '[bhg_active_hunt prize_layout="carousel" prize_size="big"]',
	),
	array(
		'tag'         => '[bhg_guess_form]',
		'description' => bhg_t( 'shortcodes_interactive_guess_submission_form_that_respects_guessing_toggles_and_allows_users_to_edit_prior_guesses', 'Interactive guess submission form that respects guessing toggles and allows users to edit prior guesses.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'hunt_id',
				'values'      => bhg_t( 'shortcodes_numeric_hunt_id', 'Numeric hunt ID' ),
				'default'     => bhg_t( 'shortcodes_0_auto_detect_latest_open_hunt', '0 (auto-detect latest open hunt)' ),
				'description' => bhg_t( 'shortcodes_lock_the_form_to_a_specific_hunt_when_multiple_hunts_are_open', 'Lock the form to a specific hunt when multiple hunts are open.' ),
			),
		),
		'example'     => '[bhg_guess_form hunt_id="42"]',
	),
	array(
		'tag'         => '[bhg_user_guesses]',
		'description' => bhg_t( 'shortcodes_paginated_leaderboard_of_guesses_for_a_hunt_with_affiliate_and_timeline_filters', 'Paginated leaderboard of guesses for a hunt with affiliate and timeline filters.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'id',
				'values'      => bhg_t( 'shortcodes_numeric_hunt_id', 'Numeric hunt ID' ),
				'default'     => bhg_t( 'shortcodes_0_latest_active_or_most_recent_hunt', '0 (latest active or most recent hunt)' ),
				'description' => bhg_t( 'shortcodes_restrict_results_to_a_specific_hunt', 'Restrict results to a specific hunt.' ),
			),
			array(
				'name'        => 'aff',
				'values'      => 'yes | no',
				'default'     => bhg_t( 'shortcodes_blank_show_all_users', 'blank (show all users)' ),
				'description' => bhg_t( 'shortcodes_filter_results_to_affiliate_or_non_affiliate_users', 'Filter results to affiliate or non-affiliate users.' ),
			),
			array(
				'name'        => 'website',
				'values'      => bhg_t( 'shortcodes_affiliate_site_id', 'Affiliate site ID' ),
				'default'     => bhg_t( 'shortcodes_0_all_sites', '0 (all sites)' ),
				'description' => bhg_t( 'shortcodes_limit_guesses_to_a_specific_affiliate_website', 'Limit guesses to a specific affiliate website.' ),
			),
			array(
				'name'        => 'status',
				'values'      => 'open | closed',
				'default'     => bhg_t( 'shortcodes_blank_any_status', 'blank (any status)' ),
				'description' => bhg_t( 'shortcodes_match_hunts_by_their_open_closed_state', 'Match hunts by their open/closed state.' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'this_week | this_month | this_year | last_year | all_time',
				'default'     => bhg_t( 'shortcodes_blank_show_all_dates', 'blank (show all dates)' ),
				'description' => bhg_t( 'shortcodes_apply_timeframe_filters_to_guesses_accepts_aliases_such_as_week_month_year', 'Apply timeframe filters to guesses. Accepts aliases such as week/month/year.' ),
			),
			array(
				'name'        => 'fields',
				'values'      => 'hunt, user, guess, final, site',
				'default'     => 'hunt,user,guess,final',
				'description' => bhg_t( 'shortcodes_choose_which_columns_appear_in_the_table', 'Choose which columns appear in the table.' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'hunt | guess | final | time | difference',
				'default'     => 'hunt',
				'description' => bhg_t( 'shortcodes_set_the_initial_sort_column_open_hunts_default_to_submission_time', 'Set the initial sort column. Open hunts default to submission time.' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => bhg_t( 'shortcodes_initial_sort_direction_open_hunts_default_to_earliest_first', 'Initial sort direction. Open hunts default to earliest first.' ),
			),
			array(
				'name'        => 'paged',
				'values'      => bhg_t( 'shortcodes_positive_integer', 'Positive integer' ),
				'default'     => '1',
				'description' => bhg_t( 'shortcodes_override_the_starting_page_when_embedding_multiple_lists', 'Override the starting page when embedding multiple lists.' ),
			),
			array(
				'name'        => 'search',
				'values'      => bhg_t( 'shortcodes_free_text', 'Free text' ),
				'default'     => bhg_t( 'shortcodes_blank', 'blank' ),
				'description' => bhg_t( 'shortcodes_seed_the_search_box_with_a_value', 'Seed the search box with a value.' ),
			),
		),
		'example'     => '[bhg_user_guesses id="12" fields="user,guess,difference" timeline="this_month"]',
	),
	array(
		'tag'         => '[bhg_hunts]',
		'description' => bhg_t( 'shortcodes_front_end_list_of_hunts_with_sortable_columns_search_and_affiliate_filters', 'Front-end list of hunts with sortable columns, search, and affiliate filters.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'id',
				'values'      => bhg_t( 'shortcodes_numeric_hunt_id', 'Numeric hunt ID' ),
				'default'     => bhg_t( 'shortcodes_0_all_hunts', '0 (all hunts)' ),
				'description' => bhg_t( 'shortcodes_show_a_single_hunt_when_an_id_is_supplied', 'Show a single hunt when an ID is supplied.' ),
			),
			array(
				'name'        => 'aff',
				'values'      => 'yes | no',
				'default'     => 'no',
				'description' => bhg_t( 'shortcodes_highlight_affiliate_status_lights_in_the_results', 'Highlight affiliate status lights in the results.' ),
			),
			array(
				'name'        => 'website',
				'values'      => bhg_t( 'shortcodes_affiliate_site_id', 'Affiliate site ID' ),
				'default'     => bhg_t( 'shortcodes_0_all_sites', '0 (all sites)' ),
				'description' => bhg_t( 'shortcodes_limit_hunts_to_a_specific_affiliate_website', 'Limit hunts to a specific affiliate website.' ),
			),
			array(
				'name'        => 'status',
				'values'      => 'active | closed',
				'default'     => bhg_t( 'shortcodes_blank_any_status', 'blank (any status)' ),
				'description' => bhg_t( 'shortcodes_filter_hunts_by_open_or_closed_status', 'Filter hunts by open or closed status.' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'this_week | this_month | this_year | last_year | all_time',
				'default'     => bhg_t( 'shortcodes_blank_show_all_dates', 'blank (show all dates)' ),
				'description' => bhg_t( 'shortcodes_restrict_hunts_by_creation_date_using_timeline_keywords', 'Restrict hunts by creation date using timeline keywords.' ),
			),
			array(
				'name'        => 'fields',
				'values'      => 'title, start, final, winners, status, user, site',
				'default'     => 'title,start,final,status',
				'description' => bhg_t( 'shortcodes_control_which_columns_render_in_the_table', 'Control which columns render in the table.' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'title | start | final | winners | status | created',
				'default'     => 'created',
				'description' => bhg_t( 'shortcodes_initial_sort_column', 'Initial sort column.' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => bhg_t( 'shortcodes_initial_sort_direction', 'Initial sort direction.' ),
			),
			array(
				'name'        => 'paged',
				'values'      => bhg_t( 'shortcodes_positive_integer', 'Positive integer' ),
				'default'     => '1',
				'description' => bhg_t( 'shortcodes_set_the_starting_page_number', 'Set the starting page number.' ),
			),
			array(
				'name'        => 'search',
				'values'      => bhg_t( 'shortcodes_free_text', 'Free text' ),
				'default'     => bhg_t( 'shortcodes_blank', 'blank' ),
				'description' => bhg_t( 'shortcodes_seed_the_hunts_search_box', 'Seed the hunts search box.' ),
			),
		),
		'example'     => '[bhg_hunts status="active" timeline="this_month" fields="title,start,final,winners,site"]',
	),
	array(
		'tag'         => '[bhg_tournaments]',
		'description' => bhg_t( 'shortcodes_tournament_directory_with_detail_view_sortable_columns_and_timeline_filters', 'Tournament directory with detail view, sortable columns, and timeline filters.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'status',
				'values'      => 'active | closed',
				'default'     => 'active',
				'description' => bhg_t( 'shortcodes_list_only_active_or_closed_tournaments', 'List only active or closed tournaments.' ),
			),
			array(
				'name'        => 'tournament',
				'values'      => bhg_t( 'shortcodes_numeric_tournament_id', 'Numeric tournament ID' ),
				'default'     => '0',
				'description' => bhg_t( 'shortcodes_preselect_a_specific_tournament_in_the_filter_dropdown', 'Preselect a specific tournament in the filter dropdown.' ),
			),
			array(
				'name'        => 'website',
				'values'      => bhg_t( 'shortcodes_affiliate_site_id', 'Affiliate site ID' ),
				'default'     => '0',
				'description' => bhg_t( 'shortcodes_filter_tournaments_by_affiliate_site', 'Filter tournaments by affiliate site.' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'weekly | monthly | yearly | quarterly | alltime | this_week | this_month | this_year | last_year',
				'default'     => bhg_t( 'shortcodes_blank_show_all_types', 'blank (show all types)' ),
				'description' => bhg_t( 'shortcodes_match_tournaments_by_time_based_type_or_explicit_timeline_alias', 'Match tournaments by time-based type or explicit timeline alias.' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'title | start_date | end_date | status | type',
				'default'     => 'start_date',
				'description' => bhg_t( 'shortcodes_initial_sort_column_for_the_table_view', 'Initial sort column for the table view.' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => bhg_t( 'shortcodes_initial_sort_direction', 'Initial sort direction.' ),
			),
			array(
				'name'        => 'paged',
				'values'      => bhg_t( 'shortcodes_positive_integer', 'Positive integer' ),
				'default'     => '1',
				'description' => bhg_t( 'shortcodes_set_the_starting_page_number', 'Set the starting page number.' ),
			),
			array(
				'name'        => 'search',
				'values'      => bhg_t( 'shortcodes_free_text', 'Free text' ),
				'default'     => bhg_t( 'shortcodes_blank', 'blank' ),
				'description' => bhg_t( 'shortcodes_seed_the_tournaments_search_box', 'Seed the tournaments search box.' ),
			),
		),
		'example'     => '[bhg_tournaments status="active" timeline="yearly"]',
	),
	array(
		'tag'         => '[bhg_leaderboards]',
		'description' => bhg_t( 'shortcodes_comprehensive_leaderboard_with_wins_averages_and_affiliate_filters', 'Comprehensive leaderboard with wins, averages, and affiliate filters.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'fields',
				'values'      => 'pos, user, wins, avg_hunt, avg_tournament, aff, site, hunt, tournament',
				'default'     => 'pos,user,wins,avg_hunt,avg_tournament',
				'description' => bhg_t( 'shortcodes_choose_which_leaderboard_columns_render', 'Choose which leaderboard columns render.' ),
			),
			array(
				'name'        => 'ranking',
				'values'      => '1 – 10',
				'default'     => '1',
				'description' => bhg_t( 'shortcodes_limit_the_number_of_rows_displayed', 'Limit the number of rows displayed.' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'day | week | month | year | quarter | all_time (aliases supported)',
				'default'     => 'all_time',
				'description' => bhg_t( 'shortcodes_set_the_scoring_window_for_results', 'Set the scoring window for results.' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'wins | avg_hunt | avg_tournament | user',
				'default'     => 'wins',
				'description' => bhg_t( 'shortcodes_initial_sort_column', 'Initial sort column.' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => bhg_t( 'shortcodes_initial_sort_direction', 'Initial sort direction.' ),
			),
			array(
				'name'        => 'search',
				'values'      => bhg_t( 'shortcodes_free_text', 'Free text' ),
				'default'     => bhg_t( 'shortcodes_blank', 'blank' ),
				'description' => bhg_t( 'shortcodes_seed_the_leaderboard_search_box', 'Seed the leaderboard search box.' ),
			),
			array(
				'name'        => 'tournament',
				'values'      => bhg_t( 'shortcodes_tournament_id', 'Tournament ID' ),
				'default'     => '0',
				'description' => bhg_t( 'shortcodes_filter_scores_by_tournament', 'Filter scores by tournament.' ),
			),
			array(
				'name'        => 'bonushunt',
				'values'      => bhg_t( 'shortcodes_hunt_id', 'Hunt ID' ),
				'default'     => '0',
				'description' => bhg_t( 'shortcodes_filter_scores_by_a_single_hunt', 'Filter scores by a single hunt.' ),
			),
			array(
				'name'        => 'website',
				'values'      => bhg_t( 'shortcodes_affiliate_site_id', 'Affiliate site ID' ),
				'default'     => '0',
				'description' => bhg_t( 'shortcodes_filter_scores_by_affiliate_website', 'Filter scores by affiliate website.' ),
			),
			array(
				'name'        => 'aff',
				'values'      => 'yes | no',
				'default'     => bhg_t( 'shortcodes_blank_any_user', 'blank (any user)' ),
				'description' => bhg_t( 'shortcodes_filter_leaderboard_rows_by_affiliate_status', 'Filter leaderboard rows by affiliate status.' ),
			),
		),
		'example'     => '[bhg_leaderboards ranking="10" timeline="month" fields="pos,user,wins,avg_hunt,aff,site"]',
	),
	array(
		'tag'         => '[bhg_leaderboard]',
		'description' => bhg_t( 'shortcodes_legacy_single_hunt_leaderboard_retained_for_backward_compatibility', 'Legacy single-hunt leaderboard retained for backward compatibility.' ),
		'aliases'     => array( '[bonus_hunt_leaderboard]' ),
		'attributes'  => array(
			array(
				'name'        => 'hunt_id',
				'values'      => bhg_t( 'shortcodes_numeric_hunt_id', 'Numeric hunt ID' ),
				'default'     => bhg_t( 'shortcodes_0_latest_hunt', '0 (latest hunt)' ),
				'description' => bhg_t( 'shortcodes_choose_which_hunt_to_display', 'Choose which hunt to display.' ),
			),
			array(
				'name'        => 'fields',
				'values'      => 'position, user, guess',
				'default'     => 'position,user,guess',
				'description' => bhg_t( 'shortcodes_columns_rendered_in_the_legacy_table', 'Columns rendered in the legacy table.' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'guess | user | position',
				'default'     => 'guess',
				'description' => bhg_t( 'shortcodes_initial_sort_column', 'Initial sort column.' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'ASC',
				'description' => bhg_t( 'shortcodes_initial_sort_direction', 'Initial sort direction.' ),
			),
			array(
				'name'        => 'paged',
				'values'      => bhg_t( 'shortcodes_positive_integer', 'Positive integer' ),
				'default'     => '1',
				'description' => bhg_t( 'shortcodes_set_the_starting_page_number', 'Set the starting page number.' ),
			),
			array(
				'name'        => 'per_page',
				'values'      => bhg_t( 'shortcodes_positive_integer', 'Positive integer' ),
				'default'     => bhg_t( 'shortcodes_bhg_get_per_page_shortcode_leaderboard', 'bhg_get_per_page( "shortcode_leaderboard" )' ),
				'description' => bhg_t( 'shortcodes_override_rows_per_page', 'Override rows per page.' ),
			),
			array(
				'name'        => 'search',
				'values'      => bhg_t( 'shortcodes_free_text', 'Free text' ),
				'default'     => bhg_t( 'shortcodes_blank', 'blank' ),
				'description' => bhg_t( 'shortcodes_seed_the_legacy_search_box', 'Seed the legacy search box.' ),
			),
		),
		'example'     => '[bhg_leaderboard hunt_id="24" order="ASC"]',
	),
	array(
		'tag'         => '[bhg_best_guessers]',
		'description' => bhg_t( 'shortcodes_tabbed_widget_that_highlights_the_best_guessers_overall_monthly_yearly_and_all_time', 'Tabbed widget that highlights the best guessers overall, monthly, yearly, and all-time.' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bhg_best_guessers]',
	),
	array(
		'tag'         => '[bhg_winner_notifications]',
		'description' => bhg_t( 'shortcodes_compact_feed_of_recently_closed_hunts_and_their_winners', 'Compact feed of recently closed hunts and their winners.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'limit',
				'values'      => bhg_t( 'shortcodes_positive_integer', 'Positive integer' ),
				'default'     => '5',
				'description' => bhg_t( 'shortcodes_number_of_closed_hunts_to_show', 'Number of closed hunts to show.' ),
			),
		),
		'example'     => '[bhg_winner_notifications limit="3"]',
	),
	array(
		'tag'         => '[bhg_prizes]',
		'description' => bhg_t( 'shortcodes_displays_prize_cards_as_a_grid_or_carousel_with_optional_category_filters', 'Displays prize cards as a grid or carousel with optional category filters.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'category',
				'values'      => 'cash | casino | coupon | merch | various',
				'default'     => bhg_t( 'shortcodes_blank_all_categories', 'blank (all categories)' ),
				'description' => bhg_t( 'shortcodes_limit_results_to_a_single_prize_category', 'Limit results to a single prize category.' ),
			),
			array(
				'name'        => 'design',
				'values'      => 'grid | carousel',
				'default'     => 'grid',
				'description' => bhg_t( 'shortcodes_choose_the_display_layout', 'Choose the display layout.' ),
			),
			array(
				'name'        => 'size',
				'values'      => 'small | medium | big',
				'default'     => 'medium',
				'description' => bhg_t( 'shortcodes_select_the_registered_prize_image_size', 'Select the registered prize image size.' ),
			),
			array(
				'name'        => 'active',
				'values'      => 'yes | no',
				'default'     => 'yes',
				'description' => bhg_t( 'shortcodes_show_only_active_prizes_by_default', 'Show only active prizes by default.' ),
			),
		),
		'example'     => '[bhg_prizes category="cash" design="carousel" size="big"]',
	),
	array(
		'tag'         => '[bhg_user_profile]',
		'description' => bhg_t( 'shortcodes_profile_summary_for_the_logged_in_user_with_affiliate_indicators_and_edit_link', 'Profile summary for the logged-in user with affiliate indicators and edit link.' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bhg_user_profile]',
	),
	array(
		'tag'         => '[bhg_advertising]',
		'description' => bhg_t( 'shortcodes_render_a_specific_ad_block_when_ads_are_enabled', 'Render a specific ad block when ads are enabled.' ),
		'aliases'     => array( '[bhg_ad]' ),
		'attributes'  => array(
			array(
				'name'        => 'id',
				'values'      => bhg_t( 'shortcodes_ad_id_also_accepts_ad', 'Ad ID (also accepts ad="")' ),
				'default'     => '0',
				'description' => bhg_t( 'shortcodes_select_which_ad_to_render', 'Select which ad to render.' ),
			),
			array(
				'name'        => 'status',
				'values'      => 'active | inactive | all',
				'default'     => 'active',
				'description' => bhg_t( 'shortcodes_choose_whether_to_render_only_active_ads_or_include_inactive_ones', 'Choose whether to render only active ads or include inactive ones.' ),
			),
		),
		'example'     => '[bhg_advertising ad="7" status="all"]',
	),
	array(
		'tag'         => '[bhg_nav]',
		'description' => bhg_t( 'shortcodes_outputs_the_appropriate_navigation_menu_for_the_requested_audience', 'Outputs the appropriate navigation menu for the requested audience.' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'area',
				'values'      => 'admin | user | guest',
				'default'     => 'guest',
				'description' => bhg_t( 'shortcodes_force_a_specific_menu_location_instead_of_auto_detecting_by_role', 'Force a specific menu location instead of auto-detecting by role.' ),
			),
		),
		'example'     => '[bhg_nav area="admin"]',
	),
	array(
		'tag'         => '[bhg_menu]',
		'description' => bhg_t( 'shortcodes_automatically_renders_the_correct_bhg_menu_based_on_the_current_visitor', 'Automatically renders the correct BHG menu based on the current visitor.' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bhg_menu]',
	),
	array(
		'tag'         => '[bonus_hunt_login]',
		'description' => bhg_t( 'shortcodes_login_prompt_that_preserves_the_current_page_and_links_to_wp_login_php', 'Login prompt that preserves the current page and links to wp-login.php.' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bonus_hunt_login]',
	),
);

?>
<div class="wrap bhg-admin bhg-shortcodes">
<h1><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>
<p class="description"><?php echo esc_html( bhg_t( 'shortcodes_all_shortcode_tables_honour_the_bhg_orderby_bhg_order_bhg_paged_bhg_search_and_bhg_timeline_query_arguments_in_addition_to_the_attributes_listed_below', 'All shortcode tables honour the bhg_orderby, bhg_order, bhg_paged, bhg_search, and bhg_timeline query arguments in addition to the attributes listed below.' ) ); ?></p>
<p class="description"><?php echo esc_html( bhg_t( 'shortcodes_per_page_limits_use_the_bhg_get_per_page_helper_so_filters_such_as_bhg_hunts_per_page_or_bhg_user_guesses_per_page_can_adjust_row_counts_globally', 'Per-page limits use the bhg_get_per_page() helper, so filters such as bhg_hunts_per_page or bhg_user_guesses_per_page can adjust row counts globally.' ) ); ?></p>
		<?php foreach ( $shortcodes as $shortcode ) : ?>
				<section class="bhg-shortcode-card">
						<h2><code><?php echo esc_html( $shortcode['tag'] ); ?></code></h2>
						<p><?php echo esc_html( $shortcode['description'] ); ?></p>
						<?php if ( ! empty( $shortcode['aliases'] ) ) : ?>
								<p><strong><?php echo esc_html( bhg_t( 'shortcodes_aliases', 'Aliases' ) ); ?>:</strong> <?php echo esc_html( implode( ', ', $shortcode['aliases'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $shortcode['attributes'] ) ) : ?>
								<table class="widefat striped">
										<thead>
												<tr>
														<th><?php echo esc_html( bhg_t( 'shortcodes_attribute', 'Attribute' ) ); ?></th>
														<th><?php echo esc_html( bhg_t( 'shortcodes_values_default', 'Values / Default' ) ); ?></th>
														<th><?php echo esc_html( bhg_t( 'shortcodes_notes', 'Notes' ) ); ?></th>
												</tr>
										</thead>
								<tbody>
									<?php
									foreach ( $shortcode['attributes'] as $attribute ) :
										$values = isset( $attribute['values'] ) ? $attribute['values'] : '';

										if ( isset( $attribute['default'] ) && '' !== $attribute['default'] ) {
											/* translators: %s Default shortcode value. */
											$values .= ' ' . sprintf( esc_html( bhg_t( 'shortcodes_default_s', '(default: %s)' ) ), $attribute['default'] );
										}
										?>
										<tr>
											<td><code><?php echo esc_html( $attribute['name'] ); ?></code></td>
											<td><?php echo esc_html( $values ); ?></td>
											<td><?php echo esc_html( $attribute['description'] ); ?></td>
										</tr>
								<?php endforeach; ?>
							</tbody>
								</table>
						<?php endif; ?>
						<?php if ( ! empty( $shortcode['example'] ) ) : ?>
								<p><strong><?php echo esc_html( bhg_t( 'shortcodes_example', 'Example' ) ); ?>:</strong></p>
								<pre><code><?php echo esc_html( $shortcode['example'] ); ?></code></pre>
						<?php endif; ?>
				</section>
		<?php endforeach; ?>
</div>
