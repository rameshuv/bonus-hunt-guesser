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
			if ( function_exists( 'NextendSocialLogin' ) ) {
				add_filter( 'nsl_login_redirect', array( $this, 'nextend_redirect' ), 10, 3 );
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
	}
}
