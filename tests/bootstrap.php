<?php
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'esc_sql' ) ) {
    function esc_sql( $string ) {
        return $string;
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) {
        return json_encode( $data );
    }
}

if ( ! function_exists( 'get_current_user_id' ) ) {
    function get_current_user_id() {
        return 0;
    }
}

if ( ! function_exists( 'sanitize_title' ) ) {
    function sanitize_title( $title ) {
        $title = strtolower( (string) $title );

        return preg_replace( '/[^a-z0-9_\-]+/', '-', trim( $title ) );
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $hook, $value ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        return $value;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
        // Filters are not executed during unit tests.
        return true;
    }
}

if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        return '2024-01-01 00:00:00';
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return is_string( $str ) ? trim( $str ) : $str;
    }
}

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return $value;
    }
}

if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $url ) {
        return $url;
    }
}

if ( ! function_exists( 'wp_parse_url' ) ) {
    function wp_parse_url( $url, $component = -1 ) {
        return parse_url( $url, $component );
    }
}

if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '' ) {
        $path = ltrim( (string) $path, '/' );

        return 'https://example.com/' . $path;
    }
}

if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        return false;
    }
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
    function wp_doing_ajax() {
        return false;
    }
}

if ( ! function_exists( 'wp_doing_cron' ) ) {
    function wp_doing_cron() {
        return false;
    }
}

if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        $key = strtolower( (string) $key );

        return preg_replace( '/[^a-z0-9_\-]/', '', $key );
    }
}

if ( ! function_exists( 'number_format_i18n' ) ) {
    function number_format_i18n( $number, $decimals = 0 ) {
        return number_format( (float) $number, (int) $decimals );
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $name, $default = false ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        return $default;
    }
}

if ( ! function_exists( 'wp_cache_get' ) ) {
    function wp_cache_get( $key, $group = '' ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        return false;
    }
}

if ( ! function_exists( 'wp_cache_set' ) ) {
    function wp_cache_set( $key, $value, $group = '', $expire = 0 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
        return true;
    }
}

if ( ! function_exists( 'wp_cache_delete' ) ) {
    function wp_cache_delete( $key, $group = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
        return true;
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

require_once __DIR__ . '/support/class-mock-wpdb.php';

global $wpdb;
$wpdb = new MockWPDB();

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/class-bhg-models.php';
