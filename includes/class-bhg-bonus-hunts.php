<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bonus hunt data helpers.
 */
class BHG_Bonus_Hunts {

	/**
	 * Retrieve latest hunts with winners.
	 *
	 * @param int $limit Number of hunts to fetch. Default 3.
	 * @return array List of hunts and winners.
	 */
	public static function get_latest_hunts_with_winners( $limit = 3 ) {
                global $wpdb;
                $hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                $guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
                $users_table   = esc_sql( $wpdb->users );
		$limit         = max( 1, (int) $limit );
		$hunts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, starting_balance, final_balance, winners_count, closed_at
							FROM {$hunts_table}
							WHERE status = %s AND final_balance IS NOT NULL AND closed_at IS NOT NULL
							ORDER BY closed_at DESC
							LIMIT %d",
				'closed',
				$limit
			)
		);

		$out       = array();
		$prize_map = array();

		if ( ! empty( $hunts ) ) {
			$hunt_ids  = wp_list_pluck( $hunts, 'id' );
			$prize_map = self::get_prize_titles_for_hunts( $hunt_ids );
		}

		foreach ( (array) $hunts as $h ) {
			$winners_count       = max( 1, (int) $h->winners_count );
			$winners = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT g.user_id, u.display_name, g.guess,
							(%f - g.guess) AS diff
					FROM {$guesses_table} g
					LEFT JOIN {$users_table} u ON u.ID = g.user_id
					WHERE g.hunt_id = %d
					ORDER BY ABS(%f - g.guess) ASC, g.id ASC
					LIMIT %d",
					$h->final_balance,
					$h->id,
					$h->final_balance,
					$winners_count
				)
			);

			$out[] = array(
				'hunt'    => $h,
				'winners' => $winners,
				'prizes'  => isset( $prize_map[ (int) $h->id ] ) ? $prize_map[ (int) $h->id ] : array(),
			);
		}

		return $out;
	}


	/**
	 * Retrieve prize titles grouped by hunt ID.
	 *
	 * @param int[] $hunt_ids Hunt IDs.
	 * @return array<int, string[]>
	 */
	public static function get_prize_titles_for_hunts( $hunt_ids ) {
		global $wpdb;

		$hunt_ids = array_map( 'absint', (array) $hunt_ids );
		$hunt_ids = array_filter( array_unique( $hunt_ids ) );

		if ( empty( $hunt_ids ) ) {
			return array();
		}

		$relation_table = esc_sql( $wpdb->prefix . 'bhg_hunt_prizes' );
		$prizes_table   = esc_sql( $wpdb->prefix . 'bhg_prizes' );
		$placeholders   = implode( ',', array_fill( 0, count( $hunt_ids ), '%d' ) );
		$query          = $wpdb->prepare(
			"SELECT hp.hunt_id, p.title FROM {$relation_table} hp INNER JOIN {$prizes_table} p ON p.id = hp.prize_id WHERE hp.hunt_id IN ({$placeholders}) ORDER BY hp.hunt_id ASC, hp.created_at ASC, hp.id ASC",
			...$hunt_ids
		);
		$rows           = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$map            = array();

		foreach ( (array) $rows as $row ) {
			$hunt_id = isset( $row->hunt_id ) ? (int) $row->hunt_id : 0;
			if ( $hunt_id <= 0 ) {
				continue;
			}

			if ( ! isset( $map[ $hunt_id ] ) ) {
				$map[ $hunt_id ] = array();
			}

			$title = isset( $row->title ) ? (string) $row->title : '';
			if ( '' !== $title ) {
				$map[ $hunt_id ][] = $title;
			}
		}

		return $map;
	}
	/**
	 * Retrieve a hunt by ID.
	 *
	 * @param int $hunt_id Hunt ID.
	 * @return object|null Hunt data or null if not found.
	 */
	public static function get_hunt( $hunt_id ) {
		global $wpdb;
		$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

		$hunt = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$hunts_table} WHERE id=%d",
				(int) $hunt_id
			)
		);

		if ( $hunt ) {
			$prize_map          = self::get_prize_titles_for_hunts( array( (int) $hunt_id ) );
			$hunt->prize_titles = isset( $prize_map[ (int) $hunt_id ] ) ? $prize_map[ (int) $hunt_id ] : array();
		}

		return $hunt;
	}

	/**
	 * Get ranked guesses for a hunt.
	 *
	 * @param int $hunt_id Hunt ID.
	 * @return array List of guesses.
	 */
	public static function get_hunt_guesses_ranked( $hunt_id ) {
                global $wpdb;
                $hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                $guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
                $users_table   = esc_sql( $wpdb->users );
		$hunt          = self::get_hunt( $hunt_id );

		if ( ! $hunt ) {
			return array(); }

		if ( null !== $hunt->final_balance ) {
                                                  return $wpdb->get_results(
                                                          $wpdb->prepare(
                                                                  "SELECT g.*, u.display_name, (%f - g.guess) AS diff
                                                                          FROM {$guesses_table} g
                                                                          LEFT JOIN {$users_table} u ON u.ID = g.user_id
                                                                          WHERE g.hunt_id = %d
                                                                          ORDER BY ABS(%f - g.guess) ASC, g.id ASC",
                                                                  $hunt->final_balance,
                                                                  $hunt_id,
                                                                  $hunt->final_balance
                                                          )
                                                  );
		}

                                                  return $wpdb->get_results(
                                                          $wpdb->prepare(
                                                                  "SELECT g.*, u.display_name, NULL AS diff
                                                                                  FROM {$guesses_table} g
                                                                                  LEFT JOIN {$users_table} u ON u.ID = g.user_id
                                                                                  WHERE g.hunt_id = %d
                                                                                  ORDER BY g.created_at ASC, g.id ASC",
                                                                  $hunt_id
                                                          )
                                                  );
	}
}
