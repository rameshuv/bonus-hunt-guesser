<?php
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'esc_sql' ) ) {
    function esc_sql( $string ) {
        return $string;
    }
}

if ( ! isset( $GLOBALS['bhg_test_options'] ) ) {
    $GLOBALS['bhg_test_options'] = array();
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $name, $default = false ) {
        global $bhg_test_options;

        return array_key_exists( $name, $bhg_test_options ) ? $bhg_test_options[ $name ] : $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $name, $value ) {
        global $bhg_test_options;

        $bhg_test_options[ $name ] = $value;

        return true;
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

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    function wp_strip_all_tags( $string ) {
        return strip_tags( (string) $string );
    }
}

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        if ( is_array( $value ) ) {
            return array_map( 'wp_unslash', $value );
        }

        return stripslashes( (string) $value );
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        $filtered = wp_strip_all_tags( (string) $str );
        $filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );

        return trim( $filtered );
    }
}

if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $email ) {
        $email = filter_var( (string) $email, FILTER_SANITIZE_EMAIL );

        return is_string( $email ) ? strtolower( trim( $email ) ) : '';
    }
}

if ( ! function_exists( 'is_email' ) ) {
    function is_email( $email ) {
        return false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        if ( is_object( $args ) ) {
            $args = get_object_vars( $args );
        }

        return array_merge( (array) $defaults, (array) $args );
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $string ) {
        $string = preg_replace( '#<script[^>]*>.*?</script>#is', '', (string) $string );

        return strip_tags( $string, '<p><a><br><strong><em><ul><ol><li><span><div><blockquote><code>' );
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
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
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/support/class-mock-wpdb.php';
