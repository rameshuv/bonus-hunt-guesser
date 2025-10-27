<?php
// phpcs:ignoreFile -- Test bootstrap uses simplified stubs for WordPress APIs.
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

if ( ! function_exists( 'sanitize_title' ) ) {
    function sanitize_title( $title ) {
        $title = strtolower( preg_replace( '/[^a-z0-9\s\-]/', '', (string) $title ) );

        $title = preg_replace( '/\s+/', '-', trim( $title ) );

        return $title;
    }
}

if ( ! function_exists( 'get_current_user_id' ) ) {
    function get_current_user_id() {
        return 0;
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) {
        return json_encode( $data );
    }
}

if ( ! function_exists( 'number_format_i18n' ) ) {
    function number_format_i18n( $number, $decimals = 0 ) {
        return number_format( $number, $decimals, '.', ',' );
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

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! isset( $GLOBALS['wpdb'] ) ) {
    class BHG_Test_WPDB {
        public $prefix = 'wp_';
        public $insert_id = 0;

        public function prepare( $query, ...$args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
            return $query;
        }

        public function get_var( $query ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
            return null;
        }

        public function query( $query ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
            return 0;
        }

        public function insert( $table, $data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
            $this->insert_id = 1;

            return true;
        }

        public function get_results( $query ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
            return array();
        }
    }

    $GLOBALS['wpdb'] = new BHG_Test_WPDB();
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $hook_name, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        return $value;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        return true;
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        return true;
    }
}

if ( ! function_exists( 'do_action' ) ) {
    function do_action( $hook_name, ...$args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        // No-op for unit tests.
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        if ( is_object( $args ) ) {
            $args = get_object_vars( $args );
        }

        if ( ! is_array( $args ) ) {
            $args = array();
        }

        return array_merge( $defaults, $args );
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $name, $default = false ) {
        global $bhg_test_options;

        if ( ! is_array( $bhg_test_options ) ) {
            $bhg_test_options = array();
        }

        return array_key_exists( $name, $bhg_test_options ) ? $bhg_test_options[ $name ] : $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $name, $value ) {
        global $bhg_test_options;

        if ( ! is_array( $bhg_test_options ) ) {
            $bhg_test_options = array();
        }

        $bhg_test_options[ $name ] = $value;

        return true;
    }
}

if ( ! function_exists( 'add_option' ) ) {
    function add_option( $name, $value ) {
        global $bhg_test_options;

        if ( ! is_array( $bhg_test_options ) ) {
            $bhg_test_options = array();
        }

        if ( array_key_exists( $name, $bhg_test_options ) ) {
            return false;
        }

        $bhg_test_options[ $name ] = $value;

        return true;
    }
}

if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $name ) {
        global $bhg_test_options;

        if ( ! is_array( $bhg_test_options ) ) {
            $bhg_test_options = array();
        }

        unset( $bhg_test_options[ $name ] );

        return true;
    }
}

if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $email ) {
        $filtered = filter_var( $email, FILTER_SANITIZE_EMAIL );

        return is_string( $filtered ) ? trim( $filtered ) : '';
    }
}

if ( ! function_exists( 'is_email' ) ) {
    function is_email( $email ) {
        return false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
}

if ( ! function_exists( 'get_bloginfo' ) ) {
    function get_bloginfo( $show ) {
        if ( 'admin_email' === $show ) {
            return 'admin@example.com';
        }

        return '';
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

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/class-bhg-models.php';
require_once __DIR__ . '/support/class-mock-wpdb.php';
