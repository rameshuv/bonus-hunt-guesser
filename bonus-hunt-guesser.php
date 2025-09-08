<?php
/**
 * Plugin Name: Bonus Hunt Guesser
 * Plugin URI: https://yourdomain.com/
 * Description: Comprehensive bonus hunt management system with tournaments, leaderboards, and user guessing functionality
 * Version: 8.0.08
 * Requires at least: 6.3.5
 * Requires PHP: 7.4
 * Author: Bonus Hunt Guesser Development Team
 * Text Domain: bonus-hunt-guesser
 * Domain Path: /languages
 * License: GPLv2 or later
 * MySQL tested up to: 5.5.5
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Helper: parse human-entered money-like strings into float.
if ( ! function_exists( 'bhg_parse_amount' ) ) {
	/**
	 * Parse a human-entered money-like string into a float.
	 *
	 * @param string $s Raw amount string.
	 * @return float|null Parsed float value or null on failure.
	 */
	function bhg_parse_amount( $s ) {
		if ( ! is_string( $s ) ) {
			return null;
		}
				// Normalize unicode spaces (NBSP / NNBSP) and trim.
		$s = str_replace( array( "\xc2\xa0", "\xe2\x80\xaf" ), ' ', $s );
		$s = trim( wp_unslash( $s ) );
		if ( '' === $s ) {
			return null;
		}

				// Remove currency symbols/letters while keeping digits, separators, minus.
		$s = preg_replace( '/[^\d,\.\-\s]/u', '', $s );
		$s = str_replace( ' ', '', $s );

				$has_comma = false !== strpos( $s, ',' );
				$has_dot   = false !== strpos( $s, '.' );

		if ( $has_comma && $has_dot ) {
						// Use the last occurring symbol as decimal separator.
			$last_comma = strrpos( $s, ',' );
			$last_dot   = strrpos( $s, '.' );
			if ( false !== $last_comma && ( false === $last_dot || $last_comma > $last_dot ) ) {
					// Comma as decimal.
					$s = str_replace( '.', '', $s );  // Thousands.
					$s = str_replace( ',', '.', $s ); // Decimal.
			} else {
					// Dot as decimal.
					$s = str_replace( ',', '', $s );
			}
		} elseif ( $has_comma ) {
				// Only comma present.
				$last = strrpos( $s, ',' );
				$frac = substr( $s, $last + 1 );
			if ( ctype_digit( $frac ) && strlen( $frac ) >= 1 && strlen( $frac ) <= 2 ) {
						// Treat as decimal.
						$s = str_replace( ',', '.', $s );
			} else {
							// Treat as thousands (incl. Indian grouping).
							$s = str_replace( ',', '', $s );
			}
		} elseif ( $has_dot ) {
				// Only dot present.
				$last = strrpos( $s, '.' );
				$frac = substr( $s, $last + 1 );
			if ( ctype_digit( $frac ) && strlen( $frac ) > 3 ) {
						// Likely thousands separators → remove all dots.
						$s = str_replace( '.', '', $s );
			}
		}

				// Keep only digits, one leading minus, and dots.
				$s = preg_replace( '/[^0-9\.\-]/', '', $s );
		if ( '' === $s || '-' === $s || '.' === $s || '-.' === $s || '.-' === $s ) {
				return null;
		}

				// Collapse multiple dots to a single decimal point (keep first as decimal).
				$parts = explode( '.', $s );
		if ( count( $parts ) > 2 ) {
				$s = $parts[0] . '.' . implode( '', array_slice( $parts, 1 ) );
		}

		if ( is_numeric( $s ) ) {
				return (float) $s;
		}

				// Permissive fallback: first number pattern.
		if ( preg_match( '/\d+(?:\.\d+)?/', $s, $m2 ) ) {
				return (float) $m2[0];
		}

		return null;
	}
}

// Ensure canonical DB class is loaded.
require_once __DIR__ . '/includes/class-bhg-db.php';

// Define plugin constants.
define( 'BHG_VERSION', '8.0.08' );
define( 'BHG_MIN_WP', '6.3.5' );
define( 'BHG_PLUGIN_FILE', __FILE__ );
define( 'BHG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BHG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BHG_TABLE_PREFIX', 'bhg_' );


// Table creation function.
/**
 * Create plugin database tables using active DB class.
 *
 * @return void
 */
function bhg_create_tables() {
	if ( class_exists( 'BHG_DB' ) ) {
		( new BHG_DB() )->create_tables();
		BHG_DB::migrate();
		return;
	}
}

// Check and create tables if needed.
/**
 * Create tables on first run if they do not exist.
 *
 * @return void
 */
function bhg_check_tables() {
	if ( ! get_option( 'bhg_tables_created' ) ) {
		bhg_create_tables();
		update_option( 'bhg_tables_created', true );
	}
}

// Autoloader for plugin classes.
spl_autoload_register(
	function ( $class_name ) {
		if ( 0 !== strpos( $class_name, 'BHG_' ) ) {
						return;
		}

		$class_map = array(
			'BHG_Admin'                  => 'admin/class-bhg-admin.php',
			'BHG_Shortcodes'             => 'includes/class-bhg-shortcodes.php',
			'BHG_Logger'                 => 'includes/class-bhg-logger.php',
			'BHG_Utils'                  => 'includes/class-bhg-utils.php',
			'BHG_Models'                 => 'includes/class-bhg-models.php',
			'BHG_Front_Menus'            => 'includes/class-bhg-front-menus.php',
			'BHG_Ads'                    => 'includes/class-bhg-ads.php',
			'BHG_Login_Redirect'         => 'includes/class-bhg-login-redirect.php',
			'BHG_Tournaments_Controller' => 'includes/class-bhg-tournaments-controller.php',
			'BHG_Demo'                   => 'admin/class-bhg-demo.php',
		);

		if ( isset( $class_map[ $class_name ] ) ) {
				$file_path = BHG_PLUGIN_DIR . $class_map[ $class_name ];
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}
);

// Include helper functions.
require_once BHG_PLUGIN_DIR . 'includes/helpers.php';
require_once BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts-helpers.php';

add_action( 'plugins_loaded', 'bhg_maybe_seed_translations', 1 );
/**
 * Seed default translations on version change.
 *
 * Ensures new translation keys appear after plugin updates.
 *
 * @return void
 */
function bhg_maybe_seed_translations() {
        bhg_seed_default_translations_if_empty();
        $stored_version = get_option( 'bhg_version' );
        if ( BHG_VERSION !== $stored_version ) {
                update_option( 'bhg_version', BHG_VERSION );
        }
}

// Activation hook: create tables and set default options.
/**
 * Activation callback for setting up the plugin.
 *
 * @return void
 */
function bhg_activate_plugin() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	bhg_create_tables();

	bhg_seed_default_translations_if_empty();

	// Set default options.
	add_option( 'bhg_version', BHG_VERSION );
	add_option(
		'bhg_plugin_settings',
		array(
			'allow_guess_changes'       => 'yes',
			'default_tournament_period' => 'monthly',
			'min_guess_amount'          => 0,
			'max_guess_amount'          => 100000,
			'max_guesses'               => 1,
			'ads_enabled'               => 1,
			'email_from'                => get_bloginfo( 'admin_email' ),
		)
	);

		// Seed demo data if empty.
	if ( function_exists( 'bhg_seed_demo_if_empty' ) ) {
		bhg_seed_demo_if_empty();
	}
	update_option( 'bhg_demo_notice', 1 );

		// Set tables created flag.
	update_option( 'bhg_tables_created', true );

		// Flush rewrite rules after database changes.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bhg_activate_plugin' );

// Deactivation hook (no destructive actions).
register_deactivation_hook(
	__FILE__,
	function () {
		// Keep data intact by default.
	}
);

// Frontend asset loader.
add_action( 'wp_enqueue_scripts', 'bhg_enqueue_public_assets' );

/**
 * Enqueue public-facing scripts and styles.
 *
 * @return void
 */
function bhg_enqueue_public_assets() {
		$settings  = get_option( 'bhg_plugin_settings', array() );
		$min_guess = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
		$max_guess = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;

		wp_register_style(
			'bhg-public',
			BHG_PLUGIN_URL . 'assets/css/public.css',
			array(),
			defined( 'BHG_VERSION' ) ? BHG_VERSION : null
		);

		wp_register_script(
			'bhg-public',
			BHG_PLUGIN_URL . 'assets/js/public.js',
			array( 'jquery' ),
			defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
			true
		);

                $guess_range = sprintf(
                                                /* translators: 1: minimum guess, 2: maximum guess. */
                                        bhg_t( 'guess_must_be_between', 'Guess must be between %1$s and %2$s.' ),
                                        bhg_format_currency( $min_guess ),
                                        bhg_format_currency( $max_guess )
                                );

		wp_localize_script(
			'bhg-public',
			'bhg_public_ajax',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'bhg_public_nonce' ),
				'is_logged_in'     => is_user_logged_in(),
				'min_guess_amount' => $min_guess,
				'max_guess_amount' => $max_guess,
                                'i18n'             => array(
                                        'guess_required'          => bhg_t( 'guess_required', 'Please enter a guess.' ),
                                        'guess_numeric'           => bhg_t( 'guess_numeric', 'Please enter a valid number.' ),
                                        'guess_range'             => $guess_range,
                                        'guess_submitted'         => bhg_t( 'guess_submitted', 'Your guess has been submitted!' ),
                                        'ajax_error'              => bhg_t( 'ajax_error', 'An error occurred. Please try again.' ),
                                        'affiliate_user'          => bhg_t( 'affiliate_user', 'Affiliate' ),
                                        'non_affiliate_user'      => bhg_t( 'non_affiliate_user', 'Non-affiliate' ),
                                        'error_loading_leaderboard' => bhg_t( 'error_loading_leaderboard', 'Error loading leaderboard.' ),
                                ),
                        )
                );

	wp_enqueue_style( 'bhg-public' );
	wp_enqueue_script( 'bhg-public' );
}

// Initialize plugin.
add_action( 'plugins_loaded', 'bhg_init_plugin' );
/**
 * Initialize plugin components.
 *
 * @return void
 */
function bhg_init_plugin() {
		// Load text domain.
	load_plugin_textdomain( 'bonus-hunt-guesser', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Initialize components.
	if ( is_admin() ) {
		if ( class_exists( 'BHG_Admin' ) ) {
			new BHG_Admin();
		}
		if ( class_exists( 'BHG_Demo' ) ) {
			new BHG_Demo();
		}
	}

	if ( class_exists( 'BHG_Shortcodes' ) ) {
		new BHG_Shortcodes();
	}
	if ( class_exists( 'BHG_Front_Menus' ) ) {
			new BHG_Front_Menus();
	}

	if ( class_exists( 'BHG_Login_Redirect' ) ) {
			new BHG_Login_Redirect();
	}

	if ( class_exists( 'BHG_Ads' ) ) {
			BHG_Ads::init();
	}

	if ( class_exists( 'BHG_DB' ) ) {
		BHG_DB::migrate();
	}

	if ( class_exists( 'BHG_Utils' ) ) {
		BHG_Utils::init_hooks();
	}

	if ( class_exists( 'BHG_Tournaments_Controller' ) ) {
		BHG_Tournaments_Controller::init();
	}

		// Register form handlers.
	add_action( 'admin_post_bhg_submit_guess', 'bhg_handle_submit_guess' );
	add_action(
		'admin_post_nopriv_bhg_submit_guess',
		function () {
			$ref = wp_get_referer();
			wp_safe_redirect( wp_login_url( $ref ? $ref : home_url() ) );
			exit;
		}
	);
		add_action( 'wp_ajax_submit_bhg_guess', 'bhg_handle_submit_guess' );
		add_action( 'wp_ajax_nopriv_submit_bhg_guess', 'bhg_handle_submit_guess' );
		add_action( 'admin_post_bhg_save_settings', 'bhg_handle_settings_save' );
}

// Early table check on init.
add_action( 'init', 'bhg_check_tables', 0 );

// Form handler for settings save.
/**
 * Handle saving of plugin settings.
 *
 * @return void
 */
function bhg_handle_settings_save() {
		// Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
                        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_perform_this_action', 'You do not have sufficient permissions to perform this action.' ) ) );
        }

				// Verify nonce.
	if ( ! check_admin_referer( 'bhg_save_settings', 'bhg_save_settings_nonce' ) ) {
					wp_safe_redirect( esc_url_raw( 'admin.php?page=bhg-settings&error=nonce_failed' ) );
					exit;
	}

		// Sanitize and validate data.
		$settings = array();

	if ( isset( $_POST['bhg_default_tournament_period'] ) ) {
			$period = sanitize_text_field( wp_unslash( $_POST['bhg_default_tournament_period'] ) );
		if ( in_array( $period, array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' ), true ) ) {
				$settings['default_tournament_period'] = $period;
		}
	}

	if ( isset( $_POST['bhg_max_guess_amount'] ) ) {
			$max = floatval( wp_unslash( $_POST['bhg_max_guess_amount'] ) );
		if ( 0 <= $max ) {
				$settings['max_guess_amount'] = $max;
		}
	}

	if ( isset( $_POST['bhg_min_guess_amount'] ) ) {
			$min = floatval( wp_unslash( $_POST['bhg_min_guess_amount'] ) );
		if ( 0 <= $min ) {
				$settings['min_guess_amount'] = $min;
		}
	}

				// Validate that min is not greater than max.
	if ( isset( $settings['min_guess_amount'] ) && isset( $settings['max_guess_amount'] ) &&
								$settings['min_guess_amount'] > $settings['max_guess_amount'] ) {
					wp_safe_redirect( esc_url_raw( 'admin.php?page=bhg-settings&error=invalid_data' ) );
					exit;
	}

	if ( isset( $_POST['bhg_allow_guess_changes'] ) ) {
			$allow = sanitize_text_field( wp_unslash( $_POST['bhg_allow_guess_changes'] ) );
		if ( in_array( $allow, array( 'yes', 'no' ), true ) ) {
				$settings['allow_guess_changes'] = $allow;
		}
	}

        $settings['ads_enabled'] = isset( $_POST['bhg_ads_enabled'] ) ? 1 : 0;

	if ( isset( $_POST['bhg_email_from'] ) ) {
			$email_from = sanitize_email( wp_unslash( $_POST['bhg_email_from'] ) );
		if ( $email_from ) {
				$settings['email_from'] = $email_from;
		}
	}

		// Save settings.
		$existing = get_option( 'bhg_plugin_settings', array() );
		update_option( 'bhg_plugin_settings', array_merge( $existing, $settings ) );

				// Redirect back to settings page.
				wp_safe_redirect( esc_url_raw( 'admin.php?page=bhg-settings&message=saved' ) );
				exit;
}

// Canonical guess submit handler.
/**
 * Process a guess submission from admin-post or AJAX.
 *
 * @return void
 */
function bhg_handle_submit_guess() {
	if ( wp_doing_ajax() ) {
			check_ajax_referer( 'bhg_public_nonce', 'nonce' );
	} else {
                if ( ! isset( $_POST['_wpnonce'] ) ) {
                                wp_die( esc_html( bhg_t( 'notice_security_check_failed', 'Security check failed.' ) ) );
                }
						check_admin_referer( 'bhg_submit_guess', 'bhg_submit_guess_nonce' );
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
                if ( wp_doing_ajax() ) {
                        wp_send_json_error( bhg_t( 'you_must_be_logged_in_to_submit_a_guess', 'You must be logged in to submit a guess.' ) );
                }
                wp_die( esc_html( bhg_t( 'you_must_be_logged_in_to_submit_a_guess', 'You must be logged in to submit a guess.' ) ) );
	}

		$hunt_id = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
	if ( 0 >= $hunt_id ) {
                if ( wp_doing_ajax() ) {
                        wp_send_json_error( bhg_t( 'notice_invalid_hunt', 'Invalid hunt.' ) );
                }
                wp_die( esc_html( bhg_t( 'notice_invalid_hunt', 'Invalid hunt.' ) ) );
	}

		// Parse guess robustly.
	if ( wp_doing_ajax() ) {
			$raw_guess = isset( $_POST['guess_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['guess_amount'] ) ) : '';
	} else {
			$raw_guess = isset( $_POST['guess'] ) ? sanitize_text_field( wp_unslash( $_POST['guess'] ) ) : ( isset( $_POST['balance_guess'] ) ? sanitize_text_field( wp_unslash( $_POST['balance_guess'] ) ) : '' );
	}
	$guess = -1.0;
	if ( function_exists( 'bhg_parse_amount' ) ) {
		$parsed = bhg_parse_amount( $raw_guess );
		$guess  = ( null === $parsed ) ? -1.0 : (float) $parsed;
	} else {
		$guess = is_numeric( $raw_guess ) ? (float) $raw_guess : -1.0;
	}
	$settings       = get_option( 'bhg_plugin_settings', array() );
	$min_guess      = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
	$max_guess      = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;
	$max            = isset( $settings['max_guesses'] ) ? (int) $settings['max_guesses'] : 1;
		$allow_edit = isset( $settings['allow_guess_changes'] ) && 'yes' === $settings['allow_guess_changes'];

	if ( $guess < $min_guess || $guess > $max_guess ) {
                if ( wp_doing_ajax() ) {
                        wp_send_json_error( bhg_t( 'notice_invalid_guess_amount', 'Invalid guess amount.' ) );
                }
                wp_die( esc_html( bhg_t( 'notice_invalid_guess_amount', 'Invalid guess amount.' ) ) );
	}

	global $wpdb;
	$hunts = $wpdb->prefix . 'bhg_bonus_hunts';
	$g_tbl = $wpdb->prefix . 'bhg_guesses';

		// db call ok; no-cache ok.
				$hunt = $wpdb->get_row( $wpdb->prepare( 'SELECT id, status FROM %i WHERE id = %d', $hunts, $hunt_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( ! $hunt ) {
                if ( wp_doing_ajax() ) {
                        wp_send_json_error( bhg_t( 'notice_hunt_not_found', 'Hunt not found.' ) );
                }
                wp_die( esc_html( bhg_t( 'notice_hunt_not_found', 'Hunt not found.' ) ) );
        }
        if ( 'open' !== $hunt->status ) {
                if ( wp_doing_ajax() ) {
                        wp_send_json_error( bhg_t( 'this_hunt_is_closed_you_cannot_submit_or_change_a_guess', 'This hunt is closed. You cannot submit or change a guess.' ) );
                }
                wp_die( esc_html( bhg_t( 'this_hunt_is_closed_you_cannot_submit_or_change_a_guess', 'This hunt is closed. You cannot submit or change a guess.' ) ) );
        }

		// Insert or update last guess per settings.

		// db call ok; no-cache ok.
				$count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE hunt_id = %d AND user_id = %d', $g_tbl, $hunt_id, $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( $count >= $max ) {
		if ( $allow_edit && $count > 0 ) {
				// db call ok; no-cache ok.
																$gid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM %i WHERE hunt_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1', $g_tbl, $hunt_id, $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $gid ) {
								// db call ok; no-cache ok.
																$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
																	$g_tbl,
																	array(
																		'guess' => $guess,
																		'updated_at' => current_time( 'mysql' ),
																	),
																	array( 'id' => $gid )
																);
				if ( wp_doing_ajax() ) {
					wp_send_json_success();
				}
								$referer = wp_get_referer();
								wp_safe_redirect( $referer ? $referer : home_url() );
				exit;
			}
		}
                if ( wp_doing_ajax() ) {
                        wp_send_json_error( bhg_t( 'you_have_reached_the_maximum_number_of_guesses', 'You have reached the maximum number of guesses.' ) );
                }
                wp_die( esc_html( bhg_t( 'you_have_reached_the_maximum_number_of_guesses', 'You have reached the maximum number of guesses.' ) ) );
	}

		// Insert.
		// db call ok; no-cache ok.
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$g_tbl,
					array(
						'hunt_id'    => $hunt_id,
						'user_id'    => $user_id,
						'guess'      => $guess,
						'created_at' => current_time( 'mysql' ),
					),
					array( '%d', '%d', '%f', '%s' )
				);

	if ( wp_doing_ajax() ) {
		wp_send_json_success();
	}

		$referer = wp_get_referer();
		wp_safe_redirect( $referer ? $referer : home_url() );
	exit;
}

// Frontend ads rendering.
/**
 * Determine if an advertisement should be displayed.
 *
 * @param string $visibility Visibility rule.
 * @return bool True if ad should be shown, false otherwise.
 */
function bhg_should_show_ad( $visibility ) {
	if ( 'all' === $visibility ) {
			return true;
	}
	if ( 'logged_in' === $visibility ) {
			return ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() );
	}
	if ( 'guests' === $visibility ) {
			return ! ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() );
	}
	if ( 'affiliates' === $visibility ) {
			return ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) && bhg_is_affiliate();
	}
	if ( 'non_affiliates' === $visibility ) {
			return ! ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) || ! bhg_is_affiliate();
	}
	return true;
}

/**
 * Safe and validated ads query builder.
 *
 * @param string $table     Table name without prefix.
 * @param string $placement Ad placement.
 * @return array List of ad rows.
 */
function bhg_build_ads_query( $table, $placement = 'footer' ) {
		global $wpdb;

		$allowed_tables = array( $wpdb->prefix . 'bhg_ads' );
	if ( ! in_array( $table, $allowed_tables, true ) ) {
			return array();
	}

		$table = esc_sql( $table );

		// db call ok; no-cache ok.
				$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						'SELECT * FROM %i WHERE placement = %s AND active = %d',
						$table,
						$placement,
						1
					)
				);
	if ( did_action( 'wp' ) && function_exists( 'get_queried_object_id' ) ) {
			$pid = (int) get_queried_object_id();
		if ( $pid && is_array( $rows ) ) {
				$rows = array_filter(
					$rows,
					function ( $r ) use ( $pid ) {
						if ( empty( $r->target_pages ) ) {
									return true;
						}
							$ids = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $r->target_pages ) ) ) );
							return in_array( $pid, $ids, true );
					}
				);
		}
	}
		return $rows;
}

// AJAX handler for loading leaderboard data.
add_action( 'wp_ajax_bhg_load_leaderboard', 'bhg_load_leaderboard_ajax' );
add_action( 'wp_ajax_nopriv_bhg_load_leaderboard', 'bhg_load_leaderboard_ajax' );

/**
 * AJAX handler for loading leaderboard markup.
 *
 * @return void
 */
function bhg_load_leaderboard_ajax() {
		check_ajax_referer( 'bhg_public_nonce', 'nonce' );

        if ( ! isset( $_POST['timeframe'] ) ) {
                wp_send_json_error( bhg_t( 'invalid_timeframe', 'Invalid timeframe' ) );
        }

		$timeframe          = sanitize_text_field( wp_unslash( $_POST['timeframe'] ) );
		$allowed_timeframes = array( 'overall', 'monthly', 'yearly', 'alltime' );
        if ( ! in_array( $timeframe, $allowed_timeframes, true ) ) {
                        wp_send_json_error( bhg_t( 'invalid_timeframe', 'Invalid timeframe' ) );
        }

		$paged = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;

		// Generate leaderboard HTML based on timeframe.
		$html = bhg_generate_leaderboard_html( $timeframe, $paged );

		wp_send_json_success( $html );
}

// Helper function to generate leaderboard HTML.
/**
 * Generate leaderboard HTML for a timeframe.
 *
 * @param string $timeframe Timeframe key.
 * @param int    $paged     Page number.
 * @return string Generated HTML.
 */
function bhg_generate_leaderboard_html( $timeframe, $paged ) {
		global $wpdb;

		$per_page = 20;
		$offset   = ( $paged - 1 ) * $per_page;

		$start_date = '';
		$now        = time();
	switch ( strtolower( $timeframe ) ) {
		case 'monthly':
			$start_date = gmdate( 'Y-m-01 00:00:00', $now );
			break;
		case 'yearly':
			$start_date = gmdate( 'Y-01-01 00:00:00', $now );
			break;
		case 'overall':
			$start_date = gmdate( 'Y-m-d H:i:s', $now - 30 * DAY_IN_SECONDS );
			break;
		case 'all-time':
		case 'all_time':
		default:
			$start_date = '';
			break;
	}

				$g = esc_sql( $wpdb->prefix . 'bhg_guesses' );
				$h = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
				$u = esc_sql( $wpdb->users );

				$where_parts = array( "h.status='closed' AND h.final_balance IS NOT NULL" );
	if ( $start_date ) {
			$where_parts[] = $wpdb->prepare( 'h.updated_at >= %s', $start_date );
	}
				$where_clause = implode( ' AND ', $where_parts );

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$total_sql = "
	SELECT COUNT(*) FROM (
		SELECT g.user_id
		FROM {$g} g
		INNER JOIN {$h} h ON h.id = g.hunt_id
		WHERE {$where_clause} AND NOT EXISTS (
			SELECT 1 FROM {$g} g2
			WHERE g2.hunt_id = g.hunt_id
			AND ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance)
		)
		GROUP BY g.user_id
	) t
	";
	// db call ok; no-cache ok.
		$total = (int) $wpdb->get_var( $total_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql      = "
	SELECT g.user_id, u.user_login, COUNT(*) AS wins
	FROM {$g} g
	INNER JOIN {$h} h ON h.id = g.hunt_id
	INNER JOIN {$u} u ON u.ID = g.user_id
	WHERE {$where_clause} AND NOT EXISTS (
		SELECT 1 FROM {$g} g2
		WHERE g2.hunt_id = g.hunt_id
		AND ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance)
	)
	GROUP BY g.user_id, u.user_login
	ORDER BY wins DESC, u.user_login ASC
	LIMIT %d OFFSET %d
	";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $per_page, $offset ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        if ( ! $rows ) {
                return '<p>' . esc_html( bhg_t( 'notice_no_data_available', 'No data available.' ) ) . '</p>';
        }

	ob_start();
	echo '<table class="bhg-leaderboard bhg-table" data-timeframe="' . esc_attr( $timeframe ) . '">';
        echo '<thead><tr>';
        echo '<th class="sortable" data-sort="position">' . esc_html( bhg_t( 'sc_position', 'Position' ) ) . '</th>';
        echo '<th class="sortable" data-sort="username">' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</th>';
        echo '<th class="sortable" data-sort="wins">' . esc_html( bhg_t( 'sc_wins', 'Wins' ) ) . '</th>';
        echo '</tr></thead><tbody>';

	$pos = $offset + 1;
	foreach ( $rows as $row ) {
                                /* translators: %d: user ID. */
                                $user_label = $row->user_login ? $row->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
				echo '<tr>';
				echo '<td>' . (int) $pos . '</td>';
				echo '<td>' . esc_html( $user_label ) . '</td>';
				echo '<td>' . (int) $row->wins . '</td>';
				echo '</tr>';
				++$pos;
	}
	echo '</tbody></table>';

	$pages = (int) ceil( $total / $per_page );
	if ( $pages > 1 ) {
		echo '<div class="bhg-pagination">';
		for ( $p = 1; $p <= $pages; $p++ ) {
				$current = $paged === $p ? 'current' : '';
						echo '<a href="#" data-page="' . (int) $p . '" class="' . esc_attr( $current ) . '">' . (int) $p . '</a> ';
		}
		echo '</div>';
	}

	return ob_get_clean();
}

// Helper function to check if user is affiliate.
/**
 * Check whether a user has affiliate status.
 *
 * @param int|null $user_id Optional. User ID to check. Defaults to current user.
 * @return bool True if user is an affiliate.
 */
function bhg_is_affiliate( $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

		return (bool) get_user_meta( $user_id, 'bhg_is_affiliate', true );
}

// Add user profile fields for affiliate status.
add_action( 'show_user_profile', 'bhg_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'bhg_extra_user_profile_fields' );

/**
 * Display affiliate status field on user profile.
 *
 * @param WP_User $user User object.
 * @return void
 */
function bhg_extra_user_profile_fields( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

		$affiliate_status = get_user_meta( $user->ID, 'bhg_is_affiliate', true );
	?>
			<h3><?php esc_html_e( 'Bonus Hunt Guesser Information', 'bonus-hunt-guesser' ); ?></h3>
	<table class="form-table">
		<tr>
							<th><label for="bhg_is_affiliate"><?php esc_html_e( 'Affiliate Status', 'bonus-hunt-guesser' ); ?></label></th>
			<td>
									<input type="checkbox" name="bhg_is_affiliate" id="bhg_is_affiliate" value="1" <?php checked( $affiliate_status, 1 ); ?> />
									<span class="description"><?php esc_html_e( 'Check if this user is an affiliate.', 'bonus-hunt-guesser' ); ?></span>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'personal_options_update', 'bhg_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'bhg_save_extra_user_profile_fields' );

/**
 * Save affiliate status from user profile.
 *
 * @param int $user_id User ID.
 * @return void|false Returns false if the user cannot be edited.
 */
function bhg_save_extra_user_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
	}

				check_admin_referer( 'update-user_' . $user_id, '_wpnonce' );

		$affiliate_status = isset( $_POST['bhg_is_affiliate'] ) ? 1 : 0;
		update_user_meta( $user_id, 'bhg_is_affiliate', $affiliate_status );
}

if ( ! function_exists( 'bhg_self_heal_db' ) ) {
	/**
	 * Attempt to repair missing database tables.
	 *
	 * @return void
	 */
	function bhg_self_heal_db() {
		if ( ! class_exists( 'BHG_DB' ) ) {
				require_once __DIR__ . '/includes/class-bhg-db.php';
		}
		try {
				$db = new BHG_DB();
				$db->create_tables();
		} catch ( Throwable $e ) {
			if ( function_exists( 'trigger_error' ) ) {
				trigger_error( '[BHG] DB self-heal failed: ' . esc_html( $e->getMessage() ), E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			}
		}
	}
		add_action( 'admin_init', 'bhg_self_heal_db' );
		register_activation_hook( __FILE__, 'bhg_self_heal_db' );
}


