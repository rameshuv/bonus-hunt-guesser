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
     * @param int        $tournament_id          Tournament ID.
     * @param array      $results                Result rows (array or objects with user_id properties).
     * @param int        $affiliate_site_id      Optional affiliate website ID tied to the tournament record.
     * @param string|int $affiliate_site_setting Optional affiliate site setting stored as meta.
     * @return array Filtered results honoring affiliate assignment.
     */
    public static function filter_results_by_affiliate( $tournament_id, $results, $affiliate_site_id = 0, $affiliate_site_setting = null ) {
        global $wpdb;

        $tournament_id   = (int) $tournament_id;
        $results         = is_array( $results ) ? $results : array();
        $affiliate_site  = (int) $affiliate_site_id;
        $affiliate_meta  = $affiliate_site_setting;

        if ( $affiliate_site <= 0 ) {
            $affiliate_site = (int) get_post_meta( $tournament_id, 'affiliate_site_id', true );

            if ( $affiliate_site <= 0 && $wpdb instanceof wpdb ) {
                $tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

                if ( $tournaments_table ) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $affiliate_site = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT affiliate_site_id FROM ' . $tournaments_table . ' WHERE id = %d', $tournament_id ) );
                }
            }
        }

        if ( null === $affiliate_meta ) {
            $affiliate_meta = get_post_meta( $tournament_id, 'affiliate_site', true );
        }

        if ( $affiliate_site <= 0 && '' === $affiliate_meta ) {
            return $results;
        }

        $filtered_results = array();

        foreach ( $results as $row ) {
            $user_id = 0;

            if ( is_object( $row ) && isset( $row->user_id ) ) {
                $user_id = (int) $row->user_id;
            } elseif ( is_array( $row ) && isset( $row['user_id'] ) ) {
                $user_id = (int) $row['user_id'];
            }

            if ( $user_id <= 0 ) {
                continue;
            }

            if ( $affiliate_site > 0 && function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
                if ( bhg_is_user_affiliate_for_site( $user_id, $affiliate_site ) ) {
                    $filtered_results[] = $row;
                }
                continue;
            }

            if ( '' !== $affiliate_meta ) {
                $user_affiliate = get_user_meta( $user_id, 'affiliate_site', true );

                if ( $user_affiliate === $affiliate_meta ) {
                    $filtered_results[] = $row;
                }
            }
        }

        return $filtered_results;
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
