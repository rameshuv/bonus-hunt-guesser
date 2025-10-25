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

		$out = array();

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
			);
		}

		return $out;
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

								return $wpdb->get_row(
										$wpdb->prepare(
												"SELECT * FROM {$hunts_table} WHERE id=%d",
												(int) $hunt_id
										)
								);
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

		/**
		 * Send hunt result notifications using configured templates.
		 *
		 * @param int   $hunt_id       Hunt identifier.
		 * @param int[] $winner_ids    Optional list of winner user IDs.
		 * @param float $final_balance Optional final balance override.
		 *
		 * @return bool True if emails were sent, false otherwise.
		 */
		public static function send_results_notifications( $hunt_id, array $winner_ids = array(), $final_balance = null ) {
				if ( ! class_exists( 'BHG_Notifications' ) ) {
						return false;
				}

				global $wpdb;

				$hunt_id = absint( $hunt_id );
				if ( $hunt_id <= 0 ) {
						return false;
				}

				$hunt = self::get_hunt( $hunt_id );
				if ( ! $hunt ) {
						return false;
				}

				if ( null === $final_balance && isset( $hunt->final_balance ) ) {
						$final_balance = (float) $hunt->final_balance;
				}

				$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
				$user_ids      = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT user_id FROM {$guesses_table} WHERE hunt_id = %d", $hunt_id ) );

				if ( empty( $user_ids ) ) {
						return false;
				}

				$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

				if ( empty( $winner_ids ) ) {
						$winner_rows = $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$winners_table} WHERE hunt_id = %d ORDER BY position ASC", $hunt_id ) );
						if ( ! empty( $winner_rows ) ) {
								$winner_ids = array_map( 'intval', wp_list_pluck( $winner_rows, 'user_id' ) );
						}
				}

				$winner_names = array();
				foreach ( array_unique( array_map( 'intval', $winner_ids ) ) as $winner_id ) {
						$winner = get_userdata( $winner_id );
						if ( ! $winner ) {
								continue;
						}

						$display = $winner->display_name ? $winner->display_name : $winner->user_login;
						if ( $display ) {
								$winner_names[] = $display;
						}
				}

				$winner_first = ! empty( $winner_names ) ? $winner_names[0] : '';

				$recipients = array();
				foreach ( $user_ids as $user_id ) {
						$user_id = (int) $user_id;
						if ( $user_id <= 0 ) {
								continue;
						}

						$user = get_userdata( $user_id );
						if ( ! $user || empty( $user->user_email ) ) {
								continue;
						}

						$display = $user->display_name ? $user->display_name : $user->user_login;
						$recipients[] = array(
								'email'        => $user->user_email,
								'replacements' => array(
										'{{username}}'     => sanitize_text_field( $user->user_login ),
										'{{display_name}}' => sanitize_text_field( $display ),
								),
						);
				}

				if ( empty( $recipients ) ) {
						return false;
				}

				$hunt_title = isset( $hunt->title ) ? sanitize_text_field( $hunt->title ) : '';
				$balance    = is_numeric( $final_balance ) ? (float) $final_balance : 0.0;
				$base       = array(
						'{{hunt_title}}'    => $hunt_title,
						'{{final_balance}}' => bhg_format_currency( $balance ),
						'{{winner_names}}'  => implode( ', ', $winner_names ),
						'{{winner_first}}'  => $winner_first,
				);

				return BHG_Notifications::send( 'hunt_results', $base, $recipients );
		}

		/**
		 * Send notifications when a new hunt is created.
		 *
		 * @param int $hunt_id Hunt identifier.
		 *
		 * @return bool True if emails were sent, false otherwise.
		 */
		public static function send_creation_notification( $hunt_id ) {
				if ( ! class_exists( 'BHG_Notifications' ) ) {
						return false;
				}

				$hunt_id = absint( $hunt_id );
				if ( $hunt_id <= 0 ) {
						return false;
				}

				$hunt = self::get_hunt( $hunt_id );
				if ( ! $hunt ) {
						return false;
				}

				$admins = get_users(
						array(
								'role__in' => array( 'administrator' ),
								'fields'   => array( 'ID', 'user_email', 'user_login', 'display_name' ),
						)
				);

				if ( empty( $admins ) ) {
						return false;
				}

				$recipients = array();
				foreach ( $admins as $admin ) {
						if ( empty( $admin->user_email ) ) {
								continue;
						}

						$display = $admin->display_name ? $admin->display_name : $admin->user_login;
						$recipients[] = array(
								'email'        => $admin->user_email,
								'replacements' => array(
										'{{username}}'     => sanitize_text_field( $admin->user_login ),
										'{{display_name}}' => sanitize_text_field( $display ),
								),
						);
				}

				if ( empty( $recipients ) ) {
						return false;
				}

				$hunt_title = isset( $hunt->title ) ? sanitize_text_field( $hunt->title ) : '';
				$base       = array(
						'{{hunt_title}}'       => $hunt_title,
						'{{starting_balance}}' => bhg_format_currency( isset( $hunt->starting_balance ) ? (float) $hunt->starting_balance : 0.0 ),
						'{{num_bonuses}}'      => isset( $hunt->num_bonuses ) ? (int) $hunt->num_bonuses : 0,
				);

				return BHG_Notifications::send( 'hunt_created', $base, $recipients );
		}
}
