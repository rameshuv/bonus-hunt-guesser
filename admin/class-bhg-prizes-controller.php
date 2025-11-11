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
                 * Register hooks for prize CRUD.
                 *
                 * @return void
                 */
                public function init() {
                        add_action( 'admin_post_bhg_prize_save', array( $this, 'save_prize' ) );
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
                 * Handle create/update submissions.
                 *
                 * @return void
                 */
                public function save_prize() {
                        $this->ensure_permission();

                        check_admin_referer( 'bhg_prize_save', 'bhg_prize_nonce' );

                        $redirect = BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' );

                        $id       = isset( $_POST['prize_id'] ) ? absint( wp_unslash( $_POST['prize_id'] ) ) : 0;
                        $category = isset( $_POST['category'] ) ? sanitize_key( wp_unslash( $_POST['category'] ) ) : 'various';

                        $image_large = 0;
                        if ( isset( $_POST['image_large'] ) ) {
                                $image_large = absint( wp_unslash( $_POST['image_large'] ) );
                        } elseif ( isset( $_POST['image_big'] ) ) {
                                $image_large = absint( wp_unslash( $_POST['image_big'] ) );
                        }

                        $data = array(
                                'title'                => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
                                'description'          => isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '',
                                'category'             => $category,
                                'link_url'             => isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '',
                                'link_target'          => isset( $_POST['link_target'] ) ? BHG_Prizes::sanitize_link_target( wp_unslash( $_POST['link_target'] ), '_self' ) : '_self',
                                'click_action'         => isset( $_POST['click_action'] ) ? BHG_Prizes::sanitize_click_action( wp_unslash( $_POST['click_action'] ), 'link' ) : 'link',
                                'category_link_url'    => isset( $_POST['category_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['category_link_url'] ) ) : '',
                                'category_link_target' => isset( $_POST['category_link_target'] ) ? BHG_Prizes::sanitize_link_target( wp_unslash( $_POST['category_link_target'] ), '_self' ) : '_self',
                                'image_small'          => isset( $_POST['image_small'] ) ? absint( wp_unslash( $_POST['image_small'] ) ) : 0,
                                'image_medium'         => isset( $_POST['image_medium'] ) ? absint( wp_unslash( $_POST['image_medium'] ) ) : 0,
                                'image_large'          => $image_large,
                                'show_title'           => isset( $_POST['show_title'] ) ? 1 : 0,
                                'show_description'     => isset( $_POST['show_description'] ) ? 1 : 0,
                                'show_category'        => isset( $_POST['show_category'] ) ? 1 : 0,
                                'show_image'           => isset( $_POST['show_image'] ) ? 1 : 0,
                                'css_settings'         => array(
                                        'border'       => isset( $_POST['css_border'] ) ? wp_unslash( $_POST['css_border'] ) : '',
                                        'border_color' => isset( $_POST['css_border_color'] ) ? wp_unslash( $_POST['css_border_color'] ) : '',
                                        'padding'      => isset( $_POST['css_padding'] ) ? wp_unslash( $_POST['css_padding'] ) : '',
                                        'margin'       => isset( $_POST['css_margin'] ) ? wp_unslash( $_POST['css_margin'] ) : '',
                                        'background'   => isset( $_POST['css_background'] ) ? wp_unslash( $_POST['css_background'] ) : '',
                                ),
                                'active'               => isset( $_POST['active'] ) ? 1 : 0,
                        );

                        $result = BHG_Prizes::save_prize( $data, $id );

                        if ( false === $result ) {
                                wp_safe_redirect( add_query_arg( 'bhg_msg', 'p_error', $redirect ) );
                                exit;
                        }

                        $message = $id ? 'p_updated' : 'p_saved';

                        wp_safe_redirect( add_query_arg( 'bhg_msg', $message, $redirect ) );
                        exit;
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
