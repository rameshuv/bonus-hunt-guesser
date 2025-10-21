<?php
/**
 * Notifications manager for Bonus Hunt Guesser.
 *
 * Handles storage of notification templates and dispatching of emails for
 * closed bonus hunts and tournaments.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for persisting notification settings and sending emails.
 */
class BHG_Notifications {

    const OPTION_KEY = 'bhg_notification_settings';

    /**
     * Ensure defaults exist.
     *
     * @return void
     */
    public static function maybe_seed_defaults() {
        $stored = get_option( self::OPTION_KEY, null );

        if ( null !== $stored && is_array( $stored ) ) {
            return;
        }

        update_option( self::OPTION_KEY, self::get_defaults() );
    }

    /**
     * Return default configuration.
     *
     * @return array
     */
    public static function get_defaults() {
        return array(
            'winners'     => array(
                'enabled'     => 0,
                'title'       => '',
                'description' => '',
                'bcc'         => '',
            ),
            'tournaments' => array(
                'enabled'     => 0,
                'title'       => '',
                'description' => '',
                'bcc'         => '',
            ),
            'hunts'       => array(
                'enabled'     => 0,
                'title'       => '',
                'description' => '',
                'bcc'         => '',
            ),
        );
    }

    /**
     * Retrieve merged settings with defaults applied.
     *
     * @return array
     */
    public static function get_settings() {
        $settings = get_option( self::OPTION_KEY, array() );
        $defaults = self::get_defaults();

        foreach ( $defaults as $key => $default_section ) {
            if ( ! isset( $settings[ $key ] ) || ! is_array( $settings[ $key ] ) ) {
                $settings[ $key ] = $default_section;
                continue;
            }

            $settings[ $key ] = wp_parse_args( $settings[ $key ], $default_section );
        }

        return $settings;
    }

    /**
     * Update settings with sanitized values.
     *
     * @param array $raw Raw input.
     *
     * @return array Sanitized configuration.
     */
    public static function update_settings( array $raw ) {
        $defaults  = self::get_defaults();
        $sanitized = array();

        foreach ( $defaults as $key => $default_section ) {
            $section = isset( $raw[ $key ] ) && is_array( $raw[ $key ] ) ? $raw[ $key ] : array();

            $sanitized[ $key ] = array(
                'enabled'     => isset( $section['enabled'] ) ? 1 : 0,
                'title'       => isset( $section['title'] ) ? sanitize_text_field( wp_unslash( $section['title'] ) ) : '',
                'description' => isset( $section['description'] ) ? wp_kses_post( wp_unslash( $section['description'] ) ) : '',
                'bcc'         => self::sanitize_bcc_list( isset( $section['bcc'] ) ? $section['bcc'] : '' ),
            );
        }

        update_option( self::OPTION_KEY, $sanitized );

        return $sanitized;
    }

    /**
     * Handle notifications when a hunt is closed.
     *
     * @param int     $hunt_id       Hunt identifier.
     * @param float   $final_balance Final balance value.
     * @param int[]   $winner_ids    Winner user IDs.
     *
     * @return void
     */
    public static function handle_hunt_closed( $hunt_id, $final_balance, array $winner_ids ) {
        $settings = self::get_settings();

        if ( empty( $settings['winners']['enabled'] ) && empty( $settings['hunts']['enabled'] ) ) {
            return;
        }

        global $wpdb;

        $hunt_id = absint( $hunt_id );

        if ( $hunt_id <= 0 ) {
            return;
        }

        $hunts_table     = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
        $guesses_table   = esc_sql( $wpdb->prefix . 'bhg_guesses' );
        $winners_table   = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
        $hunt_row        = $wpdb->get_row( $wpdb->prepare( "SELECT title, final_balance FROM {$hunts_table} WHERE id = %d", $hunt_id ) );
        $hunt_title      = $hunt_row && isset( $hunt_row->title ) ? (string) $hunt_row->title : '';
        $final_balance_f = $hunt_row && isset( $hunt_row->final_balance ) ? (float) $hunt_row->final_balance : (float) $final_balance;

        $winner_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, position, guess, diff FROM {$winners_table} WHERE hunt_id = %d ORDER BY position ASC",
                $hunt_id
            )
        );

        $winner_details = array();
        $winner_names   = array();

        if ( ! empty( $winner_rows ) ) {
            foreach ( $winner_rows as $row ) {
                $user_id  = isset( $row->user_id ) ? (int) $row->user_id : 0;
                $position = isset( $row->position ) ? (int) $row->position : 0;
                $guess    = isset( $row->guess ) ? (float) $row->guess : 0.0;
                $diff     = isset( $row->diff ) ? (float) $row->diff : 0.0;

                if ( $user_id <= 0 ) {
                    continue;
                }

                $user = get_userdata( $user_id );
                if ( ! $user ) {
                    continue;
                }

                $winner_details[ $user_id ] = array(
                    'position' => $position,
                    'guess'    => $guess,
                    'diff'     => $diff,
                    'user'     => $user,
                );

                $winner_names[] = $user->display_name ? $user->display_name : $user->user_login;
            }
        }

        $winner_list = implode( ', ', $winner_names );
        $site_name   = get_bloginfo( 'name' );
        $common      = array(
            'site_name'      => esc_html( $site_name ),
            'hunt_title'     => esc_html( $hunt_title ),
            'final_balance'  => esc_html( bhg_format_money( $final_balance_f ) ),
            'winner_names'   => esc_html( $winner_list ),
            'winner_list'    => esc_html( $winner_list ),
        );

        if ( ! empty( $settings['hunts']['enabled'] ) ) {
            $participant_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT user_id FROM {$guesses_table} WHERE hunt_id = %d",
                    $hunt_id
                )
            );

            if ( ! empty( $participant_ids ) ) {
                foreach ( $participant_ids as $participant_id ) {
                    $participant_id = (int) $participant_id;
                    if ( $participant_id <= 0 ) {
                        continue;
                    }

                    $user = get_userdata( $participant_id );
                    if ( ! $user ) {
                        continue;
                    }

                    $tokens = array_merge(
                        $common,
                        array(
                            'username'     => esc_html( $user->user_login ),
                            'display_name' => esc_html( $user->display_name ? $user->display_name : $user->user_login ),
                        )
                    );

                    self::send_email(
                        $user->user_email,
                        $settings['hunts']['title'],
                        $settings['hunts']['description'],
                        $settings['hunts']['bcc'],
                        $tokens
                    );
                }
            }
        }

        if ( empty( $settings['winners']['enabled'] ) || empty( $winner_details ) ) {
            return;
        }

        foreach ( $winner_details as $user_id => $data ) {
            if ( ! isset( $data['user'] ) || ! $data['user'] instanceof WP_User ) {
                continue;
            }

            /** @var WP_User $user */
            $user = $data['user'];

            $tokens = array_merge(
                $common,
                array(
                    'username'     => esc_html( $user->user_login ),
                    'display_name' => esc_html( $user->display_name ? $user->display_name : $user->user_login ),
                    'position'     => esc_html( (string) $data['position'] ),
                    'guess'        => esc_html( bhg_format_money( $data['guess'] ) ),
                    'difference'   => esc_html( bhg_format_money( $data['diff'] ) ),
                )
            );

            self::send_email(
                $user->user_email,
                $settings['winners']['title'],
                $settings['winners']['description'],
                $settings['winners']['bcc'],
                $tokens
            );
        }
    }

    /**
     * Handle notifications when a tournament is closed.
     *
     * @param int $tournament_id Tournament identifier.
     *
     * @return void
     */
    public static function handle_tournament_closed( $tournament_id ) {
        $settings = self::get_settings();

        if ( empty( $settings['tournaments']['enabled'] ) ) {
            return;
        }

        $tournament_id = absint( $tournament_id );

        if ( $tournament_id <= 0 ) {
            return;
        }

        global $wpdb;

        $tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
        $results_table     = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );

        $tournament = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT title FROM {$tournaments_table} WHERE id = %d",
                $tournament_id
            )
        );

        $title = $tournament && isset( $tournament->title ) ? (string) $tournament->title : '';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, wins, last_win_date FROM {$results_table} WHERE tournament_id = %d ORDER BY wins DESC, last_win_date ASC, user_id ASC",
                $tournament_id
            )
        );

        if ( empty( $rows ) ) {
            return;
        }

        $site_name = get_bloginfo( 'name' );
        $position  = 1;

        foreach ( $rows as $row ) {
            $user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;
            if ( $user_id <= 0 ) {
                continue;
            }

            $user = get_userdata( $user_id );
            if ( ! $user ) {
                ++$position;
                continue;
            }

            $tokens = array(
                'site_name'        => esc_html( $site_name ),
                'tournament_title' => esc_html( $title ),
                'username'         => esc_html( $user->user_login ),
                'display_name'     => esc_html( $user->display_name ? $user->display_name : $user->user_login ),
                'wins'             => esc_html( number_format_i18n( isset( $row->wins ) ? (int) $row->wins : 0 ) ),
                'position'         => esc_html( (string) $position ),
            );

            self::send_email(
                $user->user_email,
                $settings['tournaments']['title'],
                $settings['tournaments']['description'],
                $settings['tournaments']['bcc'],
                $tokens
            );

            ++$position;
        }
    }

    /**
     * Sanitize BCC string.
     *
     * @param string $value Raw BCC value.
     *
     * @return string
     */
    private static function sanitize_bcc_list( $value ) {
        if ( '' === $value || null === $value ) {
            return '';
        }

        $parts  = preg_split( '/[\r\n;,]+/', (string) $value );
        $emails = array();

        if ( $parts ) {
            foreach ( $parts as $part ) {
                $email = sanitize_email( trim( $part ) );
                if ( $email ) {
                    $emails[ $email ] = $email;
                }
            }
        }

        return implode( ', ', array_values( $emails ) );
    }

    /**
     * Extract BCC addresses.
     *
     * @param string $bcc Stored BCC string.
     *
     * @return array
     */
    private static function get_bcc_addresses( $bcc ) {
        if ( '' === $bcc ) {
            return array();
        }

        $parts  = preg_split( '/[\r\n;,]+/', $bcc );
        $emails = array();

        if ( $parts ) {
            foreach ( $parts as $part ) {
                $email = sanitize_email( trim( $part ) );
                if ( $email ) {
                    $emails[ $email ] = $email;
                }
            }
        }

        return array_values( $emails );
    }

    /**
     * Replace tokens in the provided template.
     *
     * @param string $template Template string.
     * @param array  $tokens   Token map.
     *
     * @return string
     */
    private static function replace_tokens( $template, array $tokens ) {
        if ( '' === $template ) {
            return '';
        }

        $search  = array();
        $replace = array();

        foreach ( $tokens as $token => $value ) {
            $search[]  = '{{' . $token . '}}';
            $replace[] = $value;
        }

        return str_replace( $search, $replace, $template );
    }

    /**
     * Send email using provided template values.
     *
     * @param string $to                Recipient email.
     * @param string $subject_template  Subject template.
     * @param string $body_template     Body template (HTML allowed).
     * @param string $bcc               BCC list.
     * @param array  $tokens            Token values.
     *
     * @return void
     */
    private static function send_email( $to, $subject_template, $body_template, $bcc, array $tokens ) {
        $to = sanitize_email( $to );

        if ( ! $to ) {
            return;
        }

        $subject = trim( self::replace_tokens( $subject_template, $tokens ) );
        if ( '' === $subject ) {
            $subject = isset( $tokens['hunt_title'] ) ? sprintf( __( 'Bonus Hunt Update: %s', 'bonus-hunt-guesser' ), $tokens['hunt_title'] ) : __( 'Bonus Hunt Update', 'bonus-hunt-guesser' );
        }

        $body = trim( self::replace_tokens( $body_template, $tokens ) );
        if ( '' === $body ) {
            $body = __( 'Congratulations! Please contact support for more details.', 'bonus-hunt-guesser' );
        }

        $headers = array(
            'From: ' . BHG_Utils::get_email_from(),
            'Content-Type: text/html; charset=UTF-8',
        );

        $bcc_addresses = self::get_bcc_addresses( $bcc );
        if ( ! empty( $bcc_addresses ) ) {
            $headers[] = 'Bcc: ' . implode( ', ', $bcc_addresses );
        }

        wp_mail( $to, $subject, wpautop( $body ), $headers );
    }
}
