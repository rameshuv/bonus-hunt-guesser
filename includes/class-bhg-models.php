<?php
/**
 * Data layer utilities for Bonus Hunt Guesser.
 *
 * This class previously handled guess submissions directly. Guess handling is
 * now centralized through {@see bhg_handle_submit_guess()} in
 * `bonus-hunt-guesser.php`. The methods related to form handling and request
 * routing were removed to avoid duplication and ensure a single canonical
 * implementation.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class providing data layer utilities for Bonus Hunt Guesser.
 */
class BHG_Models {

	/**
	 * Close a bonus hunt and determine winners.
	 *
	 * @param int   $hunt_id       Hunt identifier.
	 * @param float $final_balance Final balance for the hunt.
	 *
	 * @return int[]|false Array of winning user IDs or false on failure.
	 */
	public static function close_hunt( $hunt_id, $final_balance ) {
		global $wpdb;

		if ( class_exists( 'BHG_DB' ) ) {
			BHG_DB::migrate();
		}

		$hunt_id       = (int) $hunt_id;
		$final_balance = (float) $final_balance;

		if ( $hunt_id <= 0 ) {
			return array();
		}

				$hunts_tbl   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
				$guesses_tbl = esc_sql( $wpdb->prefix . 'bhg_guesses' );
				$winners_tbl = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
				$tres_tbl    = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );

				// Determine number of winners and tournament association for this hunt.
				$hunt_row = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT winners_count, tournament_id FROM ' . $wpdb->prefix . 'bhg_bonus_hunts WHERE id = %d',
						(int) $hunt_id
					)
				);
		$winners_count    = $hunt_row ? (int) $hunt_row->winners_count : 0;
		if ( $winners_count <= 0 ) {
			$winners_count = 1;
		}
		$tournament_id = $hunt_row ? (int) $hunt_row->tournament_id : 0;

		// Update hunt status and final details.
		$now     = current_time( 'mysql' );
		$updated = $wpdb->update(
			$hunts_tbl,
			array(
				'status'        => 'closed',
				'final_balance' => $final_balance,
				'closed_at'     => $now,
				'updated_at'    => $now,
			),
			array( 'id' => $hunt_id ),
			array( '%s', '%f', '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			bhg_log( $wpdb->last_error );
			return false;
		}

				// Fetch winners based on proximity to final balance.
				$rows = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT user_id, guess, ABS(guess - %f) AS diff FROM ' . $wpdb->prefix . 'bhg_guesses WHERE hunt_id = %d ORDER BY diff ASC, id ASC LIMIT %d',
						(float) $final_balance,
						(int) $hunt_id,
						(int) $winners_count
					)
				);

		if ( empty( $rows ) ) {
			return array();
		}

		// Record winners and update tournament results.
		$position = 1;
		foreach ( (array) $rows as $row ) {
			$wpdb->insert(
				$winners_tbl,
				array(
					'hunt_id'    => $hunt_id,
					'user_id'    => (int) $row->user_id,
					'position'   => $position,
					'guess'      => (float) $row->guess,
					'diff'       => (float) $row->diff,
					'created_at' => $now,
				),
				array( '%d', '%d', '%d', '%f', '%f', '%s' )
			);

			if ( $tournament_id > 0 ) {
										$existing = $wpdb->get_row(
											$wpdb->prepare(
												'SELECT id, wins FROM ' . $wpdb->prefix . 'bhg_tournament_results WHERE tournament_id = %d AND user_id = %d',
												(int) $tournament_id,
												(int) $row->user_id
											)
										);
				if ( $existing ) {
					$updated = $wpdb->update(
						$tres_tbl,
						array(
							'wins'          => (int) $existing->wins + 1,
							'last_win_date' => $now,
						),
						array( 'id' => (int) $existing->id ),
						array( '%d', '%s' ),
						array( '%d' )
					);

					if ( false === $updated ) {
						bhg_log( $wpdb->last_error );
						return false;
					}
				} else {
					$wpdb->insert(
						$tres_tbl,
						array(
							'tournament_id' => $tournament_id,
							'user_id'       => (int) $row->user_id,
							'wins'          => 1,
							'last_win_date' => $now,
						),
						array( '%d', '%d', '%d', '%s' )
					);
				}
			}

			++$position;
		}

		return array_map( 'intval', wp_list_pluck( $rows, 'user_id' ) );
	}

	/**
	 * Recalculate tournament leaderboards based on current hunt winners.
	 *
	 * @param int[] $tournament_ids Tournament identifiers to recalculate.
	 *
	 * @return void
	 */
	public static function recalculate_tournament_results( array $tournament_ids ) {
		global $wpdb;

		if ( empty( $tournament_ids ) ) {
			return;
		}

		$normalized = array();
		foreach ( $tournament_ids as $tournament_id ) {
			$tournament_id = absint( $tournament_id );
			if ( $tournament_id > 0 ) {
				$normalized[ $tournament_id ] = $tournament_id;
			}
		}

		if ( empty( $normalized ) ) {
			return;
		}

		$hunts_tbl   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$winners_tbl = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
		$results_tbl = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );

		foreach ( $normalized as $tournament_id ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT hw.user_id, COUNT(*) AS wins, MAX(COALESCE(hw.created_at, h.closed_at, h.updated_at, h.created_at)) AS last_win_date\n"
					. "FROM {$winners_tbl} hw\n"
					. "INNER JOIN {$hunts_tbl} h ON h.id = hw.hunt_id\n"
					. "WHERE h.tournament_id = %d\n"
					. "GROUP BY hw.user_id",
					$tournament_id
				)
			);

			if ( null === $rows && $wpdb->last_error ) {
				bhg_log( sprintf( 'Failed to fetch recalculated standings for tournament #%d: %s', $tournament_id, $wpdb->last_error ) );
				continue;
			}

			$deleted = $wpdb->delete( $results_tbl, array( 'tournament_id' => $tournament_id ), array( '%d' ) );
			if ( false === $deleted ) {
				bhg_log( sprintf( 'Failed to clear existing standings for tournament #%d: %s', $tournament_id, $wpdb->last_error ) );
				continue;
			}

			if ( empty( $rows ) ) {
				continue;
			}

			foreach ( $rows as $row ) {
				$wins    = isset( $row->wins ) ? (int) $row->wins : 0;
				$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;

				if ( $wins <= 0 || $user_id <= 0 ) {
					continue;
				}

				$last_win = ! empty( $row->last_win_date ) ? $row->last_win_date : current_time( 'mysql' );

				$inserted = $wpdb->insert(
					$results_tbl,
					array(
						'tournament_id' => $tournament_id,
						'user_id'       => $user_id,
						'wins'          => $wins,
						'last_win_date' => $last_win,
					),
					array( '%d', '%d', '%d', '%s' )
				);

				if ( false === $inserted ) {
					bhg_log( sprintf( 'Failed to store recalculated standings for tournament #%1$d and user #%2$d: %3$s', $tournament_id, $user_id, $wpdb->last_error ) );
				}
			}
		}
	}
}
