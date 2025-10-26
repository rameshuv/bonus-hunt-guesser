<?php
/**
 * Utility functions and helpers for Bonus Hunt Guesser plugin.
 *
 * @package Bonus_Hunt_Guesser
 */

// phpcs:disable WordPress.Files.FileOrganization

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General utility methods used throughout the plugin.
 */
class BHG_Utils {
	/**
	 * Register hooks used by utility functions.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes handled in the shortcode constructor.
	 *
	 * @return void
	 */
	public static function register_shortcodes() {
		// Handled in BHG_Shortcodes constructor, kept for legacy.
	}

	/**
	 * Retrieve plugin settings merged with defaults.
	 *
	 * @return array Plugin settings.
	 */
        public static function get_settings() {
                $defaults = array(
                        'allow_guess_edit' => 1,
                        'ads_enabled'      => 1,
                        'email_from'       => get_bloginfo( 'admin_email' ),
                );
                $opt      = get_option( 'bhg_settings', array() );
                if ( ! is_array( $opt ) ) {
                        $opt = array();
                }
                return wp_parse_args( $opt, $defaults );
        }

        /**
         * Default notification settings for each channel.
         *
         * @return array<string,array<string,mixed>>
         */
        public static function get_notification_defaults() {
                return array(
                        'winners'    => array(
                                'enabled' => 0,
                                'subject' => bhg_t( 'notification_winners_subject', 'Congratulations on your Bonus Hunt win!' ),
                                'body'    => bhg_t( 'notification_winners_body', 'Hi {{username}},<br>Congratulations on placing {{position}} in {{hunt}} with a guess of {{guess}} (difference {{difference}}). Final balance: {{final_balance}}.' ),
                                'bcc'     => '',
                        ),
                        'bonushunt' => array(
                                'enabled' => 0,
                                'subject' => bhg_t( 'notification_bonushunt_subject', 'Results for {{hunt}}' ),
                                'body'    => bhg_t( 'notification_bonushunt_body', 'Hi {{username}},<br>{{hunt}} has closed with a final balance of {{final_balance}}. Winners: {{winners}}.' ),
                                'bcc'     => '',
                        ),
                        'tournament' => array(
                                'enabled' => 0,
                                'subject' => bhg_t( 'notification_tournament_subject', 'Tournament update for {{tournament}}' ),
                                'body'    => bhg_t( 'notification_tournament_body', 'Hi {{username}},<br>You are ranked {{rank}} with {{wins}} wins in {{tournament}}.' ),
                                'bcc'     => '',
                        ),
                );
        }

        /**
         * Retrieve stored notification settings merged with defaults.
         *
         * @return array<string,array<string,mixed>>
         */
        public static function get_notification_settings() {
                $defaults = self::get_notification_defaults();
                $stored   = get_option( 'bhg_notification_settings', array() );
                if ( ! is_array( $stored ) ) {
                        $stored = array();
                }

                $settings = $defaults;
                foreach ( $defaults as $key => $default ) {
                        if ( isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ) {
                                $current = $stored[ $key ];
                                $settings[ $key ]['enabled'] = ! empty( $current['enabled'] ) ? 1 : 0;
                                $settings[ $key ]['subject'] = isset( $current['subject'] ) ? sanitize_text_field( $current['subject'] ) : $default['subject'];
                                $settings[ $key ]['body']    = isset( $current['body'] ) ? wp_kses_post( $current['body'] ) : $default['body'];
                                $settings[ $key ]['bcc']     = isset( $current['bcc'] ) ? sanitize_text_field( $current['bcc'] ) : '';
                        }
                }

                return $settings;
        }

        /**
         * Normalize a BCC string into a list of sanitized email addresses.
         *
         * @param string|array $bcc Raw BCC input.
         * @return array<int,string>
         */
        public static function sanitize_bcc_list( $bcc ) {
                if ( is_array( $bcc ) ) {
                        $bcc = implode( ',', $bcc );
                }

                $clean  = array();
                $pieces = preg_split( '/[\s,;]+/', (string) $bcc );

                foreach ( $pieces as $piece ) {
                        $email = sanitize_email( $piece );
                        if ( $email && is_email( $email ) ) {
                                $clean[ $email ] = $email;
                        }
                }

                return array_values( $clean );
        }

        /**
         * Build email headers for plugin notifications.
         *
         * @param array<int,string> $bcc_list Optional BCC recipients.
         * @return array<int,string>
         */
        public static function build_email_headers( array $bcc_list = array() ) {
                $headers   = array( 'From: ' . self::get_email_from(), 'Content-Type: text/html; charset=UTF-8' );
                $bcc_list  = array_filter( array_map( 'sanitize_email', $bcc_list ) );
                if ( ! empty( $bcc_list ) ) {
                        $headers[] = 'Bcc: ' . implode( ',', $bcc_list );
                }

                return $headers;
        }

	/**
	 * Update plugin settings.
	 *
	 * @param array $data New settings data.
	 * @return array Updated settings.
	 */
	public static function update_settings( $data ) {
		$current = self::get_settings();
		$new     = array_merge( $current, $data );
		update_option( 'bhg_settings', $new );
		return $new;
	}

	/**
	 * Retrieve the "From" email address for notifications.
	 *
	 * @return string Email address.
	 */
	public static function get_email_from() {
		$settings = get_option( 'bhg_plugin_settings', array() );
		$email    = isset( $settings['email_from'] ) ? $settings['email_from'] : get_bloginfo( 'admin_email' );
		$email    = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			$email = sanitize_email( get_bloginfo( 'admin_email' ) );
		}

		return $email;
	}

	/**
	 * Require manage options capability or abort.
	 *
	 * @return void
	 */
	public static function require_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html(
					bhg_t(
						'you_do_not_have_permission_to_access_this_page',
						'You do not have permission to access this page'
					)
				)
			);
		}
	}

		/**
		 * Retrieve an admin URL, respecting network admin context.
		 *
		 * @param string $path Optional path relative to the admin URL.
		 * @return string Full admin URL for the current context.
		 */
	public static function admin_url( $path = '' ) {
			return is_network_admin() ? network_admin_url( $path ) : admin_url( $path );
	}

	/**
	 * Output a nonce field for the given action.
	 *
	 * @param string $action Action name.
	 * @return void
	 */
	public static function nonce_field( $action ) {
		wp_nonce_field( $action, $action . '_nonce' );
	}

	/**
	 * Verify a nonce for the given action.
	 *
	 * @param string $action Action name.
	 * @return bool Whether the nonce is valid.
	 */
	public static function verify_nonce( $action ) {
		return isset( $_POST[ $action . '_nonce' ] )
			&& wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST[ $action . '_nonce' ] ) ),
				$action
			);
	}

	/**
	 * Execute a callback during template redirect after conditionals are set up.
	 *
	 * @param callable $cb Callback to execute.
	 * @return void
	 */
	public static function safe_query_conditionals( callable $cb ) {
		add_action(
			'template_redirect',
			function () use ( $cb ) {
				$cb();
			}
		);
	}
}

// phpcs:enable WordPress.Files.FileOrganization
