<?php
/**
 * Badge storage and evaluation helpers.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

class BHG_Badges {

        /**
         * Cached badge collection for the request.
         *
         * @var array<int,object>|null
         */
        private static $all_cache = null;

        /**
         * Cached table existence flags keyed by table name for the request.
         *
         * @var array<string,bool>
         */
        private static $table_exists = array();

        /**
         * Cached badge matches per user for the request.
         *
         * @var array<int,array<int,object>>
         */
        private static $user_badges_cache = array();

        /**
         * Cached rendered badge markup per user for the request.
         *
         * @var array<int,string>
         */
        private static $render_cache = array();

        /**
         * Fetch all badges.
         *
         * @return array<int,object>
         */
        public static function all() {
                global $wpdb;

                if ( null !== self::$all_cache ) {
                        return self::$all_cache;
                }

                $table_name = $wpdb->prefix . 'bhg_badges';
                $table      = esc_sql( $table_name );
                if ( ! self::badges_table_exists( $table_name ) ) {
                        self::$all_cache = array();
                        return self::$all_cache;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                self::$all_cache = (array) $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC, id DESC" );

                return self::$all_cache;
        }

        /**
         * Save or update a badge row.
         *
         * @param array $data Badge payload.
         * @return int Badge ID.
         */
        public static function save( $data ) {
                global $wpdb;

                $table_name = $wpdb->prefix . 'bhg_badges';
                $table      = esc_sql( $table_name );
                if ( ! self::badges_table_exists( $table_name ) ) {
                        return 0;
                }

                $id   = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
                $now  = current_time( 'mysql' );
                $row  = array(
                        'title'            => sanitize_text_field( $data['title'] ?? '' ),
                        'image_id'         => isset( $data['image_id'] ) ? absint( $data['image_id'] ) : 0,
                        'affiliate_site_id'=> isset( $data['affiliate_site_id'] ) ? absint( $data['affiliate_site_id'] ) : null,
                        'user_data'        => sanitize_key( $data['user_data'] ?? 'none' ),
                        'threshold'        => isset( $data['threshold'] ) ? max( 0, (int) $data['threshold'] ) : 0,
                        'active'           => empty( $data['active'] ) ? 0 : 1,
                        'updated_at'       => $now,
                );

                if ( $id > 0 ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $wpdb->update( $table, $row, array( 'id' => $id ) );
                        self::reset_cache();
                        return $id;
                }

                $row['created_at'] = $now;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->insert( $table, $row );
                self::reset_cache();

                return (int) $wpdb->insert_id;
        }

        /**
         * Delete a badge.
         *
         * @param int $id Badge ID.
         * @return void
         */
        public static function delete( $id ) {
                global $wpdb;

                $table_name = $wpdb->prefix . 'bhg_badges';
                $table      = esc_sql( $table_name );
                if ( ! self::badges_table_exists( $table_name ) ) {
                        return;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->delete( $table, array( 'id' => absint( $id ) ) );

                self::reset_cache();
        }

        /**
         * Get badge markup for a user.
         *
         * @param int $user_id User ID.
         * @return string
         */
        public static function render_for_user( $user_id ) {
                $user_id = (int) $user_id;
                if ( isset( self::$render_cache[ $user_id ] ) ) {
                        return self::$render_cache[ $user_id ];
                }

                $badges = self::get_user_badges( $user_id );
                if ( empty( $badges ) ) {
                        self::$render_cache[ $user_id ] = '';
                        return '';
                }

                $parts = array();
                foreach ( $badges as $badge ) {
                        $parts[] = self::render_badge( $badge );
                }

                self::$render_cache[ $user_id ] = $parts ? '<span class="bhg-user-badges">' . implode( '', $parts ) . '</span>' : '';

                return self::$render_cache[ $user_id ];
        }

        /**
         * Retrieve qualifying badges for a user.
         *
         * @param int $user_id User ID.
         * @return array<int,object>
         */
        public static function get_user_badges( $user_id ) {
                $user_id = (int) $user_id;
                if ( $user_id <= 0 ) {
                        return array();
                }

                if ( isset( self::$user_badges_cache[ $user_id ] ) ) {
                        return self::$user_badges_cache[ $user_id ];
                }

                $badges = array_filter( self::all(), static function ( $badge ) {
                        return ! empty( $badge->active );
                } );

                if ( empty( $badges ) ) {
                        self::$user_badges_cache[ $user_id ] = array();
                        return array();
                }

                self::$user_badges_cache[ $user_id ] = array_values(
                        array_filter(
                                $badges,
                                static function ( $badge ) use ( $user_id ) {
                                        return self::user_meets_badge( $user_id, $badge );
                                }
                        )
                );

                return self::$user_badges_cache[ $user_id ];
        }

        /**
         * Reset in-request caches after a mutation.
         *
         * @return void
         */
        private static function reset_cache() {
                self::$all_cache          = null;
                self::$user_badges_cache  = array();
                self::$render_cache       = array();
                self::$table_exists       = array();
        }

        /**
         * Determine if the badges table exists.
         *
         * @param string $table Table name (unescaped).
         * @return bool
         */
        private static function badges_table_exists( $table ) {
                global $wpdb;

                if ( empty( $table ) ) {
                        return false;
                }

                if ( isset( self::$table_exists[ $table ] ) ) {
                        return self::$table_exists[ $table ];
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $table_like = $wpdb->esc_like( $table );
                $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_like ) );

                self::$table_exists[ $table ] = ! empty( $found );

                return self::$table_exists[ $table ];
        }

        /**
         * Determine if a user qualifies for a badge.
         *
         * @param int    $user_id User ID.
         * @param object $badge   Badge config.
         * @return bool
         */
private static function user_meets_badge( $user_id, $badge ) {
$threshold = isset( $badge->threshold ) ? (int) $badge->threshold : 0;
$metric    = isset( $badge->user_data ) ? (string) $badge->user_data : 'none';
$site_id   = isset( $badge->affiliate_site_id ) ? (int) $badge->affiliate_site_id : 0;

                // Affiliate website based badges require membership.
                if ( $site_id > 0 && ! bhg_is_user_affiliate_for_site( $user_id, $site_id ) ) {
                        return false;
                }

                switch ( $metric ) {
                        case 'bonushunt_wins':
                                return self::get_bonushunt_wins( $user_id ) >= $threshold;
                        case 'tournament_wins':
                                return self::get_tournament_wins( $user_id ) >= $threshold;
                        case 'guesses':
                                return self::get_total_guesses( $user_id ) >= $threshold;
                        case 'registration_days':
                                return self::get_days_since_registration( $user_id ) >= $threshold;
case 'affiliate_days':
$days = self::get_days_affiliate_active( $user_id, $site_id );
if ( $days < 0 ) {
return false;
}

return $days >= $threshold;
case 'none':
default:
if ( $site_id > 0 ) {
$days = self::get_days_affiliate_active( $user_id, $site_id );
if ( $days < 0 ) {
return false;
}

return $days >= $threshold;
}

return false;
}
}

        /**
         * Render a single badge element.
         *
         * @param object $badge Badge config.
         * @return string
         */
        private static function render_badge( $badge ) {
                $title = isset( $badge->title ) ? $badge->title : '';
                $img   = isset( $badge->image_id ) ? (int) $badge->image_id : 0;

                if ( $img ) {
                        $image = wp_get_attachment_image( $img, 'thumbnail', false, array( 'class' => 'bhg-badge-image', 'alt' => esc_attr( $title ) ) );
                        if ( $image ) {
                                return '<span class="bhg-badge bhg-badge--image" title="' . esc_attr( $title ) . '">' . wp_kses_post( $image ) . '</span>';
                        }
                }

                return '<span class="bhg-badge" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</span>';
        }

        /**
         * Count total hunt wins for a user.
         *
         * @param int $user_id User ID.
         * @return int
         */
        private static function get_bonushunt_wins( $user_id ) {
                global $wpdb;

                $table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
                if ( ! $table ) {
                        return 0;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$table} WHERE user_id = %d AND eligible = 1", $user_id ) );
        }

        /**
         * Count total tournament wins for a user.
         *
         * @param int $user_id User ID.
         * @return int
         */
        private static function get_tournament_wins( $user_id ) {
                global $wpdb;

                $table = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
                if ( ! $table ) {
                        return 0;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                return (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(wins) FROM {$table} WHERE user_id = %d", $user_id ) );
        }

        /**
         * Count total guesses for a user.
         *
         * @param int $user_id User ID.
         * @return int
         */
        private static function get_total_guesses( $user_id ) {
                global $wpdb;

                $table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
                if ( ! $table ) {
                        return 0;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$table} WHERE user_id = %d", $user_id ) );
        }

        /**
         * Days since registration.
         *
         * @param int $user_id User ID.
         * @return int
         */
        private static function get_days_since_registration( $user_id ) {
                $user = get_userdata( $user_id );
                if ( ! $user || empty( $user->user_registered ) ) {
                        return 0;
                }

                $registered = strtotime( $user->user_registered );
                return $registered ? floor( ( time() - $registered ) / DAY_IN_SECONDS ) : 0;
        }

        /**
         * Days since affiliate activation.
         *
         * @param int $user_id User ID.
         * @param int $site_id Affiliate site ID (optional).
         * @return int
         */
private static function get_days_affiliate_active( $user_id, $site_id = 0 ) {
$date = bhg_get_affiliate_activation_date( $user_id, $site_id );
if ( ! $date ) {
return -1;
}

$timestamp = strtotime( $date );
if ( ! $timestamp ) {
return -1;
}

return floor( ( time() - $timestamp ) / DAY_IN_SECONDS );
}
}
