<?php
/**
 * Tournaments controller for Bonus Hunt Guesser.
 *
 * Previously applied default tournament settings during creation. The default
 * period logic has been removed since start and end dates define scope.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles tournament-related hooks and logic.
 */
class BHG_Tournaments_Controller {
	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init() {
		// Default period logic removed; start and end dates define scope.
	}

	/**
	 * Send tournament closure notifications.
	 *
	 * @param int $tournament_id Tournament identifier.
	 *
	 * @return bool True if notifications were sent, false otherwise.
	 */
	public static function send_tournament_closed_notification( $tournament_id ) {
		if ( ! class_exists( 'BHG_Notifications' ) ) {
			return false;
		}

		global $wpdb;

		$tournament_id = absint( $tournament_id );
		if ( $tournament_id <= 0 ) {
			return false;
		}

		$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
		$results_table     = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );

		$tournament = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT title, type FROM {$tournaments_table} WHERE id = %d",
				$tournament_id
			)
		);

		if ( ! $tournament ) {
			return false;
		}

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, wins FROM {$results_table} WHERE tournament_id = %d ORDER BY wins DESC, user_id ASC",
				$tournament_id
			)
		);

		if ( empty( $rows ) ) {
			return false;
		}

		$recipients = array();
		$rank       = 1;

		foreach ( $rows as $row ) {
			$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;
			if ( $user_id <= 0 ) {
				continue;
			}

			$user = get_userdata( $user_id );
			if ( ! $user || empty( $user->user_email ) ) {
				++$rank;
				continue;
			}

			$display = $user->display_name ? $user->display_name : $user->user_login;

			$recipients[] = array(
				'email'        => $user->user_email,
				'replacements' => array(
					'{{username}}'     => sanitize_text_field( $user->user_login ),
					'{{display_name}}' => sanitize_text_field( $display ),
					'{{wins}}'         => isset( $row->wins ) ? (int) $row->wins : 0,
					'{{rank}}'         => $rank,
				),
			);

			++$rank;
		}

		if ( empty( $recipients ) ) {
			return false;
		}

		$title = isset( $tournament->title ) ? sanitize_text_field( $tournament->title ) : '';
		$type  = isset( $tournament->type ) ? sanitize_key( $tournament->type ) : '';

		$base = array(
			'{{tournament_title}}' => $title,
			'{{tournament_type}}'  => $type,
		);

		return BHG_Notifications::send( 'tournament_results', $base, $recipients );
	}
}
