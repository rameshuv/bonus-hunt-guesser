<?php
/**
 * Email notification management for Bonus Hunt Guesser.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Handles storage and delivery of plugin notifications.
 */
class BHG_Notifications {

		const OPTION_NAME = 'bhg_notifications_settings';

		/**
		 * Retrieve merged notification settings.
		 *
		 * @return array
		 */
	public static function get_settings() {
			$defaults = self::get_default_settings();
			$stored   = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $stored ) ) {
				$stored = array();
		}

		foreach ( $defaults as $key => $default ) {
			if ( isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ) {
					$defaults[ $key ] = array_merge( $default, $stored[ $key ] );
			}
		}

			return $defaults;
	}

		/**
		 * Persist notification settings from the admin form.
		 *
		 * @param array $data Raw submitted data.
		 * @return void
		 */
	public static function save_settings( $data ) {
			$defaults  = self::get_default_settings();
			$sanitized = array();

		foreach ( $defaults as $key => $default ) {
				$incoming = isset( $data[ $key ] ) && is_array( $data[ $key ] ) ? $data[ $key ] : array();

				$enabled = isset( $incoming['enabled'] ) ? (int) ( (int) $incoming['enabled'] ? 1 : 0 ) : 0;
			if ( $enabled && isset( $incoming['enabled'] ) && is_string( $incoming['enabled'] ) && in_array( strtolower( $incoming['enabled'] ), array( 'on', 'yes', 'true' ), true ) ) {
				$enabled = 1;
			}

				$subject = isset( $incoming['subject'] ) ? sanitize_text_field( wp_unslash( $incoming['subject'] ) ) : $default['subject'];
				$body    = isset( $incoming['body'] ) ? wp_kses_post( wp_unslash( $incoming['body'] ) ) : $default['body'];
				$bcc     = isset( $incoming['bcc'] ) ? self::sanitize_bcc( $incoming['bcc'] ) : '';

				$sanitized[ $key ] = array(
					'enabled' => $enabled ? 1 : 0,
					'subject' => $subject,
					'body'    => $body,
					'bcc'     => $bcc,
				);
		}

			update_option( self::OPTION_NAME, $sanitized );
	}

		/**
		 * Send hunt-related notifications when a hunt closes.
		 *
		 * @param int   $hunt_id       Hunt identifier.
		 * @param float $final_balance Final balance.
		 * @param array $winner_ids    Winner user IDs.
		 * @return void
		 */
	public static function send_hunt_notifications( $hunt_id, $final_balance, $winner_ids ) {
			global $wpdb;

			$settings = self::get_settings();
			$hunt_id  = (int) $hunt_id;

		if ( $hunt_id <= 0 ) {
				return;
		}

		$hunts_tbl   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$guesses_tbl = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$winners_tbl = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

		$hunt_query = sprintf(
			'SELECT id, title, starting_balance, final_balance, affiliate_site_id FROM `%s` WHERE id = %%d',
			$hunts_tbl
		);
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$hunt = $wpdb->get_row( $wpdb->prepare( $hunt_query, $hunt_id ) );

		if ( ! $hunt ) {
				return;
		}

			$final_balance = null !== $hunt->final_balance ? (float) $hunt->final_balance : (float) $final_balance;

		$winner_query = sprintf(
			'SELECT user_id, position, guess, diff FROM `%s` WHERE hunt_id = %%d ORDER BY position ASC',
			$winners_tbl
		);
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$winner_rows   = $wpdb->get_results( $wpdb->prepare( $winner_query, $hunt_id ) );
		$winner_filter = array_filter( array_map( 'intval', (array) $winner_ids ) );
		if ( $winner_filter && $winner_rows ) {
			$winner_rows = array_values(
				array_filter(
					$winner_rows,
					function ( $row ) use ( $winner_filter ) {
							$uid = isset( $row->user_id ) ? (int) $row->user_id : 0;
							return $uid > 0 && in_array( $uid, $winner_filter, true );
					}
				)
			);
		}

		$latest_guesses = array();
		$guess_query    = sprintf(
			'SELECT user_id, guess FROM `%s` WHERE hunt_id = %%d ORDER BY id DESC',
			$guesses_tbl
		);
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$guess_rows = $wpdb->get_results( $wpdb->prepare( $guess_query, $hunt_id ) );

		if ( $guess_rows ) {
			foreach ( $guess_rows as $guess_row ) {
				$uid = isset( $guess_row->user_id ) ? (int) $guess_row->user_id : 0;
				if ( $uid <= 0 || isset( $latest_guesses[ $uid ] ) ) {
						continue;
				}
				$latest_guesses[ $uid ] = (float) $guess_row->guess;
			}
		}

			$winner_map   = array();
			$winner_names = array();
		if ( $winner_rows ) {
			foreach ( $winner_rows as $winner_row ) {
					$uid = isset( $winner_row->user_id ) ? (int) $winner_row->user_id : 0;
				if ( $uid <= 0 ) {
					continue;
				}

					$winner_map[ $uid ] = array(
						'position' => isset( $winner_row->position ) ? (int) $winner_row->position : 0,
						'guess'    => isset( $winner_row->guess ) ? (float) $winner_row->guess : 0,
						'diff'     => isset( $winner_row->diff ) ? abs( (float) $winner_row->diff ) : 0,
					);

					$user = get_userdata( $uid );
					if ( $user ) {
							$winner_names[] = $user->display_name ? $user->display_name : $user->user_login;
					}
			}
		}

		if ( ! empty( $settings['bonushunt']['enabled'] ) && ! empty( $latest_guesses ) ) {
				self::send_hunt_participant_notifications( $settings['bonushunt'], $hunt, $latest_guesses, $winner_names, $final_balance );
		}

		if ( ! empty( $settings['winner']['enabled'] ) && ! empty( $winner_map ) ) {
				self::send_winner_notifications( $settings['winner'], $hunt, $winner_map, $final_balance );
		}
	}

		/**
		 * Send notifications when a tournament is closed.
		 *
		 * @param int $tournament_id Tournament identifier.
		 * @return void
		 */
	public static function send_tournament_notifications( $tournament_id ) {
			global $wpdb;

			$settings = self::get_settings();
		if ( empty( $settings['tournament']['enabled'] ) ) {
				return;
		}

			$tournament_id = (int) $tournament_id;
		if ( $tournament_id <= 0 ) {
				return;
		}

			$tours_tbl = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
			$res_tbl   = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );

		$tournament_query = sprintf(
			'SELECT id, title, type, start_date, end_date FROM `%s` WHERE id = %%d',
			$tours_tbl
		);
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$tournament = $wpdb->get_row( $wpdb->prepare( $tournament_query, $tournament_id ) );

		if ( ! $tournament ) {
				return;
		}

		$result_query = sprintf(
			'SELECT user_id, wins FROM `%s` WHERE tournament_id = %%d',
			$res_tbl
		);
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( $result_query, $tournament_id ) );

		if ( empty( $results ) ) {
				return;
		}

			$period_slug = isset( $tournament->type ) ? sanitize_key( (string) $tournament->type ) : '';
		if ( '' === $period_slug && function_exists( 'bhg_resolve_tournament_period_slug' ) ) {
				$period_slug = bhg_resolve_tournament_period_slug( $tournament->start_date, $tournament->end_date );
		}

		foreach ( $results as $result ) {
				$user_id = isset( $result->user_id ) ? (int) $result->user_id : 0;
			if ( $user_id <= 0 ) {
					continue;
			}

				$user = get_userdata( $user_id );
			if ( ! $user || ! $user->user_email ) {
					continue;
			}

				$context = array(
					'user_name'        => $user->display_name ? $user->display_name : $user->user_login,
					'tournament_title' => $tournament->title ? $tournament->title : bhg_t( 'menu_tournaments', 'Tournaments' ),
					'wins'             => isset( $result->wins ) ? (int) $result->wins : 0,
					'tournament_type'  => $period_slug ? bhg_t( $period_slug, ucfirst( $period_slug ) ) : '',
					'site_name'        => get_bloginfo( 'name' ),
				);

				$subject = self::replace_tokens( $settings['tournament']['subject'], $context );
				$body    = self::replace_tokens( $settings['tournament']['body'], $context );

				if ( '' === trim( $subject ) || '' === trim( wp_strip_all_tags( $body ) ) ) {
						continue;
				}

				wp_mail(
					$user->user_email,
					$subject,
					$body,
					self::build_headers( $settings['tournament']['bcc'] )
				);
		}
	}

		/**
		 * Retrieve the default notification configuration.
		 *
		 * @return array
		 */
	private static function get_default_settings() {
			return array(
				'bonushunt'  => array(
					'enabled' => 0,
					'subject' => __( 'Bonus Hunt Closed', 'bonus-hunt-guesser' ),
					'body'    => __( '<p>Hi {{user_name}},</p><p>The bonus hunt "{{hunt_title}}" has closed with a final balance of {{final_balance}}.</p><p>Top guesses: {{winner_list}}</p>', 'bonus-hunt-guesser' ),
					'bcc'     => '',
				),
				'winner'     => array(
					'enabled' => 0,
					'subject' => __( 'You placed {{position}} in {{hunt_title}}', 'bonus-hunt-guesser' ),
					'body'    => __( '<p>Congratulations {{user_name}}!</p><p>You finished {{position}} in the bonus hunt "{{hunt_title}}" with a guess of {{guess}} (difference {{difference}}).</p>', 'bonus-hunt-guesser' ),
					'bcc'     => '',
				),
				'tournament' => array(
					'enabled' => 0,
					'subject' => __( 'Tournament results for {{tournament_title}}', 'bonus-hunt-guesser' ),
					'body'    => __( '<p>Hi {{user_name}},</p><p>The tournament "{{tournament_title}}" ({{tournament_type}}) has closed. You recorded {{wins}} wins.</p>', 'bonus-hunt-guesser' ),
					'bcc'     => '',
				),
			);
	}

		/**
		 * Send notifications to all hunt participants.
		 *
		 * @param array  $config          Notification configuration.
		 * @param object $hunt           Hunt row.
		 * @param array  $guess_map      Latest guesses keyed by user ID.
		 * @param array  $winner_names   Winner display names.
		 * @param float  $final_balance  Final balance.
		 * @return void
		 */
	private static function send_hunt_participant_notifications( $config, $hunt, $guess_map, $winner_names, $final_balance ) {
		foreach ( $guess_map as $user_id => $guess ) {
				$user_id = (int) $user_id;
			if ( $user_id <= 0 ) {
				continue;
			}

				$user = get_userdata( $user_id );
			if ( ! $user || ! $user->user_email ) {
					continue;
			}

				$context = array(
					'user_name'     => $user->display_name ? $user->display_name : $user->user_login,
					'hunt_title'    => $hunt->title ? $hunt->title : bhg_t( 'bonus_hunt', 'Bonus Hunt' ),
					'final_balance' => self::format_money( $final_balance ),
					'start_balance' => self::format_money( isset( $hunt->starting_balance ) ? (float) $hunt->starting_balance : 0 ),
					'user_guess'    => self::format_money( $guess ),
					'winner_list'   => $winner_names ? implode( ', ', $winner_names ) : bhg_t( 'label_emdash', '—' ),
					'site_name'     => get_bloginfo( 'name' ),
				);

				$subject = self::replace_tokens( $config['subject'], $context );
				$body    = self::replace_tokens( $config['body'], $context );

				if ( '' === trim( $subject ) || '' === trim( wp_strip_all_tags( $body ) ) ) {
						continue;
				}

				wp_mail(
					$user->user_email,
					$subject,
					$body,
					self::build_headers( $config['bcc'] )
				);
		}
	}

		/**
		 * Send winner-specific notifications.
		 *
		 * @param array  $config         Notification configuration.
		 * @param object $hunt           Hunt data.
		 * @param array  $winner_map     Winner context keyed by user ID.
		 * @param float  $final_balance  Final balance.
		 * @return void
		 */
	private static function send_winner_notifications( $config, $hunt, $winner_map, $final_balance ) {
		foreach ( $winner_map as $user_id => $info ) {
				$user = get_userdata( (int) $user_id );
			if ( ! $user || ! $user->user_email ) {
				continue;
			}

				$context = array(
					'user_name'     => $user->display_name ? $user->display_name : $user->user_login,
					'hunt_title'    => $hunt->title ? $hunt->title : bhg_t( 'bonus_hunt', 'Bonus Hunt' ),
					'final_balance' => self::format_money( $final_balance ),
					'position'      => isset( $info['position'] ) ? (int) $info['position'] : 0,
					'guess'         => self::format_money( isset( $info['guess'] ) ? (float) $info['guess'] : 0 ),
					'difference'    => self::format_money( isset( $info['diff'] ) ? (float) $info['diff'] : 0 ),
					'site_name'     => get_bloginfo( 'name' ),
				);

				$subject = self::replace_tokens( $config['subject'], $context );
				$body    = self::replace_tokens( $config['body'], $context );

				if ( '' === trim( $subject ) || '' === trim( wp_strip_all_tags( $body ) ) ) {
						continue;
				}

				wp_mail(
					$user->user_email,
					$subject,
					$body,
					self::build_headers( $config['bcc'] )
				);
		}
	}

		/**
		 * Replace template tokens in a string.
		 *
		 * @param string $text    Template string.
		 * @param array  $context Replacement context.
		 * @return string
		 */
	private static function replace_tokens( $text, $context ) {
		if ( '' === $text ) {
				return '';
		}

			$replacements = array();
		foreach ( $context as $key => $value ) {
				$replacements[ '{{' . $key . '}}' ]   = $value;
				$replacements[ '{{ ' . $key . ' }}' ] = $value;
		}

			return strtr( $text, $replacements );
	}

		/**
		 * Build headers for outgoing email.
		 *
		 * @param string $bcc BCC list.
		 * @return array
		 */
	private static function build_headers( $bcc ) {
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$from_addr = method_exists( 'BHG_Utils', 'get_email_from' ) ? BHG_Utils::get_email_from() : get_bloginfo( 'admin_email' );
		if ( $from_addr ) {
				$headers[] = 'From: ' . $from_addr;
		}

		if ( $bcc ) {
				$headers[] = 'Bcc: ' . $bcc;
		}

			return $headers;
	}

		/**
		 * Sanitize a BCC value.
		 *
		 * @param string $value Raw BCC input.
		 * @return string
		 */
	private static function sanitize_bcc( $value ) {
		if ( is_array( $value ) ) {
				$value = array_map( 'wp_unslash', $value );
				$value = implode( ',', $value );
		} else {
				$value = wp_unslash( $value );
		}
			$value   = (string) $value;
			$entries = preg_split( '/[;,]+/', $value );
			$clean   = array();

		foreach ( (array) $entries as $entry ) {
				$email = sanitize_email( trim( $entry ) );
			if ( $email ) {
					$clean[] = $email;
			}
		}

			return implode( ', ', array_unique( $clean ) );
	}

		/**
		 * Format money values using plugin helpers when available.
		 *
		 * @param float $amount Raw amount.
		 * @return string
		 */
	private static function format_money( $amount ) {
		if ( function_exists( 'bhg_format_money' ) ) {
				return bhg_format_money( $amount );
		}

			return number_format_i18n( (float) $amount, 2 );
	}
}
