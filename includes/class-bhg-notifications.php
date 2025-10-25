<?php
/**
 * Notification utilities for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Centralized helper for notification templates and delivery.
 */
class BHG_Notifications {

	const OPTION_KEY = 'bhg_notifications_settings';

	/**
	 * Retrieve the notification configuration map.
	 *
	 * @return array
	 */
	public static function get_notification_types() {
		return array(
			'hunt_results'      => array(
				'label'        => bhg_t( 'notification_hunt_results', 'Hunt Results' ),
				'description'  => bhg_t( 'notification_hunt_results_desc', 'Sent to all hunt participants when a hunt is closed.' ),
				'placeholders' => array(
					'{{site_name}}'     => bhg_t( 'notification_placeholder_site', 'Site name' ),
					'{{username}}'      => bhg_t( 'notification_placeholder_username', 'Recipient username' ),
					'{{display_name}}'  => bhg_t( 'notification_placeholder_display_name', 'Recipient display name' ),
					'{{hunt_title}}'    => bhg_t( 'notification_placeholder_hunt_title', 'Hunt title' ),
					'{{final_balance}}' => bhg_t( 'notification_placeholder_final_balance', 'Final hunt balance' ),
					'{{winner_names}}'  => bhg_t( 'notification_placeholder_winner_names', 'Comma separated list of winners' ),
					'{{winner_first}}'  => bhg_t( 'notification_placeholder_winner_first', 'First place winner' ),
				),
			),
			'tournament_results' => array(
				'label'        => bhg_t( 'notification_tournament_results', 'Tournament Results' ),
				'description'  => bhg_t( 'notification_tournament_results_desc', 'Sent to tournament participants when a tournament is closed.' ),
				'placeholders' => array(
					'{{site_name}}'        => bhg_t( 'notification_placeholder_site', 'Site name' ),
					'{{username}}'         => bhg_t( 'notification_placeholder_username', 'Recipient username' ),
					'{{display_name}}'     => bhg_t( 'notification_placeholder_display_name', 'Recipient display name' ),
					'{{tournament_title}}' => bhg_t( 'notification_placeholder_tournament_title', 'Tournament title' ),
					'{{tournament_type}}'  => bhg_t( 'notification_placeholder_tournament_type', 'Tournament type' ),
					'{{wins}}'             => bhg_t( 'notification_placeholder_wins', 'Wins recorded for the recipient' ),
					'{{rank}}'             => bhg_t( 'notification_placeholder_rank', 'Recipient rank' ),
				),
			),
			'hunt_created'      => array(
				'label'        => bhg_t( 'notification_hunt_created', 'Hunt Created' ),
				'description'  => bhg_t( 'notification_hunt_created_desc', 'Sent when a new hunt is created.' ),
				'placeholders' => array(
					'{{site_name}}'        => bhg_t( 'notification_placeholder_site', 'Site name' ),
					'{{username}}'         => bhg_t( 'notification_placeholder_username', 'Recipient username' ),
					'{{display_name}}'     => bhg_t( 'notification_placeholder_display_name', 'Recipient display name' ),
					'{{hunt_title}}'       => bhg_t( 'notification_placeholder_hunt_title', 'Hunt title' ),
					'{{starting_balance}}' => bhg_t( 'notification_placeholder_starting_balance', 'Starting balance' ),
					'{{num_bonuses}}'      => bhg_t( 'notification_placeholder_num_bonuses', 'Number of bonuses' ),
				),
			),
		);
	}

	/**
	 * Default notification settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'hunt_results'       => array(
				'enabled'     => 1,
				'title'       => bhg_t( 'notification_default_hunt_results_subject', 'Results for {{hunt_title}}' ),
				'description' => bhg_t( 'notification_default_hunt_results_body', '<p>Hi {{display_name}},</p><p>The bonus hunt "{{hunt_title}}" has concluded with a final balance of {{final_balance}}.</p><p>Congratulations to our winners: {{winner_names}}.</p><p>Thanks for playing!</p>' ),
				'bcc'         => '',
			),
			'tournament_results' => array(
				'enabled'     => 1,
				'title'       => bhg_t( 'notification_default_tournament_subject', 'Tournament {{tournament_title}} has finished' ),
				'description' => bhg_t( 'notification_default_tournament_body', '<p>Hello {{display_name}},</p><p>The tournament "{{tournament_title}}" ({{tournament_type}}) has finished. You achieved rank {{rank}} with {{wins}} win(s).</p><p>Thank you for participating!</p>' ),
				'bcc'         => '',
			),
			'hunt_created'      => array(
				'enabled'     => 0,
				'title'       => bhg_t( 'notification_default_hunt_created_subject', 'New hunt created: {{hunt_title}}' ),
				'description' => bhg_t( 'notification_default_hunt_created_body', '<p>Hi {{display_name}},</p><p>A new hunt "{{hunt_title}}" is now live with a starting balance of {{starting_balance}} and {{num_bonuses}} bonuses.</p><p>Get ready to submit your guess!</p>' ),
				'bcc'         => '',
			),
		);
	}

	/**
	 * Retrieve saved settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = self::get_defaults();
		$saved    = get_option( self::OPTION_KEY, array() );
		$settings = array();

		foreach ( $defaults as $type => $default ) {
			$saved_type                  = isset( $saved[ $type ] ) && is_array( $saved[ $type ] ) ? $saved[ $type ] : array();
			$merged                      = array_merge( $default, $saved_type );
			$settings[ $type ]           = self::sanitize_notification( $merged );
			$settings[ $type ]['enabled'] = empty( $settings[ $type ]['enabled'] ) ? 0 : 1;
		}

		return $settings;
	}

	/**
	 * Fetch a single notification configuration.
	 *
	 * @param string $type Notification type.
	 * @return array
	 */
	public static function get_notification( $type ) {
		$settings = self::get_settings();
		return isset( $settings[ $type ] ) ? $settings[ $type ] : array();
	}

	/**
	 * Persist notification settings.
	 *
	 * @param array $input Raw settings data.
	 * @return void
	 */
	public static function save_settings( array $input ) {
		$defaults  = self::get_defaults();
		$sanitized = array();

		foreach ( $defaults as $type => $default ) {
			$raw                = isset( $input[ $type ] ) && is_array( $input[ $type ] ) ? $input[ $type ] : array();
			$sanitized[ $type ] = self::sanitize_notification( array_merge( $default, $raw ) );
		}

		update_option( self::OPTION_KEY, $sanitized );
	}

	/**
	 * Sanitize a notification definition.
	 *
	 * @param array $notification Raw notification data.
	 * @return array
	 */
	private static function sanitize_notification( array $notification ) {
		$sanitized = array(
			'enabled'     => empty( $notification['enabled'] ) ? 0 : 1,
			'title'       => isset( $notification['title'] ) ? sanitize_text_field( $notification['title'] ) : '',
			'description' => isset( $notification['description'] ) ? wp_kses_post( $notification['description'] ) : '',
			'bcc'         => self::sanitize_bcc( isset( $notification['bcc'] ) ? $notification['bcc'] : '' ),
		);

		return $sanitized;
	}

	/**
	 * Sanitize BCC string.
	 *
	 * @param string $bcc Raw BCC value.
	 * @return string
	 */
	private static function sanitize_bcc( $bcc ) {
		$bcc = is_string( $bcc ) ? $bcc : '';
		if ( '' === trim( $bcc ) ) {
			return '';
		}

		$addresses = array();
		foreach ( explode( ',', $bcc ) as $candidate ) {
			$candidate = trim( $candidate );
			$email     = sanitize_email( $candidate );
			if ( $email && is_email( $email ) ) {
				$addresses[ $email ] = $email;
			}
		}

		return implode( ', ', array_values( $addresses ) );
	}

	/**
	 * Send a notification email.
	 *
	 * @param string $type         Notification type key.
	 * @param array  $replacements Base replacements for placeholders.
	 * @param array  $recipients   Recipient list. Each recipient should include 'email' and optional 'replacements'.
	 *
	 * @return bool True if at least one email was sent, false otherwise.
	 */
	public static function send( $type, array $replacements, array $recipients ) {
		$notification = self::get_notification( $type );

		if ( empty( $notification ) || empty( $notification['enabled'] ) ) {
			return false;
		}

		$sent_any = false;
		$headers  = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . BHG_Utils::get_email_from(),
		);

		$bcc_header = '';
		if ( ! empty( $notification['bcc'] ) ) {
			$bcc_header = 'Bcc: ' . $notification['bcc'];
		}

		$site_name = get_bloginfo( 'name' );
		if ( $site_name ) {
			$replacements['{{site_name}}'] = $site_name;
		}

		foreach ( $recipients as $index => $recipient ) {
			$email = isset( $recipient['email'] ) ? sanitize_email( $recipient['email'] ) : '';
			if ( ! $email || ! is_email( $email ) ) {
				continue;
			}

			$recipient_replacements = isset( $recipient['replacements'] ) && is_array( $recipient['replacements'] ) ? $recipient['replacements'] : array();
			$all_replacements       = array_merge( $replacements, $recipient_replacements );
			$subject                = self::replace_tags( $notification['title'], $all_replacements );
			$body                   = self::replace_tags( $notification['description'], $all_replacements );

			$mail_headers = $headers;
			if ( 0 === $index && $bcc_header ) {
				$mail_headers[] = $bcc_header;
			}

			if ( wp_mail( $email, wp_strip_all_tags( $subject ), $body, $mail_headers ) ) {
				$sent_any = true;
			}
		}

		return $sent_any;
	}

	/**
	 * Replace template tags in content.
	 *
	 * @param string $content      Source content.
	 * @param array  $replacements Replacement map.
	 *
	 * @return string
	 */
	private static function replace_tags( $content, array $replacements ) {
		$content = is_string( $content ) ? $content : '';
		if ( empty( $replacements ) ) {
			return $content;
		}

		$safe_replacements = array();
		foreach ( $replacements as $tag => $value ) {
			if ( ! is_string( $tag ) ) {
				continue;
			}

			$safe_replacements[ $tag ] = is_scalar( $value ) ? (string) $value : '';
		}

		return strtr( $content, $safe_replacements );
	}
}
