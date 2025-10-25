<?php
/**
 * Plugin Name: Bonus Hunt Guesser
 * Plugin URI: https://yourdomain.com/
 * Description: Comprehensive bonus hunt management system with tournaments, leaderboards, and user guessing functionality
 * Version: 8.0.14
 * Requires at least: 6.3.0
 * Requires PHP: 7.4
 * Author: Bonus Hunt Guesser Development Team
 * Text Domain: bonus-hunt-guesser
 * Domain Path: /languages
 * License: GPLv2 or later
 * Requires MySQL: 5.5.5
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Helper: parse human-entered money-like strings into float.
if ( ! function_exists( 'bhg_parse_amount' ) ) {
	/**
	 * Parse a human-entered money-like string into a float.
	 *
	 * @param string $s Raw amount string.
	 * @return float|null Parsed float value or null on failure.
	 */
	function bhg_parse_amount( $s ) {
		if ( ! is_string( $s ) ) {
			return null;
		}
				// Normalize unicode spaces (NBSP / NNBSP) and trim.
		$s = str_replace( array( "\xc2\xa0", "\xe2\x80\xaf" ), ' ', $s );
		$s = trim( wp_unslash( $s ) );
		if ( '' === $s ) {
			return null;
		}

				// Remove currency symbols/letters while keeping digits, separators, minus.
		$s = preg_replace( '/[^\d,\.\-\s]/u', '', $s );
		$s = str_replace( ' ', '', $s );

				$has_comma = false !== strpos( $s, ',' );
				$has_dot   = false !== strpos( $s, '.' );

		if ( $has_comma && $has_dot ) {
						// Use the last occurring symbol as decimal separator.
			$last_comma = strrpos( $s, ',' );
			$last_dot   = strrpos( $s, '.' );
			if ( false !== $last_comma && ( false === $last_dot || $last_comma > $last_dot ) ) {
					// Comma as decimal.
					$s = str_replace( '.', '', $s );  // Thousands.
					$s = str_replace( ',', '.', $s ); // Decimal.
			} else {
					// Dot as decimal.
					$s = str_replace( ',', '', $s );
			}
		} elseif ( $has_comma ) {
				// Only comma present.
				$last = strrpos( $s, ',' );
				$frac = substr( $s, $last + 1 );
			if ( ctype_digit( $frac ) && strlen( $frac ) >= 1 && strlen( $frac ) <= 2 ) {
						// Treat as decimal.
						$s = str_replace( ',', '.', $s );
			} else {
							// Treat as thousands (incl. Indian grouping).
							$s = str_replace( ',', '', $s );
			}
		} elseif ( $has_dot ) {
				// Only dot present.
				$last = strrpos( $s, '.' );
				$frac = substr( $s, $last + 1 );
			if ( ctype_digit( $frac ) && strlen( $frac ) > 3 ) {
						// Likely thousands separators → remove all dots.
						$s = str_replace( '.', '', $s );
			}
		}

				// Keep only digits, one leading minus, and dots.
				$s = preg_replace( '/[^0-9\.\-]/', '', $s );
		if ( '' === $s || '-' === $s || '.' === $s || '-.' === $s || '.-' === $s ) {
				return null;
		}

				// Collapse multiple dots to a single decimal point (keep first as decimal).
				$parts = explode( '.', $s );
		if ( count( $parts ) > 2 ) {
				$s = $parts[0] . '.' . implode( '', array_slice( $parts, 1 ) );
		}

		if ( is_numeric( $s ) ) {
				return (float) $s;
		}

				// Permissive fallback: first number pattern.
		if ( preg_match( '/\d+(?:\.\d+)?/', $s, $m2 ) ) {
				return (float) $m2[0];
		}

		return null;
	}
}

if ( ! function_exists( 'bhg_sanitize_tournament_id' ) ) {

/**
 * Sanitize a tournament ID value.
 *
 * @param mixed $tid Raw tournament ID.
 * @return int Sanitized ID.
 */
function bhg_sanitize_tournament_id( $tid ) {
return max( 0, absint( $tid ) );
}
}

if ( ! function_exists( 'bhg_sanitize_tournament_ids' ) ) {
/**
 * Sanitize a list of tournament IDs.
 *
 * @param mixed $ids Raw IDs or array of IDs.
 * @return int[] Sanitized, unique IDs.
 */
function bhg_sanitize_tournament_ids( $ids ) {
$ids        = is_array( $ids ) ? $ids : array( $ids );
$normalized = array();

foreach ( $ids as $id ) {
$id = bhg_sanitize_tournament_id( $id );
if ( $id > 0 ) {
$normalized[ $id ] = $id;
}
}

return array_values( $normalized );
}
}

if ( ! function_exists( 'bhg_get_default_tournament_points' ) ) {
        /**
         * Default tournament points mapping for the first eight placements.
         *
         * @return array<int,int> Default points per placement.
         */
        function bhg_get_default_tournament_points() {
                return array(
                        1 => 25,
                        2 => 15,
                        3 => 10,
                        4 => 5,
                        5 => 4,
                        6 => 3,
                        7 => 2,
                        8 => 1,
                );
        }
}

if ( ! function_exists( 'bhg_normalize_tournament_points' ) ) {
        /**
         * Normalize a points configuration array.
         *
         * @param array $points Raw points array keyed by placement.
         * @param int   $max    Maximum placement slots to consider.
         *
         * @return array<int,int> Normalized points mapping.
         */
        function bhg_normalize_tournament_points( $points, $max = 25 ) {
                $defaults   = bhg_get_default_tournament_points();
                $normalized = array();

                if ( ! is_array( $points ) ) {
                        $points = array();
                }

                for ( $i = 1; $i <= $max; $i++ ) {
                        if ( isset( $points[ $i ] ) ) {
                                $value = $points[ $i ];
                        } elseif ( isset( $points[ (string) $i ] ) ) {
                                $value = $points[ (string) $i ];
                        } elseif ( isset( $defaults[ $i ] ) ) {
                                $value = $defaults[ $i ];
                        } else {
                                $value = 0;
                        }

                        $normalized[ $i ] = max( 0, (int) $value );
                }

                return $normalized;
        }
}

if ( ! function_exists( 'bhg_get_tournament_points_settings' ) ) {
        /**
         * Retrieve the configured tournament points mapping.
         *
         * @return array<int,int> Points per placement.
         */
        function bhg_get_tournament_points_settings() {
                $settings = get_option( 'bhg_plugin_settings', array() );
                $points   = isset( $settings['tournament_points'] ) ? $settings['tournament_points'] : array();

                return bhg_normalize_tournament_points( $points );
        }
}

if ( ! function_exists( 'bhg_get_points_for_position' ) ) {
        /**
         * Get the configured points for a specific placement.
         *
         * @param int $position Placement (1-indexed).
         *
         * @return int Points awarded for the placement.
         */
        function bhg_get_points_for_position( $position ) {
                $position = (int) $position;
                if ( $position <= 0 ) {
                        return 0;
                }

                $points = bhg_get_tournament_points_settings();

                return isset( $points[ $position ] ) ? (int) $points[ $position ] : 0;
        }
}

if ( ! function_exists( 'bhg_get_default_design_settings' ) ) {
        /**
         * Default design tokens for shortcode styling.
         *
         * @return array<string,string> Default values (empty string indicates no override).
         */
        function bhg_get_default_design_settings() {
                return array(
                        'title_block_background' => '',
                        'title_block_radius'     => '',
                        'title_block_padding'    => '',
                        'title_block_margin'     => '',
                        'h2_font_size'           => '',
                        'h2_font_weight'         => '',
                        'h2_color'               => '',
                        'h2_padding'             => '',
                        'h2_margin'              => '',
                        'h3_font_size'           => '',
                        'h3_font_weight'         => '',
                        'h3_color'               => '',
                        'h3_padding'             => '',
                        'h3_margin'              => '',
                        'description_font_size'  => '',
                        'description_font_weight'=> '',
                        'description_color'      => '',
                        'description_padding'    => '',
                        'description_margin'     => '',
                        'body_font_size'         => '',
                        'body_padding'           => '',
                        'body_margin'            => '',
                );
        }
}

if ( ! function_exists( 'bhg_get_design_settings' ) ) {
        /**
         * Retrieve shortcode design settings merged with defaults.
         *
         * @return array<string,string> Design tokens.
         */
        function bhg_get_design_settings() {
                $settings = get_option( 'bhg_plugin_settings', array() );
                $design   = isset( $settings['design'] ) && is_array( $settings['design'] ) ? $settings['design'] : array();

                return array_merge( bhg_get_default_design_settings(), $design );
        }
}

if ( ! function_exists( 'bhg_sanitize_measurement_value' ) ) {
        /**
         * Sanitize a CSS measurement string (padding, margin, font-size).
         *
         * @param string $value Raw value.
         * @param bool   $allow_percent Whether percentages are allowed.
         *
         * @return string Sanitized value or empty string on failure.
         */
        function bhg_sanitize_measurement_value( $value, $allow_percent = true ) {
                $value = is_string( $value ) ? trim( $value ) : '';
                if ( '' === $value ) {
                        return '';
                }

                $parts = preg_split( '/\s+/', $value );
                if ( empty( $parts ) ) {
                        return '';
                }

                $allowed_units = $allow_percent ? '(px|em|rem|%|vh|vw)?' : '(px|em|rem|vh|vw)?';
                foreach ( $parts as $part ) {
                        if ( ! preg_match( '/^-?\d+(?:\.\d+)?' . $allowed_units . '$/i', $part ) ) {
                                return '';
                        }
                }

                return implode( ' ', $parts );
        }
}

if ( ! function_exists( 'bhg_sanitize_font_weight_value' ) ) {
        /**
         * Sanitize a CSS font-weight value.
         *
         * @param string $value Raw value.
         *
         * @return string Sanitized value or empty string.
         */
        function bhg_sanitize_font_weight_value( $value ) {
                $value = is_string( $value ) ? trim( strtolower( $value ) ) : '';
                if ( '' === $value ) {
                        return '';
                }

                $allowed_keywords = array( 'normal', 'bold', 'bolder', 'lighter' );
                if ( in_array( $value, $allowed_keywords, true ) ) {
                        return $value;
                }

                if ( preg_match( '/^(?:[1-9]00)$/', $value ) ) {
                        return $value;
                }

                return '';
        }
}

if ( ! function_exists( 'bhg_sanitize_color_value' ) ) {
        /**
         * Sanitize a color value, allowing hex colors or known keywords.
         *
         * @param string $value Raw value.
         *
         * @return string Sanitized value or empty string.
         */
        function bhg_sanitize_color_value( $value ) {
                $value = is_string( $value ) ? trim( $value ) : '';
                if ( '' === $value ) {
                        return '';
                }

                $hex = sanitize_hex_color( $value );
                if ( $hex ) {
                        return $hex;
                }

                $keywords = array( 'transparent', 'inherit', 'initial', 'currentcolor' );
                if ( in_array( strtolower( $value ), $keywords, true ) ) {
                        return strtolower( $value );
                }

                return '';
        }
}

if ( ! function_exists( 'bhg_output_design_tokens' ) ) {
        /**
         * Output inline CSS variables for configured design settings.
         *
         * @return void
         */
        function bhg_output_design_tokens() {
                if ( is_admin() ) {
                        return;
                }

                $design = bhg_get_design_settings();
                $map    = array(
                        'title_block_background' => '--bhg-title-block-background',
                        'title_block_radius'     => '--bhg-title-block-radius',
                        'title_block_padding'    => '--bhg-title-block-padding',
                        'title_block_margin'     => '--bhg-title-block-margin',
                        'h2_font_size'           => '--bhg-h2-font-size',
                        'h2_font_weight'         => '--bhg-h2-font-weight',
                        'h2_color'               => '--bhg-h2-color',
                        'h2_padding'             => '--bhg-h2-padding',
                        'h2_margin'              => '--bhg-h2-margin',
                        'h3_font_size'           => '--bhg-h3-font-size',
                        'h3_font_weight'         => '--bhg-h3-font-weight',
                        'h3_color'               => '--bhg-h3-color',
                        'h3_padding'             => '--bhg-h3-padding',
                        'h3_margin'              => '--bhg-h3-margin',
                        'description_font_size'  => '--bhg-description-font-size',
                        'description_font_weight'=> '--bhg-description-font-weight',
                        'description_color'      => '--bhg-description-color',
                        'description_padding'    => '--bhg-description-padding',
                        'description_margin'     => '--bhg-description-margin',
                        'body_font_size'         => '--bhg-body-font-size',
                        'body_padding'           => '--bhg-body-padding',
                        'body_margin'            => '--bhg-body-margin',
                );

                $css_vars = array();

                foreach ( $map as $key => $var ) {
                        if ( ! isset( $design[ $key ] ) ) {
                                continue;
                        }

                        $value = $design[ $key ];

                        switch ( $key ) {
                                case 'title_block_background':
                                case 'h2_color':
                                case 'h3_color':
                                case 'description_color':
                                        $value = bhg_sanitize_color_value( $value );
                                        break;
                                case 'title_block_radius':
                                case 'body_font_size':
                                case 'body_padding':
                                case 'body_margin':
                                        $value = bhg_sanitize_measurement_value( $value );
                                        break;
                                case 'title_block_padding':
                                case 'title_block_margin':
                                case 'h2_padding':
                                case 'h2_margin':
                                case 'h3_padding':
                                case 'h3_margin':
                                case 'description_padding':
                                case 'description_margin':
                                        $value = bhg_sanitize_measurement_value( $value );
                                        break;
                                case 'h2_font_size':
                                case 'h3_font_size':
                                case 'description_font_size':
                                        $value = bhg_sanitize_measurement_value( $value, false );
                                        break;
                                case 'h2_font_weight':
                                case 'h3_font_weight':
                                case 'description_font_weight':
                                        $value = bhg_sanitize_font_weight_value( $value );
                                        break;
                                default:
                                        $value = is_string( $value ) ? trim( $value ) : '';
                        }

                        if ( '' === $value ) {
                                continue;
                        }

                        $css_vars[] = $var . ':' . $value;
                }

                if ( empty( $css_vars ) ) {
                        return;
                }

                printf( '<style id="bhg-design-tokens">:root{%s}</style>', esc_html( implode( ';', $css_vars ) ) );
        }
        add_action( 'wp_head', 'bhg_output_design_tokens' );
}

if ( ! function_exists( 'bhg_get_default_notifications_settings' ) ) {
        /**
         * Default notification templates for winners, tournaments, and hunts.
         *
         * @return array<string,array<string,mixed>> Default settings per notification type.
         */
        function bhg_get_default_notifications_settings() {
                return array(
                        'winners'     => array(
                                'enabled'     => 0,
                                'bcc'         => '',
                                'title'       => bhg_t( 'default_winner_subject', 'Results for {{hunt}}' ),
                                'description' => 'Hi {{username}},<br><br>' . bhg_t( 'default_winner_body', 'The bonus hunt "{{hunt}}" has finished with a final balance of {{final}}.<br><br>Congratulations to {{winner}}! Full winners: {{winners}}.<br><br>Thank you for playing!' ),
                        ),
                        'tournaments' => array(
                                'enabled'     => 0,
                                'bcc'         => '',
                                'title'       => bhg_t( 'default_tournament_subject', 'New Tournament: {{tournament}}' ),
                                'description' => bhg_t( 'default_tournament_body', '<p>A new tournament, <strong>{{tournament}}</strong>, is now available.</p><p>{{description}}</p><p>Starts: {{start}}<br>Ends: {{end}}</p>' ),
                        ),
                        'hunts'       => array(
                                'enabled'     => 0,
                                'bcc'         => '',
                                'title'       => bhg_t( 'default_hunt_subject', 'New Bonus Hunt: {{hunt}}' ),
                                'description' => bhg_t( 'default_hunt_body', '<p>The bonus hunt <strong>{{hunt}}</strong> is live.</p><p>Starting balance: {{starting_balance}}<br>Bonuses: {{bonuses}}<br>Prizes: {{prizes}}</p>' ),
                        ),
                );
        }
}

if ( ! function_exists( 'bhg_get_notifications_settings' ) ) {
        /**
         * Retrieve stored notification settings merged with defaults.
         *
         * @return array<string,array<string,mixed>> Notification settings.
         */
        function bhg_get_notifications_settings() {
                $defaults = bhg_get_default_notifications_settings();
                $stored   = get_option( 'bhg_notifications_settings', array() );
                if ( ! is_array( $stored ) ) {
                        $stored = array();
                }

                $normalized = array();
                foreach ( $defaults as $type => $default ) {
                        $current = isset( $stored[ $type ] ) && is_array( $stored[ $type ] ) ? $stored[ $type ] : array();
                        $enabled = isset( $current['enabled'] ) ? (int) $current['enabled'] : (int) $default['enabled'];
                        $bcc     = isset( $current['bcc'] ) ? sanitize_text_field( $current['bcc'] ) : $default['bcc'];
                        $title   = isset( $current['title'] ) ? wp_kses_post( $current['title'] ) : $default['title'];
                        $body    = isset( $current['description'] ) ? wp_kses_post( $current['description'] ) : $default['description'];

                        $normalized[ $type ] = array(
                                'enabled'     => $enabled ? 1 : 0,
                                'bcc'         => $bcc,
                                'title'       => $title,
                                'description' => $body,
                        );
                }

                return $normalized;
        }
}

if ( ! function_exists( 'bhg_get_notification_settings' ) ) {
        /**
         * Retrieve notification settings for a given type.
         *
         * @param string $type Notification type key.
         *
         * @return array<string,mixed> Settings for the given type.
         */
        function bhg_get_notification_settings( $type ) {
                $all = bhg_get_notifications_settings();

                return isset( $all[ $type ] ) ? $all[ $type ] : array();
        }
}

if ( ! function_exists( 'bhg_render_notification_template' ) ) {
        /**
         * Replace template tokens with values.
         *
         * @param string               $template Template string containing tokens.
         * @param array<string,string> $tokens   Replacement map of token => value.
         *
         * @return string Rendered template.
         */
        function bhg_render_notification_template( $template, array $tokens ) {
                if ( '' === $template ) {
                        return '';
                }

                return strtr( $template, $tokens );
        }
}

if ( ! function_exists( 'bhg_parse_bcc_list' ) ) {
        /**
         * Convert a BCC list string into an array of valid emails.
         *
         * @param string $bcc Raw BCC string.
         *
         * @return string[] Sanitized email addresses.
         */
        function bhg_parse_bcc_list( $bcc ) {
                if ( ! is_string( $bcc ) || '' === trim( $bcc ) ) {
                        return array();
                }

                $parts  = array_map( 'trim', preg_split( '/[,;\n]+/', $bcc ) );
                $emails = array();
                foreach ( $parts as $part ) {
                        $email = sanitize_email( $part );
                        if ( $email && is_email( $email ) ) {
                                $emails[ $email ] = $email;
                        }
                }

                return array_values( $emails );
        }
}

if ( ! function_exists( 'bhg_prepare_notification_headers' ) ) {
        /**
         * Prepare email headers for plugin notifications.
         *
         * @param string $bcc Raw BCC string.
         *
         * @return string[] Headers array.
         */
        function bhg_prepare_notification_headers( $bcc ) {
                $headers   = array( 'From: ' . BHG_Utils::get_email_from(), 'Content-Type: text/html; charset=UTF-8' );
                $bcc_list  = bhg_parse_bcc_list( $bcc );
                if ( ! empty( $bcc_list ) ) {
                        $headers[] = 'Bcc: ' . implode( ',', $bcc_list );
                }

                return $headers;
        }
}

if ( ! function_exists( 'bhg_recalculate_points_scope' ) ) {
        /**
         * Recalculate stored winner points for hunts and tournament standings.
         *
         * @param string $scope Scope value (all|active|closed).
         *
         * @return void
         */
        function bhg_recalculate_points_scope( $scope ) {
                global $wpdb;

                $scope = sanitize_key( $scope );
                if ( ! in_array( $scope, array( 'all', 'active', 'closed' ), true ) ) {
                        return;
                }

                $hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                $winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

                $query  = "SELECT id, winners_count FROM {$hunts_table}";
                $params = array();
                if ( 'active' === $scope ) {
                        $query   .= " WHERE status = %s";
                        $params[] = 'open';
                } elseif ( 'closed' === $scope ) {
                        $query   .= " WHERE status = %s";
                        $params[] = 'closed';
                }

                $hunts = empty( $params ) ? $wpdb->get_results( $query ) : $wpdb->get_results( $wpdb->prepare( $query, $params ) );

                if ( ! empty( $hunts ) ) {
                        $points = bhg_get_tournament_points_settings();
                        foreach ( $hunts as $hunt ) {
                                $hunt_id = isset( $hunt->id ) ? (int) $hunt->id : 0;
                                if ( $hunt_id <= 0 ) {
                                        continue;
                                }

                                $winners = $wpdb->get_results(
                                        $wpdb->prepare(
                                                "SELECT id, position FROM {$winners_table} WHERE hunt_id = %d",
                                                $hunt_id
                                        )
                                );

                                if ( empty( $winners ) ) {
                                        continue;
                                }

                                foreach ( $winners as $winner ) {
                                        $winner_id = isset( $winner->id ) ? (int) $winner->id : 0;
                                        $position  = isset( $winner->position ) ? (int) $winner->position : 0;
                                        if ( $winner_id <= 0 || $position <= 0 ) {
                                                continue;
                                        }

                                        $points_value = isset( $points[ $position ] ) ? (int) $points[ $position ] : 0;
                                        $wpdb->update(
                                                $winners_table,
                                                array( 'points' => $points_value ),
                                                array( 'id' => $winner_id ),
                                                array( '%d' ),
                                                array( '%d' )
                                        );
                                }
                        }
                }

                $tournament_ids = $wpdb->get_col( 'SELECT id FROM ' . esc_sql( $wpdb->prefix . 'bhg_tournaments' ) );
                if ( ! empty( $tournament_ids ) && class_exists( 'BHG_Models' ) ) {
                        BHG_Models::recalculate_tournament_results( array_map( 'intval', $tournament_ids ) );
                }
        }
}

// Ensure canonical DB class is loaded.
require_once __DIR__ . '/includes/class-bhg-db.php';

// Define plugin constants.
define( 'BHG_VERSION', '8.0.14' );
define( 'BHG_MIN_WP', '6.3.0' );
define( 'BHG_PLUGIN_FILE', __FILE__ );
define( 'BHG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BHG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BHG_TABLE_PREFIX', 'bhg_' );

// Table creation function.
/**
 * Create plugin database tables using active DB class.
 *
 * @return void
 */
function bhg_create_tables() {
	if ( ! class_exists( 'BHG_DB' ) ) {
			return;
	}

	try {
			( new BHG_DB() )->create_tables();
	} catch ( Throwable $e ) {
		if ( function_exists( 'do_action' ) ) {
				do_action( 'bhg_db_error', $e );
		}
	}
}

// Check and create tables if needed.
/**
 * Create tables on first run if they do not exist.
 *
 * @return void
 */
function bhg_check_tables() {
	if ( ! get_option( 'bhg_tables_created' ) ) {
					bhg_create_tables();
					update_option( 'bhg_tables_created', true );
	}
}

/**
 * Run database migrations when needed.
 *
 * Stores the last migrated version in an option to avoid
 * repeated migrations on every request.
 *
 * @return void
 */
function bhg_maybe_run_migrations() {
	if ( ! class_exists( 'BHG_DB' ) ) {
			return;
	}

		$migrated = get_option( 'bhg_last_migrated_version', '' );
	if ( version_compare( $migrated, BHG_VERSION, '<' ) ) {
			BHG_DB::migrate();
			update_option( 'bhg_last_migrated_version', BHG_VERSION );
	}
}

add_action( 'plugins_loaded', 'bhg_maybe_run_migrations', 0 );

// Autoloader for plugin classes.
spl_autoload_register(
	function ( $class_name ) {
		if ( 0 !== strpos( $class_name, 'BHG_' ) ) {
						return;
		}

		$class_map = array(
			'BHG_Admin'                  => 'admin/class-bhg-admin.php',
			'BHG_Demo'                   => 'admin/class-bhg-demo.php',
			'BHG_Shortcodes'             => 'includes/class-bhg-shortcodes.php',
			'BHG_Logger'                 => 'includes/class-bhg-logger.php',
			'BHG_Utils'                  => 'includes/class-bhg-utils.php',
			'BHG_Models'                 => 'includes/class-bhg-models.php',
			'BHG_Front_Menus'            => 'includes/class-bhg-front-menus.php',
                        'BHG_Ads'                    => 'includes/class-bhg-ads.php',
                        'BHG_Prizes'                 => 'includes/class-bhg-prizes.php',
			'BHG_Login_Redirect'         => 'includes/class-bhg-login-redirect.php',
			'BHG_Tournaments_Controller' => 'includes/class-bhg-tournaments-controller.php',
		);

		if ( isset( $class_map[ $class_name ] ) ) {
				$file_path = BHG_PLUGIN_DIR . $class_map[ $class_name ];
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}
);

// Include helper functions.
require_once BHG_PLUGIN_DIR . 'includes/helpers.php';
require_once BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts-helpers.php';

add_action( 'plugins_loaded', 'bhg_maybe_seed_translations', 1 );
/**
 * Seed default translations on version change.
 *
 * Ensures new translation keys appear after plugin updates.
 *
 * @return void
 */
function bhg_maybe_seed_translations() {
		bhg_seed_default_translations_if_empty();
		$stored_version = get_option( 'bhg_version' );
	if ( BHG_VERSION !== $stored_version ) {
			update_option( 'bhg_version', BHG_VERSION );
	}
}

// Activation hook: create tables and set default options.
/**
 * Activation callback for setting up the plugin.
 *
 * @return void
 */
function bhg_activate_plugin() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
	}

				bhg_maybe_run_migrations();

				// Ensure database schema is up to date on activation.
				bhg_create_tables();

				bhg_seed_default_translations_if_empty();

	// Set default options.
	add_option( 'bhg_version', BHG_VERSION );
	add_option(
		'bhg_plugin_settings',
		array(
			'allow_guess_changes'       => 'yes',
			'default_tournament_period' => 'monthly',
			'min_guess_amount'          => 0,
			'max_guess_amount'          => 100000,
			'max_guesses'               => 1,
			'ads_enabled'               => 1,
			'email_from'                => get_bloginfo( 'admin_email' ),
		)
	);

		// Seed demo data if empty.
	if ( function_exists( 'bhg_seed_demo_if_empty' ) ) {
		bhg_seed_demo_if_empty();
	}
		update_option( 'bhg_demo_notice', 1 );

				// Set tables created flag.
				update_option( 'bhg_tables_created', true );

				// Flush rewrite rules after database changes.
		flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bhg_activate_plugin' );

// Deactivation hook (no destructive actions).
register_deactivation_hook(
	__FILE__,
	function () {
			// Keep data intact by default.
	}
);

// Frontend asset loader.
add_action( 'wp_enqueue_scripts', 'bhg_enqueue_public_assets' );

/**
 * Enqueue public-facing scripts and styles.
 *
 * @return void
 */
function bhg_enqueue_public_assets() {
		$settings  = get_option( 'bhg_plugin_settings', array() );
		$min_guess = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
		$max_guess = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;

		wp_register_style(
			'bhg-public',
			BHG_PLUGIN_URL . 'assets/css/public.css',
			array(),
			defined( 'BHG_VERSION' ) ? BHG_VERSION : null
		);

		wp_register_script(
			'bhg-public',
			BHG_PLUGIN_URL . 'assets/js/public.js',
			array( 'jquery' ),
			defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
			true
		);

				$guess_range = sprintf(
												/* translators: 1: minimum guess, 2: maximum guess. */
					bhg_t( 'guess_must_be_between', 'Guess must be between %1$s and %2$s.' ),
					bhg_format_currency( $min_guess ),
					bhg_format_currency( $max_guess )
				);

		wp_localize_script(
			'bhg-public',
			'bhg_public_ajax',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'bhg_public_nonce' ),
				'is_logged_in'     => is_user_logged_in(),
				'min_guess_amount' => $min_guess,
				'max_guess_amount' => $max_guess,
				'i18n'             => array(
					'guess_required'            => bhg_t( 'guess_required', 'Please enter a guess.' ),
                                        'guess_numeric'             => bhg_t( 'guess_numeric', 'Please enter a valid number.' ),
                                        'guess_range'               => $guess_range,
                                        'guess_submitted'           => bhg_t( 'guess_submitted', 'Your guess has been submitted!' ),
                                        'guess_updated'             => bhg_t( 'notice_guess_updated', 'Your guess has been updated.' ),
                                        'ajax_error'                => bhg_t( 'ajax_error', 'An error occurred. Please try again.' ),
					'affiliate_user'            => bhg_t( 'affiliate_user', 'Affiliate' ),
					'non_affiliate_user'        => bhg_t( 'non_affiliate_user', 'Non-affiliate' ),
					'error_loading_leaderboard' => bhg_t( 'error_loading_leaderboard', 'Error loading leaderboard.' ),
				),
			)
		);

	wp_enqueue_style( 'bhg-public' );
	wp_enqueue_script( 'bhg-public' );
}

// Initialize plugin.
add_action( 'plugins_loaded', 'bhg_init_plugin' );
/**
 * Initialize plugin components.
 *
 * @return void
 */
function bhg_init_plugin() {
		// Load text domain.
	load_plugin_textdomain( 'bonus-hunt-guesser', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Initialize components.
	if ( is_admin() ) {
		if ( class_exists( 'BHG_Demo' ) ) {
			BHG_Demo::instance();
		}

		if ( class_exists( 'BHG_Admin' ) ) {
			new BHG_Admin();
		}
	}

	if ( class_exists( 'BHG_Shortcodes' ) ) {
		new BHG_Shortcodes();
	}
	if ( class_exists( 'BHG_Front_Menus' ) ) {
			new BHG_Front_Menus();
	}

	if ( class_exists( 'BHG_Login_Redirect' ) ) {
			new BHG_Login_Redirect();
	}

	if ( class_exists( 'BHG_Ads' ) ) {
					BHG_Ads::init();
	}

	if ( class_exists( 'BHG_Utils' ) ) {
			BHG_Utils::init_hooks();
	}

	if ( class_exists( 'BHG_Tournaments_Controller' ) ) {
		BHG_Tournaments_Controller::init();
	}

		// Register form handlers.
	add_action( 'admin_post_bhg_submit_guess', 'bhg_handle_submit_guess' );
	add_action(
		'admin_post_nopriv_bhg_submit_guess',
		function () {
			$ref = wp_get_referer();
			wp_safe_redirect( wp_login_url( $ref ? $ref : home_url() ) );
			exit;
		}
	);
        add_action( 'wp_ajax_submit_bhg_guess', 'bhg_handle_submit_guess' );
        add_action( 'wp_ajax_nopriv_submit_bhg_guess', 'bhg_handle_submit_guess' );
        add_action( 'admin_post_bhg_save_settings', 'bhg_handle_settings_save' );
        add_action( 'admin_post_bhg_save_notifications', 'bhg_handle_notifications_save' );
}

// Early table check on init.
add_action( 'init', 'bhg_check_tables', 0 );
add_action( 'admin_init', 'bhg_create_tables' );

// Form handler for settings save.
/**
 * Handle saving of plugin settings.
 *
 * @return void
 */
function bhg_handle_settings_save() {
		// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_perform_this_action', 'You do not have sufficient permissions to perform this action.' ) ) );
	}

				// Verify nonce.
	if ( ! check_admin_referer( 'bhg_settings', 'bhg_settings_nonce' ) ) {
									wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-settings&error=nonce_failed' ) );
									exit;
	}

        // Sanitize and validate data.
        $settings     = array();
        $recalc_scope = 'none';

	if ( isset( $_POST['bhg_default_tournament_period'] ) ) {
			$period = sanitize_key( wp_unslash( $_POST['bhg_default_tournament_period'] ) );
		if ( in_array( $period, array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' ), true ) ) {
				$settings['default_tournament_period'] = $period;
		}
	}

	if ( isset( $_POST['bhg_max_guess_amount'] ) ) {
			$max = floatval( wp_unslash( $_POST['bhg_max_guess_amount'] ) );
		if ( 0 <= $max ) {
				$settings['max_guess_amount'] = $max;
		}
	}

	if ( isset( $_POST['bhg_min_guess_amount'] ) ) {
					$min = floatval( wp_unslash( $_POST['bhg_min_guess_amount'] ) );
		if ( 0 <= $min ) {
						$settings['min_guess_amount'] = $min;
		}
	}

	if ( isset( $_POST['bhg_currency'] ) ) {
					$currency = sanitize_key( wp_unslash( $_POST['bhg_currency'] ) );
		if ( in_array( $currency, array( 'eur', 'usd' ), true ) ) {
						$settings['currency'] = $currency;
		}
	}

				// Validate that min is not greater than max.
	if ( isset( $settings['min_guess_amount'] ) && isset( $settings['max_guess_amount'] ) &&
																$settings['min_guess_amount'] > $settings['max_guess_amount'] ) {
									wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-settings&error=invalid_data' ) );
									exit;
	}

        if ( isset( $_POST['bhg_allow_guess_changes'] ) ) {
                        $allow = sanitize_key( wp_unslash( $_POST['bhg_allow_guess_changes'] ) );
                if ( in_array( $allow, array( 'yes', 'no' ), true ) ) {
                                $settings['allow_guess_changes'] = $allow;
                }
        }

$ads_enabled_value       = isset( $_POST['bhg_ads_enabled'] ) ? wp_unslash( $_POST['bhg_ads_enabled'] ) : '';
$settings['ads_enabled'] = (string) $ads_enabled_value === '1' ? 1 : 0;

        if ( isset( $_POST['bhg_email_from'] ) ) {
                        $email_from = sanitize_email( wp_unslash( $_POST['bhg_email_from'] ) );
                if ( $email_from ) {
                                $settings['email_from'] = $email_from;
                }
        }

        if ( isset( $_POST['bhg_post_submit_redirect'] ) ) {
                        $redirect = trim( wp_unslash( $_POST['bhg_post_submit_redirect'] ) );
                if ( '' === $redirect ) {
                                $settings['post_submit_redirect'] = '';
                } else {
                                $url = esc_url_raw( $redirect );
                        if ( ! empty( $url ) ) {
                                                $settings['post_submit_redirect'] = $url;
                        }
                }
        }

        // Tournament points configuration.
        if ( isset( $_POST['bhg_tournament_points'] ) && is_array( $_POST['bhg_tournament_points'] ) ) {
                $raw_points = wp_unslash( $_POST['bhg_tournament_points'] );
                $points     = array();
                foreach ( $raw_points as $position => $value ) {
                        $position = (int) $position;
                        if ( $position <= 0 ) {
                                continue;
                        }
                        $points[ $position ] = max( 0, (int) $value );
                }

                if ( ! empty( $points ) ) {
                        $settings['tournament_points'] = bhg_normalize_tournament_points( $points );
                }
        }

        if ( isset( $_POST['bhg_tournament_points_scope'] ) ) {
                $scope = sanitize_key( wp_unslash( $_POST['bhg_tournament_points_scope'] ) );
                if ( in_array( $scope, array( 'all', 'active', 'closed', 'none' ), true ) ) {
                        $recalc_scope = $scope;
                }
        }

        // Design/CSS settings.
        $design_defaults = bhg_get_default_design_settings();
        $design_values   = array();
        if ( isset( $_POST['bhg_design'] ) && is_array( $_POST['bhg_design'] ) ) {
                $design_values = wp_unslash( $_POST['bhg_design'] );
        }

        $design_settings = array();
        foreach ( $design_defaults as $key => $default_value ) {
                $raw = isset( $design_values[ $key ] ) ? $design_values[ $key ] : '';
                switch ( $key ) {
                        case 'title_block_background':
                        case 'h2_color':
                        case 'h3_color':
                        case 'description_color':
                                $sanitized = bhg_sanitize_color_value( $raw );
                                break;
                        case 'h2_font_weight':
                        case 'h3_font_weight':
                        case 'description_font_weight':
                                $sanitized = bhg_sanitize_font_weight_value( $raw );
                                break;
                        case 'title_block_padding':
                        case 'title_block_margin':
                        case 'h2_padding':
                        case 'h2_margin':
                        case 'h3_padding':
                        case 'h3_margin':
                        case 'description_padding':
                        case 'description_margin':
                        case 'body_padding':
                        case 'body_margin':
                                $sanitized = bhg_sanitize_measurement_value( $raw );
                                break;
                        case 'h2_font_size':
                        case 'h3_font_size':
                        case 'description_font_size':
                        case 'body_font_size':
                                $sanitized = bhg_sanitize_measurement_value( $raw, false );
                                break;
                        case 'title_block_radius':
                                $sanitized = bhg_sanitize_measurement_value( $raw );
                                break;
                        default:
                                $sanitized = is_string( $raw ) ? trim( $raw ) : '';
                }

                $design_settings[ $key ] = $sanitized;
        }

        if ( ! empty( $design_settings ) ) {
                $settings['design'] = $design_settings;
        }

        // Save settings.
        $existing     = get_option( 'bhg_plugin_settings', array() );
        $merged       = array_merge( $existing, $settings );
        update_option( 'bhg_plugin_settings', $merged );

        if ( in_array( $recalc_scope, array( 'all', 'active', 'closed' ), true ) ) {
                bhg_recalculate_points_scope( $recalc_scope );
        }

        // Redirect back to settings page.
        $redirect_url = BHG_Utils::admin_url( 'admin.php?page=bhg-settings&message=saved' );
        if ( in_array( $recalc_scope, array( 'all', 'active', 'closed' ), true ) ) {
                $redirect_url = add_query_arg( 'points_scope', $recalc_scope, $redirect_url );
        }

        wp_safe_redirect( $redirect_url );
        exit;
}

/**
 * Handle saving of notification templates.
 *
 * @return void
 */
function bhg_handle_notifications_save() {
        if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_perform_this_action', 'You do not have sufficient permissions to perform this action.' ) ) );
        }

        if ( ! check_admin_referer( 'bhg_notifications', 'bhg_notifications_nonce' ) ) {
                wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-notifications&error=nonce_failed' ) );
                exit;
        }

        $submitted = isset( $_POST['bhg_notifications'] ) && is_array( $_POST['bhg_notifications'] ) ? wp_unslash( $_POST['bhg_notifications'] ) : array();
        $defaults  = bhg_get_default_notifications_settings();
        $settings  = array();

        foreach ( $defaults as $type => $default ) {
                $row      = isset( $submitted[ $type ] ) && is_array( $submitted[ $type ] ) ? $submitted[ $type ] : array();
                $enabled  = isset( $row['enabled'] ) ? (int) $row['enabled'] : 0;
                $bcc      = isset( $row['bcc'] ) ? sanitize_text_field( $row['bcc'] ) : '';
                $title    = isset( $row['title'] ) ? wp_kses_post( $row['title'] ) : '';
                $body     = isset( $row['description'] ) ? wp_kses_post( $row['description'] ) : '';

                $settings[ $type ] = array(
                        'enabled'     => $enabled ? 1 : 0,
                        'bcc'         => $bcc,
                        'title'       => '' !== $title ? $title : $default['title'],
                        'description' => '' !== $body ? $body : $default['description'],
                );
        }

        update_option( 'bhg_notifications_settings', $settings );

        wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-notifications&message=saved' ) );
        exit;
}

// Canonical guess submit handler.
/**
 * Process a guess submission from admin-post or AJAX.
 *
 * @return void
 */
function bhg_handle_submit_guess() {
	$last_guess_key = '';
	if ( wp_doing_ajax() ) {
			check_ajax_referer( 'bhg_public_nonce', 'nonce' );
	} else {
		if ( ! isset( $_POST['_wpnonce'] ) ) {
						wp_die( esc_html( bhg_t( 'notice_security_check_failed', 'Security check failed.' ) ) );
		}
						check_admin_referer( 'bhg_submit_guess', 'bhg_submit_guess_nonce' );
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		if ( wp_doing_ajax() ) {
				wp_send_json_error( bhg_t( 'you_must_be_logged_in_to_submit_a_guess', 'You must be logged in to submit a guess.' ) );
		}
				wp_die( esc_html( bhg_t( 'you_must_be_logged_in_to_submit_a_guess', 'You must be logged in to submit a guess.' ) ) );
	}

		$hunt_id = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
	if ( 0 >= $hunt_id ) {
		if ( wp_doing_ajax() ) {
				wp_send_json_error( bhg_t( 'notice_invalid_hunt', 'Invalid hunt.' ) );
		}
				wp_die( esc_html( bhg_t( 'notice_invalid_hunt', 'Invalid hunt.' ) ) );
	}

		// Parse guess robustly.
	if ( wp_doing_ajax() ) {
			$raw_guess = isset( $_POST['guess_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['guess_amount'] ) ) : '';
	} else {
			$raw_guess = isset( $_POST['guess'] ) ? sanitize_text_field( wp_unslash( $_POST['guess'] ) ) : ( isset( $_POST['balance_guess'] ) ? sanitize_text_field( wp_unslash( $_POST['balance_guess'] ) ) : '' );
	}
	$guess = -1.0;
	if ( function_exists( 'bhg_parse_amount' ) ) {
		$parsed = bhg_parse_amount( $raw_guess );
		$guess  = ( null === $parsed ) ? -1.0 : (float) $parsed;
	} else {
		$guess = is_numeric( $raw_guess ) ? (float) $raw_guess : -1.0;
	}
        $settings         = get_option( 'bhg_plugin_settings', array() );
        $min_guess        = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
        $max_guess        = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;
        $max              = isset( $settings['max_guesses'] ) ? (int) $settings['max_guesses'] : 1;
        $allow_edit       = isset( $settings['allow_guess_changes'] ) && 'yes' === $settings['allow_guess_changes'];
        $redirect_setting = isset( $settings['post_submit_redirect'] ) ? $settings['post_submit_redirect'] : '';
        $redirect_target  = $redirect_setting ? wp_validate_redirect( $redirect_setting, '' ) : '';
        if ( isset( $_POST['redirect_to'] ) ) {
                        $requested_redirect = trim( wp_unslash( $_POST['redirect_to'] ) );
                if ( '' !== $requested_redirect ) {
                                $maybe_redirect = wp_validate_redirect( $requested_redirect, '' );
                        if ( $maybe_redirect ) {
                                                $redirect_target = $maybe_redirect;
                        }
                }
        }
        $did_update       = false;

	if ( $guess < $min_guess || $guess > $max_guess ) {
		if ( wp_doing_ajax() ) {
				wp_send_json_error( bhg_t( 'notice_invalid_guess_amount', 'Invalid guess amount.' ) );
		}
				wp_die( esc_html( bhg_t( 'notice_invalid_guess_amount', 'Invalid guess amount.' ) ) );
	}

		global $wpdb;
				$wpdb->bhg_bonus_hunts = $wpdb->prefix . 'bhg_bonus_hunts';
				$wpdb->bhg_guesses     = $wpdb->prefix . 'bhg_guesses';

	// db call ok; caching added.
	$hunt_cache_key = 'bhg_hunt_' . $hunt_id;
	$hunt           = wp_cache_get( $hunt_cache_key );
	if ( false === $hunt ) {
			// db call ok; caching added.
			$hunt = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, status, guessing_enabled FROM {$wpdb->bhg_bonus_hunts} WHERE id = %d",
					$hunt_id
				)
			);
			wp_cache_set( $hunt_cache_key, $hunt );
	}
	if ( ! $hunt ) {
		if ( wp_doing_ajax() ) {
				wp_send_json_error( bhg_t( 'notice_hunt_not_found', 'Hunt not found.' ) );
		}
			wp_die( esc_html( bhg_t( 'notice_hunt_not_found', 'Hunt not found.' ) ) );
	}
	if ( 'open' !== $hunt->status || ! $hunt->guessing_enabled ) {
		$msg = ! $hunt->guessing_enabled ? bhg_t( 'guessing_disabled_for_this_hunt', 'Guessing is disabled for this hunt.' ) : bhg_t( 'this_hunt_is_closed_you_cannot_submit_or_change_a_guess', 'This hunt is closed. You cannot submit or change a guess.' );
		if ( wp_doing_ajax() ) {
			wp_send_json_error( $msg );
		}
		wp_die( esc_html( $msg ) );
	}

		// Insert or update last guess per settings.

		// db call ok; caching added.
		$count_cache_key = 'bhg_guess_count_' . $hunt_id . '_' . $user_id;
		$count           = wp_cache_get( $count_cache_key );
	if ( false === $count ) {
			// db call ok; caching added.
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->bhg_guesses} WHERE hunt_id = %d AND user_id = %d",
					$hunt_id,
					$user_id
				)
			);
			wp_cache_set( $count_cache_key, $count );
	}
	if ( $count >= $max ) {
		if ( $allow_edit && $count > 0 ) {
			// db call ok; caching added.
			$last_guess_key = 'bhg_last_guess_' . $hunt_id . '_' . $user_id;
			$gid            = wp_cache_get( $last_guess_key );
			if ( false === $gid ) {
					// db call ok; caching added.
					$gid = (int) $wpdb->get_var(
						$wpdb->prepare(
							"SELECT id FROM {$wpdb->bhg_guesses} WHERE hunt_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1",
							$hunt_id,
							$user_id
						)
					);
					wp_cache_set( $last_guess_key, $gid );
			}
                        if ( $gid ) {
                                                                // db call ok; no-cache ok.
                                                                                                                               $wpdb->update(
                                                                                                                               $wpdb->bhg_guesses,
                                                                                                                               array(
                                                                                                                               'guess'      => $guess,
                                                                                                                               'updated_at' => current_time( 'mysql' ),
                                                                                                                               ),
                                                                                                                               array( 'id' => $gid ),
                                                                                                                               array( '%f', '%s' ),
                                                                                                                               array( '%d' )
                                                                                                                               );
                                                                wp_cache_delete( $count_cache_key );
                                if ( ! empty( $last_guess_key ) ) {
                                                wp_cache_delete( $last_guess_key );
                                }
                                $did_update = true;
                                if ( wp_doing_ajax() ) {
                                        wp_send_json_success(
                                                array(
                                                        'status'   => 'updated',
                                                        'message'  => bhg_t( 'notice_guess_updated', 'Your guess has been updated.' ),
                                                        'redirect' => $redirect_target,
                                                )
                                        );
                                }
                                                                $referer = wp_get_referer();
                                                                $target  = $redirect_target ? $redirect_target : ( $referer ? $referer : home_url() );
                                                                wp_safe_redirect( $target );
                                                        exit;
                        }
		}
		if ( wp_doing_ajax() ) {
				wp_send_json_error( bhg_t( 'you_have_reached_the_maximum_number_of_guesses', 'You have reached the maximum number of guesses.' ) );
		}
				wp_die( esc_html( bhg_t( 'you_have_reached_the_maximum_number_of_guesses', 'You have reached the maximum number of guesses.' ) ) );
	}

				// Insert.
				// db call ok; no-cache ok.
				$wpdb->insert(
					$wpdb->bhg_guesses,
					array(
						'hunt_id'    => $hunt_id,
						'user_id'    => $user_id,
						'guess'      => $guess,
						'created_at' => current_time( 'mysql' ),
					),
					array( '%d', '%d', '%f', '%s' )
				);
	wp_cache_delete( $count_cache_key );
		$last_guess_key = 'bhg_last_guess_' . $hunt_id . '_' . $user_id;
	if ( ! empty( $last_guess_key ) ) {
			wp_cache_delete( $last_guess_key );
	}

        if ( wp_doing_ajax() ) {
                wp_send_json_success(
                        array(
                                'status'   => $did_update ? 'updated' : 'created',
                                'message'  => $did_update ? bhg_t( 'notice_guess_updated', 'Your guess has been updated.' ) : bhg_t( 'notice_guess_saved', 'Your guess has been saved.' ),
                                'redirect' => $redirect_target,
                        )
                );
        }

                $referer = wp_get_referer();
                $target  = $redirect_target ? $redirect_target : ( $referer ? $referer : home_url() );
                wp_safe_redirect( $target );
        exit;
}

// Frontend ads rendering.
/**
 * Determine if an advertisement should be displayed.
 *
 * @param string $visibility Visibility rule.
 * @return bool True if ad should be shown, false otherwise.
 */
function bhg_should_show_ad( $visibility ) {
	if ( 'all' === $visibility ) {
			return true;
	}
	if ( 'logged_in' === $visibility ) {
			return ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() );
	}
	if ( 'guests' === $visibility ) {
			return ! ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() );
	}
	if ( 'affiliates' === $visibility ) {
			return ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) && bhg_is_affiliate();
	}
	if ( 'non_affiliates' === $visibility ) {
			return ! ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) || ! bhg_is_affiliate();
	}
	return true;
}

/**
 * Safe and validated ads query builder.
 *
 * @param string $table     Table name without prefix.
 * @param string $placement Ad placement.
 * @return array List of ad rows.
 */
function bhg_build_ads_query( $table, $placement = 'footer' ) {
				global $wpdb;

				$wpdb->bhg_ads  = $wpdb->prefix . 'bhg_ads';
				$allowed_tables = array( $wpdb->bhg_ads );
	if ( ! in_array( $table, $allowed_tables, true ) ) {
					return array();
	}

				$placement  = sanitize_key( $placement );
		$allowed_placements = array( 'footer', 'bottom', 'sidebar', 'shortcode', 'none' );
	if ( ! in_array( $placement, $allowed_placements, true ) ) {
			return array();
	}

		$cache_key = 'bhg_ads_' . md5( $wpdb->bhg_ads . '_' . $placement );
		$rows      = wp_cache_get( $cache_key );
	if ( false === $rows ) {
			// db call ok; caching added.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->bhg_ads} WHERE placement = %s AND active = %d",
					$placement,
					1
				)
			);
			wp_cache_set( $cache_key, $rows );
	}
	if ( did_action( 'wp' ) && function_exists( 'get_queried_object_id' ) ) {
			$pid = (int) get_queried_object_id();
		if ( $pid && is_array( $rows ) ) {
				$rows = array_filter(
					$rows,
					function ( $r ) use ( $pid ) {
						if ( empty( $r->target_pages ) ) {
									return true;
						}
							$ids = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $r->target_pages ) ) ) );
							return in_array( $pid, $ids, true );
					}
				);
		}
	}
		return $rows;
}

// AJAX handler for loading leaderboard data.
add_action( 'wp_ajax_bhg_load_leaderboard', 'bhg_load_leaderboard_ajax' );
add_action( 'wp_ajax_nopriv_bhg_load_leaderboard', 'bhg_load_leaderboard_ajax' );

/**
 * AJAX handler for loading leaderboard markup.
 *
 * @return void
 */
function bhg_load_leaderboard_ajax() {
		check_ajax_referer( 'bhg_public_nonce', 'nonce' );

	if ( ! isset( $_POST['timeframe'] ) ) {
			wp_send_json_error( bhg_t( 'invalid_timeframe', 'Invalid timeframe' ) );
	}

		$timeframe          = sanitize_text_field( wp_unslash( $_POST['timeframe'] ) );
		$allowed_timeframes = array( 'overall', 'monthly', 'yearly', 'alltime' );
	if ( ! in_array( $timeframe, $allowed_timeframes, true ) ) {
					wp_send_json_error( bhg_t( 'invalid_timeframe', 'Invalid timeframe' ) );
	}

		$paged = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;

		// Generate leaderboard HTML based on timeframe.
		$html = bhg_generate_leaderboard_html( $timeframe, $paged );

		wp_send_json_success( $html );
}

// Helper function to generate leaderboard HTML.
/**
 * Generate leaderboard HTML for a timeframe.
 *
 * @param string $timeframe Timeframe key.
 * @param int    $paged     Page number.
 * @return string Generated HTML.
 */
function bhg_generate_leaderboard_html( $timeframe, $paged ) {
		global $wpdb;

		$per_page = 20;
		$offset   = ( $paged - 1 ) * $per_page;

		$start_date = '';
		$now        = time();
	switch ( strtolower( $timeframe ) ) {
		case 'monthly':
			$start_date = gmdate( 'Y-m-01 00:00:00', $now );
			break;
		case 'yearly':
			$start_date = gmdate( 'Y-01-01 00:00:00', $now );
			break;
		case 'overall':
			$start_date = gmdate( 'Y-m-d H:i:s', $now - 30 * DAY_IN_SECONDS );
			break;
		case 'all-time':
		case 'all_time':
		default:
			$start_date = '';
			break;
	}

		$wpdb->bhg_guesses     = $wpdb->prefix . 'bhg_guesses';
		$wpdb->bhg_bonus_hunts = $wpdb->prefix . 'bhg_bonus_hunts';

		$total_query = "SELECT COUNT(*) FROM (
SELECT g.user_id
FROM {$wpdb->bhg_guesses} g
INNER JOIN {$wpdb->bhg_bonus_hunts} h ON h.id = g.hunt_id
WHERE h.status='closed' AND h.final_balance IS NOT NULL";
	if ( $start_date ) {
			// db call ok; no-cache ok.
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM (
SELECT g.user_id
FROM {$wpdb->bhg_guesses} g
INNER JOIN {$wpdb->bhg_bonus_hunts} h ON h.id = g.hunt_id
WHERE h.status='closed' AND h.final_balance IS NOT NULL
AND h.updated_at >= %s
AND NOT EXISTS (
SELECT 1 FROM {$wpdb->bhg_guesses} g2
WHERE g2.hunt_id = g.hunt_id
AND ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance)
)
GROUP BY g.user_id
) t",
					$start_date
				)
			);

			// db call ok; no-cache ok.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT g.user_id, u.user_login, COUNT(*) AS wins
FROM {$wpdb->bhg_guesses} g
INNER JOIN {$wpdb->bhg_bonus_hunts} h ON h.id = g.hunt_id
INNER JOIN {$wpdb->users} u ON u.ID = g.user_id
WHERE h.status='closed' AND h.final_balance IS NOT NULL
AND h.updated_at >= %s
AND NOT EXISTS (
SELECT 1 FROM {$wpdb->bhg_guesses} g2
WHERE g2.hunt_id = g.hunt_id
AND ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance)
)
GROUP BY g.user_id, u.user_login
ORDER BY wins DESC, u.user_login ASC
LIMIT %d OFFSET %d",
					$start_date,
					$per_page,
					$offset
				)
			);
	} else {
			// db call ok; no-cache ok.
			$total = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM (
SELECT g.user_id
FROM {$wpdb->bhg_guesses} g
INNER JOIN {$wpdb->bhg_bonus_hunts} h ON h.id = g.hunt_id
WHERE h.status='closed' AND h.final_balance IS NOT NULL
AND NOT EXISTS (
SELECT 1 FROM {$wpdb->bhg_guesses} g2
WHERE g2.hunt_id = g.hunt_id
AND ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance)
)
GROUP BY g.user_id
) t"
			);

			// db call ok; no-cache ok.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT g.user_id, u.user_login, COUNT(*) AS wins
FROM {$wpdb->bhg_guesses} g
INNER JOIN {$wpdb->bhg_bonus_hunts} h ON h.id = g.hunt_id
INNER JOIN {$wpdb->users} u ON u.ID = g.user_id
WHERE h.status='closed' AND h.final_balance IS NOT NULL
AND NOT EXISTS (
SELECT 1 FROM {$wpdb->bhg_guesses} g2
WHERE g2.hunt_id = g.hunt_id
AND ABS(g2.guess - h.final_balance) < ABS(g.guess - h.final_balance)
)
GROUP BY g.user_id, u.user_login
ORDER BY wins DESC, u.user_login ASC
LIMIT %d OFFSET %d",
					$per_page,
					$offset
				)
			);
	}
	if ( ! $rows ) {
		return '<p>' . esc_html( bhg_t( 'notice_no_data_available', 'No data available.' ) ) . '</p>';
	}

	ob_start();
	echo '<table class="bhg-leaderboard bhg-table" data-timeframe="' . esc_attr( $timeframe ) . '">';
		echo '<thead><tr>';
		echo '<th class="sortable" data-sort="position">' . esc_html( bhg_t( 'sc_position', 'Position' ) ) . '</th>';
		echo '<th class="sortable" data-sort="username">' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</th>';
           echo '<th class="sortable" data-sort="wins">' . esc_html( bhg_t( 'points', 'Points' ) ) . '</th>';
		echo '</tr></thead><tbody>';

	$pos = $offset + 1;
	foreach ( $rows as $row ) {
								/* translators: %d: user ID. */
								$user_label = $row->user_login ? $row->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
				echo '<tr>';
				echo '<td>' . (int) $pos . '</td>';
				echo '<td>' . esc_html( $user_label ) . '</td>';
				echo '<td>' . (int) $row->wins . '</td>';
				echo '</tr>';
				++$pos;
	}
	echo '</tbody></table>';

	$pages = (int) ceil( $total / $per_page );
	if ( $pages > 1 ) {
		echo '<div class="bhg-pagination">';
		for ( $p = 1; $p <= $pages; $p++ ) {
				$current = $paged === $p ? 'current' : '';
						echo '<a href="#" data-page="' . (int) $p . '" class="' . esc_attr( $current ) . '">' . (int) $p . '</a> ';
		}
		echo '</div>';
	}

	return ob_get_clean();
}

// Helper function to check if user is affiliate.
/**
 * Check whether a user has affiliate status.
 *
 * @param int|null $user_id Optional. User ID to check. Defaults to current user.
 * @return bool True if user is an affiliate.
 */
function bhg_is_affiliate( $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

		return (bool) get_user_meta( $user_id, 'bhg_is_affiliate', true );
}

// Add user profile fields for affiliate status.
add_action( 'show_user_profile', 'bhg_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'bhg_extra_user_profile_fields' );

/**
 * Display affiliate status field on user profile.
 *
 * @param WP_User $user User object.
 * @return void
 */
function bhg_extra_user_profile_fields( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

		$affiliate_status = get_user_meta( $user->ID, 'bhg_is_affiliate', true );
	?>
			<h3><?php esc_html_e( 'Bonus Hunt Guesser Information', 'bonus-hunt-guesser' ); ?></h3>
	<table class="form-table">
		<tr>
							<th><label for="bhg_is_affiliate"><?php esc_html_e( 'Affiliate Status', 'bonus-hunt-guesser' ); ?></label></th>
			<td>
									<input type="checkbox" name="bhg_is_affiliate" id="bhg_is_affiliate" value="1" <?php checked( $affiliate_status, 1 ); ?> />
									<span class="description"><?php esc_html_e( 'Check if this user is an affiliate.', 'bonus-hunt-guesser' ); ?></span>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'personal_options_update', 'bhg_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'bhg_save_extra_user_profile_fields' );

/**
 * Save affiliate status from user profile.
 *
 * @param int $user_id User ID.
 * @return void|false Returns false if the user cannot be edited.
 */
function bhg_save_extra_user_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
	}

				check_admin_referer( 'update-user_' . $user_id, '_wpnonce' );

		$affiliate_status = isset( $_POST['bhg_is_affiliate'] ) ? 1 : 0;
		update_user_meta( $user_id, 'bhg_is_affiliate', $affiliate_status );
}

