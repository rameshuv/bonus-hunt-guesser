<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
	* Helper functions for hunts and guesses used by admin dashboard, list and results.
	* DB tables assumed:
	*  - {$wpdb->prefix}bhg_bonus_hunts (id, title, starting_balance, final_balance, winners_count, status, closed_at)
	*  - {$wpdb->prefix}bhg_guesses (id, hunt_id, user_id, guess, created_at)
	*/

if ( ! function_exists( 'bhg_get_hunt' ) ) {
	function bhg_get_hunt( $hunt_id ) {
		global $wpdb;
				$t = $wpdb->prefix . 'bhg_bonus_hunts';
				return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE id=%d', $t, (int) $hunt_id ) );
	}
}

if ( ! function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	function bhg_get_latest_closed_hunts( $limit = 3 ) {
		global $wpdb;
				$t   = $wpdb->prefix . 'bhg_bonus_hunts';
				$sql = $wpdb->prepare(
					'SELECT id, title, starting_balance, final_balance, winners_count, closed_at FROM %i WHERE status = %s ORDER BY closed_at DESC LIMIT %d',
					$t,
					'closed',
					(int) $limit
				);
				return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

if ( ! function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
	function bhg_get_top_winners_for_hunt( $hunt_id, $winners_limit = 3 ) {
		global $wpdb;
		$t_g = $wpdb->prefix . 'bhg_guesses';
		$t_h = $wpdb->prefix . 'bhg_bonus_hunts';

				$hunt = $wpdb->get_row( $wpdb->prepare( 'SELECT final_balance, winners_count FROM %i WHERE id=%d', $t_h, (int) $hunt_id ) );
		if ( ! $hunt || null === $hunt->final_balance ) {
				return array();
		}
		if ( $winners_limit ) {
				$limit = (int) $winners_limit;
		} elseif ( $hunt->winners_count ) {
				$limit = (int) $hunt->winners_count;
		} else {
				$limit = 3;
		}

				$sql = $wpdb->prepare(
					'SELECT g.user_id, g.guess, ABS(g.guess - %f) AS diff FROM %i g WHERE g.hunt_id = %d ORDER BY diff ASC LIMIT %d',
					(float) $hunt->final_balance,
					$t_g,
					(int) $hunt_id,
					(int) $limit
				);
				return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

if ( ! function_exists( 'bhg_get_all_ranked_guesses' ) ) {
	function bhg_get_all_ranked_guesses( $hunt_id ) {
		global $wpdb;
		$t_g          = $wpdb->prefix . 'bhg_guesses';
		$t_h          = $wpdb->prefix . 'bhg_bonus_hunts';
				$hunt = $wpdb->get_row( $wpdb->prepare( 'SELECT final_balance FROM %i WHERE id=%d', $t_h, (int) $hunt_id ) );
		if ( ! $hunt || null === $hunt->final_balance ) {
				return array();
		}

				$sql = $wpdb->prepare(
					'SELECT g.id, g.user_id, g.guess, ABS(g.guess - %f) AS diff FROM %i g WHERE g.hunt_id = %d ORDER BY diff ASC',
					(float) $hunt->final_balance,
					$t_g,
					(int) $hunt_id
				);
				return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

if ( ! function_exists( 'bhg_get_hunt_participants' ) ) {
	function bhg_get_hunt_participants( $hunt_id, $paged = 1, $per_page = 30 ) {
		global $wpdb;
				$t_g    = $wpdb->prefix . 'bhg_guesses';
				$offset = max( 0, ( (int) $paged - 1 ) * (int) $per_page );

				$rows  = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT id, user_id, guess, created_at FROM %i WHERE hunt_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d',
						$t_g,
						(int) $hunt_id,
						(int) $per_page,
						(int) $offset
					)
				);
				$total = (int) $wpdb->get_var(
					$wpdb->prepare(
						'SELECT COUNT(*) FROM %i WHERE hunt_id = %d',
						$t_g,
						(int) $hunt_id
					)
				);
				return array(
					'rows'  => $rows,
					'total' => $total,
				);
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
		$t_g = $wpdb->prefix . 'bhg_guesses';
		return $wpdb->delete( $t_g, array( 'id' => (int) $guess_id ), array( '%d' ) );
	}
}
