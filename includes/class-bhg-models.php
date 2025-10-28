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
$hunt_row = $wpdb->get_row(
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
				'SELECT user_id, position FROM ' . $winners_tbl . ' WHERE hunt_id = %d',
				$hunt_id
			)
		);

		if ( null === $existing_winners && $wpdb->last_error ) {
			bhg_log( $wpdb->last_error );
			return false;
		}

		if ( ! empty( $existing_winners ) ) {
			$winner_positions = array();
			foreach ( $existing_winners as $existing_winner ) {
				$user_id  = isset( $existing_winner->user_id ) ? (int) $existing_winner->user_id : 0;
				$position = isset( $existing_winner->position ) ? (int) $existing_winner->position : 0;

				if ( $user_id <= 0 ) {
					continue;
				}

				if ( ! isset( $winner_positions[ $user_id ] ) ) {
					$winner_positions[ $user_id ] = array();
				}

				$winner_positions[ $user_id ][] = $position;
			}

			$deleted = $wpdb->delete( $winners_tbl, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
			if ( false === $deleted ) {
				bhg_log( $wpdb->last_error );
				return false;
			}

			if ( ! empty( $tournament_ids ) && ! empty( $winner_positions ) ) {
				foreach ( $tournament_ids as $tournament_id ) {
					$mode = isset( $tournament_modes[ $tournament_id ] ) ? $tournament_modes[ $tournament_id ] : 'winners';

					foreach ( $winner_positions as $user_id => $positions ) {
						$remove_count = 0;

						if ( 'all' === $mode ) {
							$remove_count = count( $positions );
						} else {
							foreach ( $positions as $position ) {
								if ( $position > 0 && $position <= $winners_count ) {
									++$remove_count;
								}
							}
						}

						if ( $remove_count <= 0 ) {
							continue;
						}

						$existing_result = $wpdb->get_row(
							$wpdb->prepare(
								'SELECT id, wins FROM ' . $tres_tbl . ' WHERE tournament_id = %d AND user_id = %d',
								(int) $tournament_id,
								$user_id
							)
						);

						if ( ! $existing_result ) {
							continue;
						}

						$remaining_wins = max( 0, (int) $existing_result->wins - (int) $remove_count );

						if ( $remaining_wins > 0 ) {
							$updated = $wpdb->update(
								$tres_tbl,
								array( 'wins' => $remaining_wins ),
								array( 'id' => (int) $existing_result->id ),
								array( '%d' ),
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
		$position = 1;
                $should_recalculate = false;
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

				if ( ! empty( $tournament_ids ) ) {
					$should_recalculate = true;
			}
++$position;
		}

		if ( $should_recalculate && ! empty( $tournament_ids ) ) {
			self::recalculate_tournament_results( $tournament_ids );
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

                $hunts_tbl    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                $winners_tbl  = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
                $results_tbl  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
                $relation_tbl = esc_sql( $wpdb->prefix . 'bhg_tournaments_hunts' );
                $tours_tbl    = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

                foreach ( $normalized as $tournament_id ) {
                        $tournament = $wpdb->get_row(
                                $wpdb->prepare(
                                        "SELECT participants_mode, points_map, ranking_scope FROM {$tours_tbl} WHERE id = %d",
                                        $tournament_id
                                )
                        );

                        if ( ! $tournament ) {
                                continue;
                        }

                        $participants_mode = isset( $tournament->participants_mode ) ? sanitize_key( $tournament->participants_mode ) : 'winners';
                        if ( ! in_array( $participants_mode, array( 'winners', 'all' ), true ) ) {
                                $participants_mode = 'winners';
                        }

                        $ranking_scope = isset( $tournament->ranking_scope ) ? sanitize_key( $tournament->ranking_scope ) : 'all';
                        if ( ! in_array( $ranking_scope, array( 'all', 'closed', 'active' ), true ) ) {
                                $ranking_scope = 'all';
                        }

                        $points_map = array();
                        if ( ! empty( $tournament->points_map ) ) {
                                $decoded = json_decode( $tournament->points_map, true );
                                if ( is_array( $decoded ) && function_exists( 'bhg_sanitize_points_map' ) ) {
                                        $points_map = bhg_sanitize_points_map( $decoded );
                                }
                        }

                        if ( empty( $points_map ) && function_exists( 'bhg_get_default_points_map' ) ) {
                                $points_map = bhg_get_default_points_map();
                        }

                        $scope_clause = '';
                        if ( 'active' === $ranking_scope ) {
                                $scope_clause = " AND h.status = 'open'";
                        } elseif ( 'closed' === $ranking_scope ) {
                                $scope_clause = " AND h.status = 'closed'";
                        }

                        $query = "
                                SELECT
                                        hw.user_id,
                                        hw.position,
                                        hw.hunt_id,
                                        COALESCE(hw.created_at, h.closed_at, h.updated_at, h.created_at) AS event_date,
                                        h.winners_count
                                FROM {$winners_tbl} hw
                                INNER JOIN {$hunts_tbl} h ON h.id = hw.hunt_id
                                LEFT JOIN {$relation_tbl} ht ON ht.hunt_id = h.id
                                WHERE (ht.tournament_id = %d OR (ht.tournament_id IS NULL AND h.tournament_id = %d))
                                {$scope_clause}
                        ";

                        $rows = $wpdb->get_results(
                                $wpdb->prepare(
                                        $query,
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

                        $results_map = array();

                        foreach ( $rows as $row ) {
                                $user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;

                                if ( $user_id <= 0 ) {
                                        continue;
                                }

                                $position = isset( $row->position ) ? (int) $row->position : 0;
                                $limit    = isset( $row->winners_count ) ? (int) $row->winners_count : 0;

                                if ( $limit <= 0 ) {
                                        $limit = max( 1, count( $points_map ) );
                                }

                                if ( 'winners' === $participants_mode && ( $position <= 0 || $position > $limit ) ) {
                                        continue;
                                }

                                if ( ! isset( $results_map[ $user_id ] ) ) {
                                        $results_map[ $user_id ] = array(
                                                'user_id'    => $user_id,
                                                'wins'       => 0,
                                                'points'     => 0,
                                                'last_event' => '',
                                        );
                                }

                                $points_awarded = 0;
                                if ( $position > 0 && isset( $points_map[ $position ] ) ) {
                                        $points_awarded = (int) $points_map[ $position ];
                                }

                                if ( $points_awarded > 0 ) {
                                        $results_map[ $user_id ]['points'] += $points_awarded;
                                }

                                if ( $position > 0 && $position <= $limit ) {
                                        $results_map[ $user_id ]['wins']++;
                                }

                                $event_date = '';
                                if ( isset( $row->event_date ) && $row->event_date ) {
                                        $event_date = (string) $row->event_date;
                                }

                                if ( '' === $event_date ) {
                                        $event_date = current_time( 'mysql' );
                                }

                                if ( '' === $results_map[ $user_id ]['last_event'] || strcmp( $event_date, $results_map[ $user_id ]['last_event'] ) > 0 ) {
                                        $results_map[ $user_id ]['last_event'] = $event_date;
                                }
                        }

                        if ( empty( $results_map ) ) {
                                continue;
                        }

                        $results = array_values( $results_map );

                        usort(
                                $results,
                                static function ( $a, $b ) {
                                        $points_a = isset( $a['points'] ) ? (int) $a['points'] : 0;
                                        $points_b = isset( $b['points'] ) ? (int) $b['points'] : 0;

                                        if ( $points_a !== $points_b ) {
                                                return ( $points_a < $points_b ) ? 1 : -1;
                                        }

                                        $wins_a = isset( $a['wins'] ) ? (int) $a['wins'] : 0;
                                        $wins_b = isset( $b['wins'] ) ? (int) $b['wins'] : 0;

                                        if ( $wins_a !== $wins_b ) {
                                                return ( $wins_a < $wins_b ) ? 1 : -1;
                                        }

                                        $date_a = isset( $a['last_event'] ) ? (string) $a['last_event'] : '';
                                        $date_b = isset( $b['last_event'] ) ? (string) $b['last_event'] : '';

                                        if ( $date_a !== $date_b ) {
                                                return strcmp( $date_a, $date_b );
                                        }

                                        $user_a = isset( $a['user_id'] ) ? (int) $a['user_id'] : 0;
                                        $user_b = isset( $b['user_id'] ) ? (int) $b['user_id'] : 0;

                                        if ( $user_a === $user_b ) {
                                                return 0;
                                        }

                                        return ( $user_a < $user_b ) ? -1 : 1;
                                }
                        );

                        foreach ( $results as $result_row ) {
                                $user_id = isset( $result_row['user_id'] ) ? (int) $result_row['user_id'] : 0;

                                if ( $user_id <= 0 ) {
                                        continue;
                                }

                                $last_event = isset( $result_row['last_event'] ) ? (string) $result_row['last_event'] : current_time( 'mysql' );

                                $inserted = $wpdb->insert(
                                        $results_tbl,
                                        array(
                                                'tournament_id' => $tournament_id,
                                                'user_id'       => $user_id,
                                                'wins'          => isset( $result_row['wins'] ) ? (int) $result_row['wins'] : 0,
                                                'points'        => isset( $result_row['points'] ) ? max( 0, (int) $result_row['points'] ) : 0,
                                                'last_win_date' => $last_event,
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
