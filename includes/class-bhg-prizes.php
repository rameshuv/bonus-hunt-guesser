<?php
/**
 * Prize management utilities.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides CRUD helpers for prizes and hunt associations.
 */
class BHG_Prizes {

        /**
         * Valid prize categories.
         *
         * @return string[]
         */
        public static function get_categories() {
                return array( 'cash_money', 'casino_money', 'coupons', 'merchandise', 'various' );
        }

        /**
         * Default CSS settings for prize blocks.
         *
         * @return array
         */
        public static function default_css_settings() {
                return array(
                        'border'         => '',
                        'border_color'   => '',
                        'padding'        => '',
                        'margin'         => '',
                        'background'     => '',
                );
        }

        /**
         * Sanitize CSS settings array.
         *
         * @param array $input Raw input values.
         * @return array
         */
        public static function sanitize_css_settings( $input ) {
                $defaults = self::default_css_settings();
                $output   = array();

                foreach ( $defaults as $key => $default ) {
                        if ( isset( $input[ $key ] ) && is_string( $input[ $key ] ) ) {
                                $value = $input[ $key ];
                        } else {
                                $value = $default;
                        }

                        if ( in_array( $key, array( 'border_color', 'background' ), true ) ) {
                                $value = self::sanitize_color_value( $value );
                        } else {
                                $value = sanitize_text_field( $value );
                        }

                        $output[ $key ] = $value;
                }

                return $output;
        }

        /**
         * Retrieve a list of prizes.
         *
         * @param array $args Optional query args (category, active, search).
         * @return array
         */
        public static function get_prizes( $args = array() ) {
                global $wpdb;

                $table = $wpdb->prefix . 'bhg_prizes';

                $where  = array();
                $params = array();

                if ( isset( $args['category'] ) && $args['category'] ) {
                        $category = sanitize_key( $args['category'] );
                        if ( in_array( $category, self::get_categories(), true ) ) {
                                $where[]  = 'category = %s';
                                $params[] = $category;
                        }
                }

                if ( isset( $args['active'] ) && '' !== $args['active'] ) {
                        $active   = (int) $args['active'];
                        $where[]  = 'active = %d';
                        $params[] = $active ? 1 : 0;
                }

                if ( isset( $args['search'] ) && '' !== $args['search'] ) {
                        $like     = '%' . $wpdb->esc_like( wp_unslash( $args['search'] ) ) . '%';
                        $where[]  = '(title LIKE %s OR description LIKE %s)';
                        $params[] = $like;
                        $params[] = $like;
                }

                $sql = "SELECT * FROM {$table}";
                if ( ! empty( $where ) ) {
                        $sql .= ' WHERE ' . implode( ' AND ', $where );
                }
                $sql .= ' ORDER BY title ASC';

                if ( ! empty( $params ) ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
                        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
                return $wpdb->get_results( $sql );
        }

        /**
         * Fetch a single prize.
         *
         * @param int $id Prize ID.
         * @return object|null
         */
        public static function get_prize( $id ) {
                global $wpdb;
                $table = $wpdb->prefix . 'bhg_prizes';

                $id = absint( $id );
                if ( $id <= 0 ) {
                        return null;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
                return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        }

        /**
         * Insert or update a prize.
         *
         * @param array $data Prize data.
         * @param int   $id   Optional existing ID.
         * @return int|false Prize ID on success, false otherwise.
         */
        public static function save_prize( $data, $id = 0 ) {
                global $wpdb;

                $table = $wpdb->prefix . 'bhg_prizes';

                $defaults = array(
                        'title'         => '',
                        'description'   => '',
                        'category'      => 'various',
                        'image_small'   => 0,
                        'image_medium'  => 0,
                        'image_large'   => 0,
                        'css_settings'  => self::default_css_settings(),
                        'active'        => 1,
                );

                $data = wp_parse_args( $data, $defaults );

                $category = sanitize_key( $data['category'] );
                if ( ! in_array( $category, self::get_categories(), true ) ) {
                        $category = 'various';
                }

                $row = array(
                        'title'        => sanitize_text_field( $data['title'] ),
                        'description'  => wp_kses_post( $data['description'] ),
                        'category'     => $category,
                        'image_small'  => isset( $data['image_small'] ) ? absint( $data['image_small'] ) : 0,
                        'image_medium' => isset( $data['image_medium'] ) ? absint( $data['image_medium'] ) : 0,
                        'image_large'  => isset( $data['image_large'] ) ? absint( $data['image_large'] ) : 0,
                        'active'       => ! empty( $data['active'] ) ? 1 : 0,
                );

                $css_settings = isset( $data['css_settings'] ) ? $data['css_settings'] : array();
                $css_settings = self::sanitize_css_settings( $css_settings );

                $row['css_border']       = $css_settings['border'];
                $row['css_border_color'] = $css_settings['border_color'];
                $row['css_padding']      = $css_settings['padding'];
                $row['css_margin']       = $css_settings['margin'];
                $row['css_background']   = $css_settings['background'];

                $formats = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' );

                if ( $id > 0 ) {
                        $row['updated_at'] = current_time( 'mysql' );
                        $formats[]         = '%s';
                        $result            = $wpdb->update( $table, $row, array( 'id' => $id ), $formats, array( '%d' ) );
                        if ( false === $result ) {
                                return false;
                        }
                        return $id;
                }

                $row['created_at'] = current_time( 'mysql' );
                $row['updated_at'] = $row['created_at'];
                $formats[]         = '%s';
                $formats[]         = '%s';

                $inserted = $wpdb->insert( $table, $row, $formats );
                if ( false === $inserted ) {
                        return false;
                }

                return (int) $wpdb->insert_id;
        }

        /**
         * Delete a prize.
         *
         * @param int $id Prize ID.
         * @return bool
         */
        public static function delete_prize( $id ) {
                global $wpdb;
                $id    = absint( $id );
                $table = $wpdb->prefix . 'bhg_prizes';

                if ( $id <= 0 ) {
                        return false;
                }

                $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
                $wpdb->delete( $wpdb->prefix . 'bhg_hunt_prizes', array( 'prize_id' => $id ), array( '%d' ) );

                return true;
        }

        /**
         * Associate prizes with a hunt.
         *
         * @param int   $hunt_id   Hunt ID.
         * @param int[] $prize_ids Prize IDs.
         * @return void
         */
        public static function set_hunt_prizes( $hunt_id, $prize_ids ) {
                global $wpdb;

                $hunt_id = absint( $hunt_id );
                if ( $hunt_id <= 0 ) {
                        return;
                }

                $table      = $wpdb->prefix . 'bhg_hunt_prizes';
                $current    = self::get_hunt_prize_ids( $hunt_id );
                $new        = array_map( 'absint', (array) $prize_ids );
                $new        = array_filter( array_unique( $new ) );
                $to_add     = array_diff( $new, $current );
                $to_remove  = array_diff( $current, $new );

                if ( ! empty( $to_remove ) ) {
                        $placeholders = implode( ',', array_fill( 0, count( $to_remove ), '%d' ) );
                        $params       = array_merge( array( $hunt_id ), array_values( $to_remove ) );
                        $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                $wpdb->prepare(
                                        "DELETE FROM {$table} WHERE hunt_id = %d AND prize_id IN ({$placeholders})",
                                        ...$params
                                )
                        );
                }

                if ( ! empty( $to_add ) ) {
                        $now = current_time( 'mysql' );
                        foreach ( $to_add as $pid ) {
                                $wpdb->insert(
                                        $table,
                                        array(
                                                'hunt_id'   => $hunt_id,
                                                'prize_id'  => $pid,
                                                'created_at'=> $now,
                                        ),
                                        array( '%d', '%d', '%s' )
                                );
                        }
                }
        }

        /**
         * Get prize IDs linked to a hunt.
         *
         * @param int $hunt_id Hunt ID.
         * @return int[]
         */
        public static function get_hunt_prize_ids( $hunt_id ) {
                global $wpdb;
                $hunt_id = absint( $hunt_id );
                if ( $hunt_id <= 0 ) {
                        return array();
                }

                $table = $wpdb->prefix . 'bhg_hunt_prizes';

                $ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $wpdb->prepare(
                                "SELECT prize_id FROM {$table} WHERE hunt_id = %d ORDER BY created_at ASC, id ASC",
                                $hunt_id
                        )
                );

                return array_map( 'intval', array_filter( array_unique( (array) $ids ) ) );
        }

        /**
         * Retrieve detailed prize rows for a hunt.
         *
         * @param int $hunt_id Hunt ID.
         * @return array
         */
        public static function get_prizes_for_hunt( $hunt_id, $args = array() ) {
                global $wpdb;
                $hunt_id = absint( $hunt_id );
                if ( $hunt_id <= 0 ) {
                        return array();
                }

                $table       = $wpdb->prefix . 'bhg_prizes';
                $relation    = $wpdb->prefix . 'bhg_hunt_prizes';
                $active_only = isset( $args['active_only'] ) ? (bool) $args['active_only'] : false;

                $where = '';
                if ( $active_only ) {
                        $where = 'AND p.active = 1';
                }

                $sql = "SELECT p.* FROM {$table} p INNER JOIN {$relation} r ON r.prize_id = p.id WHERE r.hunt_id = %d {$where} ORDER BY r.created_at ASC, r.id ASC";

                return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $wpdb->prepare( $sql, $hunt_id )
                );
        }

        /**
         * Format CSS inline style attribute based on prize settings.
         *
         * @param object $prize Prize row.
         * @return string
         */
        public static function build_style_attr( $prize ) {
                $styles = array();

                if ( ! empty( $prize->css_border ) ) {
                        $styles[] = 'border:' . sanitize_text_field( $prize->css_border );
                }

                if ( ! empty( $prize->css_border_color ) ) {
                        $color = self::sanitize_color_value( $prize->css_border_color );
                        if ( '' !== $color ) {
                                $styles[] = 'border-color:' . $color;
                        }
                }

                if ( ! empty( $prize->css_padding ) ) {
                        $styles[] = 'padding:' . sanitize_text_field( $prize->css_padding );
                }

                if ( ! empty( $prize->css_margin ) ) {
                        $styles[] = 'margin:' . sanitize_text_field( $prize->css_margin );
                }

                if ( ! empty( $prize->css_background ) ) {
                        $background = self::sanitize_color_value( $prize->css_background );
                        if ( '' !== $background ) {
                                $styles[] = 'background-color:' . $background;
                        }
                }

                if ( empty( $styles ) ) {
                        return '';
                }

                return implode( ';', $styles );
        }

        /**
         * Sanitize CSS color-like values while allowing common keywords and functions.
         *
         * @param string $value Raw color value.
         * @return string Sanitized value or empty string when invalid.
         */
        private static function sanitize_color_value( $value ) {
                if ( ! is_string( $value ) ) {
                        return '';
                }

                if ( function_exists( 'wp_unslash' ) ) {
                        $value = wp_unslash( $value );
                }

                $value = trim( $value );

                if ( '' === $value ) {
                        return '';
                }

                if ( function_exists( 'wp_strip_all_tags' ) ) {
                        $value = wp_strip_all_tags( $value );
                } else {
                        $value = strip_tags( $value );
                }

                $value = preg_replace( '/[^#a-zA-Z0-9(),.%_\s\-]/', '', $value );

                if ( ! is_string( $value ) ) {
                        return '';
                }

                $value = trim( $value );

                if ( '' === $value ) {
                        return '';
                }

                $value = substr( $value, 0, 100 );

                $compacted = preg_replace( '/\s+/', '', $value );
                $normalized = strtolower( $compacted );

                if ( '' === $normalized ) {
                        return '';
                }

                $keywords = array( 'transparent', 'inherit', 'initial', 'unset', 'currentcolor' );
                if ( in_array( $normalized, $keywords, true ) ) {
                        return $normalized;
                }

                if ( preg_match( '/^#[0-9a-f]{3,8}$/', $normalized ) ) {
                        return '#' . substr( $normalized, 1 );
                }

                if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $compacted ) ) {
                        return $compacted;
                }

                if ( preg_match( '/^(rgba?|hsla?)\([0-9.,%]+\)$/', $normalized ) ) {
                        return $normalized;
                }

                if ( preg_match( '/^[a-z]+$/', $normalized ) ) {
                        return $normalized;
                }

                return '';
        }

	/**
	 * Retrieve image URL for a prize.
	 *
	 * @param object $prize  Prize row.
	 * @param string $size   Size key.
	 * @param bool   $escape Whether to escape the URL before returning.
	 * @return string Attachment URL (escaped by default).
	 */
	public static function get_image_url( $prize, $size = 'medium', $escape = true ) {
                $size = sanitize_key( $size );
                $map  = array(
                        'small'  => 'image_small',
                        'medium' => 'image_medium',
                        'big'    => 'image_large',
                );

                if ( ! isset( $map[ $size ] ) ) {
                        $size = 'medium';
                }

                $field = $map[ $size ];
                $id    = isset( $prize->$field ) ? absint( $prize->$field ) : 0;

                if ( $id <= 0 ) {
                        return '';
                }

                $wp_size = 'medium';
                if ( 'small' === $size ) {
                        $wp_size = 'thumbnail';
                } elseif ( 'big' === $size ) {
                        $wp_size = 'large';
                } else {
                        $wp_size = $size;
                }

               $url = wp_get_attachment_image_url( $id, $wp_size );
               if ( ! $url ) {
                       $url = wp_get_attachment_url( $id );
               }

               if ( ! $url ) {
                       return '';
               }

               return $escape ? esc_url( $url ) : $url;
       }
}
