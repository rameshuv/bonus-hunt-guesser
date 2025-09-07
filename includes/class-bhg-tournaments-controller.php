<?php
/**
 * Tournaments controller for Bonus Hunt Guesser.
 *
 * Applies default tournament settings during creation.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Handles tournament-related hooks and logic.
 */
class BHG_Tournaments_Controller {
		/**
		 * Initialize hooks.
		 *
		 * @return void
		 */
	public static function init() {
			add_action( 'admin_post_bhg_tournament_save', array( __CLASS__, 'apply_default_period' ), 5 );
	}

		/**
		 * Apply default tournament period if none supplied.
		 *
		 * @return void
		 */
	public static function apply_default_period() {
		if ( isset( $_POST['type'] ) && '' !== $_POST['type'] ) {
				return;
		}

			$settings = get_option( 'bhg_plugin_settings', array() );
			$period   = isset( $settings['default_tournament_period'] ) ? $settings['default_tournament_period'] : 'monthly';
			$period   = sanitize_text_field( $period );

			$allowed = array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' );
		if ( ! in_array( $period, $allowed, true ) ) {
				$period = 'monthly';
		}

			$_POST['type'] = $period;
	}
}
