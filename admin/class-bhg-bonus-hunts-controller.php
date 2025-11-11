<?php
/**
 * Admin controller for bonus hunt forms.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Bonus_Hunts_Controller' ) ) {
	/**
	 * Handles create, update and delete actions for bonus hunts.
	 */
	class BHG_Bonus_Hunts_Controller {
		/**
		 * Singleton instance.
		 *
		 * @var BHG_Bonus_Hunts_Controller|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return BHG_Bonus_Hunts_Controller
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {}

		/**
		 * Initialize hooks.
		 *
		 * @return void
		 */
		public function init() {
				add_action( 'admin_post_bhg_create_bonus_hunt', array( $this, 'handle_form_submissions' ) );
				add_action( 'admin_post_bhg_update_bonus_hunt', array( $this, 'handle_form_submissions' ) );
				add_action( 'admin_post_bhg_delete_bonus_hunt', array( $this, 'handle_form_submissions' ) );
				add_action( 'admin_post_bhg_delete_guess', array( $this, 'delete_guess' ) );
		}

		/**
		 * Retrieve data for bonus hunt admin views.
		 *
		 * @return array
		 */
		public function get_admin_view_vars() {
			$db = new BHG_DB();

			return array(
				'bonus_hunts'        => $db->get_all_bonus_hunts(),
				'affiliate_websites' => $db->get_affiliate_websites(),
			);
		}

		/**
		 * Retrieve the latest hunts with winner information for dashboard displays.
		 *
		 * @param int $limit Number of hunts to fetch.
		 * @return array
		 */
		public function get_latest_hunts( $limit = 3 ) {
			$limit = max( 1, (int) $limit );

			if ( ! class_exists( 'BHG_Bonus_Hunts' ) ) {
				$file = BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts.php';
				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}

			if ( class_exists( 'BHG_Bonus_Hunts' ) && method_exists( 'BHG_Bonus_Hunts', 'get_latest_hunts_with_winners' ) ) {
				$hunts = BHG_Bonus_Hunts::get_latest_hunts_with_winners( $limit );
				if ( is_array( $hunts ) ) {
					return $hunts;
				}
			}

			$results = array();

			if ( function_exists( 'bhg_get_latest_closed_hunts' ) ) {
				$legacy_hunts = bhg_get_latest_closed_hunts( $limit );

				foreach ( (array) $legacy_hunts as $hunt ) {
					$hunt_id       = isset( $hunt->id ) ? (int) $hunt->id : 0;
					$winners_limit = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : 0;
					$winners_limit = $winners_limit > 0 ? $winners_limit : 25;
					$winners       = array();

					if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
						$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_limit );
					}

					$results[] = array(
						'hunt'    => $hunt,
						'winners' => $winners,
					);
				}
			}

			return $results;
		}

		/**
		 * Handle bonus hunt form submissions.
		 *
		 * @return void
		 */
		public function handle_form_submissions() {
			if ( empty( $_POST['action'] ) ) {
						return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
									wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
			}

							$action = sanitize_text_field( wp_unslash( $_POST['action'] ) );

							check_admin_referer( $action, 'bhg_nonce' );

							$db      = new BHG_DB();
							$message = 'error';

			switch ( $action ) {
				case 'bhg_create_bonus_hunt':
						$title                         = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
						$starting_balance              = floatval( wp_unslash( $_POST['starting_balance'] ?? 0 ) );
						$num_bonuses                   = absint( wp_unslash( $_POST['num_bonuses'] ?? 0 ) );
									$prizes            = sanitize_textarea_field( wp_unslash( $_POST['prizes'] ?? '' ) );
									$status            = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
									$affiliate_site_id = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;

									$result = $db->create_bonus_hunt(
										array(
											'title'       => $title,
											'starting_balance' => $starting_balance,
											'num_bonuses' => $num_bonuses,
											'prizes'      => $prizes,
											'status'      => $status,
											'affiliate_site_id' => $affiliate_site_id,
											'created_by'  => get_current_user_id(),
											'created_at'  => current_time( 'mysql' ),
										)
									);

										$message = $result ? 'success' : 'error';
					break;

				case 'bhg_update_bonus_hunt':
						$id               = absint( wp_unslash( $_POST['id'] ?? 0 ) );
						$title            = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
						$starting_balance = floatval( wp_unslash( $_POST['starting_balance'] ?? 0 ) );
					$num_bonuses          = absint( wp_unslash( $_POST['num_bonuses'] ?? 0 ) );
					$prizes               = sanitize_textarea_field( wp_unslash( $_POST['prizes'] ?? '' ) );
					$status               = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
					$final_balance        = isset( $_POST['final_balance'] ) ? floatval( wp_unslash( $_POST['final_balance'] ) ) : null;
					$affiliate_site_id    = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;

					$result = $db->update_bonus_hunt(
						$id,
						array(
							'title'             => $title,
							'starting_balance'  => $starting_balance,
							'num_bonuses'       => $num_bonuses,
							'prizes'            => $prizes,
							'status'            => $status,
							'final_balance'     => $final_balance,
							'affiliate_site_id' => $affiliate_site_id,
						)
					);

					if ( $result && 'closed' === $status && null !== $final_balance ) {
						if ( class_exists( 'BHG_Models' ) ) {
							$winner_ids = BHG_Models::close_hunt( $id, $final_balance );
							if ( function_exists( 'bhg_send_hunt_results_email' ) ) {
								bhg_send_hunt_results_email( $id, $winner_ids );
							}
						}
					}

						$message = $result ? 'updated' : 'error';
					break;

				case 'bhg_delete_bonus_hunt':
						$id      = absint( wp_unslash( $_POST['id'] ?? 0 ) );
						$result  = $db->delete_bonus_hunt( $id );
						$message = $result ? 'deleted' : 'error';
					break;
			}

							$url = esc_url_raw( add_query_arg( 'message', $message, wp_get_referer() ) );
							wp_safe_redirect( $url );
							exit;
		}

				/**
				 * Delete a guess submitted for a hunt.
				 *
				 * @return void
				 */
		public function delete_guess() {
			if ( ! current_user_can( 'manage_options' ) ) {
						wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
			}

																check_admin_referer( 'bhg_delete_guess', 'bhg_delete_guess_nonce' );

				$guess_id = isset( $_GET['guess_id'] ) ? absint( wp_unslash( $_GET['guess_id'] ) ) : 0;

				global $wpdb;
				$table   = $wpdb->prefix . 'bhg_guesses';
				$deleted = false;

			if ( $guess_id > 0 ) {
				$deleted = (bool) $wpdb->delete( $table, array( 'id' => $guess_id ), array( '%d' ) );
			}

							$message = $deleted ? 'guess_deleted' : 'error';
							$url     = esc_url_raw( add_query_arg( 'message', $message, wp_get_referer() ) );

							wp_safe_redirect( $url );
							exit;
		}
	}
}


