<?php
/**
 * Tournaments controller for Bonus Hunt Guesser.
 *
 * Previously applied default tournament settings during creation. The default
 * period logic has been removed since start and end dates define scope.
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
                // Reserved for future hooks.
        }

        /**
         * Sanitize a list of submitted prize IDs.
         *
         * @param mixed $input Raw input from the request.
         * @return int[] Normalized prize IDs.
         */
        public static function sanitize_prize_ids( $input ) {
                $ids = array();

                if ( is_array( $input ) ) {
                        foreach ( $input as $value ) {
                                $value = absint( $value );
                                if ( $value > 0 ) {
                                        $ids[ $value ] = $value;
                                }
                        }
                } elseif ( null !== $input && '' !== $input ) {
                        $value = absint( $input );
                        if ( $value > 0 ) {
                                $ids[ $value ] = $value;
                        }
                }

                return array_values( $ids );
        }

        /**
         * Validate and sanitize an affiliate URL.
         *
         * @param string $url Raw URL string.
         * @return array{url:string,error:bool} Sanitized URL and error flag.
         */
        public static function sanitize_affiliate_url( $url ) {
                $raw = is_string( $url ) ? trim( $url ) : '';

                if ( '' === $raw ) {
                        return array(
                                'url'   => '',
                                'error' => false,
                        );
                }

                $sanitized = esc_url_raw( $raw );
                if ( '' === $sanitized ) {
                        return array(
                                'url'   => '',
                                'error' => true,
                        );
                }

                if ( function_exists( 'wp_http_validate_url' ) && ! wp_http_validate_url( $sanitized ) ) {
                        return array(
                                'url'   => '',
                                'error' => true,
                        );
                }

                return array(
                        'url'   => $sanitized,
                        'error' => false,
                );
        }

        /**
         * Retrieve affiliate websites for selection fields.
         *
         * @return array
         */
        public static function get_affiliate_sites() {
                if ( ! class_exists( 'BHG_DB' ) ) {
                        return array();
                }

                $db = new BHG_DB();

                return $db->get_affiliate_websites();
        }

        /**
         * Retrieve available prizes for selection fields.
         *
         * @return array
         */
        public static function get_prize_options() {
                if ( ! class_exists( 'BHG_Prizes' ) ) {
                        return array();
                }

                return BHG_Prizes::get_prizes();
        }

        /**
         * Retrieve selected prize IDs for a tournament.
         *
         * @param int $tournament_id Tournament identifier.
         * @return int[]
         */
        public static function get_selected_prize_ids( $tournament_id ) {
                if ( ! class_exists( 'BHG_Prizes' ) ) {
                        return array();
                }

                return BHG_Prizes::get_tournament_prize_ids( $tournament_id );
        }

        /**
         * Persist prize relationships for a tournament.
         *
         * @param int   $tournament_id Tournament identifier.
         * @param int[] $prize_ids     Prize IDs.
         * @return void
         */
        public static function save_prize_links( $tournament_id, $prize_ids ) {
                if ( ! class_exists( 'BHG_Prizes' ) ) {
                        return;
                }

                BHG_Prizes::set_tournament_prizes( $tournament_id, $prize_ids );
        }

        /**
         * Provide context for the tournaments admin form.
         *
         * @param int $tournament_id Optional tournament ID.
         * @return array
         */
        public static function get_admin_form_context( $tournament_id = 0 ) {
                $tournament_id = absint( $tournament_id );

                return array(
                        'affiliate_sites'    => self::get_affiliate_sites(),
                        'prize_rows'         => self::get_prize_options(),
                        'selected_prize_ids' => $tournament_id > 0 ? self::get_selected_prize_ids( $tournament_id ) : array(),
                );
        }
}
