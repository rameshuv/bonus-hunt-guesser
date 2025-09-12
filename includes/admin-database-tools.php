<?php
/**
 * Database maintenance tools for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'bhg_database_cleanup' ) ) {
    /**
     * Truncate all plugin tables and reinsert demo data.
     */
    function bhg_database_cleanup() {
        global $wpdb;

        $tables = array(
            esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' ),
            esc_sql( $wpdb->prefix . 'bhg_guesses' ),
            esc_sql( $wpdb->prefix . 'bhg_tournaments' ),
            esc_sql( $wpdb->prefix . 'bhg_tournament_results' ),
            esc_sql( $wpdb->prefix . 'bhg_translations' ),
            esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' ),
            esc_sql( $wpdb->prefix . 'bhg_hunt_winners' ),
            esc_sql( $wpdb->prefix . 'bhg_ads' ),
        );

        foreach ( $tables as $table ) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
                $wpdb->query( "TRUNCATE TABLE {$table}" );
            }
        }

        bhg_insert_demo_data();
    }
}

if ( ! function_exists( 'bhg_database_optimize' ) ) {
    /**
     * Optimize all plugin tables.
     */
    function bhg_database_optimize() {
        global $wpdb;

        $tables = array(
            esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' ),
            esc_sql( $wpdb->prefix . 'bhg_guesses' ),
            esc_sql( $wpdb->prefix . 'bhg_tournaments' ),
            esc_sql( $wpdb->prefix . 'bhg_tournament_results' ),
            esc_sql( $wpdb->prefix . 'bhg_translations' ),
            esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' ),
            esc_sql( $wpdb->prefix . 'bhg_hunt_winners' ),
            esc_sql( $wpdb->prefix . 'bhg_ads' ),
        );

        foreach ( $tables as $table ) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
                $wpdb->query( "OPTIMIZE TABLE {$table}" );
            }
        }
    }
}

if ( ! function_exists( 'bhg_insert_demo_data' ) ) {
    /**
     * Insert basic demo data.
     */
    function bhg_insert_demo_data() {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'bhg_bonus_hunts',
            array(
                'title'            => 'Demo Bonus Hunt',
                'starting_balance' => 2000,
                'num_bonuses'      => 10,
                'status'           => 'active',
                'created_at'       => current_time( 'mysql' ),
            ),
            array( '%s', '%d', '%d', '%s', '%s' )
        );
    }
}

