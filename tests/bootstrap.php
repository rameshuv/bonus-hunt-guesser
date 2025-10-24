<?php
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'esc_sql' ) ) {
    function esc_sql( $string ) {
        return $string;
    }
}

if ( ! function_exists( 'add_shortcode' ) ) {
    function add_shortcode( $tag, $callback ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInFunction
        if ( ! isset( $GLOBALS['shortcode_tags'] ) || ! is_array( $GLOBALS['shortcode_tags'] ) ) {
            $GLOBALS['shortcode_tags'] = array();
        }

        $GLOBALS['shortcode_tags'][ $tag ] = $callback;
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInFunction
        if ( ! isset( $GLOBALS['wp_actions'] ) || ! is_array( $GLOBALS['wp_actions'] ) ) {
            $GLOBALS['wp_actions'] = array();
        }

        if ( ! isset( $GLOBALS['wp_actions'][ $hook ] ) || ! is_array( $GLOBALS['wp_actions'][ $hook ] ) ) {
            $GLOBALS['wp_actions'][ $hook ] = array();
        }

        $GLOBALS['wp_actions'][ $hook ][ $priority ][] = $callback;
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

if ( ! function_exists( 'bhg_get_points_for_position' ) ) {
    function bhg_get_points_for_position( $position ) {
        $defaults = array(
            1 => 25,
            2 => 15,
            3 => 10,
            4 => 5,
            5 => 4,
            6 => 3,
            7 => 2,
            8 => 1,
        );

        $position = max( 1, (int) $position );

        return isset( $defaults[ $position ] ) ? $defaults[ $position ] : 0;
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

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_html_x' ) ) {
    function esc_html_x( $text, $context = '', $domain = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInFunction
        unset( $context, $domain );

        return esc_html( $text );
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return filter_var( $url, FILTER_SANITIZE_URL );
    }
}

if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $url ) {
        return filter_var( $url, FILTER_SANITIZE_URL );
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $text ) {
        return $text;
    }
}

if ( ! function_exists( 'wpautop' ) ) {
    function wpautop( $pee ) {
        $pee = str_replace( array( "\r\n", "\r" ), "\n", (string) $pee );
        $paragraphs = array_filter( array_map( 'trim', explode( "\n\n", $pee ) ) );

        if ( empty( $paragraphs ) ) {
            return '';
        }

        $output = '';
        foreach ( $paragraphs as $p ) {
            $output .= '<p>' . $p . '</p>';
        }

        return $output;
    }
}

if ( ! function_exists( 'bhg_t' ) ) {
    function bhg_t( $slug, $default_text = '', $locale = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInFunction
        unset( $slug, $locale );

        return $default_text;
    }
}

if ( ! class_exists( 'BHG_DB' ) ) {
    class BHG_DB {
        public static function migrate() {}
    }
}

require_once __DIR__ . '/../includes/class-bhg-prizes.php';
require_once __DIR__ . '/../includes/class-bhg-models.php';
require_once __DIR__ . '/../includes/class-bhg-shortcodes.php';
require_once __DIR__ . '/support/class-mock-wpdb.php';
