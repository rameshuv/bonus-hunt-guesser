<?php
/**
 * Login redirect handling for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Login_Redirect' ) ) {
	/**
	 * Manage login redirection.
	 */
        class BHG_Login_Redirect {

		/**
		 * Setup hooks.
		 */
                public function __construct() {
                        add_filter( 'login_redirect', array( $this, 'core_login_redirect' ), 10, 3 );

                        // Nextend Social Login compatibility if plugin active.
                        $nextend_active = function_exists( 'bhg_nextend_is_available' ) && bhg_nextend_is_available();
                        if ( ! $nextend_active ) {
                                $nextend_active = class_exists( '\\NextendSocialLogin\\NextendSocialLogin', false )
                                        || class_exists( 'NextendSocialLogin', false )
                                        || defined( 'NEXTEND_SOCIAL_LOGIN_PLUGIN_VERSION' )
                                        || defined( 'NEXTEND_SOCIAL_LOGIN_PLUGIN_BASENAME' );
                        }

                        if ( $nextend_active ) {
                                require_once BHG_PLUGIN_DIR . 'includes/class-bhg-nextend-profile.php';
                                add_filter( 'nsl_login_redirect', array( $this, 'nextend_redirect' ), 10, 3 );
                                add_action( 'nsl_register_new_user', array( $this, 'capture_nextend_profile' ), 10, 3 );
                        }
                }

		/**
		 * Core login redirect handler.
		 *
		 * @param string  $redirect_to Default redirect destination.
		 * @param string  $requested   Requested redirect URL.
		 * @param WP_User $user        Logged-in user object.
		 * @return string Sanitized redirect URL.
		 */
		public function core_login_redirect( $redirect_to, $requested, $user ) {
			if ( ! empty( $_REQUEST['redirect_to'] ) ) {
				$requested_redirect = sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) );
				$validated_redirect = wp_validate_redirect( $requested_redirect, home_url( '/' ) );
				return esc_url_raw( $validated_redirect );
			}

			// Fall back to referer if safe.
			$ref = wp_get_referer();
			if ( $ref ) {
				$validated_ref = wp_validate_redirect( $ref, home_url( '/' ) );
				return esc_url_raw( $validated_ref );
			}

			$validated_default = wp_validate_redirect( $redirect_to, home_url( '/' ) );
			return esc_url_raw( $validated_default );
		}

		/**
		 * Handle Nextend Social Login redirect.
		 *
		 * @param string  $redirect_to Requested redirect URL.
		 * @param WP_User $user       WP_User object of the logged-in user.
		 * @param string  $provider    Social login provider slug.
		 * @return string Sanitized redirect URL.
		 */
                public function nextend_redirect( $redirect_to, $user, $provider ) {
			if ( ! empty( $_REQUEST['redirect_to'] ) ) {
				$requested_redirect = sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) );
				$validated_redirect = wp_validate_redirect( $requested_redirect, home_url( '/' ) );
				return esc_url_raw( $validated_redirect );
			}

			$ref = wp_get_referer();
			if ( $ref ) {
				$validated_ref = wp_validate_redirect( $ref, home_url( '/' ) );
				return esc_url_raw( $validated_ref );
			}

                        $validated_default = wp_validate_redirect( $redirect_to, home_url( '/' ) );
                        return esc_url_raw( $validated_default );
                }

                /**
                 * Capture profile data when a user registers via Nextend Social Login.
                 *
                 * @param int    $user_id  Newly created user ID.
                 * @param string $provider Provider slug (e.g., google, twitch).
                 * @param array  $data     Raw profile data from Nextend.
                 * @return void
                 */
                public function capture_nextend_profile( $user_id, $provider, $data ) {
                        if ( ! $user_id ) {
                                return;
                        }

                        $profile = BHG_Nextend_Profile::sanitize_profile( $data );
                        $profile['provider'] = sanitize_text_field( $provider );

                        /**
                         * Filter profile data captured from Nextend before saving.
                         *
                         * @param array $profile Sanitized profile data.
                         * @param int   $user_id User ID.
                         * @param array $data    Raw profile data from Nextend.
                         */
                        $profile = apply_filters( 'bhg_nextend_profile_data', $profile, $user_id, $data );

                        if ( ! empty( $profile['provider'] ) ) {
                                update_user_meta( $user_id, 'bhg_social_provider', $profile['provider'] );
                        }
                        if ( ! empty( $profile['profile_url'] ) ) {
                                update_user_meta( $user_id, 'bhg_social_profile_url', $profile['profile_url'] );
                        }
                        if ( ! empty( $profile['avatar'] ) ) {
                                update_user_meta( $user_id, 'bhg_social_avatar', $profile['avatar'] );
                        }
                        if ( ! empty( $profile['display_name'] ) ) {
                                update_user_meta( $user_id, 'bhg_social_display_name', $profile['display_name'] );
                        }

                        /**
                         * Fires after Nextend profile meta was saved.
                         *
                         * @param int   $user_id User ID.
                         * @param array $profile Profile data saved to user meta.
                         * @param array $data    Raw profile data from Nextend.
                         */
                        do_action( 'bhg_nextend_profile_saved', $user_id, $profile, $data );
                }
        }
}
