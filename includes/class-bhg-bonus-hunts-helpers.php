<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
	* Helper functions for hunts and guesses used by admin dashboard, list and results.
	* DB tables assumed:
	*  - {$wpdb->prefix}bhg_bonus_hunts (id, title, starting_balance, final_balance, winners_count, status, closed_at)
	*  - {$wpdb->prefix}bhg_guesses (id, hunt_id, user_id, guess, created_at)
	*/

if ( ! function_exists( 'bhg_normalize_int_list' ) ) {
function bhg_normalize_int_list( $ids ) {
$ids        = is_array( $ids ) ? $ids : array( $ids );
$normalized = array();

foreach ( $ids as $value ) {
$value = max( 0, absint( $value ) );
if ( $value > 0 ) {
$normalized[ $value ] = $value;
}
}

return array_values( $normalized );
}
}

if ( ! function_exists( 'bhg_get_hunt_tournament_ids' ) ) {
function bhg_get_hunt_tournament_ids( $hunt_id ) {
global $wpdb;

$hunt_id     = absint( $hunt_id );
$relation_tbl = $wpdb->prefix . 'bhg_hunt_tournaments';
$hunts_tbl    = $wpdb->prefix . 'bhg_bonus_hunts';

if ( $hunt_id <= 0 ) {
return array();
}

$ids = $wpdb->get_col(
$wpdb->prepare(
"SELECT tournament_id FROM `{$relation_tbl}` WHERE hunt_id = %d ORDER BY created_at ASC, id ASC",
$hunt_id
)
);

$ids = bhg_normalize_int_list( $ids );

if ( ! empty( $ids ) ) {
return $ids;
}

$legacy = (int) $wpdb->get_var(
$wpdb->prepare(
"SELECT tournament_id FROM `{$hunts_tbl}` WHERE id = %d",
$hunt_id
)
);

return $legacy > 0 ? array( $legacy ) : array();
}
}

if ( ! function_exists( 'bhg_get_tournament_hunt_ids' ) ) {
function bhg_get_tournament_hunt_ids( $tournament_id ) {
global $wpdb;

$tournament_id = absint( $tournament_id );
$table         = $wpdb->prefix . 'bhg_hunt_tournaments';

if ( $tournament_id <= 0 ) {
return array();
}

$ids = $wpdb->get_col(
$wpdb->prepare(
"SELECT hunt_id FROM `{$table}` WHERE tournament_id = %d ORDER BY created_at ASC, id ASC",
$tournament_id
)
);

return bhg_normalize_int_list( $ids );
}
}

if ( ! function_exists( 'bhg_sync_legacy_hunt_tournament_column' ) ) {
function bhg_sync_legacy_hunt_tournament_column( $hunt_id, $known_ids = null ) {
global $wpdb;

$hunt_id = absint( $hunt_id );
if ( $hunt_id <= 0 ) {
return;
}

$hunts_tbl = $wpdb->prefix . 'bhg_bonus_hunts';

if ( null === $known_ids ) {
$known_ids = bhg_get_hunt_tournament_ids( $hunt_id );
}

$known_ids = bhg_normalize_int_list( $known_ids );
$primary   = $known_ids ? (int) reset( $known_ids ) : 0;

$wpdb->update(
$hunts_tbl,
array( 'tournament_id' => $primary ),
array( 'id' => $hunt_id ),
array( '%d' ),
array( '%d' )
);
}
}

if ( ! function_exists( 'bhg_set_hunt_tournaments' ) ) {
function bhg_set_hunt_tournaments( $hunt_id, $tournament_ids ) {
global $wpdb;

$hunt_id = absint( $hunt_id );
if ( $hunt_id <= 0 ) {
return;
}

$table     = $wpdb->prefix . 'bhg_hunt_tournaments';
$new_ids   = bhg_normalize_int_list( $tournament_ids );
$current   = bhg_get_hunt_tournament_ids( $hunt_id );
$to_add    = array_diff( $new_ids, $current );
$to_remove = array_diff( $current, $new_ids );

if ( ! empty( $to_remove ) ) {
$placeholders = implode( ',', array_fill( 0, count( $to_remove ), '%d' ) );
$params       = array_merge( array( $hunt_id ), array_values( $to_remove ) );
$wpdb->query(
$wpdb->prepare(
"DELETE FROM `{$table}` WHERE hunt_id = %d AND tournament_id IN ({$placeholders})",
...$params
)
);
}

if ( ! empty( $to_add ) ) {
$now = current_time( 'mysql' );
foreach ( $to_add as $tid ) {
$wpdb->insert(
$table,
array(
'hunt_id'       => $hunt_id,
'tournament_id' => $tid,
'created_at'    => $now,
),
array( '%d', '%d', '%s' )
);
}
}

bhg_sync_legacy_hunt_tournament_column( $hunt_id, $new_ids );
}
}

if ( ! function_exists( 'bhg_set_tournament_hunts' ) ) {
function bhg_set_tournament_hunts( $tournament_id, $hunt_ids ) {
global $wpdb;

$tournament_id = absint( $tournament_id );
if ( $tournament_id <= 0 ) {
return;
}

$table       = $wpdb->prefix . 'bhg_hunt_tournaments';
$new_hunts   = bhg_normalize_int_list( $hunt_ids );
$current     = bhg_get_tournament_hunt_ids( $tournament_id );
$to_add      = array_diff( $new_hunts, $current );
$to_remove   = array_diff( $current, $new_hunts );
$affected    = array_unique( array_merge( $to_add, $to_remove ) );

if ( ! empty( $to_remove ) ) {
$placeholders = implode( ',', array_fill( 0, count( $to_remove ), '%d' ) );
$params       = array_merge( array( $tournament_id ), array_values( $to_remove ) );
$wpdb->query(
$wpdb->prepare(
"DELETE FROM `{$table}` WHERE tournament_id = %d AND hunt_id IN ({$placeholders})",
...$params
)
);
}

if ( ! empty( $to_add ) ) {
$now = current_time( 'mysql' );
foreach ( $to_add as $hunt_id ) {
$wpdb->insert(
$table,
array(
'hunt_id'       => $hunt_id,
'tournament_id' => $tournament_id,
'created_at'    => $now,
),
array( '%d', '%d', '%s' )
);
}
}

foreach ( $affected as $hunt_id ) {
bhg_sync_legacy_hunt_tournament_column( $hunt_id );
}
}
}

if ( ! function_exists( 'bhg_get_hunt' ) ) {
        function bhg_get_hunt( $hunt_id ) {
                                global $wpdb;
                                $t    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                                $hunt = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id=%d", (int) $hunt_id ) );

                if ( $hunt ) {
                        $hunt->tournament_ids = bhg_get_hunt_tournament_ids( (int) $hunt->id );
                }

                                return $hunt;
        }
}

if ( ! function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	function bhg_get_latest_closed_hunts( $limit = 3 ) {
		$limit = max( 1, absint( $limit ) );

		if ( function_exists( 'bhg_cache_get' ) ) {
			$cached = bhg_cache_get( 'latest_closed_hunts', array( 'limit' => $limit ) );
			if ( null !== $cached ) {
				return $cached;
			}
		}

		global $wpdb;
		$table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, starting_balance, final_balance, winners_count, closed_at FROM {$table} WHERE status = %s ORDER BY closed_at DESC LIMIT %d",
				'closed',
				$limit
			)
		);

		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		if ( function_exists( 'bhg_cache_set' ) ) {
			bhg_cache_set( 'latest_closed_hunts', array( 'limit' => $limit ), $rows, 5 * MINUTE_IN_SECONDS );
		}

		return $rows;
	}
}
if ( ! function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
	function bhg_get_top_winners_for_hunt( $hunt_id, $winners_limit = 3 ) {
		global $wpdb;
		$hunt_id = (int) $hunt_id;
		if ( $hunt_id <= 0 ) {
			return array();
		}

		$limit = (int) $winners_limit > 0 ? (int) $winners_limit : 0;
		$t_g   = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$t_h   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

		$cache_group = 'hunt_winners_' . $hunt_id;
		if ( function_exists( 'bhg_cache_get' ) ) {
			$cached = bhg_cache_get( $cache_group, array( 'limit' => max( 1, $limit ) ) );
			if ( null !== $cached ) {
				return $cached;
			}
		}

		$hunt = $wpdb->get_row( $wpdb->prepare( "SELECT final_balance, winners_count FROM {$t_h} WHERE id=%d", $hunt_id ) );
		if ( ! $hunt || null === $hunt->final_balance ) {
			return array();
		}

		if ( $limit <= 0 ) {
			if ( ! empty( $hunt->winners_count ) ) {
				$limit = (int) $hunt->winners_count;
			} else {
				$limit = 3;
			}
		}

		$sql = $wpdb->prepare(
			sprintf(
				'SELECT g.user_id, g.guess, (%%f - g.guess) AS diff FROM `%s` g WHERE g.hunt_id = %%d ORDER BY ABS(%%f - g.guess) ASC LIMIT %%d',
				$t_g
			),
			(float) $hunt->final_balance,
			$hunt_id,
			(float) $hunt->final_balance,
			$limit
		);
		$rows = $wpdb->get_results( $sql );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		if ( function_exists( 'bhg_cache_set' ) ) {
			bhg_cache_set( $cache_group, array( 'limit' => $limit ), $rows, 5 * MINUTE_IN_SECONDS );
		}

		return $rows;
	}
}

if ( ! function_exists( 'bhg_get_all_ranked_guesses' ) ) {
	function bhg_get_all_ranked_guesses( $hunt_id ) {
		global $wpdb;
		$hunt_id = (int) $hunt_id;
		if ( $hunt_id <= 0 ) {
			return array();
		}

		$cache_group = 'hunt_ranked_' . $hunt_id;
		if ( function_exists( 'bhg_cache_get' ) ) {
			$cached = bhg_cache_get( $cache_group, array() );
			if ( null !== $cached ) {
				return $cached;
			}
		}

		$t_g = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$t_h = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                                $hunt = $wpdb->get_row( $wpdb->prepare( 'SELECT final_balance FROM `' . $t_h . '` WHERE id=%d', (int) $hunt_id ) );
		if ( ! $hunt || null === $hunt->final_balance ) {
			return array();
		}

                $sql = $wpdb->prepare(
                        sprintf(
                                'SELECT g.id, g.user_id, g.guess, (%%f - g.guess) AS diff FROM `%s` g WHERE g.hunt_id = %%d ORDER BY ABS(%%f - g.guess) ASC',
                                $t_g
                        ),
                        (float) $hunt->final_balance,
                        (int) $hunt_id,
                        (float) $hunt->final_balance
                );
                $rows = $wpdb->get_results( $sql );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		if ( function_exists( 'bhg_cache_set' ) ) {
			bhg_cache_set( $cache_group, array(), $rows, 5 * MINUTE_IN_SECONDS );
		}

		return $rows;
	}
}

if ( ! function_exists( 'bhg_get_hunt_participants' ) ) {
	function bhg_get_hunt_participants( $hunt_id, $paged = 1, $per_page = 30 ) {
		global $wpdb;
		$hunt_id = (int) $hunt_id;
		if ( $hunt_id <= 0 ) {
			return array( 'rows' => array(), 'total' => 0 );
		}

		$paged    = max( 1, (int) $paged );
		$per_page = max( 1, (int) $per_page );
		$offset   = max( 0, ( $paged - 1 ) * $per_page );
		$t_g      = esc_sql( $wpdb->prefix . 'bhg_guesses' );

                $cache_group = 'hunt_participants_' . $hunt_id;
                $cache_args  = array(
                        'page'      => $paged,
                        'per_page'  => $per_page,
                        'with_rows' => true,
                );
                if ( function_exists( 'bhg_cache_get' ) ) {
                        $cached = bhg_cache_get( $cache_group, $cache_args );
                        if ( null !== $cached ) {
                                return $cached;
                        }
                }

                $rows = $wpdb->get_results(
                        $wpdb->prepare(
                                sprintf(
                                        'SELECT id, user_id, guess, created_at FROM `%s` WHERE hunt_id = %%d ORDER BY created_at DESC LIMIT %%d OFFSET %%d',
                                        $t_g
                                ),
                                $hunt_id,
                                $per_page,
                                $offset
                        )
                );
                if ( ! is_array( $rows ) ) {
                        $rows = array();
                }

                $total = (int) $wpdb->get_var(
                        $wpdb->prepare(
                                sprintf( 'SELECT COUNT(*) FROM `%s` WHERE hunt_id = %%d', $t_g ),
                                $hunt_id
                        )
                );

                $result = array(
                        'rows'  => $rows,
                        'total' => $total,
                );

                if ( function_exists( 'bhg_cache_set' ) ) {
                        bhg_cache_set( $cache_group, $cache_args, $result, 5 * MINUTE_IN_SECONDS );
                }

                return $result;
	}
}

if ( ! function_exists( 'bhg_remove_guess' ) ) {
	/**
	 * Remove a guess by ID.
	 *
	 * @param int $guess_id Guess ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	function bhg_remove_guess( $guess_id ) {
		global $wpdb;
		$t_g      = $wpdb->prefix . 'bhg_guesses';
		$guess_id = (int) $guess_id;
		if ( $guess_id <= 0 ) {
			return false;
		}

		$hunt_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT hunt_id FROM {$t_g} WHERE id = %d",
				$guess_id
			)
		);

		$result = $wpdb->delete( $t_g, array( 'id' => $guess_id ), array( '%d' ) );

		if ( $result && $hunt_id > 0 && function_exists( 'bhg_clear_hunt_guess_cache' ) ) {
			bhg_clear_hunt_guess_cache( $hunt_id );
		}

		return $result;
	}
}
