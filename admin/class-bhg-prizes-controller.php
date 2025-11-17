<?php
/**
 * Admin controller for managing prizes.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! class_exists( 'BHG_Prizes_Controller' ) ) {
		/**
		 * Handles CRUD interactions for prizes.
		 */
	class BHG_Prizes_Controller {
			/**
			 * Singleton instance.
			 *
			 * @var BHG_Prizes_Controller|null
			 */
		private static $instance = null;

			/**
			 * Retrieve singleton instance.
			 *
			 * @return BHG_Prizes_Controller
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
			 * Register hooks for prize management actions handled by this controller.
			 *
			 * @return void
			 */
		public function init() {
				add_action( 'admin_post_bhg_prize_delete', array( $this, 'delete_prize' ) );
				add_action( 'wp_ajax_bhg_get_prize', array( $this, 'ajax_get_prize' ) );
		}

			/**
			 * Determine if the current user can manage prizes.
			 *
			 * @return bool
			 */
		protected function current_user_can_manage() {
				$capability = apply_filters( 'bhg_manage_prizes_capability', 'manage_options' );

				return current_user_can( $capability );
		}

			/**
			 * Ensure the current user has permission to manage prizes.
			 *
			 * @return void
			 */
		protected function ensure_permission() {
			if ( $this->current_user_can_manage() ) {
					return;
			}

				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
		}

			/**
			 * Handle prize deletions.
			 *
			 * @return void
			 */
		public function delete_prize() {
				$this->ensure_permission();

				check_admin_referer( 'bhg_prize_delete', 'bhg_prize_delete_nonce' );

				$id = isset( $_POST['prize_id'] ) ? absint( wp_unslash( $_POST['prize_id'] ) ) : 0;

			if ( $id ) {
					BHG_Prizes::delete_prize( $id );
			}

				wp_safe_redirect( add_query_arg( 'bhg_msg', 'p_deleted', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
				exit;
		}

			/**
			 * Provide prize data via AJAX.
			 *
			 * @return void
			 */
		public function ajax_get_prize() {
			if ( ! $this->current_user_can_manage() ) {
					wp_send_json_error( array( 'message' => __( 'You are not allowed to view this prize.', 'bonus-hunt-guesser' ) ), 403 );
			}

				check_ajax_referer( 'bhg_get_prize', 'nonce' );

				$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;

			if ( ! $id ) {
					wp_send_json_error( array( 'message' => __( 'Invalid prize ID supplied.', 'bonus-hunt-guesser' ) ), 400 );
			}

				$prize = BHG_Prizes::get_prize( $id );

			if ( ! $prize ) {
					wp_send_json_error( array( 'message' => __( 'Prize not found.', 'bonus-hunt-guesser' ) ), 404 );
			}

				wp_send_json_success( BHG_Prizes::format_prize_for_response( $prize ) );
		}
	}
}
