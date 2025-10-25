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
				foreach ( $tournament_ids as $tournament_id ) {
					$mode = isset( $tournament_modes[ $tournament_id ] ) ? $tournament_modes[ $tournament_id ] : 'winners';

					if ( 'all' !== $mode && $position > $winners_count ) {
						continue;
					}

					$existing = $wpdb->get_row(
						$wpdb->prepare(
							'SELECT id, wins FROM ' . $tres_tbl . ' WHERE tournament_id = %d AND user_id = %d',
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
								'tournament_id' => (int) $tournament_id,
								'user_id'       => (int) $row->user_id,
								'wins'          => 1,
								'last_win_date' => $now,
							),
							array( '%d', '%d', '%d', '%s' )
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
$relation_tbl = esc_sql( $wpdb->prefix . 'bhg_hunt_tournaments' );
$tours_tbl   = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

		foreach ( $normalized as $tournament_id ) {
			$query = "
				SELECT
					hw.user_id,
					hw.position,
					hw.hunt_id,
					COALESCE(hw.created_at, h.closed_at, h.updated_at, h.created_at) AS event_date,
					h.winners_count,
					t.participants_mode
				FROM {$winners_tbl} hw
				INNER JOIN {$hunts_tbl} h ON h.id = hw.hunt_id
				LEFT JOIN {$relation_tbl} ht ON ht.hunt_id = h.id
				INNER JOIN {$tours_tbl} t ON t.id = COALESCE(ht.tournament_id, h.tournament_id)
				WHERE t.id = %d
				  AND (ht.tournament_id = %d OR (ht.tournament_id IS NULL AND h.tournament_id = %d))
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

			$wins_map   = array();
			$last_dates = array();

			foreach ( $rows as $row ) {
				$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;

				if ( $user_id <= 0 ) {
					continue;
				}

				$mode = isset( $row->participants_mode ) ? sanitize_key( $row->participants_mode ) : 'winners';
				if ( ! in_array( $mode, array( 'winners', 'all' ), true ) ) {
					$mode = 'winners';
				}

				if ( 'all' !== $mode ) {
					$position = isset( $row->position ) ? (int) $row->position : 0;
					$limit    = isset( $row->winners_count ) ? (int) $row->winners_count : 0;
					if ( $limit <= 0 ) {
						$limit = 1;
					}

					if ( $position <= 0 || $position > $limit ) {
						continue;
					}
				}

				if ( ! isset( $wins_map[ $user_id ] ) ) {
					$wins_map[ $user_id ] = 0;
				}

				++$wins_map[ $user_id ];

				$event_date = '';
				if ( isset( $row->event_date ) && $row->event_date ) {
					$event_date = $row->event_date;
				}

				if ( empty( $event_date ) ) {
					$event_date = current_time( 'mysql' );
				}

				if ( ! isset( $last_dates[ $user_id ] ) || strcmp( $event_date, $last_dates[ $user_id ] ) > 0 ) {
					$last_dates[ $user_id ] = $event_date;
				}
			}

			if ( empty( $wins_map ) ) {
				continue;
			}

			foreach ( $wins_map as $user_id => $wins ) {
				if ( $wins <= 0 ) {
					continue;
				}

				$last_win = isset( $last_dates[ $user_id ] ) ? $last_dates[ $user_id ] : current_time( 'mysql' );

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

	/**
	 * Retrieve paginated list of hunts a user has participated in.
	 *
	 * @param int   $user_id User identifier.
	 * @param array $args    Optional arguments: per_page, paged.
	 *
	 * @return array
	 */
	public static function get_user_bonus_hunts( $user_id, array $args = array() ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$defaults = array(
			'per_page' => 10,
			'paged'    => 1,
		);
		$args     = wp_parse_args( $args, $defaults );
		$per_page = max( 1, (int) $args['per_page'] );
		$paged    = max( 1, (int) $args['paged'] );
		$offset   = ( $paged - 1 ) * $per_page;

		if ( $user_id <= 0 ) {
			return array(
				'items'    => array(),
				'total'    => 0,
				'per_page' => $per_page,
				'paged'    => $paged,
			);
		}

		$hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

		$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$guesses_table} WHERE user_id = %d",
				$user_id
			)
		);

		$items = array();
		if ( $total > 0 ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$sql  = "SELECT g.id AS guess_id, g.hunt_id, g.guess, g.created_at, g.updated_at, h.title, h.status, h.final_balance, h.closed_at, h.starting_balance, h.winners_count, w.position AS winner_position, w.diff AS winner_diff, CASE WHEN h.final_balance IS NULL THEN NULL ELSE ABS(g.guess - h.final_balance) END AS difference, CASE WHEN h.final_balance IS NULL THEN NULL ELSE ( SELECT COUNT(*) + 1 FROM {$guesses_table} g2 WHERE g2.hunt_id = g.hunt_id AND ( ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance) OR ( ABS(g2.guess - h.final_balance) = ABS(g.guess - h.final_balance) AND g2.id < g.id ) ) ) END AS relative_rank FROM {$guesses_table} g INNER JOIN {$hunts_table} h ON h.id = g.hunt_id LEFT JOIN {$winners_table} w ON w.hunt_id = g.hunt_id AND w.user_id = g.user_id WHERE g.user_id = %d ORDER BY h.created_at DESC, g.id DESC LIMIT %d OFFSET %d";
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id, $per_page, $offset ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( $rows ) {
				foreach ( $rows as $row ) {
					$items[] = array(
						'guess_id'        => isset( $row->guess_id ) ? (int) $row->guess_id : 0,
						'hunt_id'         => isset( $row->hunt_id ) ? (int) $row->hunt_id : 0,
						'title'           => isset( $row->title ) ? (string) $row->title : '',
						'guess'           => isset( $row->guess ) ? (float) $row->guess : 0.0,
						'difference'      => isset( $row->difference ) ? (float) $row->difference : null,
						'rank'            => isset( $row->relative_rank ) ? (int) $row->relative_rank : null,
						'winner_position' => isset( $row->winner_position ) ? (int) $row->winner_position : null,
						'winner_diff'     => isset( $row->winner_diff ) ? (float) $row->winner_diff : null,
						'final_balance'   => isset( $row->final_balance ) ? (float) $row->final_balance : null,
						'closed_at'       => isset( $row->closed_at ) ? (string) $row->closed_at : '',
						'created_at'      => isset( $row->created_at ) ? (string) $row->created_at : '',
						'updated_at'      => isset( $row->updated_at ) ? (string) $row->updated_at : '',
						'status'          => isset( $row->status ) ? (string) $row->status : '',
						'winners_count'   => isset( $row->winners_count ) ? (int) $row->winners_count : 0,
						'starting_balance' => isset( $row->starting_balance ) ? (float) $row->starting_balance : 0.0,
					);
				}
			}
		}

		return array(
			'items'    => $items,
			'total'    => $total,
			'per_page' => $per_page,
			'paged'    => $paged,
		);
	}

	/**
	 * Retrieve tournament results for a user.
	 *
	 * @param int   $user_id User identifier.
	 * @param array $args    Optional arguments: per_page, paged.
	 *
	 * @return array
	 */
	public static function get_user_tournament_results( $user_id, array $args = array() ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$defaults = array(
			'per_page' => 10,
			'paged'    => 1,
		);
		$args     = wp_parse_args( $args, $defaults );
		$per_page = max( 1, (int) $args['per_page'] );
		$paged    = max( 1, (int) $args['paged'] );
		$offset   = ( $paged - 1 ) * $per_page;

		if ( $user_id <= 0 ) {
			return array(
				'items'    => array(),
				'total'    => 0,
				'per_page' => $per_page,
				'paged'    => $paged,
			);
		}

		$results_table     = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
		$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

		$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$results_table} WHERE user_id = %d",
				$user_id
			)
		);

		$items = array();
		if ( $total > 0 ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$sql  = "SELECT r.tournament_id, r.wins, r.last_win_date, t.title, t.type, t.status, t.start_date, t.end_date FROM {$results_table} r INNER JOIN {$tournaments_table} t ON t.id = r.tournament_id WHERE r.user_id = %d ORDER BY r.wins DESC, t.title ASC LIMIT %d OFFSET %d";
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id, $per_page, $offset ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( $rows ) {
				foreach ( $rows as $row ) {
					$items[] = array(
						'tournament_id' => isset( $row->tournament_id ) ? (int) $row->tournament_id : 0,
						'title'         => isset( $row->title ) ? (string) $row->title : '',
						'type'          => isset( $row->type ) ? (string) $row->type : '',
						'wins'          => isset( $row->wins ) ? (int) $row->wins : 0,
						'last_win_date' => isset( $row->last_win_date ) ? (string) $row->last_win_date : '',
						'status'        => isset( $row->status ) ? (string) $row->status : '',
						'start_date'    => isset( $row->start_date ) ? (string) $row->start_date : '',
						'end_date'      => isset( $row->end_date ) ? (string) $row->end_date : '',
					);
				}
			}
		}

		return array(
			'items'    => $items,
			'total'    => $total,
			'per_page' => $per_page,
			'paged'    => $paged,
		);
	}

	/**
	 * Retrieve ranking entries (winnings) for a user.
	 *
	 * @param int   $user_id User identifier.
	 * @param array $args    Optional arguments: per_page, paged.
	 *
	 * @return array
	 */
	public static function get_user_rankings( $user_id, array $args = array() ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$defaults = array(
			'per_page' => 10,
			'paged'    => 1,
		);
		$args     = wp_parse_args( $args, $defaults );
		$per_page = max( 1, (int) $args['per_page'] );
		$paged    = max( 1, (int) $args['paged'] );
		$offset   = ( $paged - 1 ) * $per_page;

		if ( $user_id <= 0 ) {
			return array(
				'items'    => array(),
				'total'    => 0,
				'per_page' => $per_page,
				'paged'    => $paged,
			);
		}

		$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
		$hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

		$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$winners_table} WHERE user_id = %d",
				$user_id
			)
		);

		$items = array();
		if ( $total > 0 ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$sql  = "SELECT w.hunt_id, w.position, w.guess, w.diff, w.created_at, h.title, h.final_balance, h.closed_at, h.winners_count FROM {$winners_table} w INNER JOIN {$hunts_table} h ON h.id = w.hunt_id WHERE w.user_id = %d ORDER BY COALESCE(w.created_at, h.closed_at, h.updated_at, h.created_at) DESC LIMIT %d OFFSET %d";
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id, $per_page, $offset ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( $rows ) {
				foreach ( $rows as $row ) {
					$items[] = array(
						'hunt_id'       => isset( $row->hunt_id ) ? (int) $row->hunt_id : 0,
						'title'         => isset( $row->title ) ? (string) $row->title : '',
						'position'      => isset( $row->position ) ? (int) $row->position : 0,
						'guess'         => isset( $row->guess ) ? (float) $row->guess : 0.0,
						'difference'    => isset( $row->diff ) ? (float) $row->diff : 0.0,
						'final_balance' => isset( $row->final_balance ) ? (float) $row->final_balance : null,
						'closed_at'     => isset( $row->closed_at ) ? (string) $row->closed_at : '',
						'created_at'    => isset( $row->created_at ) ? (string) $row->created_at : '',
						'winners_count' => isset( $row->winners_count ) ? (int) $row->winners_count : 0,
					);
				}
			}
		}

		return array(
			'items'    => $items,
			'total'    => $total,
			'per_page' => $per_page,
			'paged'    => $paged,
		);
	}

	/**
	 * Retrieve ranking entries and attach prizes for the user.
	 *
	 * @param int   $user_id User identifier.
	 * @param array $args    Optional arguments.
	 *
	 * @return array
	 */
	public static function get_user_prizes( $user_id, array $args = array() ) {
		$data  = self::get_user_rankings( $user_id, $args );
		$items = array();
		if ( ! empty( $data['items'] ) ) {
			foreach ( $data['items'] as $entry ) {
				$entry['prize'] = self::get_prize_for_position( $entry['hunt_id'], $entry['position'] );
				$items[]        = $entry;
			}
		}

		$data['items'] = $items;

		return $data;
	}

	/**
	 * Resolve the configured prize for a hunt position.
	 *
	 * @param int $hunt_id  Hunt identifier.
	 * @param int $position Winner position (1-indexed).
	 *
	 * @return array|null
	 */
	private static function get_prize_for_position( $hunt_id, $position ) {
		static $cache = array();

		$hunt_id  = absint( $hunt_id );
		$position = absint( $position );

		if ( $hunt_id <= 0 || $position <= 0 || ! class_exists( 'BHG_Prizes' ) ) {
			return null;
		}

		if ( ! isset( $cache[ $hunt_id ] ) ) {
			$cache[ $hunt_id ] = BHG_Prizes::get_prizes_for_hunt( $hunt_id, array( 'active_only' => false ) );
		}

		$prizes = $cache[ $hunt_id ];
		if ( empty( $prizes ) ) {
			return null;
		}

		$index = $position - 1;
		if ( ! isset( $prizes[ $index ] ) ) {
			return null;
		}

		$prize = $prizes[ $index ];

		return array(
			'id'          => isset( $prize->id ) ? (int) $prize->id : 0,
			'title'       => isset( $prize->title ) ? (string) $prize->title : '',
			'description' => isset( $prize->description ) ? (string) $prize->description : '',
		);
	}

}
