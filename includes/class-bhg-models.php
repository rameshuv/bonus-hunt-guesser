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
		$tours_tbl   = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

		// Determine number of winners and tournament association for this hunt.
		$hunt_row      = $wpdb->get_row(
			$wpdb->prepare(
				' SELECT winners_count, tournament_id FROM ' . $hunts_tbl . ' WHERE id = %d ',
				$hunt_id
			)
		);
		$winners_count = $hunt_row ? (int) $hunt_row->winners_count : 0;
		if ( $winners_count <= 0 ) {
			$winners_count = 1;
		}
		$tournament_ids = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( $hunt_id ) : array();
		if ( empty( $tournament_ids ) && $hunt_row && ! empty( $hunt_row->tournament_id ) ) {
			$tournament_ids = array( (int) $hunt_row->tournament_id );
		}
		$tournament_ids   = array_map( 'intval', array_unique( $tournament_ids ) );
		$tournament_modes = array();
		if ( ! empty( $tournament_ids ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $tournament_ids ), '%d' ) );
			$mode_rows    = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, participants_mode FROM {$tours_tbl} WHERE id IN ({$placeholders})",
					$tournament_ids
				)
			);
			if ( ! empty( $mode_rows ) ) {
				foreach ( $mode_rows as $mode_row ) {
					$tid  = isset( $mode_row->id ) ? (int) $mode_row->id : 0;
					$mode = isset( $mode_row->participants_mode ) ? sanitize_key( $mode_row->participants_mode ) : 'winners';
					if ( $tid <= 0 ) {
						continue;
					}
					if ( ! in_array( $mode, array( 'winners', 'all' ), true ) ) {
						$mode = 'winners';
					}
					$tournament_modes[ $tid ] = $mode;
				}
			}
		}
		$has_all_mode = in_array( 'all', $tournament_modes, true );

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

		// Remove existing winners and reverse previous tournament tallies.
						$existing_winners = $wpdb->get_results(
							$wpdb->prepare(
								'SELECT user_id, position, points FROM ' . $winners_tbl . ' WHERE hunt_id = %d',
								$hunt_id
							)
						);

		if ( null === $existing_winners && $wpdb->last_error ) {
			bhg_log( $wpdb->last_error );
			return false;
		}

		if ( ! empty( $existing_winners ) ) {
				$winner_records = array();
			foreach ( $existing_winners as $existing_winner ) {
						$user_id  = isset( $existing_winner->user_id ) ? (int) $existing_winner->user_id : 0;
						$position = isset( $existing_winner->position ) ? (int) $existing_winner->position : 0;
						$points   = isset( $existing_winner->points ) ? (int) $existing_winner->points : 0;

				if ( $user_id <= 0 ) {
					continue;
				}

				if ( ! isset( $winner_records[ $user_id ] ) ) {
					$winner_records[ $user_id ] = array();
				}

						$winner_records[ $user_id ][] = array(
							'position' => $position,
							'points'   => $points,
						);
			}

				$deleted = $wpdb->delete( $winners_tbl, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
			if ( false === $deleted ) {
							bhg_log( $wpdb->last_error );
							return false;
			}

			if ( ! empty( $tournament_ids ) && ! empty( $winner_records ) ) {
				foreach ( $tournament_ids as $tournament_id ) {
						$mode = isset( $tournament_modes[ $tournament_id ] ) ? $tournament_modes[ $tournament_id ] : 'winners';

					foreach ( $winner_records as $user_id => $entries ) {
							$remove_count  = 0;
							$remove_points = 0;

						if ( 'all' === $mode ) {
											$remove_count = count( $entries );
							foreach ( $entries as $entry ) {
								$remove_points += isset( $entry['points'] ) ? (int) $entry['points'] : 0;
							}
						} else {
							foreach ( $entries as $entry ) {
								$position_value = isset( $entry['position'] ) ? (int) $entry['position'] : 0;
								if ( $position_value > 0 && $position_value <= $winners_count ) {
													++$remove_count;
													$points_value = isset( $entry['points'] ) ? (int) $entry['points'] : 0;
									if ( $points_value <= 0 ) {
										$points_value = bhg_get_points_for_position( $position_value, 'closed' );
									}
													$remove_points += $points_value;
								}
							}
						}

						if ( $remove_count <= 0 && $remove_points <= 0 ) {
								continue;
						}

														$existing_result = $wpdb->get_row(
															$wpdb->prepare(
																'SELECT id, wins, points FROM ' . $tres_tbl . ' WHERE tournament_id = %d AND user_id = %d',
																(int) $tournament_id,
																$user_id
															)
														);

						if ( ! $existing_result ) {
							continue;
						}

							$remaining_wins   = max( 0, (int) $existing_result->wins - (int) $remove_count );
							$remaining_points = max( 0, (int) $existing_result->points - (int) $remove_points );

						if ( $remaining_wins > 0 || $remaining_points > 0 ) {
											$updated = $wpdb->update(
												$tres_tbl,
												array(
													'wins' => $remaining_wins,
													'points' => $remaining_points,
												),
												array( 'id' => (int) $existing_result->id ),
												array( '%d', '%d' ),
												array( '%d' )
											);

							if ( false === $updated ) {
								bhg_log( $wpdb->last_error );
								return false;
							}
						} else {
												$deleted_result = $wpdb->delete( $tres_tbl, array( 'id' => (int) $existing_result->id ), array( '%d' ) );
							if ( false === $deleted_result ) {
								bhg_log( $wpdb->last_error );
								return false;
							}
						}
					}
				}
			}
		}
		// Fetch winners based on proximity to final balance.
				$query  = 'SELECT user_id, guess, (%f - guess) AS diff FROM ' . $guesses_tbl . ' WHERE hunt_id = %d ORDER BY ABS(%f - guess) ASC, id ASC';
				$params = array( (float) $final_balance, (int) $hunt_id, (float) $final_balance );
		if ( ! $has_all_mode ) {
				$query   .= ' LIMIT %d';
				$params[] = (int) $winners_count;
		}

				$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $query ), $params ) );
				$rows     = $wpdb->get_results( $prepared );
		if ( empty( $rows ) ) {
			return array();
		}

		// Record winners and update tournament results.
				$points_context = 'closed';
				$position       = 1;
		foreach ( (array) $rows as $row ) {
				$earned_points = bhg_get_points_for_position( $position, $points_context );
				$wpdb->insert(
					$winners_tbl,
					array(
						'hunt_id'    => $hunt_id,
						'user_id'    => (int) $row->user_id,
						'position'   => $position,
						'guess'      => (float) $row->guess,
						'diff'       => (float) $row->diff,
						'points'     => (int) $earned_points,
						'created_at' => $now,
					),
					array( '%d', '%d', '%d', '%f', '%f', '%d', '%s' )
				);

			if ( ! empty( $tournament_ids ) ) {
				foreach ( $tournament_ids as $tournament_id ) {
						$mode = isset( $tournament_modes[ $tournament_id ] ) ? $tournament_modes[ $tournament_id ] : 'winners';

					if ( 'all' !== $mode && $position > $winners_count ) {
									continue;
					}

					$existing = $wpdb->get_row(
						$wpdb->prepare(
							'SELECT id, wins, points FROM ' . $tres_tbl . ' WHERE tournament_id = %d AND user_id = %d',
							(int) $tournament_id,
							(int) $row->user_id
						)
					);

					if ( $existing ) {
						$updated = $wpdb->update(
							$tres_tbl,
							array(
								'wins'          => (int) $existing->wins + 1,
								'points'        => (int) $existing->points + (int) $earned_points,
								'last_win_date' => $now,
							),
							array( 'id' => (int) $existing->id ),
							array( '%d', '%d', '%s' ),
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
											'tournament_id' => (int) $tournament_id,
											'user_id' => (int) $row->user_id,
											'wins'    => 1,
											'points'  => (int) $earned_points,
											'last_win_date' => $now,
										),
										array( '%d', '%d', '%d', '%d', '%s' )
									);
					}
				}
			}
			++$position;
		}

		return array_map( 'intval', wp_list_pluck( $rows, 'user_id' ) );
	}


		/**
		 * Recalculate tournament leaderboards based on current hunt winners.
		 *
		 * @param int[]  $tournament_ids Tournament identifiers to recalculate.
		 * @param string $status_filter  Optional status filter (all|active|closed).
		 *
		 * @return void
		 */
	public static function recalculate_tournament_results( array $tournament_ids, $status_filter = 'all' ) {
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

			$status_filter  = sanitize_key( $status_filter );
			$allowed_filter = array( 'all', 'active', 'closed' );
		if ( ! in_array( $status_filter, $allowed_filter, true ) ) {
				$status_filter = 'all';
		}

			$hunts_tbl    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$winners_tbl  = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
			$results_tbl  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
			$relation_tbl = esc_sql( $wpdb->prefix . 'bhg_hunt_tournaments' );
			$tours_tbl    = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

		foreach ( $normalized as $tournament_id ) {
				$status_condition = '';
			if ( 'active' === $status_filter ) {
					$status_condition = " AND h.status = 'open'";
			} elseif ( 'closed' === $status_filter ) {
					$status_condition = " AND h.status = 'closed'";
			}

				$query = "
                                SELECT
                                        hw.user_id,
                                        hw.position,
                                        hw.points,
                                        hw.hunt_id,
                                        COALESCE( hw.created_at, h.closed_at, h.updated_at, h.created_at ) AS event_date,
                                        h.winners_count,
                                        h.status,
                                        t.participants_mode
                                FROM {$winners_tbl} hw
                                INNER JOIN {$hunts_tbl} h ON h.id = hw.hunt_id
                                LEFT JOIN {$relation_tbl} ht ON ht.hunt_id = h.id
                                INNER JOIN {$tours_tbl} t ON t.id = COALESCE( ht.tournament_id, h.tournament_id )
                                WHERE t.id = %d
                                  AND ( ht.tournament_id = %d OR ( ht.tournament_id IS NULL AND h.tournament_id = %d ) )
                                {$status_condition}
                        ";

				$rows = $wpdb->get_results(
					$wpdb->prepare(
						$query,
						$tournament_id,
						$tournament_id,
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

				$standings = array();

			foreach ( $rows as $row ) {
					$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;
				if ( $user_id <= 0 ) {
						continue;
				}

					$mode = isset( $row->participants_mode ) ? sanitize_key( $row->participants_mode ) : 'winners';
				if ( ! in_array( $mode, array( 'winners', 'all' ), true ) ) {
						$mode = 'winners';
				}

					$position = isset( $row->position ) ? (int) $row->position : 0;
					$limit    = isset( $row->winners_count ) ? (int) $row->winners_count : 0;
				if ( $limit <= 0 ) {
						$limit = 1;
				}

				if ( 'winners' === $mode && ( $position <= 0 || $position > $limit ) ) {
						continue;
				}

				if ( ! isset( $standings[ $user_id ] ) ) {
						$standings[ $user_id ] = array(
							'wins'          => 0,
							'points'        => 0,
							'last_win_date' => '',
						);
				}

					++$standings[ $user_id ]['wins'];

					$points_context = 'all';
					$hunt_status    = isset( $row->status ) ? sanitize_key( $row->status ) : '';
				if ( 'closed' === $hunt_status ) {
						$points_context = 'closed';
				} elseif ( 'open' === $hunt_status ) {
						$points_context = 'active';
				}

				if ( 'active' === $status_filter ) {
						$points_context = 'active';
				} elseif ( 'closed' === $status_filter ) {
						$points_context = 'closed';
				}

					$awarded_points = isset( $row->points ) ? (int) $row->points : 0;
				if ( $awarded_points <= 0 ) {
						$awarded_points = bhg_get_points_for_position( max( 1, $position ), $points_context );
				}

				if ( $awarded_points > 0 ) {
						$standings[ $user_id ]['points'] += $awarded_points;
				}

					$event_date = isset( $row->event_date ) ? $row->event_date : '';
				if ( empty( $event_date ) ) {
						$event_date = current_time( 'mysql' );
				}

				if ( empty( $standings[ $user_id ]['last_win_date'] ) || strcmp( $event_date, $standings[ $user_id ]['last_win_date'] ) > 0 ) {
						$standings[ $user_id ]['last_win_date'] = $event_date;
				}
			}

			if ( empty( $standings ) ) {
					continue;
			}

			foreach ( $standings as $user_id => $result ) {
					$wins          = isset( $result['wins'] ) ? (int) $result['wins'] : 0;
					$points        = isset( $result['points'] ) ? (int) $result['points'] : 0;
					$last_win_date = isset( $result['last_win_date'] ) ? $result['last_win_date'] : '';

				if ( $wins <= 0 && $points <= 0 ) {
						continue;
				}

				if ( empty( $last_win_date ) ) {
						$last_win_date = current_time( 'mysql' );
				}

					$inserted = $wpdb->insert(
						$results_tbl,
						array(
							'tournament_id' => $tournament_id,
							'user_id'       => (int) $user_id,
							'wins'          => $wins,
							'points'        => $points,
							'last_win_date' => $last_win_date,
						),
						array( '%d', '%d', '%d', '%d', '%s' )
					);

				if ( false === $inserted ) {
					bhg_log( sprintf( 'Failed to store recalculated standings for tournament #%1$d and user#%2$d: %3$s', $tournament_id, $user_id, $wpdb->last_error ) );
				}
			}
		}
	}
}
