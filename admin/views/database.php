<?php
/**
 * Database maintenance admin view.
 *
 * Provides cleanup and optimization tools for Bonus Hunt Guesser tables.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

// Handle form submissions.
if ( isset( $_POST['bhg_action'] ) ) {
	if ( 'db_cleanup' === $_POST['bhg_action'] && isset( $_POST['bhg_db_cleanup'] ) ) {
		check_admin_referer( 'bhg_db_cleanup_action', 'bhg_nonce' );

		// Perform database cleanup.
		bhg_database_cleanup();
		$cleanup_completed = true;
	} elseif ( 'db_optimize' === $_POST['bhg_action'] && isset( $_POST['bhg_db_optimize'] ) ) {
		check_admin_referer( 'bhg_db_optimize_action', 'bhg_nonce' );

		// Perform database optimization.
		bhg_database_optimize();
		$optimize_completed = true;
	}
}

/**
 * Truncate all plugin tables and reinsert demo data.
 *
 * @return void
 */
function bhg_database_cleanup() {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'bhg_bonus_hunts',
		$wpdb->prefix . 'bhg_guesses',
		$wpdb->prefix . 'bhg_tournaments',
		$wpdb->prefix . 'bhg_tournament_results',
		$wpdb->prefix . 'bhg_translations',
		$wpdb->prefix . 'bhg_affiliate_websites',
		$wpdb->prefix . 'bhg_hunt_winners',
		$wpdb->prefix . 'bhg_ads',
	);

	foreach ( $tables as $table ) {
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Identifier placeholder is valid in WP 6.3.
			$wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %i', $table ) );
		}
	}

	// Reinsert default data if needed.
	bhg_insert_demo_data();
}

/**
 * Optimize all plugin tables.
 *
 * @return void
 */
function bhg_database_optimize() {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'bhg_bonus_hunts',
		$wpdb->prefix . 'bhg_guesses',
		$wpdb->prefix . 'bhg_tournaments',
		$wpdb->prefix . 'bhg_tournament_results',
		$wpdb->prefix . 'bhg_translations',
		$wpdb->prefix . 'bhg_affiliate_websites',
		$wpdb->prefix . 'bhg_hunt_winners',
		$wpdb->prefix . 'bhg_ads',
	);

	foreach ( $tables as $table ) {
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Identifier placeholder is valid in WP 6.3.
			$wpdb->query( $wpdb->prepare( 'OPTIMIZE TABLE %i', $table ) );
		}
	}
}

/**
 * Insert basic demo data.
 *
 * This would typically live in a separate demo seeder file.
 *
 * @return void
 */
function bhg_insert_demo_data() {
	global $wpdb;

	// Insert default bonus hunt.
	$wpdb->insert(
		$wpdb->prefix . 'bhg_bonus_hunts',
		array(
			'title'             => 'Demo Bonus Hunt',
			'starting_balance'  => 2000,
			'number_of_bonuses' => 10,
			'status'            => 'active',
			'created_at'        => current_time( 'mysql' ),
		),
		array( '%s', '%d', '%d', '%s', '%s' )
	);
}

