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
     * Filter tournament result rows by the tournament's affiliate site setting.
     *
     * @param int   $tournament_id Tournament ID.
     * @param array $results       Result rows (array or objects with user_id properties).
     * @return array Filtered results honoring affiliate assignment.
     */
    public static function filter_results_by_affiliate( $tournament_id, $results ) {
        $tournament_id  = (int) $tournament_id;
        $results        = is_array( $results ) ? $results : array();
        $tournament_aff = get_post_meta( $tournament_id, 'affiliate_site', true );

        if ( '' === $tournament_aff ) {
            return $results;
        }

        $filtered = array_filter(
            $results,
            function ( $row ) use ( $tournament_aff ) {
                $user_id = 0;

                if ( is_object( $row ) && isset( $row->user_id ) ) {
                    $user_id = (int) $row->user_id;
                } elseif ( is_array( $row ) && isset( $row['user_id'] ) ) {
                    $user_id = (int) $row['user_id'];
                }

                if ( $user_id <= 0 ) {
                    return false;
                }

                $user_aff = get_user_meta( $user_id, 'affiliate_site', true );

                return $user_aff === $tournament_aff;
            }
        );

        return array_values( $filtered );
    }

    /**
     * Initialize hooks.
     *
     * @return void
     */
    public static function init() {
        // Default period logic removed; start and end dates define scope.
    }
}
