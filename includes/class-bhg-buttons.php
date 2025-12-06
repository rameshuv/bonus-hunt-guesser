<?php
/**
 * Storage and rendering helpers for configurable CTA buttons.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BHG_Buttons {

    /**
     * Fetch active buttons filtered by placement.
     *
     * @param string $placement Placement key.
     * @return array<int,object>
     */
    public static function get_buttons( $placement = '' ) {
        global $wpdb;

        $table = esc_sql( $wpdb->prefix . 'bhg_buttons' );
        if ( ! $table ) {
            return array();
        }

        $where  = array( 'active = 1' );
        $params = array();

        if ( $placement ) {
            $where[]  = 'placement = %s';
            $params[] = $placement;
        }

        $where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $params ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC, id DESC", ...$params ) ) : $wpdb->get_results( "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC, id DESC" );
    }

    /**
     * Insert or update a button row.
     *
     * @param array $data Input data.
     * @return int Button ID.
     */
    public static function save_button( $data ) {
        global $wpdb;

        $table = esc_sql( $wpdb->prefix . 'bhg_buttons' );
        if ( ! $table ) {
            return 0;
        }

        $id       = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
        $now      = current_time( 'mysql' );
        $payload  = array(
            'title'            => sanitize_text_field( $data['title'] ?? '' ),
            'text'             => sanitize_text_field( $data['text'] ?? '' ),
            'placement'        => sanitize_key( $data['placement'] ?? 'none' ),
            'visible_to'       => sanitize_key( $data['visible_to'] ?? 'all' ),
            'visible_when'     => sanitize_key( $data['visible_when'] ?? 'always' ),
            'link_url'         => esc_url_raw( $data['link_url'] ?? '' ),
            'link_target'      => in_array( $data['link_target'] ?? '_self', array( '_self', '_blank' ), true ) ? $data['link_target'] : '_self',
            'background'       => sanitize_text_field( $data['background'] ?? '' ),
            'background_hover' => sanitize_text_field( $data['background_hover'] ?? '' ),
            'text_color'       => sanitize_text_field( $data['text_color'] ?? '' ),
            'text_hover'       => sanitize_text_field( $data['text_hover'] ?? '' ),
            'border_color'     => sanitize_text_field( $data['border_color'] ?? '' ),
            'text_size'        => isset( $data['text_size'] ) ? (int) $data['text_size'] : null,
            'size'             => in_array( $data['size'] ?? 'medium', array( 'small', 'medium', 'big' ), true ) ? $data['size'] : 'medium',
            'active'           => ! empty( $data['active'] ) ? 1 : 0,
            'updated_at'       => $now,
        );

        if ( $id > 0 ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->update( $table, $payload, array( 'id' => $id ) );
            return $id;
        }

        $payload['created_at'] = $now;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->insert( $table, $payload );

        return (int) $wpdb->insert_id;
    }

    /**
     * Delete a button.
     *
     * @param int $id Button ID.
     * @return void
     */
    public static function delete_button( $id ) {
        global $wpdb;

        $table = esc_sql( $wpdb->prefix . 'bhg_buttons' );
        if ( ! $table ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete( $table, array( 'id' => absint( $id ) ) );
    }

    /**
     * Render buttons assigned to a placement.
     *
     * @param string $placement Placement key.
     * @return string
     */
    public static function render_for_placement( $placement ) {
        $buttons = self::get_buttons( $placement );
        if ( empty( $buttons ) ) {
            return '';
        }

        $output = '';
        foreach ( $buttons as $button ) {
            if ( ! self::user_can_see( $button->visible_to ) || ! self::timing_allows( $button->visible_when ) ) {
                continue;
            }

            $output .= self::render_button_markup( $button );
        }

        if ( '' === $output ) {
            return '';
        }

        return '<div class="bhg-button-stack">' . $output . '</div>';
    }

    /**
     * Convert a stored button to frontend markup.
     *
     * @param object $button Button row.
     * @return string
     */
    public static function render_button_markup( $button ) {
        $label  = $button->text ?: bhg_t( 'cta_guess_now', 'Guess Now' );
        $link   = $button->link_url ? esc_url( $button->link_url ) : '#';
        $target = in_array( $button->link_target, array( '_self', '_blank' ), true ) ? $button->link_target : '_self';
        $size   = in_array( $button->size, array( 'small', 'medium', 'big' ), true ) ? $button->size : 'medium';

        $classes = array( 'bhg-button-shortcode', 'bhg-button-size-' . $size );
        $style   = array();

        if ( $button->background ) {
            $style[] = 'background-color:' . sanitize_hex_color( $button->background );
        }
        if ( $button->text_color ) {
            $style[] = 'color:' . sanitize_hex_color( $button->text_color );
        }
        if ( $button->border_color ) {
            $style[] = 'border-color:' . sanitize_hex_color( $button->border_color );
        }
        if ( $button->text_size ) {
            $style[] = 'font-size:' . (float) $button->text_size . 'px';
        }

        $hover_rules = array();
        if ( $button->background_hover ) {
            $hover_rules[] = '--bhg-button-bg-hover:' . sanitize_hex_color( $button->background_hover );
        }
        if ( $button->text_hover ) {
            $hover_rules[] = '--bhg-button-text-hover:' . sanitize_hex_color( $button->text_hover );
        }

        $style_attr = $style ? ' style="' . esc_attr( implode( ';', $style ) ) . ( $hover_rules ? ';' . implode( ';', $hover_rules ) : '' ) . '"' : ( $hover_rules ? ' style="' . esc_attr( implode( ';', $hover_rules ) ) . '"' : '' );

        return sprintf(
            '<a class="%1$s" href="%2$s" target="%3$s"%5$s>%4$s</a>',
            esc_attr( implode( ' ', $classes ) ),
            esc_url( $link ),
            esc_attr( $target ),
            esc_html( $label ),
            $style_attr
        );
    }

    /**
     * Check whether the current user can see a button.
     *
     * @param string $visible_to Visibility rule.
     * @return bool
     */
    public static function user_can_see( $visible_to ) {
        switch ( $visible_to ) {
            case 'guests':
                return ! is_user_logged_in();
            case 'logged_in':
                return is_user_logged_in();
            case 'affiliates':
                return is_user_logged_in() && function_exists( 'bhg_is_user_affiliate' ) && bhg_is_user_affiliate( get_current_user_id() );
            case 'non_affiliates':
                return ! is_user_logged_in() || ! ( function_exists( 'bhg_is_user_affiliate' ) && bhg_is_user_affiliate( get_current_user_id() ) );
            default:
                return true;
        }
    }

    /**
     * Determine if a button should render for the current state.
     *
     * @param string $when Timing keyword.
     * @return bool
     */
    public static function timing_allows( $when ) {
        switch ( $when ) {
            case 'active_bonushunt':
                return self::has_open_hunt();
            case 'active_tournament':
                return self::has_open_tournament();
            default:
                return true;
        }
    }

    /**
     * Whether an open hunt exists.
     *
     * @return bool
     */
    private static function has_open_hunt() {
        global $wpdb;

        $hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
        if ( ! $hunts_table ) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$hunts_table} WHERE status = %s", 'open' ) );
    }

    /**
     * Whether an open tournament exists.
     *
     * @return bool
     */
    private static function has_open_tournament() {
        global $wpdb;

        $table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
        if ( ! $table ) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$table} WHERE status = %s", 'open' ) );
    }
}
