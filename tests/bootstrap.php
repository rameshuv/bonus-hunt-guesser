<?php
// phpcs:ignoreFile -- Legacy test bootstrap uses simplified WordPress shims for unit tests.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'esc_sql' ) ) {
    function esc_sql( $string ) {
        return $string;
    }
}

if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        return '2024-01-01 00:00:00';
    }
}

if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        $key = strtolower( (string) $key );

        return preg_replace( '/[^a-z0-9_\-]/', '', $key );
    }
}

if ( ! function_exists( 'absint' ) ) {
    function absint( $value ) {
        return abs( (int) $value );
    }
}

if ( ! function_exists( 'bhg_get_hunt_tournament_ids' ) ) {
    function bhg_get_hunt_tournament_ids( $hunt_id ) {
        global $wpdb;

        if ( ! isset( $wpdb->hunt_tournaments ) ) {
            return array();
        }

        $ids = array();
        foreach ( $wpdb->hunt_tournaments as $map ) {
            if ( (int) $map['hunt_id'] === (int) $hunt_id ) {
                $ids[] = (int) $map['tournament_id'];
            }
        }

        return array_values( array_unique( $ids ) );
    }
}

if ( ! function_exists( 'bhg_log' ) ) {
    function bhg_log( $message ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        // Intentionally left blank for tests.
    }
}

if ( ! function_exists( 'wp_list_pluck' ) ) {
    function wp_list_pluck( $input_list, $field ) {
        $values = array();

        foreach ( (array) $input_list as $item ) {
            if ( is_object( $item ) && isset( $item->{$field} ) ) {
                $values[] = $item->{$field};
            } elseif ( is_array( $item ) && isset( $item[ $field ] ) ) {
                $values[] = $item[ $field ];
            }
        }

        return $values;
    }
}

if ( ! class_exists( 'BHG_DB' ) ) {
    class BHG_DB {
        public static function migrate() {}
    }
}

require_once __DIR__ . '/../includes/class-bhg-models.php';
require_once __DIR__ . '/support/class-mock-wpdb.php';
