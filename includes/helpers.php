<?php
/**
 * Helper functions.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log debug messages when WP_DEBUG is enabled.
 *
 * @param mixed $message Message to log.
 * @return void
 */
function bhg_log( $message ) {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}
	if ( is_array( $message ) || is_object( $message ) ) {
			$message = wp_json_encode( $message );
	}
		error_log( '[BHG] ' . $message );
}

/**
 * Get the current user ID or 0 if not logged in.
 *
 * @return int
 */
function bhg_current_user_id() {
	$uid = get_current_user_id();
	return $uid ? (int) $uid : 0;
}

/**
 * Create a URL-friendly slug.
 *
 * @param string $text Text to slugify.
 * @return string
 */
function bhg_slugify( $text ) {
	$text = sanitize_title( $text );
	if ( '' === $text ) {
		$text = uniqid( 'bhg' );
	}
	return $text;
}

/**
 * Get admin capability for BHG plugin.
 *
 * @return string
 */
function bhg_admin_cap() {
	return apply_filters( 'bhg_admin_capability', 'manage_options' );
}

/**
 * Default points awarded per ranking position.
 *
 * @return array<int,int> Associative array indexed by position.
 */
function bhg_get_default_points_config() {
        return array(
                1 => 25,
                2 => 15,
                3 => 10,
                4 => 5,
                5 => 4,
                6 => 3,
                7 => 2,
                8 => 1,
        );
}

/**
 * Retrieve sanitized points configuration.
 *
 * @return array{positions:array<int,int>} Configuration array.
 */
function bhg_get_points_config() {
        $stored    = get_option( 'bhg_points_config', array() );
        $defaults  = bhg_get_default_points_config();
        $positions = isset( $stored['positions'] ) && is_array( $stored['positions'] ) ? $stored['positions'] : array();

        $sanitized = array();
        foreach ( $positions as $pos => $points ) {
                $pos     = absint( $pos );
                $points  = is_numeric( $points ) ? max( 0, (int) $points ) : 0;
                if ( $pos <= 0 ) {
                        continue;
                }
                $sanitized[ $pos ] = $points;
        }

        $config = array( 'positions' => $defaults );
        if ( ! empty( $sanitized ) ) {
                $config['positions'] = array_merge( $defaults, $sanitized );
        }

        ksort( $config['positions'], SORT_NUMERIC );

        return $config;
}

/**
 * Get the number of points for a given winner position.
 *
 * @param int $position Finishing position (1-indexed).
 * @return int Points assigned to the position.
 */
function bhg_get_points_for_position( $position ) {
        $position = max( 1, (int) $position );
        $config   = bhg_get_points_config();

        return isset( $config['positions'][ $position ] ) ? (int) $config['positions'][ $position ] : 0;
}

// Smart login redirect back to referring page.
add_filter(
	'login_redirect',
	function ( $redirect_to, $requested_redirect_to, $user ) {
		$r = isset( $_REQUEST['bhg_redirect'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bhg_redirect'] ) ) : '';
		if ( ! empty( $r ) ) {
				$safe      = esc_url_raw( $r );
				$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
				$r_host    = wp_parse_url( $safe, PHP_URL_HOST );
			if ( ! $r_host || $r_host === $home_host ) {
				return $safe;
			}
		}
		return $redirect_to;
	},
	10,
	3
);

/**
 * Determine if code runs on the frontend.
 *
 * @return bool
 */
function bhg_is_frontend() {
	return ! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron();
}

if ( ! function_exists( 'bhg_t' ) ) {
		/**
		 * Retrieve a translation value from the database.
		 *
		 * @param string $slug    Translation slug.
		 * @param string $default_text Default text if not found.
		 * @param string $locale  Locale to use. Defaults to current locale.
		 * @return string
		 */
	function bhg_t( $slug, $default_text = '', $locale = '' ) {
			global $wpdb;

						$slug      = (string) $slug;
						$locale    = $locale ? (string) $locale : get_locale();
						$cache_key = 'bhg_t_' . $slug . '_' . $locale;
						$cached    = wp_cache_get( $cache_key, 'bhg_translations' );

		if ( false !== $cached ) {
				return (string) $cached;
		}

						$table = esc_sql( $wpdb->prefix . 'bhg_translations' );
						$sql   = $wpdb->prepare(
							"SELECT text, default_text FROM {$table} WHERE slug = %s AND locale = %s",
							$slug,
							$locale
						);
						$row   = $wpdb->get_row( $sql );

		if ( $row ) {
						$value = '' !== $row->text ? (string) $row->text : (string) $row->default_text;
						wp_cache_set( $cache_key, $value, 'bhg_translations' );
						return $value;
		}

						wp_cache_set( $cache_key, (string) $default_text, 'bhg_translations' );
						return (string) $default_text;
	}
}

if ( ! function_exists( 'bhg_clear_translation_cache' ) ) {
	/**
	 * Flush all cached translations.
	 *
	 * Group-based cache flushing is preferred because it targets only
	 * this plugin's cache group, avoiding side effects on unrelated
	 * cached data. Some object cache implementations lack support for
	 * flushing by group, so we fall back to deleting known translation
	 * keys individually.
	 *
	 * @return void
	 */
	function bhg_clear_translation_cache() {
		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( 'bhg_translations' );
		} else {
			global $wpdb;

			$cache_keys = array();
			$locales    = array_unique( array_merge( array( get_locale() ), get_available_languages() ) );
			$slugs      = array_keys( bhg_get_default_translations() );

			foreach ( $locales as $locale ) {
				foreach ( $slugs as $slug ) {
					$cache_keys[] = 'bhg_t_' . $slug . '_' . $locale;
				}
			}

			$table = esc_sql( $wpdb->prefix . 'bhg_translations' );
			$rows  = $wpdb->get_results( "SELECT slug, locale FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( $rows ) {
				foreach ( $rows as $row ) {
					$cache_keys[] = 'bhg_t_' . $row->slug . '_' . $row->locale;
				}
			}

			$cache_keys = array_unique( $cache_keys );

			foreach ( $cache_keys as $key ) {
				wp_cache_delete( $key, 'bhg_translations' );
			}
		}
	}
}

if ( ! function_exists( 'bhg_get_default_translations' ) ) {
	/**
	 * Retrieve default translation key/value pairs.
	 *
	 * @return array
	 */
	function bhg_get_default_translations() {
		return array(
			// General / menus / labels.
			'welcome_message'                              => 'Welcome!',
			'goodbye_message'                              => 'Goodbye!',
			'menu_dashboard'                               => 'Dashboard',
			'menu_bonus_hunts'                             => 'Bonus Hunts',
			'menu_results'                                 => 'Results',
			'menu_tournaments'                             => 'Tournaments',
			'menu_users'                                   => 'Users',
			'menu_affiliates'                              => 'Affiliate Websites',
                        'menu_advertising'                             => 'Advertising',
                        'menu_prizes'                                  => 'Prizes',
			'menu_translations'                            => 'Translations',
			'menu_settings'                                => 'Settings',
			'menu_database'                                => 'Database',
			'menu_tools'                                   => 'Tools',
			'menu_ads'                                     => 'Ads',
			'bhg_menu_admin'                               => 'BHG Menu — Admin/Moderators',
			'bhg_menu_loggedin'                            => 'BHG Menu — Logged-in Users',
			'bhg_menu_guests'                              => 'BHG Menu — Guests',

			// Standalone labels.
			'dashboard'                                    => 'Dashboard',
			'bonus_hunts'                                  => 'Bonus Hunts',
			'results'                                      => 'Results',
			'affiliate_websites'                           => 'Affiliate Websites',
			'advertising'                                  => 'Advertising',
			'translations'                                 => 'Translations',
			'tools'                                        => 'Tools',
			'tournament'                                   => 'Tournament',
			'affiliate'                                    => 'Affiliate',

			// Form/field labels.
			'label_start_balance'                          => 'Starting Balance',
			'label_number_bonuses'                         => 'Number of Bonuses',
                        'label_prizes'                                 => 'Prizes',
                        'category'                                     => 'Category',
			'label_submit_guess'                           => 'Submit Guess',
                        'label_guess'                                  => 'Guess',
                        'label_unknown_user'                           => 'Unknown user',
                        'label_username'                               => 'Username',
                        'images'                                       => 'Images',
                        'css_settings'                                 => 'CSS Settings',
                        'border'                                       => 'Border',
                        'border_color'                                 => 'Border Color',
                        'padding'                                      => 'Padding',
                        'margin'                                       => 'Margin',
                        'background_color'                             => 'Background Color',
                        'image_small'                                  => 'Small',
                        'image_medium'                                 => 'Medium',
                        'image_large'                                  => 'Big',
                        'select_image'                                 => 'Select Image',
                        'clear'                                        => 'Clear',
                        'no_image_selected'                            => 'No image selected',
                        'available'                                    => 'Available',
                        'prize_list'                                   => 'Prize List',
                        'no_prizes_yet'                                => 'No prizes found.',
                        'add_new_prize'                                => 'Add New Prize',
                        'edit_prize'                                   => 'Edit Prize',
                        'add_prize'                                    => 'Add Prize',
                        'update_prize'                                 => 'Update Prize',
                        'prize_saved'                                  => 'Prize saved.',
                        'prize_updated'                                => 'Prize updated.',
                        'prize_deleted'                                => 'Prize deleted.',
                        'prize_error'                                  => 'Unable to save prize.',
                        'prize_slide_label'                            => 'Go to prize %d',
                        'confirm_delete_prize'                         => 'Are you sure you want to delete this prize?',
                        'label_position'                               => 'Position',
                        'label_final_balance'                          => 'Final Balance',
                        'label_difference'                             => 'Difference',
                        'label_pagination'                             => 'Pagination',
			'label_leaderboard'                            => 'Leaderboard',
			'label_best_guessers'                          => 'Best Guessers',
			'label_leaderboard_history'                    => 'Leaderboard History',
			'label_affiliate'                              => 'Affiliate',
			'label_non_affiliate'                          => 'Non-affiliate',
			'label_affiliate_status'                       => 'Affiliate Status',
			'label_site'                                   => 'Site',
			'label_tournament'                             => 'Tournament',
			'label_actions'                                => 'Actions',
			'admin_action'                                 => 'Admin Action',
			'label_id'                                     => 'ID',
			'label_key'                                    => 'Key',
			'label_name'                                   => 'Name',
			'slug'                                         => 'Slug',
			'label_winners'                                => 'Winners',
			'label_title_content'                          => 'Title/Content',
			'label_placement'                              => 'Placement',
			'label_placements'                             => 'Placements',
			'label_visible_to'                             => 'Visible To',
			'label_target_page_slugs'                      => 'Target Page Slugs',
			'label_existing_ads'                           => 'Existing Ads',
			'select_bulk_action'                           => 'Select bulk action',
			'bulk_actions'                                 => 'Bulk actions',
			'apply'                                        => 'Apply',
			'label_yes'                                    => 'Yes',
			'label_no'                                     => 'No',
			'label_default'                                => 'Default',
			'label_custom'                                 => 'Custom',
			'label_items_per_page'                         => 'Items per page',
			'label_search_translations'                    => 'Search translations',
			'label_start_date'                             => 'Start Date',
			'label_end_date'                               => 'End Date',
			'currency'                                     => 'Currency',
			'label_email'                                  => 'Email',
			'label_real_name'                              => 'Real Name',
			'label_search'                                 => 'Search',
			'search_hunts'                                 => 'Search Hunts',
			'label_user'                                   => 'User',
			'label_users'                                  => 'Users',
			'label_role'                                   => 'Role',
			'label_guesses'                                => 'Guesses',
			'label_profile'                                => 'Profile',
			'label_start'                                  => 'Start',
			'label_end'                                    => 'End',
			'label_status'                                 => 'Status',
			'label_status_colon'                           => 'Status:',
			'label_wins'                                   => 'Wins',
			'wins'                                         => 'Wins',
			'label_last_win'                               => 'Last win',
			'label_all'                                    => 'All',
			'label_weekly'                                 => 'Weekly',
			'label_monthly'                                => 'Monthly',
			'label_yearly'                                 => 'Yearly',
			'label_active'                                 => 'Active',
			'label_closed'                                 => 'Closed',
			'label_type'                                   => 'Type',
			'label_details'                                => 'Details',
			'label_show_details'                           => 'Show details',
			'label_bonus_hunts'                            => 'Bonus Hunts',
			'label_overall'                                => 'Overall',
			'label_all_time'                               => 'All-Time',
			'label_final'                                  => 'Final',
			'label_user_number'                            => 'User #%d',
			'label_diff'                                   => 'diff',
			'label_latest_hunts'                           => 'Latest Hunts',
			'label_bonushunt'                              => 'Bonushunt',
			'label_all_winners'                            => 'All Winners',
			'label_closed_at'                              => 'Closed At',
			'label_hunt'                                   => 'Hunt',
			'label_title'                                  => 'Title',
			'label_your_hunts'                             => 'Your Hunts',
			'label_your_guesses'                           => 'Your Guesses',
			'label_winner_notifications'                   => 'Winner Notifications',
			'label_timeline'                               => 'Timeline',
			'label_choose_hunt'                            => 'Choose a hunt:',
                        'label_select_hunt'                            => 'Select a hunt',
                        'label_guess_final_balance'                    => 'Your guess (final balance):',
                        'button_apply'                                 => 'Apply',
			'label_bonus_hunt_title'                       => 'Bonus Hunt Title',
			'label_existing_bonus_hunts'                   => 'Existing Bonus Hunts',
			'label_start_balance_euro'                     => 'Starting Balance (€)',
			'label_prizes_description'                     => 'Prizes Description',
			'label_created'                                => 'Created',
			'label_completed'                              => 'Completed',
			'label_upcoming'                               => 'Upcoming',
			'label_diff_short'                             => 'Diff',
			'label_hash'                                   => '#',
			'label_not_set'                                => 'Not set',
			'label_dash'                                   => '-',
			'label_untitled'                               => '(untitled)',
			'label_back_to_tournaments'                    => 'Back to tournaments',
			'label_last_day'                               => 'Last day',
			'label_last_week'                              => 'Last week',
			'label_last_month'                             => 'Last month',
			'label_last_year'                              => 'Last year',
			'label_quarterly'                              => 'Quarterly',
			'label_alltime'                                => 'Alltime',
			'weekly'                                       => 'Weekly',
			'monthly'                                      => 'Monthly',
			'quarterly'                                    => 'Quarterly',
			'yearly'                                       => 'Yearly',
			'alltime'                                      => 'All-time',
			'this_month'                                   => 'This Month',
			'this_year'                                    => 'This Year',
			'all_time'                                     => 'All Time',
			'label_guests'                                 => 'Guests',
			'label_logged_in'                              => 'Logged In',
			'label_affiliates'                             => 'Affiliates',
			'label_log_in'                                 => 'Log in',
			'label_log_out'                                => 'Log out',
			'label_non_affiliates'                         => 'Non Affiliates',
			'label_affiliate_website'                      => 'Affiliate Website',
			'label_affiliate_websites'                     => 'Affiliate Websites',
			'label_affiliate_user_title'                   => 'Affiliate User',
			'guessing_enabled'                             => 'Guessing Enabled',
			'label_footer'                                 => 'Footer',
			'label_bottom'                                 => 'Bottom',
			'label_sidebar'                                => 'Sidebar',
			'label_shortcode'                              => 'Shortcode',
			'label_timeline_colon'                         => 'Timeline:',
			'label_user_hash'                              => 'user#%d',
			'label_emdash'                                 => '—',
                        'placeholder_enter_guess'                      => 'Enter your guess',
                        'placeholder_custom_value'                     => 'Custom value',
                        'select_multiple_tournaments_hint'             => 'Hold Ctrl (Windows) or Command (Mac) to select multiple tournaments.',
                        'select_multiple_prizes_hint'                  => 'Hold Ctrl (Windows) or Command (Mac) to select multiple prizes.',
                        'post_submit_redirect_url'                     => 'Post-submit redirect URL',
                        'post_submit_redirect_description'             => 'Send users to this URL after submitting or editing a guess. Leave blank to stay on the same page.',
                        'post_submit_redirect_placeholder'             => 'https://example.com/thank-you',

                        // Buttons.
			'button_save'                                  => 'Save',
			'button_cancel'                                => 'Cancel',
			'button_delete'                                => 'Delete',
			'button_edit'                                  => 'Edit',
			'button_view'                                  => 'View',
			'button_back'                                  => 'Back',
			'button_create_new_bonus_hunt'                 => 'Create New Bonus Hunt',
                        'button_results'                               => 'Results',
                        'button_submit_guess'                          => 'Submit Guess',
                        'button_edit_guess'                            => 'Edit Guess',
                        'button_filter'                                => 'Filter',
			'button_log_in'                                => 'Log in',
			'button_view_edit'                             => 'View / Edit',
                        'button_update'                                => 'Update',
                        'close'                                        => 'Close',
                        'delete'                                       => 'Delete',
                        'previous'                                     => 'Previous',
                        'next'                                         => 'Next',
                        'disable_guessing'                             => 'Disable Guessing',
                        'enable_guessing'                              => 'Enable Guessing',

			// Notices / messages.
			'notice_login_required'                        => 'You must be logged in to guess.',
                        'notice_guess_saved'                           => 'Your guess has been saved.',
                        'notice_guess_updated'                         => 'Your guess has been updated.',
                        'guess_updated'                                => 'Your guess has been updated!',
			'notice_hunt_closed'                           => 'This bonus hunt is closed.',
			'notice_invalid_guess'                         => 'Please enter a valid guess.',
			'notice_ajax_error'                            => 'An error occurred. Please try again.',
			'error_loading_leaderboard'                    => 'Error loading leaderboard.',
			'notice_not_authorized'                        => 'You are not authorized to perform this action.',
			'notice_translations_saved'                    => 'Translations saved.',
			'notice_translations_reset'                    => 'Translations reset.',
			'notice_security_check_failed'                 => 'Security check failed.',
			'notice_invalid_nonce'                         => 'Invalid nonce.',
			'notice_invalid_hunt'                          => 'Invalid hunt.',
			'notice_invalid_hunt_id'                       => 'Invalid hunt id.',
			'notice_hunt_not_found'                        => 'Hunt not found.',
			'notice_invalid_guess_amount'                  => 'Invalid guess amount.',
			'notice_max_guesses_reached'                   => 'You have reached the maximum number of guesses.',
			'notice_no_data_available'                     => 'No data available.',
			'notice_guess_removed'                         => 'Guess removed.',
			'notice_hunt_closed_successfully'              => 'Hunt closed successfully.',
			'notice_missing_hunt_id'                       => 'Missing hunt id.',
			'notice_db_update_required'                    => 'Database upgrade required. Please run plugin upgrades.',
			'notice_no_active_hunt'                        => 'No active bonus hunt found.',
			'notice_no_results'                            => 'No results available.',
			'notice_user_removed'                          => 'User removed.',
			'notice_ad_saved'                              => 'Advertisement saved.',
			'notice_ad_deleted'                            => 'Advertisement deleted.',
			'notice_settings_saved'                        => 'Settings saved.',
			'settings_saved'                               => 'Settings saved.',
			'notice_profile_updated'                       => 'Profile updated.',
			'notice_login_to_continue'                     => 'Please log in to continue.',
                        'notice_no_active_hunts'                       => 'No active bonus hunts at the moment.',
                        'notice_no_guesses_yet'                        => 'No guesses have been submitted for this hunt yet.',
			'notice_login_to_guess'                        => 'Please log in to submit your guess.',
			'notice_no_open_hunt'                          => 'No open hunt found to guess.',
			'notice_no_hunts_found'                        => 'No hunts found.',
			'notice_tournament_not_found'                  => 'Tournament not found.',
			'tournament_not_found'                         => 'Tournament not found',
			'notice_no_results_yet'                        => 'No results yet.',
			'notice_no_data_yet'                           => 'No data yet.',
			'notice_no_closed_hunts'                       => 'No closed hunts yet.',
			'notice_login_view_content'                    => 'Please log in to view this content.',
			'notice_no_user_specified'                     => 'No user specified.',
			'notice_no_guesses_found'                      => 'No guesses found.',
			'notice_no_winners_yet'                        => 'No winners yet.',
			'notice_login_view_profile'                    => 'Please log in to view your profile.',
			'notice_please_log_in'                         => 'Please log in.',
			'notice_results_pending'                       => 'Results pending.',
			'notice_bonus_hunt_created'                    => 'Bonus hunt created successfully!',
			'notice_bonus_hunt_updated'                    => 'Bonus hunt updated successfully!',
			'notice_bonus_hunt_deleted'                    => 'Bonus hunt deleted successfully!',
			'notice_guess_removed_successfully'            => 'Guess removed successfully.',
			'notice_no_ads_yet'                            => 'No ads yet.',
			'notice_no_bonus_hunts_found'                  => 'No bonus hunts found.',
			'notice_no_guesses_yet'                        => 'No guesses yet.',
			'notice_no_tournaments_found'                  => 'No tournaments found.',
			'notice_invalid_table'                         => 'Invalid table.',
			'notice_required_helpers_missing'              => 'Required helper functions are missing. Please include class-bhg-bonus-hunts.php helpers.',
			'guessing_disabled_for_this_hunt'              => 'Guessing is disabled for this hunt.',
			'delete_this_hunt'                             => 'Delete this hunt?',
			'confirm_delete_bonus_hunt'                    => 'Are you sure you want to delete this bonus hunt?',
			'title_results_s'                              => 'Results — %s',

			// Shortcode labels for public views.
			'sc_hunt'                                      => 'Hunt',
			'sc_guess'                                     => 'Guess',
			'sc_final'                                     => 'Final',
                        'sc_title'                                     => 'Title',
                        'sc_start_balance'                             => 'Start Balance',
                        'sc_final_balance'                             => 'Final Balance',
                        'sc_winners'                                   => 'Winners',
                        'sc_status'                                    => 'Status',
			'sc_affiliate'                                 => 'Affiliate',
			'sc_position'                                  => 'Position',
			'sc_user'                                      => 'User',
                        'sc_wins'                                      => 'Wins',
                        'label_times_won'                              => 'Times Won',
                        'sc_avg_rank'                                  => 'Avg Hunt Pos',
                        'sc_avg_tournament_pos'                        => 'Avg Tournament Pos',
			'sc_start'                                     => 'Start',
			'sc_end'                                       => 'End',
			'sc_prizes'                                    => 'Prizes',

			// Extended admin/UI strings.
			's_participant'                                => '%s participant',
			'add_ad'                                       => 'Add Ad',
			'add_affiliate'                                => 'Add Affiliate Website',
			'add_new'                                      => 'Add New',
			'add_new_bonus_hunt'                           => 'Add New Bonus Hunt',
			'add_tournament'                               => 'Add Tournament',
			'ads'                                          => 'Ads:',
			'ads_enabled'                                  => 'Enable Ads',
			'affiliate_site'                               => 'Affiliate Site',
			'affiliate_user'                               => 'Affiliate',
			'non_affiliate_user'                           => 'Non-affiliate',
			'affiliate_management_ui_not_provided_yet'     => 'Affiliate management UI not provided yet.',
			'all_affiliate_websites'                       => 'All Affiliate Websites',
			'all_tournaments'                              => 'All Tournaments',
			'allow_guess_changes'                          => 'Allow Guess Changes',
			'allow_guess_editing'                          => 'Allow Guess Editing',
			'allow_users_to_change_their_guesses_before_a_bonus_hunt_closes' => 'Allow users to change their guesses before a bonus hunt closes.',
			'an_error_occurred_while_saving_settings'      => 'An error occurred while saving settings.',
			'an_error_occurred_please_try_again'           => 'An error occurred. Please try again.',
			'are_you_sure_you_want_to_run_database_cleanup_this_action_cannot_be_undone' => 'Are you sure you want to run database cleanup? This action cannot be undone.',
			'bhg_adminmoderator_menu'                      => 'BHG Admin/Moderator Menu',
			'bhg_guest_menu'                               => 'BHG Guest Menu',
			'bhg_loggedin_user_menu'                       => 'BHG Logged-in User Menu',
			'bhg_tools'                                    => 'BHG Tools',
			'bonus_hunt'                                   => 'Bonus Hunt',
			'bonus_hunt_settings'                          => 'Bonus Hunt - Settings',
			'bonus_hunt_guesser'                           => 'Bonus Hunt Guesser',
			'bonus_hunt_guesser_development_team'          => 'Bonus Hunt Guesser Development Team',
			'bonus_hunt_guesser_information'               => 'Bonus Hunt Guesser Information',
			'bonus_hunt_guesser_settings'                  => 'Bonus Hunt Guesser Settings',
			'bonus_hunt_demo_closed'                       => 'Bonus Hunt – Demo Closed',
			'bonus_hunt_demo_open'                         => 'Bonus Hunt – Demo Open',
			'check_if_this_user_is_an_affiliate'           => 'Check if this user is an affiliate.',
			'close_bonus_hunt'                             => 'Close Bonus Hunt',
			'close_hunt'                                   => 'Close Hunt',
			'comprehensive_bonus_hunt_management_system_with_tournaments_leaderboards_and_user_guessing_functionality' => 'Comprehensive bonus hunt management system with tournaments, leaderboards, and user guessing functionality',
			'content'                                      => 'Content',
			'could_not_save_tournament_check_logs'         => 'Could not save tournament. Check logs.',
			'create_ad'                                    => 'Create Ad',
			'create_affiliate'                             => 'Create Affiliate Website',
			'create_bonus_hunt'                            => 'Create Bonus Hunt',
			'create_tournament'                            => 'Create Tournament',
			'current_database_status'                      => 'Current Database Status',
			'database'                                     => 'Database',
			'database_maintenance'                         => 'Database Maintenance',
			'database_tools'                               => 'Database Tools',
			'cleanup_database'                             => 'Cleanup Database',
			'optimize_database'                            => 'Optimize Database',
			'database_cleanup_completed'                   => 'Database cleanup completed.',
			'database_optimization_completed'              => 'Database optimization completed.',
			'database_cleanup_completed_successfully'      => 'Database cleanup completed successfully.',
			'database_optimization_completed_successfully' => 'Database optimization completed successfully.',
			'date'                                         => 'Date',
			'default_tournament_period'                    => 'Default Tournament Period',
			'default_period_for_tournament_calculations'   => 'Default period for tournament calculations.',
			'delete_this_ad'                               => 'Delete this ad?',
			'delete_this_affiliate'                        => 'Delete this affiliate website?',
			'delete_this_guess'                            => 'Delete this guess?',
			'demo_tools'                                   => 'Demo Tools',
			'description'                                  => 'Description',
			'diagnostics'                                  => 'Diagnostics',
			'difference'                                   => 'Difference',
			'edit_ad'                                      => 'Edit Ad',
			'edit_affiliate'                               => 'Edit Affiliate Website',
			'edit_bonus_hunt'                              => 'Edit Bonus Hunt',
			'edit_hunt_s'                                  => 'Edit Hunt — %s',
			'edit_tournament'                              => 'Edit Tournament',
			'email_from'                                   => 'Email From Address',

			// Email notifications.
			'email_results_title'                          => 'The Bonus Hunt has been closed!',
			'email_final_balance'                          => 'Final Balance',
			'email_winner'                                 => 'Winner',
			'email_congrats_subject'                       => 'Congratulations! You won the Bonus Hunt',
			'email_congrats_body'                          => 'You had the closest guess. Great job!',
			'email_hunt'                                   => 'Hunt',

			'enable_ads'                                   => 'Enable Ads',
			'existing_ads'                                 => 'Existing Ads',
			'existing_keys'                                => 'Existing keys',
			'custom_translations_highlighted'              => 'Custom translations are highlighted.',
			'exists'                                       => 'Exists',
			'final_balance_optional'                       => 'Final Balance (optional)',
			'gift_card_swag'                               => 'Gift card + swag',
			'guess_must_be_between_0_and_100000'           => 'Guess must be between €0 and €100,000.',
			'guess_must_be_between'                        => 'Guess must be between %1$s and %2$s.',
			'guess_removed'                                => 'Guess removed.',
			'guesses'                                      => 'Guesses',
			'guesses_2'                                    => 'Guesses:',
			'helper_function_bhggetlatestclosedhunts_missing_please_include_classbhgbonushuntsphp_helpers' => 'Helper function bhg_get_latest_closed_hunts() missing. Please include class-bhg-bonus-hunts.php helpers.',
			'hunt_close_failed'                            => 'Failed to close the hunt.',
			'hunt_closed_successfully'                     => 'Hunt closed successfully.',
			'hunt_not_found'                               => 'Hunt not found',
			'hunt_not_found_2'                             => 'Hunt not found.',
			'hunts'                                        => 'Hunts:',
			'id'                                           => 'ID',
			'insufficient_permissions'                     => 'Insufficient permissions',
			'invalid_data_submitted_please_check_your_inputs' => 'Invalid data submitted. Please check your inputs.',
			'invalid_final_balance_please_enter_a_nonnegative_number' => 'Invalid final balance. Please enter a non-negative number.',
			'invalid_guess_amount'                         => 'Invalid guess amount.',
			'invalid_hunt'                                 => 'Invalid hunt',
			'invalid_hunt_2'                               => 'Invalid hunt.',
			'invalid_timeframe'                            => 'Invalid timeframe',
			'invalid_settings'                             => 'Invalid data submitted.',
			'key'                                          => 'Key',
			'key_field_is_required'                        => 'Key field is required.',
			'link_url_optional'                            => 'Link URL (optional)',
			'maximum_guess_amount'                         => 'Maximum Guess Amount',
			'max_guess_amount'                             => 'Maximum Guess Amount',
			'maximum_amount_users_can_guess_for_a_bonus_hunt' => 'Maximum amount users can guess for a bonus hunt.',
			'menu_not_assigned'                            => 'Menu not assigned.',
			'minimum_guess_amount'                         => 'Minimum Guess Amount',
			'min_guess_amount'                             => 'Minimum Guess Amount',
			'minimum_amount_users_can_guess_for_a_bonus_hunt' => 'Minimum amount users can guess for a bonus hunt.',
			'missing'                                      => 'Missing',
			'missing_helper_functions_please_include_classbhgbonushuntshelpersphp' => 'Missing helper functions. Please include class-bhg-bonus-hunts-helpers.php.',
			'missing_hunt_id'                              => 'Missing hunt id',
			'name'                                         => 'Name',
			'no'                                           => 'No',
			'no_active_hunt_selected'                      => 'No active hunt selected.',
			'no_affiliates_yet'                            => 'No affiliate websites yet.',
			'no_data_available'                            => 'No data available.',
			'no_database_ui_found'                         => 'No database UI found.',
			'no_participants_yet'                          => 'No participants yet.',
			'no_permission'                                => 'No permission',
			'no_settings_ui_found'                         => 'No settings UI found.',
			'no_tools_ui_found'                            => 'No tools UI found.',
			'no_tournaments_yet'                           => 'No tournaments yet.',
			'no_translations_ui_found'                     => 'No translations UI found.',
			'no_translations_yet'                          => 'No translations yet.',
			'no_users_found'                               => 'No users found.',
			'no_winners_yet'                               => 'No winners yet',
			'none'                                         => 'None',
			'note_this_will_remove_any_demo_data_and_reset_tables_to_their_initial_state' => 'Note: This will remove any demo data and reset tables to their initial state.',
			'nothing_to_show_yet_start_by_creating_a_hunt_or_a_test_user' => 'Nothing to show yet. Start by creating a hunt or a test user.',
			'number_of_winners'                            => 'Number of Winners',
			'open'                                         => 'Open',
			'closed'                                       => 'Closed',
			'active'                                       => 'Active',
			'inactive'                                     => 'Inactive',
			'optimize_database_tables'                     => 'Optimize Database Tables',
			'participants'                                 => 'Participants',
			'placement'                                    => 'Placement',
			'play_responsibly'                             => 'Play responsibly.',
			'please_enter_a_guess'                         => 'Please enter a guess.',
			'guess_required'                               => 'Please enter a guess.',
			'please_enter_a_valid_number'                  => 'Please enter a valid number.',
			'guess_numeric'                                => 'Please enter a valid number.',
			'guess_range'                                  => 'Guess must be between %1$s and %2$s.',
			'guess_submitted'                              => 'Your guess has been submitted!',
			'msg_thank_you'                                => 'Thank you!',
			'msg_error'                                    => 'An error occurred.',
			'ajax_error'                                   => 'An error occurred. Please try again.',
			'profile'                                      => 'Profile',
			'reminder_assign_your_bhg_menus_adminmoderator_loggedin_guest_under_appearance_menus_manage_locations_use_shortcode_bhgnav_to_display' => 'Reminder: Assign your BHG menus (Admin/Moderator, Logged-in, Guest) under Appearance → Menus → Manage Locations. Use shortcode [bhg_nav] to display.',
			'remove'                                       => 'Remove',
			'remove_this_guess'                            => 'Remove this guess?',
			'reset_reseed_demo'                            => 'Reset & Reseed Demo',
			'reset_reseed_demo_data'                       => 'Reset & Reseed Demo Data',
			'demo_data_reset_complete'                     => 'Demo data was reset and reseeded.',
			'demo_data_reset_failed'                       => 'Demo data reset failed.',
			'results_for'                                  => 'Results for ',
			'results_for_s'                                => 'Results for %s',
			'role'                                         => 'Role',
			'rows'                                         => 'Rows',
			'run_database_cleanup'                         => 'Run Database Cleanup',
			'save_changes'                                 => 'Save Changes',
			'save_hunt'                                    => 'Save Hunt',
			'save_settings'                                => 'Save Settings',
			'search_users'                                 => 'Search Users',
			'search_users_2'                               => 'Search users',
			'security_check_failed'                        => 'Security check failed. Please try again.',
			'security_check_failed_2'                      => 'Security check failed.',
			'security_check_failed_please_retry'           => 'Security check failed. Please retry.',
			'security_check_failed_please_try_again'       => 'Security check failed. Please try again.',
			'see_promo'                                    => 'See promo',
			'settings'                                     => 'Settings',
			'settings_saved_successfully'                  => 'Settings saved successfully.',
			'settings_currently_unavailable'               => 'Settings management is currently unavailable.',
			'show_ads_block_on_selected_pages'             => 'Show ads block on selected pages.',
			'status'                                       => 'Status:',
			'participants_mode'                            => 'Participants Mode',
			'tshirt'                                       => 'T-shirt',
			'table_name'                                   => 'Table Name',
			'tables_are_automatically_created_on_activation_if_you_need_to_reinstall_them_deactivate_and_activate_the_plugin_again' => 'Tables are automatically created on activation. If you need to reinstall them, deactivate and activate the plugin again.',
			'target_page_slugs'                            => 'Target Page Slugs',
			'this_hunt_is_closed_you_cannot_submit_or_change_a_guess' => 'This hunt is closed. You cannot submit or change a guess.',
			'this_will_delete_all_demo_data_and_pages_then_recreate_fresh_demo_content' => 'This will delete all demo data and pages, then recreate fresh demo content.',
			'titlecontent'                                 => 'Title/Content',
			'tournament_saved'                             => 'Tournament saved.',
			'tournament_deleted'                           => 'Tournament deleted.',
			'tournament_closed'                            => 'Tournament closed.',
			'tournaments'                                  => 'Tournaments:',
			'translation_saved'                            => 'Translation saved.',
			'tools_action_completed'                       => 'Tools action completed.',
			'summary'                                      => 'Summary',
			'view_all_hunts'                               => 'View All Hunts',
			'url'                                          => 'URL',
			'unknown_user'                                 => 'Unknown User',
			'update_ad'                                    => 'Update Ad',
			'update_affiliate'                             => 'Update Affiliate Website',
			'update_tournament'                            => 'Update Tournament',
			'users_can_edit_their_guess_while_hunt_is_open' => 'Users can edit their guess while hunt is open.',
			'users'                                        => 'Users:',
			'value'                                        => 'Value',
			'view_edit'                                    => 'View / Edit',
			'view_not_found'                               => 'View Not Found',
			'requested_view_not_found'                     => 'The requested view "%s" was not found.',
			'winners'                                      => 'Winners',
			'all'                                          => 'All',
			'search'                                       => 'Search',
			'search_tournaments'                           => 'Search Tournaments',
			'are_you_sure'                                 => 'Are you sure?',
			'yes'                                          => 'Yes',
			'you_do_not_have_permission_to_access_this_page' => 'You do not have permission to access this page',
			'you_do_not_have_permission_to_do_that'        => 'You do not have permission to do that.',
			'you_do_not_have_sufficient_permissions_to_access_this_page' => 'You do not have sufficient permissions to access this page.',
			'you_do_not_have_sufficient_permissions_to_perform_this_action' => 'You do not have sufficient permissions to perform this action.',
			'you_have_reached_the_maximum_number_of_guesses' => 'You have reached the maximum number of guesses.',
			'you_must_be_logged_in_to_submit_a_guess'      => 'You must be logged in to submit a guess.',
			'your_guess_0_100000'                          => 'Your Guess (0 - 100,000)',
			'your_guess_has_been_submitted'                => 'Your guess has been submitted!',
			'httpsyourdomaincom'                           => 'https://yourdomain.com/',
		);
	}
}

if ( ! function_exists( 'bhg_seed_default_translations' ) ) {
		/**
		 * Seed default translations, inserting any missing keys.
		 *
		 * @return void
		 */
	function bhg_seed_default_translations() {
			global $wpdb;

						$table  = esc_sql( $wpdb->prefix . 'bhg_translations' );
						$locale = get_locale();

		foreach ( bhg_get_default_translations() as $slug => $def_text ) {
			$slug = trim( (string) $slug );
			if ( '' === $slug ) {
					continue; // Skip invalid keys.
			}

						$sql    = $wpdb->prepare(
							"SELECT COUNT(*) FROM {$table} WHERE slug = %s AND locale = %s",
							$slug,
							$locale
						);
						$exists = $wpdb->get_var( $sql );
			if ( $exists ) {
				continue;
			}

						$wpdb->insert(
							$table,
							array(
								'slug'         => $slug,
								'default_text' => (string) $def_text,
								'text'         => '',
								'locale'       => $locale,
							),
							array( '%s', '%s', '%s', '%s' )
						);
		}

				bhg_clear_translation_cache();
	}
}

if ( ! function_exists( 'bhg_seed_default_translations_if_empty' ) ) {
				/**
				 * Ensure default translations exist.
				 *
				 * Inserts any missing translation keys so they appear in the admin.
				 *
				 * @return void
				 */
	function bhg_seed_default_translations_if_empty() {
			global $wpdb;

			$table = $wpdb->prefix . 'bhg_translations';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
				bhg_seed_default_translations();
		}
	}
}

/**
 * Get the configured currency symbol.
 *
 * Defaults to the Euro symbol.
 *
 * @return string
 */
function bhg_currency_symbol() {
		$settings = get_option( 'bhg_plugin_settings', array() );
		$currency = isset( $settings['currency'] ) ? $settings['currency'] : 'eur';
		$map      = array(
			'usd' => '$',
			'eur' => '€',
		);
		$symbol   = isset( $map[ $currency ] ) ? $map[ $currency ] : '€';
		return apply_filters( 'bhg_currency_symbol', $symbol, $currency );
}

/**
 * Format an amount as currency using the selected symbol.
 *
 * @param float $amount Amount to format.
 * @return string
 */
function bhg_format_currency( $amount ) {
		return sprintf( '%s%s', bhg_currency_symbol(), number_format_i18n( (float) $amount, 2 ) );
}

/**
 * Validate a guess amount against settings.
 *
 * @param mixed $guess Guess value.
 * @return bool True if the guess is within the allowed range.
 */
function bhg_validate_guess( $guess ) {
	$settings  = get_option( 'bhg_plugin_settings', array() );
	$min_guess = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
	$max_guess = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;

	if ( ! is_numeric( $guess ) ) {
		return false;
	}

	$guess = (float) $guess;
	return ( $guess >= $min_guess && $guess <= $max_guess );
}

/**
 * Get a user's display name with affiliate indicator.
 *
 * Uses the `bhg_is_affiliate` user meta to determine affiliate status.
 *
 * @param int $user_id User ID.
 * @return string Display name with optional affiliate indicator, sanitized for safe HTML output.
 */
function bhg_get_user_display_name( $user_id ) {
		$user = get_userdata( (int) $user_id );
	if ( ! $user ) {
			return wp_kses_post( bhg_t( 'unknown_user', 'Unknown User' ) );
	}

		$display_name = $user->display_name ? $user->display_name : $user->user_login;
		$is_affiliate = bhg_is_user_affiliate( (int) $user_id );

	if ( $is_affiliate ) {
			$display_name .= ' <span class="bhg-affiliate-indicator" title="' . esc_attr( bhg_t( 'label_affiliate_user_title', 'Affiliate User' ) ) . '">★</span>';
	}

		return wp_kses_post( $display_name );
}

if ( ! function_exists( 'bhg_is_user_affiliate' ) ) {
	/**
	 * Check if a user is an affiliate.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	function bhg_is_user_affiliate( $user_id ) {
		$val = get_user_meta( (int) $user_id, 'bhg_is_affiliate', true );
		return ( '1' === $val || 1 === $val || true === $val || 'yes' === $val );
	}
}

if ( ! function_exists( 'bhg_get_user_affiliate_websites' ) ) {
		/**
		 * Get affiliate website IDs for a user.
		 *
		 * @param int $user_id User ID.
		 * @return array
		 */
	function bhg_get_user_affiliate_websites( $user_id ) {
			$ids = get_user_meta( (int) $user_id, 'bhg_affiliate_websites', true );
		if ( is_array( $ids ) ) {
				return array_map( 'absint', $ids );
		}
		if ( is_string( $ids ) && '' !== $ids ) {
				return array_map( 'absint', array_filter( array_map( 'trim', explode( ',', $ids ) ) ) );
		}
			return array();
	}
}

if ( ! function_exists( 'bhg_set_user_affiliate_websites' ) ) {
		/**
		 * Store affiliate website IDs for a user.
		 *
		 * @param int   $user_id  User ID.
		 * @param array $site_ids Site IDs.
		 * @return void
		 */
	function bhg_set_user_affiliate_websites( $user_id, $site_ids ) {
			$clean = array();
		if ( is_array( $site_ids ) ) {
			foreach ( $site_ids as $sid ) {
				$sid = absint( $sid );
				if ( $sid ) {
						$clean[] = $sid;
				}
			}
		}
			update_user_meta( (int) $user_id, 'bhg_affiliate_websites', $clean );
	}
}

if ( ! function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
	/**
	 * Determine if a user is an affiliate for a specific site.
	 *
	 * @param int $user_id User ID.
	 * @param int $site_id Site ID.
	 * @return bool
	 */
        function bhg_is_user_affiliate_for_site( $user_id, $site_id ) {
                $user_id = (int) $user_id;

                if ( ! $site_id ) {
                        return bhg_is_user_affiliate( $user_id );
                }

                                $sites = bhg_get_user_affiliate_websites( $user_id );

                if ( empty( $sites ) ) {
                        return bhg_is_user_affiliate( $user_id );
                }

                                return in_array( absint( $site_id ), array_map( 'absint', (array) $sites ), true );
        }
}

if ( ! function_exists( 'bhg_render_affiliate_dot' ) ) {
	/**
	 * Render affiliate status dot.
	 *
	 * @param int $user_id                User ID.
	 * @param int $hunt_affiliate_site_id Hunt affiliate site ID.
	 * @return string
	 */
	function bhg_render_affiliate_dot( $user_id, $hunt_affiliate_site_id = 0 ) {
		$is_aff                       = bhg_is_user_affiliate_for_site( (int) $user_id, (int) $hunt_affiliate_site_id );
				$cls                  = $is_aff ? 'bhg-aff-green' : 'bhg-aff-red';
				$label                = $is_aff ? bhg_t( 'label_affiliate', 'Affiliate' ) : bhg_t( 'label_non_affiliate', 'Non-affiliate' );
								$html = '<span class="bhg-aff-dot ' . esc_attr( $cls ) . '" aria-label="' . esc_attr( $label ) . '"></span>';
								return wp_kses_post( $html );
	}
}

if ( ! function_exists( 'bhg_cleanup_translation_duplicates' ) ) {
		/**
		 * Remove duplicate translation rows keeping the lowest ID.
		 *
		 * @return void
		 */
	function bhg_cleanup_translation_duplicates() {
			global $wpdb;

						$table = esc_sql( $wpdb->prefix . 'bhg_translations' );

				$sql = "DELETE t1 FROM {$table} t1 INNER JOIN {$table} t2 ON t1.slug = t2.slug AND t1.locale = t2.locale AND t1.id > t2.id";
				$wpdb->query( $sql );
	}
}

/**
 * Render advertising blocks based on placement and user state.
 *
 * @param string $placement Placement location.
 * @param int    $hunt_id   Hunt ID.
 * @return string
 */
function bhg_render_ads( $placement = 'footer', $hunt_id = 0 ) {
		global $wpdb;
				$tbl          = esc_sql( $wpdb->prefix . 'bhg_ads' );
				$placement    = sanitize_text_field( $placement );
				$sql          = $wpdb->prepare(
					"SELECT content, link_url, visible_to FROM {$tbl} WHERE active=1 AND placement=%s ORDER BY id DESC",
					$placement
				);
				$rows         = $wpdb->get_results( $sql );
				$hunt_site_id = 0;

	if ( $hunt_id ) {
					$hunts_tbl    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
					$hunt_site_id = (int) $wpdb->get_var(
						$wpdb->prepare(
							"SELECT affiliate_site_id FROM {$hunts_tbl} WHERE id=%d",
							(int) $hunt_id
						)
					);
	}

	if ( ! $rows ) {
		return '';
	}

	$out = '<div class="bhg-ads bhg-ads-' . esc_attr( $placement ) . '">';
	foreach ( $rows as $r ) {
		$vis  = $r->visible_to ? $r->visible_to : 'all';
		$show = false;

		if ( 'all' === $vis ) {
			$show = true;
		} elseif ( 'guests' === $vis && ! is_user_logged_in() ) {
			$show = true;
		} elseif ( 'logged_in' === $vis && is_user_logged_in() ) {
			$show = true;
		} elseif ( 'affiliates' === $vis && is_user_logged_in() ) {
			$uid  = get_current_user_id();
			$show = $hunt_site_id > 0
				? bhg_is_user_affiliate_for_site( (int) $uid, (int) $hunt_site_id )
				: (bool) get_user_meta( (int) $uid, 'bhg_is_affiliate', true );
		}

		if ( ! $show ) {
			continue;
		}

		$msg  = wp_kses_post( $r->content );
		$link = $r->link_url ? esc_url( $r->link_url ) : '';

		$out .= '<div class="bhg-ad" style="margin:10px 0;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">';
		if ( $link ) {
			$out .= '<a href="' . $link . '">';
		}
		$out .= $msg;
		if ( $link ) {
			$out .= '</a>';
		}
		$out .= '</div>';
	}
	$out .= '</div>';

	return $out;
}

// Demo reset and seed data.
if ( ! function_exists( 'bhg_reset_demo_and_seed' ) ) {
	/**
	 * Reset demo tables and seed sample data.
	 *
	 * @return void
	 */
	function bhg_reset_demo_and_seed() {
		global $wpdb;

		$p = $wpdb->prefix;

		// Ensure tables exist before touching.
		$tables = array(
			"{$p}bhg_guesses",
			"{$p}bhg_bonus_hunts",
			"{$p}bhg_tournaments",
			"{$p}bhg_tournament_results",
			"{$p}bhg_hunt_winners",
			"{$p}bhg_ads",
			"{$p}bhg_translations",
			"{$p}bhg_affiliate_websites",
		);

		// Soft delete to preserve schema even if user lacks TRIGGER/TRUNCATE.
		foreach ( $tables as $tbl ) {
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tbl ) );
			if ( $exists !== $tbl ) {
				continue;
			}
			if ( false !== strpos( $tbl, 'bhg_translations' ) || false !== strpos( $tbl, 'bhg_affiliate_websites' ) ) {
					continue; // keep; upsert below.
			}
$wpdb->query( "DELETE FROM {$tbl}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

		// Seed affiliate websites (idempotent upsert by slug).
				$aff_tbl = esc_sql( "{$p}bhg_affiliate_websites" );
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $aff_tbl ) ) === $aff_tbl ) {
			$affs = array(
				array(
					'name' => 'Main Site',
					'slug' => 'main-site',
					'url'  => home_url( '/' ),
				),
				array(
					'name' => 'Casino Hub',
					'slug' => 'casino-hub',
					'url'  => home_url( '/casino' ),
				),
			);
			foreach ( $affs as $a ) {
										$id = $wpdb->get_var(
											$wpdb->prepare( "SELECT id FROM {$aff_tbl} WHERE slug=%s", $a['slug'] )
										);
				if ( $id ) {
					$wpdb->update( $aff_tbl, $a, array( 'id' => (int) $id ), array( '%s', '%s', '%s' ), array( '%d' ) );
				} else {
								$wpdb->insert( $aff_tbl, $a, array( '%s', '%s', '%s' ) );
				}
			}
		}

		// Seed hunts.
                                $hunts_tbl = esc_sql( "{$p}bhg_bonus_hunts" );
                if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $hunts_tbl ) ) === $hunts_tbl ) {
                        $now          = current_time( 'mysql', 1 );
                        $users_table  = esc_sql( $wpdb->users );
                        $winners_tbl  = esc_sql( "{$p}bhg_hunt_winners" );
                        $guesses_tbl  = esc_sql( "{$p}bhg_guesses" );
                        $users        = $wpdb->get_col( "SELECT ID FROM {$users_table} ORDER BY ID ASC LIMIT 5" );
                        if ( empty( $users ) ) {
                                $users = array( 1 );
                        }
                        $open_winners_limit   = 3;
                        $closed_winners_limit = 3;

                        // Open hunt.
                        $wpdb->insert(
                                $hunts_tbl,
                                array(
                                        'title'             => __( 'Bonus Hunt – Demo Open', 'bonus-hunt-guesser' ),
                                       'starting_balance'  => 2000.00,
                                        'num_bonuses'       => 10,
                                        'prizes'            => __( 'Gift card + swag', 'bonus-hunt-guesser' ),
                                        'status'            => 'open',
                                        'winners_count'     => $open_winners_limit,
                                        'affiliate_site_id' => (int) $wpdb->get_var(
                                                'SELECT id FROM ' . esc_sql( "{$p}bhg_affiliate_websites" ) . ' ORDER BY id ASC LIMIT 1'
                                        ),
                                        'created_at'        => $now,
                                        'updated_at'        => $now,
                                ),
                                array( '%s', '%f', '%d', '%s', '%s', '%d', '%d', '%s', '%s' )
                        );
                        $open_id = (int) $wpdb->insert_id;

                        // Closed hunt.
                        $final_balance = 1875.50;
                        $closed_at     = gmdate( 'Y-m-d H:i:s', time() - 86400 );
                        $wpdb->insert(
                                $hunts_tbl,
                                array(
                                        'title'            => __( 'Bonus Hunt – Demo Closed', 'bonus-hunt-guesser' ),
                                        'starting_balance' => 1500.00,
                                        'num_bonuses'      => 8,
                                        'prizes'           => __( 'T-shirt', 'bonus-hunt-guesser' ),
                                        'status'           => 'closed',
                                        'final_balance'    => $final_balance,
                                        'winners_count'    => $closed_winners_limit,
                                        'closed_at'        => $closed_at,
                                        'created_at'       => $now,
                                        'updated_at'       => $now,
                                ),
                                array( '%s', '%f', '%d', '%s', '%s', '%f', '%d', '%s', '%s', '%s' )
                        );
                        $closed_id = (int) $wpdb->insert_id;

                        // Seed guesses for open hunt.
                        $val = 2100.00;
                        foreach ( $users as $uid ) {
                                $wpdb->insert(
                                        $guesses_tbl,
                                        array(
                                                'hunt_id'    => $open_id,
                                                'user_id'    => (int) $uid,
                                                'guess'      => $val,
                                                'created_at' => $now,
                                                'updated_at' => $now,
                                        ),
                                        array( '%d', '%d', '%f', '%s', '%s' )
                                );
                                $val += 23.45;
                        }

                        // Seed guesses for closed hunt.
                        $closed_guesses      = array( 1863.40, 1889.20, 1876.10, 1854.75, 1895.60 );
                        $last_closed_guess   = $closed_guesses[ array_key_last( $closed_guesses ) ];
                        $idx                 = 0;
                        foreach ( $users as $uid ) {
                                $guess_value = isset( $closed_guesses[ $idx ] ) ? $closed_guesses[ $idx ] : $last_closed_guess;
                                $wpdb->insert(
                                        $guesses_tbl,
                                        array(
                                                'hunt_id'    => $closed_id,
                                                'user_id'    => (int) $uid,
                                                'guess'      => (float) $guess_value,
                                                'created_at' => $now,
                                                'updated_at' => $now,
                                        ),
                                        array( '%d', '%d', '%f', '%s', '%s' )
                                );
                                ++$idx;
                        }

                        // Populate winners for closed hunt.
                        if ( $closed_id > 0 && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $winners_tbl ) ) === $winners_tbl ) {
                                $limit = max( 1, min( $closed_winners_limit, count( $users ) ) );
                                $winners_sql = $wpdb->prepare(
                                        "SELECT user_id, guess, (%f - guess) AS diff FROM {$guesses_tbl} WHERE hunt_id = %d ORDER BY ABS(%f - guess) ASC, id ASC LIMIT %d",
                                        $final_balance,
                                        $closed_id,
                                        $final_balance,
                                        $limit
                                );
                                $winner_rows = $wpdb->get_results( $winners_sql );

                                $position = 1;
                                foreach ( (array) $winner_rows as $winner ) {
                                        $user_id = isset( $winner->user_id ) ? (int) $winner->user_id : 0;
                                        if ( $user_id <= 0 ) {
                                                continue;
                                        }

                                        $wpdb->insert(
                                                $winners_tbl,
                                                array(
                                                        'hunt_id'    => $closed_id,
                                                        'user_id'    => $user_id,
                                                        'position'   => $position,
                                                        'guess'      => isset( $winner->guess ) ? (float) $winner->guess : 0.0,
                                                        'diff'       => isset( $winner->diff ) ? (float) $winner->diff : 0.0,
                                                        'created_at' => $now,
                                                ),
                                                array( '%d', '%d', '%d', '%f', '%f', '%s' )
                                        );
                                        ++$position;
                                }
                        }
                }

                // Tournaments + results based on closed hunts.
				$t_tbl = esc_sql( "{$p}bhg_tournaments" );
				$r_tbl = esc_sql( "{$p}bhg_tournament_results" );
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t_tbl ) ) === $t_tbl ) {
				// Wipe results only.
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $r_tbl ) ) === $r_tbl ) {
						$wpdb->delete( $r_tbl, '1=1' );
			}

                                                               $winners_tbl = esc_sql( "{$p}bhg_hunt_winners" );
                                                               $closed      = $wpdb->get_results(
                                                                       "SELECT h.closed_at, w.user_id FROM {$hunts_tbl} h INNER JOIN {$winners_tbl} w ON w.hunt_id = h.id WHERE h.status='closed'"
                                                               );
                       foreach ( $closed as $row ) {
				$ts        = $row->closed_at ? strtotime( $row->closed_at ) : time();
				$iso_year  = gmdate( 'o', $ts );
				$week      = str_pad( gmdate( 'W', $ts ), 2, '0', STR_PAD_LEFT );
				$week_key  = $iso_year . '-W' . $week;
				$month_key = gmdate( 'Y-m', $ts );
				$year_key  = gmdate( 'Y', $ts );

				$ensure = function ( $type, $period ) use ( $wpdb, $t_tbl ) {
						$now   = current_time( 'mysql', 1 );
						$start = $now;
						$end   = $now;

					if ( 'weekly' === $type ) {
						$start = gmdate( 'Y-m-d', strtotime( $period . '-1' ) );
						$end   = gmdate( 'Y-m-d', strtotime( $period . '-7' ) );
					} elseif ( 'monthly' === $type ) {
						$start = $period . '-01';
						$end   = gmdate( 'Y-m-t', strtotime( $start ) );
					} elseif ( 'yearly' === $type ) {
						$start = $period . '-01-01';
						$end   = $period . '-12-31';
					}

																				$sql = $wpdb->prepare(
																					"SELECT id FROM {$t_tbl} WHERE type=%s AND start_date=%s AND end_date=%s",
																					$type,
																					$start,
																					$end
																				);
																				$id  = $wpdb->get_var( $sql );
					if ( $id ) {
						return (int) $id;
					}
						$wpdb->insert(
							$t_tbl,
							array(
								'type'       => $type,
								'start_date' => $start,
								'end_date'   => $end,
								'status'     => 'active',
								'created_at' => $now,
								'updated_at' => $now,
							),
							array( '%s', '%s', '%s', '%s', '%s', '%s' )
						);
						return (int) $wpdb->insert_id;
				};

                                               $uid = isset( $row->user_id ) ? (int) $row->user_id : 0;
                                               if ( $uid <= 0 ) {
                                                       continue;
                                               }
				foreach ( array(
					$ensure( 'weekly', $week_key ),
					$ensure( 'monthly', $month_key ),
					$ensure( 'yearly', $year_key ),
				) as $tid ) {
					if ( $tid && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $r_tbl ) ) === $r_tbl ) {
									$insert_sql = $wpdb->prepare(
										"INSERT INTO {$r_tbl} (tournament_id, user_id, wins) VALUES (%d, %d, 1) ON DUPLICATE KEY UPDATE wins = wins + 1",
										$tid,
										$uid
									);
									$wpdb->query( $insert_sql );
					}
				}
			}
		}
	}

				global $wpdb;
				$p = $wpdb->prefix;

				// Seed ads.
								$ads_tbl = esc_sql( "{$p}bhg_ads" );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $ads_tbl ) ) === $ads_tbl ) {
		// Remove any duplicate ads, keeping the lowest ID for identical content/placement pairs.
			$wpdb->query(
				"DELETE a1 FROM {$ads_tbl} a1 INNER JOIN {$ads_tbl} a2 ON a1.id > a2.id AND a1.content = a2.content AND a1.placement = a2.placement"
			);
		// Only seed default ad if table is empty.
			$existing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$ads_tbl}" );
		if ( 0 === $existing ) {
			$now = current_time( 'mysql', 1 );
			$wpdb->insert(
				$ads_tbl,
				array(
					'title'        => '',
					'content'      => '<strong>Play responsibly.</strong> <a href="' . esc_url( home_url( '/promo' ) ) . '">See promo</a>',
					'link_url'     => '',
					'placement'    => 'footer',
					'visible_to'   => 'all',
					'target_pages' => '',
					'active'       => 1,
					'created_at'   => $now,
					'updated_at'   => $now,
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
			);
		}
	}

	global $wpdb;
	$p      = $wpdb->prefix;
	$tr_tbl = "{$p}bhg_translations";

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tr_tbl ) ) === $tr_tbl ) {
			bhg_seed_default_translations_if_empty();
	}

	return;
}

// Ensure default translations are seeded on load so newly added keys appear
// in the Translations page without requiring manual intervention.
if ( function_exists( 'bhg_seed_default_translations_if_empty' ) ) {
		bhg_seed_default_translations_if_empty();
}
