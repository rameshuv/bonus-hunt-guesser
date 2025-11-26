<?php
/**
 * Shortcodes for Bonus Hunt Guesser.
 *
 * PHP 7.4 safe, WP 6.3.5+ compatible.
 * Registers all shortcodes on init (once) and avoids parse errors.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'BHG_Shortcodes' ) ) {

	/**
	 * Handles shortcode registration and rendering.
	 */
        class BHG_Shortcodes {

		/**
		 * Default filter keys for the leaderboard shortcode.
		 *
		 * @var string[]
		 */
		private const LEADERBOARD_DEFAULT_FILTERS = array( 'timeline', 'tournament', 'site', 'affiliate' );

		/**
		 * Flag to prevent enqueueing prize assets multiple times per request.
		 *
		 * @var bool
		 */
		private $prize_assets_enqueued = false;

		/**
		 * Cached profile visibility settings.
		 *
		 * @var array|null
		 */
		private $profile_visibility_settings = null;

               /**
                * Tracks whether shortcodes have already been registered.
                *
                * @var bool
                */
               private static $shortcodes_registered = false;

               /**
                * Registers all shortcodes.
                */
               public function __construct() {
                       add_action( 'init', array( $this, 'register_shortcodes' ), 1 );

                       if ( did_action( 'init' ) ) {
                               $this->register_shortcodes();
                       }
               }

		/**
		 * Register all plugin shortcodes with WordPress.
		 *
		 * @return void
		 */
               public function register_shortcodes() {
                       if ( self::$shortcodes_registered ) {
                               return;
                       }

                       // Core shortcodes.
			add_shortcode( 'bhg_active_hunt', array( $this, 'active_hunt_shortcode' ) );
			add_shortcode( 'bhg_guess_form', array( $this, 'guess_form_shortcode' ) );
			add_shortcode( 'bhg_leaderboard', array( $this, 'leaderboard_shortcode' ) );
			add_shortcode( 'bhg_tournaments', array( $this, 'tournaments_shortcode' ) );
			add_shortcode( 'bhg_winner_notifications', array( $this, 'winner_notifications_shortcode' ) );
			add_shortcode( 'bhg_user_profile', array( $this, 'user_profile_shortcode' ) );

			// Addons.
			add_shortcode( 'bhg_best_guessers', array( $this, 'best_guessers_shortcode' ) );
			add_shortcode( 'bhg_user_guesses', array( $this, 'user_guesses_shortcode' ) );
			add_shortcode( 'bhg_hunts', array( $this, 'hunts_shortcode' ) );
			add_shortcode( 'bhg_leaderboards', array( $this, 'leaderboards_shortcode' ) );
			add_shortcode( 'bhg_prizes', array( $this, 'prizes_shortcode' ) );
			add_shortcode( 'my_bonushunts', array( $this, 'my_bonushunts_shortcode' ) );
			add_shortcode( 'my_tournaments', array( $this, 'my_tournaments_shortcode' ) );
			add_shortcode( 'my_prizes', array( $this, 'my_prizes_shortcode' ) );
			add_shortcode( 'my_rankings', array( $this, 'my_rankings_shortcode' ) );
			add_shortcode( 'bhg_jackpot_current', array( $this, 'jackpot_current_shortcode' ) );
                       add_shortcode( 'bhg_jackpot_ticker', array( $this, 'jackpot_ticker_shortcode' ) );
                       add_shortcode( 'bhg_jackpot_winners', array( $this, 'jackpot_winners_shortcode' ) );
add_shortcode( 'bhg_latest_winners_list', array( $this, 'latest_winners_list_shortcode' ) );
add_shortcode( 'bhg_leaderboard_list', array( $this, 'leaderboard_list_shortcode' ) );
add_shortcode( 'bhg_tournament_list', array( $this, 'tournament_list_shortcode' ) );
add_shortcode( 'bhg_bonushunt_list', array( $this, 'bonushunt_list_shortcode' ) );

// Back-compat aliases for early adopters without the bhg_ prefix.
add_shortcode( 'latest-winners-list', array( $this, 'latest_winners_list_shortcode' ) );
add_shortcode( 'leaderboard-list', array( $this, 'leaderboard_list_shortcode' ) );
add_shortcode( 'tournament-list', array( $this, 'tournament_list_shortcode' ) );
add_shortcode( 'bonushunt-list', array( $this, 'bonushunt_list_shortcode' ) );

			// Legacy/aliases.
			add_shortcode( 'bonus_hunt_leaderboard', array( $this, 'leaderboard_shortcode' ) );
			add_shortcode( 'bonus_hunt_login', array( $this, 'login_hint_shortcode' ) );
                       add_shortcode( 'bhg_active', array( $this, 'active_hunt_shortcode' ) );

                       self::$shortcodes_registered = true;
               }

		/**
		 * Validates a database table name against known tables.
		 *
		 * @param string $table Database table name to validate.
		 * @return string Sanitized table name or empty string if invalid.
		 */
               private function sanitize_table( $table ) {
                               global $wpdb;

                                $allowed = array(
                                                $wpdb->prefix . 'bhg_bonus_hunts',
                                                $wpdb->prefix . 'bhg_guesses',
                                                $wpdb->prefix . 'bhg_tournaments',
                                                $wpdb->prefix . 'bhg_tournament_results',
                                                $wpdb->prefix . 'bhg_affiliate_websites',
                                                $wpdb->prefix . 'bhg_hunt_winners',
                                                $wpdb->prefix . 'bhg_tournaments_hunts',
                                                $wpdb->prefix . 'bhg_prizes',
                                                $wpdb->prefix . 'bhg_hunt_prizes',
                                                $wpdb->prefix . 'bhg_jackpots',
                                                $wpdb->prefix . 'bhg_jackpot_events',
                                                $wpdb->users,
                                                $wpdb->usermeta,
                                );

                               return in_array( $table, $allowed, true ) ? $table : '';
               }

               /**
                * Return an admin-visible notice with a silent front-end comment.
                *
                * @param string $message Notice content.
                * @return string HTML to render.
                */
               private function shortcode_notice( $message ) {
                               $comment = '<!-- ' . esc_html( $message ) . ' -->';

                               if ( current_user_can( 'manage_options' ) ) {
                                               return '<div class="bhg-shortcode-note">' . esc_html( $message ) . '</div>' . $comment;
                               }

                               return $comment;
               }

                /**
                 * Calculates start and end datetime for a given timeline keyword.
                 *
                 * @param string $timeline Timeline keyword.
                 * @return array|null Array with 'start' and 'end' in `Y-m-d H:i:s` or null for no restriction.
                 */
              private function get_timeline_range( $timeline ) {
                              $timeline = strtolower( (string) $timeline );
                              $timeline = str_replace( array( ' ', '-' ), '_', $timeline );

                              if ( '' === $timeline ) {
                                              return null;
                              }

                              if ( 'alltime' === $timeline ) {
                                              $timeline = 'all_time';
                              }

                              $allowed = array(
                                              'today',
                                              'this_week',
                                              'this_month',
                                              'this_quarter',
                                              'this_year',
                                              'last_year',
                                              'all_time',
                              );

                              if ( ! in_array( $timeline, $allowed, true ) ) {
                                              return null;
                              }

                              $canonical = $timeline;

                               $site_timezone = wp_timezone();
                               $now           = new DateTimeImmutable( 'now', $site_timezone );

                               switch ( $canonical ) {
                                       case 'today':
                                               $start_dt = $now->setTime( 0, 0, 0 );
                                               $end_dt   = $now->setTime( 23, 59, 59 );
                                               break;

                                       case 'this_week':
                                               $week     = get_weekstartend( $now->format( 'Y-m-d' ) );
                                               $start_dt = ( new DateTimeImmutable( '@' . $week['start'] ) )->setTimezone( $site_timezone );
                                               $end_dt   = ( new DateTimeImmutable( '@' . $week['end'] ) )->setTimezone( $site_timezone );
                                               break;

                                       case 'this_month':
                                               $start_dt = $now->modify( 'first day of this month' )->setTime( 0, 0, 0 );
                                               $end_dt   = $now->modify( 'last day of this month' )->setTime( 23, 59, 59 );
                                               break;

                                       case 'this_year':
                                               $start_dt = $now->setDate( (int) $now->format( 'Y' ), 1, 1 )->setTime( 0, 0, 0 );
                                               $end_dt   = $now->setDate( (int) $now->format( 'Y' ), 12, 31 )->setTime( 23, 59, 59 );
                                               break;

                                       case 'this_quarter':
                                               $year        = (int) $now->format( 'Y' );
                                               $month       = (int) $now->format( 'n' );
                                               $quarter     = (int) floor( ( $month - 1 ) / 3 ) + 1;
                                               $start_month = ( ( $quarter - 1 ) * 3 ) + 1;
                                               $start_dt    = $now->setDate( $year, $start_month, 1 )->setTime( 0, 0, 0 );
                                               $end_dt      = $start_dt->modify( '+2 months' )->modify( 'last day of this month' )->setTime( 23, 59, 59 );
                                               break;

                                       case 'last_year':
                                               $year     = (int) $now->format( 'Y' ) - 1;
                                               $start_dt = $now->setDate( $year, 1, 1 )->setTime( 0, 0, 0 );
                                               $end_dt   = $now->setDate( $year, 12, 31 )->setTime( 23, 59, 59 );
                                               break;

                                       case 'all_time':
                                       default:
                                               return null;
                               }

                               $utc_timezone = new DateTimeZone( 'UTC' );

                               return array(
                                               'start' => $start_dt->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' ),
                                               'end'   => $end_dt->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' ),
                               );
               }

               /**
                * Normalize yes/no style shortcode attributes to booleans.
                *
                * @param mixed $value   Raw attribute value.
                * @param bool  $default Default boolean when value is empty.
                * @return bool
                */
               private function attribute_to_bool( $value, $default = true ) {
                               if ( null === $value || '' === $value ) {
                                               return (bool) $default;
                               }

                               if ( is_bool( $value ) ) {
                                               return $value;
                               }

                               $value = strtolower( (string) $value );

                               if ( in_array( $value, array( '1', 'true', 'yes', 'on', 'show' ), true ) ) {
                                               return true;
                               }

                               if ( in_array( $value, array( '0', 'false', 'no', 'off', 'hide' ), true ) ) {
                                               return false;
                               }

                               return (bool) $default;
               }

               /**
                * Execute the shared leaderboard aggregation query.
                *
                * @param array $args Query arguments.
                * @return array{
                *     rows: array,
                *     total: int,
                *     per_page: int,
                *     paged: int,
                *     offset: int,
                *     total_pages: int,
                *     orderby_key: string,
                *     direction_key: string,
                *     limit: int,
                *     error: string
                * }
                */
               private function run_leaderboard_query( $args ) {
                               global $wpdb;

                               $defaults = array(
                                               'fields'               => array(),
                                               'timeline'             => '',
                                               'search'               => '',
                                               'tournament_id'        => 0,
                                               'hunt_id'              => 0,
                                               'website_id'           => 0,
                                               'aff_filter'           => '',
'ranking_limit'        => 0,
'paged'                => 1,
'per_page'             => 25,
'orderby'              => 'wins',
'order'                => 'desc',
'website_id'           => 0,
'need_avg_hunt'        => false,
                                               'need_avg_tournament'  => false,
                                               'need_site'            => false,
                                               'need_tournament_name' => false,
                                               'need_hunt_name'       => false,
                                               'need_aff'             => false,
                               );

                $args = wp_parse_args( $args, $defaults );

                $fields               = array_map( 'sanitize_key', (array) $args['fields'] );
                $timeline             = sanitize_key( (string) $args['timeline'] );
                $search               = (string) $args['search'];
                $search_like          = '' !== $search ? '%' . $wpdb->esc_like( $search ) . '%' : '';
                               $tournament_id        = max( 0, (int) $args['tournament_id'] );
                               $hunt_id              = max( 0, (int) $args['hunt_id'] );
                               $website_id           = max( 0, (int) $args['website_id'] );
                               $aff_filter           = sanitize_key( (string) $args['aff_filter'] );
                               $ranking_limit        = max( 0, (int) $args['ranking_limit'] );
$per_page             = max( 1, (int) $args['per_page'] );
$website_id           = max( 0, (int) $args['website_id'] );
                               $paged                = max( 1, (int) $args['paged'] );
                               $orderby_request      = sanitize_key( (string) $args['orderby'] );
                               $direction_key        = strtolower( sanitize_key( (string) $args['order'] ) );
                               $need_avg_hunt        = ! empty( $args['need_avg_hunt'] );
                               $need_avg_tournament  = ! empty( $args['need_avg_tournament'] );
                $need_site            = ! empty( $args['need_site'] );
                $need_tournament_name = ! empty( $args['need_tournament_name'] );
                $need_hunt_name       = ! empty( $args['need_hunt_name'] );
                $need_aff             = ! empty( $args['need_aff'] );
                $need_site_details    = $need_site || $need_aff;

               if ( $tournament_id > 0 && $hunt_id <= 0 ) {
               $tournament_rows = $this->run_tournament_results_leaderboard(
array(
'tournament_id'        => $tournament_id,
'timeline'             => $timeline,
'search'               => $search,
'aff_filter'           => $aff_filter,
'website_id'           => $website_id,
'ranking_limit'        => $ranking_limit,
                                                                'paged'                => $paged,
                                                                'per_page'             => $per_page,
                                                                'orderby'              => $orderby_request,
                                                                'order'                => $direction_key,
                                                                'need_avg_hunt'        => $need_avg_hunt,
                                                                'need_avg_tournament'  => $need_avg_tournament,
                                                                'need_site'            => $need_site,
                                                                'need_tournament_name' => $need_tournament_name,
                                                                'need_hunt_name'       => $need_hunt_name,
'need_aff'             => $need_aff,
'fields'               => $fields,
                                               )
                               );

                                if ( is_array( $tournament_rows ) && empty( $tournament_rows['error'] ) && isset( $tournament_rows['total'] ) && $tournament_rows['total'] >= 1 ) {
                                                return $tournament_rows;
                                }
               }

                               if ( ! in_array( $direction_key, array( 'asc', 'desc' ), true ) ) {
                                               $direction_key = 'desc';
                               }

                               $timeline_filter = ( 'all_time' === $timeline ) ? '' : $timeline;
                               $win_date_expr   = $this->get_leaderboard_win_date_expression();
                               $range           = $this->get_timeline_range( $timeline_filter );

                               // When a specific tournament is selected for all-time results, include
                               // every win regardless of the timeline filter so the leaderboard lists
                               // all participants and prize wins for that tournament.
                               if ( $tournament_id > 0 && 'all_time' === $timeline ) {
                                               $range = null;
                               }

                               $r  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
                               $u  = esc_sql( $this->sanitize_table( $wpdb->users ) );
                               $t  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
                               $w  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
                               $hw = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
                               $h  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
                               $ht = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments_hunts' ) );
                               $um = esc_sql( $this->sanitize_table( $wpdb->usermeta ) );

                               if ( ! $r || ! $u || ! $t || ! $w || ! $hw || ! $h || ! $um || ! $ht ) {
                                               return array(
                                                               'rows'          => array(),
                                                               'total'         => 0,
                                                               'per_page'      => $per_page,
                                                               'paged'         => $paged,
                                                               'offset'        => 0,
                                                               'total_pages'   => 1,
                                                               'orderby_key'   => $orderby_request,
                                                               'direction_key' => $direction_key,
                                                               'limit'         => 0,
                                                               'error'         => bhg_t( 'notice_no_data_available', 'No data available.' ),
                                               );
                               }

                               $aff_yes_values = array( '1', 'yes', 'true', 'on' );
                               $aff_yes_sql    = array();
                               foreach ( $aff_yes_values as $val ) {
                                               $aff_yes_sql[] = '\'' . esc_sql( $val ) . '\'';
                               }
                               $aff_yes_list = implode( ',', $aff_yes_sql );

                               $joins = array(
                                               "INNER JOIN {$h} h ON h.id = hw.hunt_id",
                                               "INNER JOIN {$u} u_filter ON u_filter.ID = hw.user_id",
                               );

                               $where      = array(
                                               'hw.eligible = 1',
                                               'h.status = %s',
                               );
                               $prep_where = array( 'closed' );

                               if ( '' !== $search_like ) {
                                               $where[]      = 'u_filter.user_login LIKE %s';
                                               $prep_where[] = $search_like;
                               }

                               if ( $hunt_id > 0 ) {
                                               $where[]      = 'hw.hunt_id = %d';
                                               $prep_where[] = $hunt_id;
                               }

                               if ( $website_id > 0 ) {
                                               $where[]      = 'h.affiliate_site_id = %d';
                                               $prep_where[] = $website_id;
                               }

                               if ( $tournament_id > 0 ) {
                                               $joins[]     = "LEFT JOIN {$ht} ht ON ht.hunt_id = h.id";
                                               $where[]      = '(ht.tournament_id = %d OR (ht.hunt_id IS NULL AND h.tournament_id = %d))';
                                               $prep_where[] = $tournament_id;
                                               $prep_where[] = $tournament_id;
                               }

                               if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
                                               $joins[] = "INNER JOIN {$um} um_aff_filter ON um_aff_filter.user_id = u_filter.ID AND um_aff_filter.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
                                               $where[] = "CAST(um_aff_filter.meta_value AS CHAR) IN ({$aff_yes_list})";
                               } elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
                                               $joins[] = "LEFT JOIN {$um} um_aff_filter ON um_aff_filter.user_id = u_filter.ID AND um_aff_filter.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
                                               $where[] = "(um_aff_filter.user_id IS NULL OR CAST(um_aff_filter.meta_value AS CHAR) = '' OR CAST(um_aff_filter.meta_value AS CHAR) NOT IN ({$aff_yes_list}))";
                               }

                               if ( $range ) {
                                               $where[]      = '(' . $win_date_expr . ' BETWEEN %s AND %s)';
                                               $prep_where[] = $range['start'];
                                               $prep_where[] = $range['end'];
                               }

                               $joins_sql = implode( ' ', $joins );
                               $where_sql = ' WHERE ' . implode( ' AND ', $where );

                               $base_select_parts = array(
                                               'hw.user_id',
                                               'hw.hunt_id',
                                               'MIN(hw.position) AS position',
                                               'MAX(' . $win_date_expr . ') AS win_date',
                                               'MAX(h.affiliate_site_id) AS affiliate_site_id',
                                               'COUNT(hw.id) AS win_count',
                               );

                               $base_sql = 'SELECT ' . implode( ', ', $base_select_parts ) . " FROM {$hw} hw {$joins_sql}{$where_sql} GROUP BY hw.user_id, hw.hunt_id";

                               if ( ! empty( $prep_where ) ) {
                                               $prepared_base_sql = $wpdb->prepare( $base_sql, ...$prep_where );
                               } else {
                                               $prepared_base_sql = $base_sql;
                               }

                               $aggregate_parts = array(
                                               'fw.user_id',
                                               'SUM(fw.win_count) AS total_wins',
                               );

                               if ( $need_avg_hunt || 'avg_hunt' === $orderby_request ) {
                                               $need_avg_hunt     = true;
                                               $aggregate_parts[] = 'AVG(fw.position) AS avg_hunt_pos';
                               }

                               $base_union_sql = $prepared_base_sql;

                               $allow_union = $tournament_id > 0 && $r && $t && $hunt_id <= 0 && $website_id <= 0;
                               if ( $allow_union ) {
                                               $participant_select = array(
                                                               'tr.user_id',
                                                               '-1 * ABS(tr.user_id) AS hunt_id',
                                                               'NULL AS position',
                                                               "COALESCE( NULLIF( tr.last_win_date, '0000-00-00 00:00:00' ), tr.last_win_date ) AS win_date",
                                                               't_union.affiliate_site_id AS affiliate_site_id',
                                                               '0 AS win_count',
                                               );

                                               $participant_joins = array( "LEFT JOIN {$t} t_union ON t_union.id = tr.tournament_id" );
                                               $participant_where = array( 'tr.tournament_id = %d' );
                                               $participant_params = array( $tournament_id );

                                               if ( '' !== $search_like ) {
                                                               $participant_joins[] = "INNER JOIN {$u} u_part ON u_part.ID = tr.user_id";
                                                               $participant_where[] = 'u_part.user_login LIKE %s';
                                                               $participant_params[] = $search_like;
                                               }

                                               if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
                                                               $participant_joins[] = "INNER JOIN {$um} um_part ON um_part.user_id = tr.user_id AND um_part.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
                                                               $participant_where[] = "CAST(um_part.meta_value AS CHAR) IN ({$aff_yes_list})";
                                               } elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
                                                               $participant_joins[] = "LEFT JOIN {$um} um_part ON um_part.user_id = tr.user_id AND um_part.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
                                                               $participant_where[] = "(um_part.user_id IS NULL OR CAST(um_part.meta_value AS CHAR) = '' OR CAST(um_part.meta_value AS CHAR) NOT IN ({$aff_yes_list}))";
                                               }

                                               if ( $range ) {
                                                               $participant_where[] = "(COALESCE( NULLIF( tr.last_win_date, '0000-00-00 00:00:00' ), tr.last_win_date ) BETWEEN %s AND %s)";
                                                               $participant_params[] = $range['start'];
                                                               $participant_params[] = $range['end'];
                                               }

                                               $participant_sql = 'SELECT ' . implode( ', ', $participant_select ) . " FROM {$r} tr " . implode( ' ', $participant_joins );
                                               if ( ! empty( $participant_where ) ) {
                                                               $participant_sql .= ' WHERE ' . implode( ' AND ', $participant_where );
                                               }

                                               $prepared_participant_sql = $wpdb->prepare( $participant_sql, ...$participant_params );
                                               $base_union_sql          = '(' . $prepared_base_sql . ') UNION ALL (' . $prepared_participant_sql . ')';
                               }

                               $filtered_wrapper_sql = '(' . $base_union_sql . ')';
                               $aggregate_sql        = 'SELECT ' . implode( ', ', $aggregate_parts ) . ' FROM ' . $filtered_wrapper_sql . ' fw GROUP BY fw.user_id';

                               $count_sql = 'SELECT COUNT(*) FROM (' . $aggregate_sql . ') wins';
                               $total     = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

                               if ( $total <= 0 ) {
                                               return array(
                                                               'rows'          => array(),
                                                               'total'         => 0,
                                                               'per_page'      => $per_page,
                                                               'paged'         => $paged,
                                                               'offset'        => 0,
                                                               'total_pages'   => 1,
                                                               'orderby_key'   => $orderby_request,
                                                               'direction_key' => $direction_key,
                                                               'limit'         => 0,
                                                               'error'         => bhg_t( 'notice_no_data_available', 'No data available.' ),
                                               );
                               }

                               if ( $ranking_limit > 0 && $total > $ranking_limit ) {
                                               $total = $ranking_limit;
                               }

                               $pages = (int) ceil( $total / $per_page );
                               if ( $pages < 1 ) {
                                               $pages = 1;
                               }

                               if ( $paged > $pages ) {
                                               $paged = $pages;
                               }

                               $offset = ( $paged - 1 ) * $per_page;
                               if ( $total <= $offset ) {
                                               $offset = max( 0, ( $pages - 1 ) * $per_page );
                               }

                               $limit       = min( $per_page, max( 1, $total - $offset ) );
                               $total_pages = $pages;

                               $select_parts = array(
                                               'wins.user_id',
                                               'u.user_login',
                                               'wins.total_wins',
                               );

                               if ( $need_avg_hunt ) {
                                               $select_parts[] = 'wins.avg_hunt_pos';
                               }

                               if ( $need_avg_tournament || 'avg_tournament' === $orderby_request ) {
                                               $need_avg_tournament = true;

                                               $rank_where     = array();
                                               $rank_joins     = array();
                                               $rank_params    = array();
                                               $rank_where_sql = '';

                                               if ( $tournament_id > 0 ) {
                                                               $rank_where[]  = 'tr.tournament_id = %d';
                                                               $rank_params[] = $tournament_id;
                                               }

                                               if ( $website_id > 0 ) {
                                                               $rank_joins[]  = "INNER JOIN {$t} t_rank ON t_rank.id = tr.tournament_id";
                                                               $rank_where[]  = 't_rank.affiliate_site_id = %d';
                                                               $rank_params[] = $website_id;
                                               }

                                               if ( $range ) {
                                                               $rank_where[]  = "(COALESCE( NULLIF( tr.last_win_date, '0000-00-00 00:00:00' ), tr.last_win_date ) BETWEEN %s AND %s)";
                                                               $rank_params[] = $range['start'];
                                                               $rank_params[] = $range['end'];
                                               }

                                               if ( ! empty( $rank_where ) ) {
                                                               $rank_where_sql = ' WHERE ' . implode( ' AND ', $rank_where );
                                               }

                                               $rank_join_sql = $rank_joins ? ' ' . implode( ' ', $rank_joins ) : '';

                                               $select_parts[] = 'tour_avg.avg_tournament_pos';

                                               $rank_query = 'SELECT tr.user_id, tr.tournament_id, (SELECT 1 + COUNT(*) FROM ' . $r . ' tr2 WHERE tr2.tournament_id = tr.tournament_id AND (tr2.wins > tr.wins OR (tr2.wins = tr.wins AND tr2.user_id < tr.user_id))) AS rank_position FROM ' . $r . ' tr' . $rank_join_sql . $rank_where_sql;

                                               if ( ! empty( $rank_params ) ) {
                                                               $rank_query = $wpdb->prepare( $rank_query, ...$rank_params );
                                               }

                                               $tour_join = "LEFT JOIN (SELECT ranks.user_id, AVG(ranks.rank_position) AS avg_tournament_pos FROM ({$rank_query}) AS ranks GROUP BY ranks.user_id) AS tour_avg ON tour_avg.user_id = wins.user_id";
                               } else {
                                               $tour_join = '';
                               }

                               $latest_hunt_subquery = '(SELECT fw_inner.hunt_id FROM ' . $filtered_wrapper_sql . ' fw_inner WHERE fw_inner.user_id = wins.user_id AND fw_inner.hunt_id > 0 ORDER BY fw_inner.win_date DESC, fw_inner.hunt_id DESC LIMIT 1)';

                               if ( $need_site_details ) {
                                               $select_parts[] = '(SELECT h2.affiliate_site_id FROM ' . $h . ' h2 WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS site_id';
                                               if ( $need_site ) {
                                                               $select_parts[] = '(SELECT w2.name FROM ' . $w . ' w2 INNER JOIN ' . $h . ' h2 ON h2.affiliate_site_id = w2.id WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS site_name';
                                               }
                               }

                               if ( $need_hunt_name ) {
                                               $select_parts[] = '(SELECT h2.title FROM ' . $h . ' h2 WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS hunt_title';
                               }

                               if ( $need_tournament_name ) {
                                               $select_parts[] = '(SELECT COALESCE(t2.title, t2_legacy.title) FROM ' . $h . ' h2 LEFT JOIN ' . $ht . ' ht2 ON ht2.hunt_id = h2.id LEFT JOIN ' . $t . ' t2 ON t2.id = ht2.tournament_id LEFT JOIN ' . $t . ' t2_legacy ON t2_legacy.id = h2.tournament_id WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS tournament_title';
                               }

                               $select_sql = 'SELECT ' . implode( ', ', $select_parts ) . ' FROM (' . $aggregate_sql . ') wins INNER JOIN ' . $u . ' u ON u.ID = wins.user_id';
                               if ( $tour_join ) {
                                               $select_sql .= ' ' . $tour_join . ' ';
                               }

                               $orderby_map = array(
                                               'wins'           => 'wins.total_wins',
                                               'user'           => 'u.user_login',
                                               'avg_hunt'       => 'wins.avg_hunt_pos',
                                               'avg_tournament' => 'tour_avg.avg_tournament_pos',
                                               'pos'            => 'wins.total_wins',
                               );

                               $direction_map = array(
                                               'asc'  => 'ASC',
                                               'desc' => 'DESC',
                               );

                               $direction = isset( $direction_map[ $direction_key ] ) ? $direction_map[ $direction_key ] : $direction_map['desc'];
                               $orderby   = isset( $orderby_map[ $orderby_request ] ) ? $orderby_map[ $orderby_request ] : $orderby_map['wins'];

                               $order_clauses = array( sprintf( '%s %s', $orderby, $direction ) );
                               if ( 'wins.total_wins' !== $orderby ) {
                                               $order_clauses[] = 'wins.total_wins DESC';
                               }
                               if ( 'u.user_login' !== $orderby ) {
                                               $order_clauses[] = 'u.user_login ASC';
                               }
                               $order_clauses[] = 'wins.user_id ASC';

                               $order_sql   = ' ORDER BY ' . implode( ', ', $order_clauses );
                               $select_sql .= $order_sql;

                               $apply_affiliate_site_filter = $website_id > 0 && function_exists( 'bhg_is_user_affiliate_for_site' );

                               if ( $apply_affiliate_site_filter ) {
                                               // Load all rows for the filtered site so we can drop users without the site enabled.
                                               $rows_all = $wpdb->get_results( $select_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                               $filtered_rows = array();

                                               foreach ( (array) $rows_all as $row_obj ) {
                                                               $uid = isset( $row_obj->user_id ) ? (int) $row_obj->user_id : 0;
                                                               if ( $uid > 0 && bhg_is_user_affiliate_for_site( $uid, $website_id ) ) {
                                                                               $filtered_rows[] = $row_obj;
                                                               }
                                               }

                                               $total       = count( $filtered_rows );
                                               $total_pages = max( 1, (int) ceil( $total / $per_page ) );
                                               if ( $paged > $total_pages ) {
                                                               $paged = $total_pages;
                                               }
                                               $offset = ( $paged - 1 ) * $per_page;
                                               $rows   = array_slice( $filtered_rows, $offset, $per_page );
                                               $limit  = count( $rows );
                               } else {
                                               $select_sql .= ' LIMIT %d OFFSET %d';
                                               $query = $wpdb->prepare( $select_sql, $limit, $offset );
                                               $rows  = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                               }

                               if ( empty( $rows ) ) {
                                               return array(
                                                               'rows'          => array(),
                                                               'total'         => 0,
                                                               'per_page'      => $per_page,
                                                               'paged'         => $paged,
                                                               'offset'        => $offset,
                                                               'total_pages'   => $total_pages,
                                                               'orderby_key'   => $orderby_request,
                                                               'direction_key' => $direction_key,
                                                               'limit'         => $limit,
                                                               'error'         => bhg_t( 'notice_no_data_available', 'No data available.' ),
                                               );
                               }

                return array(
                                'rows'          => $rows,
                                'total'         => $total,
                                'per_page'      => $per_page,
                                'paged'         => $paged,
                                'offset'        => $offset,
                                'total_pages'   => $total_pages,
                                'orderby_key'   => $orderby_request,
                                'direction_key' => $direction_key,
                                'limit'         => $limit,
                                'error'         => '',
                );
                }

                /**
                 * Run a leaderboard query that targets a single tournament's cached results.
                 *
                 * @param array $args Query arguments (same shape as run_leaderboard_query).
                 * @return array|null Result set array or null on failure.
                 */
                private function run_tournament_results_leaderboard( $args ) {
                                global $wpdb;

                                $defaults = array(
                                                'tournament_id'        => 0,
                                                'timeline'             => '',
                                                'search'               => '',
                                                'aff_filter'           => '',
'ranking_limit'        => 0,
'paged'                => 1,
'per_page'             => 25,
'orderby'              => 'wins',
'order'                => 'desc',
'website_id'           => 0,
'need_avg_hunt'        => false,
                                                'need_avg_tournament'  => false,
                                                'need_site'            => false,
                                                'need_tournament_name' => false,
                                                'need_hunt_name'       => false,
                                                'need_aff'             => false,
                                                'fields'               => array(),
                                );

                                $args = wp_parse_args( $args, $defaults );

                                $tournament_id        = max( 0, (int) $args['tournament_id'] );
                                $timeline             = sanitize_key( (string) $args['timeline'] );
                                $search               = sanitize_text_field( (string) $args['search'] );
                                $aff_filter           = sanitize_key( (string) $args['aff_filter'] );
$ranking_limit        = max( 0, (int) $args['ranking_limit'] );
$paged                = max( 1, (int) $args['paged'] );
$per_page             = max( 1, (int) $args['per_page'] );
$website_id           = max( 0, (int) $args['website_id'] );
$orderby_request      = sanitize_key( (string) $args['orderby'] );
                                $direction_key        = strtolower( sanitize_key( (string) $args['order'] ) );
                                $need_avg_hunt        = ! empty( $args['need_avg_hunt'] );
                                $need_avg_tournament  = ! empty( $args['need_avg_tournament'] );
                                $need_site            = ! empty( $args['need_site'] );
                                $need_tournament_name = ! empty( $args['need_tournament_name'] );
                                $need_hunt_name       = ! empty( $args['need_hunt_name'] );
                                $need_aff             = ! empty( $args['need_aff'] );
                                $need_site_details    = $need_site || $need_aff;

                                if ( $tournament_id <= 0 ) {
                                                return null;
                                }

                                if ( ! in_array( $direction_key, array( 'asc', 'desc' ), true ) ) {
                                                $direction_key = 'desc';
                                }

                                $search_like = '' !== $search ? '%' . $wpdb->esc_like( $search ) . '%' : '';
                               $timeline_filter = ( 'all_time' === $timeline ) ? '' : $timeline;
                               $range           = $this->get_timeline_range( $timeline_filter );

                               // Tournament leaderboards include all entries when explicitly requesting
                               // all-time results for a specific tournament.
                               if ( 'all_time' === $timeline ) {
                                               $range = null;
                               }

                                $r  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
                                $u  = esc_sql( $this->sanitize_table( $wpdb->users ) );
                                $um = esc_sql( $this->sanitize_table( $wpdb->usermeta ) );
                                $hw = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
                                $h  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
                                $ht = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments_hunts' ) );
                                $t  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
                                $w  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );

                                if ( ! $r || ! $u ) {
                                                return null;
                                }

                                $has_hw      = (bool) $hw;
                                $has_hunts   = (bool) $h;
                                $has_ht      = (bool) $ht;
                                $has_sites   = (bool) $w;
                                $has_t_table = (bool) $t;

                                $can_use_hunt_meta      = $has_hw && $has_hunts;
                                $needs_tournament_table = ( $need_tournament_name || $website_id > 0 );

                                if ( $need_avg_hunt && ! $can_use_hunt_meta ) {
                                                $need_avg_hunt = false;
                                }

                                if ( $need_hunt_name && ! $can_use_hunt_meta ) {
                                                $need_hunt_name = false;
                                }

                                if ( $need_site_details && ( ! $can_use_hunt_meta || ( $need_site && ! $has_sites ) ) ) {
                                                $need_site_details = false;
                                                $need_site         = false;
                                                $need_aff          = false;
                                }

                                if ( $need_tournament_name && ! $has_t_table ) {
                                                $need_tournament_name = false;
                                }

                                $count_joins = array( "INNER JOIN {$u} u ON u.ID = tr.user_id" );
                                $select_joins = $count_joins;
                                $where        = array( 'tr.tournament_id = %d' );
                                $params       = array( $tournament_id );

                                if ( $needs_tournament_table ) {
                                                if ( ! $has_t_table ) {
                                                                return null;
                                                }

                                                $t_join_clause  = "LEFT JOIN {$t} t_main ON t_main.id = tr.tournament_id";
                                                $count_joins[]  = $t_join_clause;
                                                $select_joins[] = $t_join_clause;

                                                if ( $website_id > 0 ) {
                                                                $where[]  = 't_main.affiliate_site_id = %d';
                                                                $params[] = $website_id;
                                                }
                                }

                                if ( '' !== $search_like ) {
                                                $where[]  = 'u.user_login LIKE %s';
                                                $params[] = $search_like;
                                }

                                if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
                                                if ( ! $um ) {
                                                                return null;
                                                }
                                                $join_clause   = "INNER JOIN {$um} um_aff_filter ON um_aff_filter.user_id = tr.user_id AND um_aff_filter.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
                                                $count_joins[] = $join_clause;
                                                $select_joins[] = $join_clause;
                                                $where[]       = "CAST(um_aff_filter.meta_value AS CHAR) IN ({$aff_yes_list})";
                                } elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
                                                if ( ! $um ) {
                                                                return null;
                                                }
                                                $join_clause   = "LEFT JOIN {$um} um_aff_filter ON um_aff_filter.user_id = tr.user_id AND um_aff_filter.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
                                                $count_joins[] = $join_clause;
                                                $select_joins[] = $join_clause;
                                                $where[]       = "(um_aff_filter.user_id IS NULL OR CAST(um_aff_filter.meta_value AS CHAR) = '' OR CAST(um_aff_filter.meta_value AS CHAR) NOT IN ({$aff_yes_list}))";
                                }

                                if ( $range ) {
                                                $where[]  = "(COALESCE( NULLIF( tr.last_win_date, '0000-00-00 00:00:00' ), tr.last_win_date ) BETWEEN %s AND %s)";
                                                $params[] = $range['start'];
                                                $params[] = $range['end'];
                                }

                                $count_sql = 'SELECT COUNT(DISTINCT tr.user_id) FROM ' . $r . ' tr ' . implode( ' ', $count_joins );
                                if ( ! empty( $where ) ) {
                                                $count_sql .= ' WHERE ' . implode( ' AND ', $where );
                                }

                                $total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

                                if ( $total <= 0 ) {
                                                return array(
                                                                'rows'          => array(),
                                                                'total'         => 0,
                                                                'per_page'      => $per_page,
                                                                'paged'         => $paged,
                                                                'offset'        => 0,
                                                                'total_pages'   => 1,
                                                                'orderby_key'   => $orderby_request,
                                                                'direction_key' => $direction_key,
                                                                'limit'         => 0,
                                                                'error'         => bhg_t( 'notice_no_data_available', 'No data available.' ),
                                                );
                                }

                                if ( $ranking_limit > 0 && $total > $ranking_limit ) {
                                                $total = $ranking_limit;
                                }

                                $pages = (int) ceil( $total / $per_page );
                                if ( $pages < 1 ) {
                                                $pages = 1;
                                }

                                if ( $paged > $pages ) {
                                                $paged = $pages;
                                }

                                $offset = ( $paged - 1 ) * $per_page;
                                if ( $offset >= $total ) {
                                                $offset = max( 0, ( $pages - 1 ) * $per_page );
                                }

                                $limit       = min( $per_page, max( 1, $total - $offset ) );
                                $total_pages = $pages;

                                $win_count_clause = 'tr.wins';
                                if ( $can_use_hunt_meta ) {
                                                $win_date_expr = $this->get_leaderboard_win_date_expression();

                                                $win_where  = array( 'hw_count.eligible = 1' );
                                                $win_params = array();
                                                $win_joins  = $has_ht ? ' LEFT JOIN ' . $ht . ' ht_count ON ht_count.hunt_id = hw_count.hunt_id' : '';

                                                if ( $has_ht ) {
                                                                $win_where[]  = '(ht_count.tournament_id = %d OR (ht_count.hunt_id IS NULL AND h_count.tournament_id = %d))';
                                                                $win_params[] = $tournament_id;
                                                                $win_params[] = $tournament_id;
                                                } else {
                                                                $win_where[]  = 'h_count.tournament_id = %d';
                                                                $win_params[] = $tournament_id;
                                                }

                                                if ( $website_id > 0 ) {
                                                                $win_where[]  = 'h_count.affiliate_site_id = %d';
                                                                $win_params[] = $website_id;
                                                }

                                                if ( $range ) {
                                                                $win_where[]  = '(' . $win_date_expr . ' BETWEEN %s AND %s)';
                                                                $win_params[] = $range['start'];
                                                                $win_params[] = $range['end'];
                                                }

                                                $win_count_sql = sprintf(
                                                                'SELECT hw_count.user_id, COUNT(*) AS win_count FROM %1$s hw_count INNER JOIN %2$s h_count ON h_count.id = hw_count.hunt_id%3$s WHERE %4$s GROUP BY hw_count.user_id',
                                                                $hw,
                                                                $h,
                                                                $win_joins,
                                                                implode( ' AND ', $win_where )
                                                );

                                                if ( ! empty( $win_params ) ) {
                                                                $win_count_sql = $wpdb->prepare( $win_count_sql, ...$win_params );
                                                }

                                                $select_joins[]   = 'LEFT JOIN (' . $win_count_sql . ') win_totals ON win_totals.user_id = tr.user_id';
                                                $win_count_clause = 'COALESCE(win_totals.win_count, tr.wins)';
                                }

                                $orderby_map = array(
                                                'wins'           => 'total_wins',
                                                'user'           => 'u.user_login',
                                                'avg_hunt'       => 'hunt_stats.avg_hunt_pos',
                                                'avg_tournament' => 'tour_rank.avg_tournament_pos',
                                                'pos'            => 'total_wins',
                                );

                                if ( ! isset( $orderby_map[ $orderby_request ] ) ) {
                                                $orderby_request = 'wins';
                                }

                                $direction_map = array(
                                                'asc'  => 'ASC',
                                                'desc' => 'DESC',
                                );

                                $direction = isset( $direction_map[ $direction_key ] ) ? $direction_map[ $direction_key ] : $direction_map['desc'];
                                $orderby   = $orderby_map[ $orderby_request ];

                                $select_parts = array(
                                                'tr.user_id',
                                                'u.user_login',
                                                $win_count_clause . ' AS total_wins',
                                );

                               if ( $need_avg_hunt ) {
                                               $avg_scope_condition = $has_ht
                                                               ? sprintf( '(ht_scope.tournament_id = %1$d OR (ht_scope.hunt_id IS NULL AND h_scope.tournament_id = %1$d))', $tournament_id )
                                                               : sprintf( 'h_scope.tournament_id = %d', $tournament_id );
                                               $avg_ht_join        = $has_ht ? ' LEFT JOIN ' . $ht . ' ht_scope ON ht_scope.hunt_id = hw_avg.hunt_id' : '';
                                               $avg_hunt_query     = sprintf(
                                                               'SELECT hw_avg.user_id, AVG(hw_avg.position) AS avg_hunt_pos FROM %1$s hw_avg INNER JOIN %2$s h_scope ON h_scope.id = hw_avg.hunt_id%3$s WHERE %4$s GROUP BY hw_avg.user_id',
                                                               $hw,
                                                               $h,
                                                               $avg_ht_join,
                                                               $avg_scope_condition
                                               );
                                               $select_parts[] = 'hunt_stats.avg_hunt_pos';
                                               $select_joins[] = 'LEFT JOIN (' . $avg_hunt_query . ') hunt_stats ON hunt_stats.user_id = tr.user_id';
                               }

                                if ( $need_avg_tournament || 'avg_tournament' === $orderby_request ) {
                                                $need_avg_tournament = true;
                                                $rank_query          = sprintf(
                                                                'SELECT tr_rank.user_id, (SELECT 1 + COUNT(*) FROM %1$s tr_cmp WHERE tr_cmp.tournament_id = %2$d AND (tr_cmp.wins > tr_rank.wins OR (tr_cmp.wins = tr_rank.wins AND tr_cmp.user_id < tr_rank.user_id))) AS avg_tournament_pos FROM %1$s tr_rank WHERE tr_rank.tournament_id = %2$d',
                                                                $r,
                                                                $tournament_id
                                                );
                                                $select_parts[] = 'tour_rank.avg_tournament_pos';
                                                $select_joins[] = 'LEFT JOIN (' . $rank_query . ') tour_rank ON tour_rank.user_id = tr.user_id';
                                }

			$latest_hunt_subquery = '';
			if ( $need_site_details || $need_hunt_name ) {
			$latest_scope = $has_ht
			? sprintf( '(ht_inner.tournament_id = %1$d OR (ht_inner.hunt_id IS NULL AND h_inner.tournament_id = %1$d))', $tournament_id )
			: sprintf( 'h_inner.tournament_id = %d', $tournament_id );
			$latest_ht_join = $has_ht ? ' LEFT JOIN ' . $ht . ' ht_inner ON ht_inner.hunt_id = hw_inner.hunt_id' : '';
			$win_expr_inner  = "COALESCE( NULLIF( h_inner.closed_at, '0000-00-00 00:00:00' ), NULLIF( hw_inner.created_at, '0000-00-00 00:00:00' ), NULLIF( h_inner.created_at, '0000-00-00 00:00:00' ) )";
			$latest_hunt_subquery = sprintf(
			'(SELECT hw_inner.hunt_id FROM %1$s hw_inner INNER JOIN %2$s h_inner ON h_inner.id = hw_inner.hunt_id%3$s WHERE hw_inner.user_id = tr.user_id AND %4$s ORDER BY %5$s DESC, hw_inner.hunt_id DESC LIMIT 1)',
			$hw,
			$h,
			$latest_ht_join,
			$latest_scope,
			$win_expr_inner
			);
			}

			if ( $need_site_details && '' !== $latest_hunt_subquery ) {
			$select_parts[] = '(SELECT h2.affiliate_site_id FROM ' . $h . ' h2 WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS site_id';
			if ( $need_site && $w ) {
			$select_parts[] = '(SELECT w2.name FROM ' . $w . ' w2 INNER JOIN ' . $h . ' h2 ON h2.affiliate_site_id = w2.id WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS site_name';
			}
			}

			if ( $need_hunt_name && '' !== $latest_hunt_subquery ) {
			$select_parts[] = '(SELECT h2.title FROM ' . $h . ' h2 WHERE h2.id = ' . $latest_hunt_subquery . ' LIMIT 1) AS hunt_title';
			}

if ( $need_tournament_name && $needs_tournament_table ) {
$select_parts[] = 't_main.title AS tournament_title';
}

                                $select_sql = 'SELECT ' . implode( ', ', $select_parts ) . ' FROM ' . $r . ' tr ' . implode( ' ', $select_joins );
                                if ( ! empty( $where ) ) {
                                                $select_sql .= ' WHERE ' . implode( ' AND ', $where );
                                }

                                $select_sql .= sprintf( ' ORDER BY %s %s', $orderby, $direction );

                                $apply_affiliate_site_filter = $website_id > 0 && function_exists( 'bhg_is_user_affiliate_for_site' );

                                if ( $apply_affiliate_site_filter ) {
                                                $rows_all = $wpdb->get_results( $select_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

                                                $filtered_rows = array();

                                                foreach ( (array) $rows_all as $row_obj ) {
                                                                $uid = isset( $row_obj->user_id ) ? (int) $row_obj->user_id : 0;

                                                                if ( $uid > 0 && bhg_is_user_affiliate_for_site( $uid, $website_id ) ) {
                                                                                $filtered_rows[] = $row_obj;
                                                                }
                                                }

                                                $total       = count( $filtered_rows );
                                                $total_pages = max( 1, (int) ceil( $total / $per_page ) );

                                                if ( $paged > $total_pages ) {
                                                                $paged = $total_pages;
                                                }

                                                $offset = ( $paged - 1 ) * $per_page;
                                                $rows   = array_slice( $filtered_rows, $offset, $per_page );
                                                $limit  = count( $rows );
                                } else {
                                                $select_sql    .= ' LIMIT %d OFFSET %d';
                                                $select_params  = $params;
                                                $select_params[] = $limit;
                                                $select_params[] = $offset;

                                                $query = $wpdb->prepare( $select_sql, ...$select_params );
                                                bhg_log(
                                                                array(
                                                                                'tournament_leaderboard_sql' => $query,
                                                                                'leaderboard_limit'          => $limit,
                                                                                'leaderboard_offset'         => $offset,
                                                                )
                                                );
                                                $rows = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                }

                                if ( empty( $rows ) ) {
                                                return array(
                                                                'rows'          => array(),
                                                                'total'         => 0,
                                                                'per_page'      => $per_page,
                                                                'paged'         => $paged,
                                                                'offset'        => $offset,
                                                                'total_pages'   => $total_pages,
                                                                'orderby_key'   => $orderby_request,
                                                                'direction_key' => $direction_key,
                                                                'limit'         => $limit,
                                                                'error'         => bhg_t( 'notice_no_data_available', 'No data available.' ),
                                                );
                                }

                                return array(
                                                'rows'          => $rows,
                                                'total'         => $total,
                                                'per_page'      => $per_page,
                                                'paged'         => $paged,
                                                'offset'        => $offset,
                                                'total_pages'   => $total_pages,
                                                'orderby_key'   => $orderby_request,
                                                'direction_key' => $direction_key,
                                                'limit'         => $limit,
                                                'error'         => '',
                                );
                }
private function normalize_prize_layout( $layout ) {
				$layout = strtolower( (string) $layout );

				return in_array( $layout, array( 'grid', 'carousel' ), true ) ? $layout : 'grid';
		}

			/**
			 * Normalize prize card size keyword.
			 *
			 * @param string $size Size keyword.
			 * @return string
			 */
			private function normalize_prize_size( $size ) {
				$size = strtolower( (string) $size );

				return in_array( $size, array( 'small', 'medium', 'big' ), true ) ? $size : 'medium';
			}

			/**
			 * Resolve responsive prize size based on visible cards.
			 *
			 * @param string $requested_size   Original size keyword.
			 * @param string $layout           Layout keyword.
			 * @param int    $visible_count    Number of prizes shown simultaneously.
			 * @param int    $total_count      Total prizes being rendered.
			 * @param array  $section_options  Section-level options.
			 * @return string
			 */
                       private function resolve_responsive_prize_size( $requested_size, $layout, $visible_count, $total_count, $section_options = array() ) {
                               $visible_count   = max( 1, (int) $visible_count );
                               $total_count     = max( 1, (int) $total_count );
                               $section_options = is_array( $section_options ) ? $section_options : array();

                               $respect_manual = apply_filters( 'bhg_prize_respect_manual_size', false, $requested_size, $layout, $visible_count, $total_count, $section_options );

				if ( $respect_manual ) {
					return $requested_size;
				}

				if ( $visible_count <= 1 ) {
					return 'big';
				}

				if ( $visible_count <= 3 ) {
					return 'medium';
				}

                               return 'small';
                       }

                       /**
                        * Build a normalized list of prize summary entries.
                        *
                        * @param array<int,object> $prizes          Prize rows.
                        * @param array             $section_options Section rendering options.
                        * @return array<int,array<string,mixed>>
                        */
                       private function build_prize_summary_entries( $prizes, $section_options = array() ) {
                               $entries        = array();
                               $position       = 1;
                               $default_label  = bhg_t( 'label_emdash', '—' );
                               $section_option = is_array( $section_options ) ? $section_options : array();

                               foreach ( $prizes as $prize ) {
                                       $title = '';
                                       if ( isset( $prize->title ) ) {
                                               $title = trim( wp_strip_all_tags( (string) $prize->title ) );
                                       }

                                       if ( '' === $title ) {
                                               $title = $default_label;
                                       }

                                       $entry_position = $position;
                                       if ( isset( $prize->bhg_prize_position ) ) {
                                               $entry_position = max( 1, (int) $prize->bhg_prize_position );
                                       }

                                       $entries[] = array(
                                               'position' => $entry_position,
                                               'text'     => $title,
                                       );

                                       $position++;
                               }

                               /**
                                * Filter the prize summary entries before rendering.
                                *
                                * @param array $entries         Normalized entries.
                                * @param array $prizes          Raw prize rows.
                                * @param array $section_options Section rendering options.
                                */
                               return apply_filters( 'bhg_prize_summary_entries', $entries, $prizes, $section_option );
                       }

                       /**
                        * Normalize stored prize data into per-position maps.
                        *
                        * @param mixed $raw_prizes    Stored prizes payload.
                        * @param int   $winners_count Optional winners limit.
                        * @return array<string,array<int,int>> Map of prize type => position => prize ID.
                        */
                       private function normalize_prize_maps_from_storage( $raw_prizes, $winners_count = 0 ) {
                               $maps = array(
                                       'regular' => array(),
                                       'premium' => array(),
                               );

                               $decoded = $raw_prizes;
                               if ( is_string( $raw_prizes ) ) {
                                       $decoded = json_decode( $raw_prizes, true );
                               }

                               if ( is_array( $decoded ) ) {
                                       if ( isset( $decoded['regular'] ) || isset( $decoded['premium'] ) ) {
                                               foreach ( array( 'regular', 'premium' ) as $type ) {
                                                       if ( empty( $decoded[ $type ] ) || ! is_array( $decoded[ $type ] ) ) {
                                                               continue;
                                                       }

                                                       foreach ( $decoded[ $type ] as $position => $maybe_id ) {
                                                               $pid       = absint( $maybe_id );
                                                               $position  = is_numeric( $position ) ? max( 1, absint( $position ) ) : 0;
                                                               if ( $pid > 0 && $position > 0 ) {
                                                                       $maps[ $type ][ $position ] = $pid;
                                                               }
                                                       }
                                               }
                                       } else {
                                               $pos = 1;
                                               foreach ( $decoded as $maybe_id ) {
                                                       $pid = absint( $maybe_id );
                                                       if ( $pid > 0 ) {
                                                               $maps['regular'][ $pos ] = $pid;
                                                               $pos++;
                                                       }
                                               }
                                       }
                               }

                               if ( $winners_count > 0 ) {
                                       foreach ( array( 'regular', 'premium' ) as $type ) {
                                               $maps[ $type ] = array_slice( $maps[ $type ], 0, $winners_count, true );
                                       }
                               }

                               foreach ( $maps as $type => $map ) {
                                       ksort( $map );
                                       $maps[ $type ] = $map;
                               }

                               return $maps;
                       }

                       /**
                        * Load prize rows keyed by type while preserving position order.
                        *
                        * @param array<string,array<int,int>> $prize_maps Position => prize ID map.
                        * @return array<string,array<int,object>>
                        */
                       private function load_prize_sets_from_maps( $prize_maps ) {
                               $results = array(
                                       'regular' => array(),
                                       'premium' => array(),
                               );

                               if ( empty( $prize_maps ) || ! class_exists( 'BHG_Prizes' ) || ! method_exists( 'BHG_Prizes', 'get_prizes_by_ids' ) ) {
                                       return $results;
                               }

                               foreach ( $prize_maps as $type => $map ) {
                                       if ( empty( $map ) || ! is_array( $map ) ) {
                                               continue;
                                       }

                                       $ids       = array_values( $map );
                                       $prize_rows = BHG_Prizes::get_prizes_by_ids( $ids );
                                       if ( empty( $prize_rows ) || ! is_array( $prize_rows ) ) {
                                               continue;
                                       }

                                       $indexed = array();
                                       foreach ( $prize_rows as $row ) {
                                               if ( isset( $row->active ) && (int) $row->active === 0 ) {
                                                       continue;
                                               }

                                               if ( isset( $row->id ) ) {
                                                       $indexed[ (int) $row->id ] = $row;
                                               }
                                       }

                                       ksort( $map );
                                       foreach ( $map as $position => $prize_id ) {
                                               if ( isset( $indexed[ $prize_id ] ) ) {
                                                       $row                        = $indexed[ $prize_id ];
                                                       $row->bhg_prize_position    = (int) $position;
                                                       $results[ $type ][] = $row;
                                               }
                                       }
                               }

                               return $results;
                       }

                       /**
                        * Render tabbed prize layout for regular and premium sets.
                        *
                        * @param array<string,array<int,object>> $prize_sets Prize rows keyed by type.
                        * @param array                           $section_options Display options passed to render_prize_section.
                        * @param array                           $display_overrides Card display overrides.
                        * @param string                          $layout Layout keyword.
                        * @param string                          $size Image size keyword.
                        * @return string
                        */
                       private function render_prize_sets_tabs( $prize_sets, $section_options = array(), $display_overrides = array(), $layout = 'carousel', $size = 'medium' ) {
                               $available = array();
                               foreach ( array( 'regular', 'premium' ) as $type ) {
                                       if ( ! empty( $prize_sets[ $type ] ) ) {
                                               $available[] = $type;
                                       }
                               }

                               if ( empty( $available ) ) {
                                       return '';
                               }

                               if ( 1 === count( $available ) ) {
                                       $type          = $available[0];
                                       $heading       = isset( $section_options['heading_text'] ) ? $section_options['heading_text'] : ( 'premium' === $type ? bhg_t( 'label_premium_prize_set', 'Premium Prize Set' ) : bhg_t( 'label_regular_prize_set', 'Regular Prize Set' ) );
                                       $options       = $section_options;
                                       $options['heading_text'] = $heading;
                                       return $this->render_prize_section( $prize_sets[ $type ], $layout, $size, $display_overrides, $options );
                               }

                               $tab_id    = uniqid( 'bhg-prize-tabs-', false );
                               $tab_index = 0;

                               ob_start();
                               echo '<div class="bhg-prize-tabset" data-bhg-prize-tabs="1">';
                               echo '<div class="bhg-prize-tabs" role="tablist">';
                               foreach ( $available as $type ) {
                                       $tab_index++;
                                       $is_active = ( 1 === $tab_index );
                                       $tab_label = 'premium' === $type ? bhg_t( 'label_premium_prizes', 'Premium Prizes' ) : bhg_t( 'label_regular_prizes', 'Regular Prizes' );
                                       $tab_target = $tab_id . '-' . $type;
                                       echo '<button type="button" class="bhg-prize-tab' . ( $is_active ? ' is-active' : '' ) . '" role="tab" id="' . esc_attr( $tab_target ) . '" aria-selected="' . ( $is_active ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $tab_target ) . '-panel" tabindex="' . ( $is_active ? '0' : '-1' ) . '">' . esc_html( $tab_label ) . '</button>';
                               }
                               echo '</div>';

                               $tab_index = 0;
                               foreach ( $available as $type ) {
                                       $tab_index++;
                                       $is_active = ( 1 === $tab_index );
                                       $panel_id  = $tab_id . '-' . $type . '-panel';
                                       $tab_ref   = $tab_id . '-' . $type;
                                       $heading   = 'premium' === $type ? bhg_t( 'label_premium_prize_set', 'Premium Prize Set' ) : bhg_t( 'label_regular_prize_set', 'Regular Prize Set' );
                                       $options   = $section_options;
                                       $options['heading_text'] = $heading;

                                       echo '<div class="bhg-prize-tab-panel' . ( $is_active ? ' is-active' : '' ) . '" role="tabpanel" id="' . esc_attr( $panel_id ) . '" aria-labelledby="' . esc_attr( $tab_ref ) . '"' . ( $is_active ? '' : ' hidden' ) . '>';
                                       echo wp_kses_post( $this->render_prize_section( $prize_sets[ $type ], $layout, $size, $display_overrides, $options ) );
                                       echo '</div>';
                               }

                               echo '</div>';

                               return ob_get_clean();
                       }

                       /**
                        * Normalize a yes/no shortcode attribute.
                        *
                        * @param mixed $value         Raw attribute value.
                        * @param bool  $allow_inherit Whether to allow inherit/default keywords.
                        * @return string Either '1', '0', or 'inherit'.
                        */
                       private function normalize_yes_no_attr( $value, $allow_inherit = true ) {
                               $value = strtolower( trim( (string) $value ) );

                               if ( $allow_inherit && in_array( $value, array( 'inherit', 'default', '' ), true ) ) {
                                       return 'inherit';
                               }

                               if ( in_array( $value, array( 'no', '0', 'false', 'off' ), true ) ) {
                                       return '0';
                               }

                               return '1';
                       }

                       /**
                        * Normalize the `filters` attribute for the leaderboard shortcode.
                        *
                        * Accepts comma-, space-, or newline-delimited values and supports
                        * minor variations such as "affiliate-site" or "affiliate status".
                        * Returns `null` when the shortcode attribute is omitted so the
                        * caller can fall back to default filters.
                        *
                        * @param mixed $filters_input Raw filters attribute value.
                        * @return array|null Normalized filter keys (timeline, tournament,
                        *                    site, affiliate) or `null` if the defaults should
                        *                    be used.
                        */
                       private function normalize_leaderboard_filters( $filters_input ) {
                               if ( null === $filters_input ) {
                                       return null;
                               }

                               if ( is_array( $filters_input ) ) {
                                       $filters_attr = implode( ',', $filters_input );
                               } else {
                                       $filters_attr = (string) $filters_input;
                               }

                               $filters_attr = trim( $filters_attr );
                               if ( '' === $filters_attr ) {
                                       return array();
                               }

                               $keyword = strtolower( $filters_attr );
                               if ( in_array( $keyword, array( 'all', 'default', 'inherit' ), true ) ) {
                                       return self::LEADERBOARD_DEFAULT_FILTERS;
                               }

                               if ( in_array( $keyword, array( 'none', 'no', 'false', '0' ), true ) ) {
                                       return array();
                               }

                               $normalized_tokens = str_replace(
                                       array( 'affiliate status', 'affiliate statuses', 'affiliate site', 'affiliate sites' ),
                                       array( 'affiliate_status', 'affiliate_statuses', 'affiliate_site', 'affiliate_sites' ),
                                       $keyword
                               );
                               $normalized_tokens = str_replace( '-', '_', $normalized_tokens );

                               $raw_tokens = wp_parse_list( $normalized_tokens );
                               if ( empty( $raw_tokens ) ) {
                                       return array();
                               }

                               $token_map = array(
                                       'timeline'           => 'timeline',
                                       'timelines'          => 'timeline',
                                       'tournament'         => 'tournament',
                                       'tournaments'        => 'tournament',
                                       'affiliate_site'     => 'site',
                                       'affiliate_sites'    => 'site',
                                       'site'               => 'site',
                                       'sites'              => 'site',
                                       'affiliate_status'   => 'affiliate',
                                       'affiliate_statuses' => 'affiliate',
                                       'affiliate'          => 'affiliate',
                                       'affiliates'         => 'affiliate',
                               );

                               $normalized = array();
                               foreach ( $raw_tokens as $token ) {
                                       $token = trim( (string) $token );
                                       if ( '' === $token ) {
                                               continue;
                                       }

                                       if ( isset( $token_map[ $token ] ) ) {
                                               $normalized[] = $token_map[ $token ];
                                       }
                               }

                               return array_values( array_unique( $normalized ) );
                       }

                       /**
                        * Build the SQL expression used for determining a winner's effective
                        * win date when filtering leaderboards by timeline.
                        *
                        * The expression coalesces the hunt's closed date, the winner record's
                        * creation date, and finally the hunt's creation date while treating
                        * `0000-00-00 00:00:00` values as `NULL` so they do not interfere with
                        * timeline windows.
                        *
                        * @return string SQL expression fragment.
                        */
                       private function get_leaderboard_win_date_expression() {
                               return "COALESCE( NULLIF( h.closed_at, '0000-00-00 00:00:00' ), NULLIF( hw.created_at, '0000-00-00 00:00:00' ), NULLIF( h.created_at, '0000-00-00 00:00:00' ) )";
                       }

                       /**
                        * Normalize a click action shortcode attribute.
                        *
                        * @param mixed $value Raw attribute value.
                        * @return string Normalized keyword or empty string when invalid.
                        */
                       private function normalize_click_action_attr( $value ) {
                               if ( ! class_exists( 'BHG_Prizes' ) ) {
                                       return '';
                               }

                               $action = sanitize_key( (string) $value );

                               if ( '' === $action ) {
                                       return '';
                               }

                               if ( in_array( $action, array( 'inherit', 'default' ), true ) ) {
                                       return 'inherit';
                               }

                               return BHG_Prizes::sanitize_click_action( $action, 'link' );
                       }

                       /**
                        * Normalize a link target shortcode attribute.
                        *
                        * @param mixed $value         Raw attribute value.
                        * @param bool  $allow_inherit Whether to accept inherit/default keywords.
                        * @return string Normalized target keyword or empty string when invalid.
                        */
                       private function normalize_link_target_attr( $value, $allow_inherit = true ) {
                               if ( ! class_exists( 'BHG_Prizes' ) ) {
                                       return '';
                               }

                               $target = sanitize_key( (string) $value );

                               if ( $allow_inherit && in_array( $target, array( 'inherit', 'default', '' ), true ) ) {
                                       return 'inherit';
                               }

                               return BHG_Prizes::sanitize_link_target( $target, '_self' );
                       }

                /**
                 * Locate a shortcode view file.
		 *
		 * @param string $view View identifier relative to the views directory.
		 * @return string
		 */
		private function get_view_path( $view ) {
				$view = trim( str_replace( array( '..', '\\' ), '', (string) $view ) );

				if ( '' === $view ) {
						return '';
				}

				$candidates = array(
						BHG_PLUGIN_DIR . 'templates/' . $view . '.php',
						BHG_PLUGIN_DIR . 'includes/views/' . $view . '.php',
				);

				/**
				 * Filters the candidate paths used to locate shortcode views.
				 *
				 * @param string[] $candidates Candidate file paths.
				 * @param string   $view       Requested view identifier.
				 */
				$candidates = apply_filters( 'bhg_shortcode_view_paths', $candidates, $view );

				foreach ( $candidates as $candidate ) {
						if ( ! $candidate ) {
								continue;
						}

						$candidate = wp_normalize_path( $candidate );

						if ( file_exists( $candidate ) ) {
								return $candidate;
						}
				}

				return '';
		}

	   /**
		* Ensure shared profile assets are loaded.
		*
		* @return void
		*/
	   private function enqueue_profile_assets() {
			   $base_url = defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ );
			   $version  = defined( 'BHG_VERSION' ) ? BHG_VERSION : null;

			   wp_enqueue_style(
					   'bhg-shortcodes',
					   $base_url . 'assets/css/bhg-shortcodes.css',
					   array(),
					   $version
			   );
	   }

	   /**
		* Ensure prize-specific assets are loaded.
		*/
	   private function enqueue_prize_assets() {
				if ( $this->prize_assets_enqueued ) {
						return;
				}

			   $base_url = defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ );
			   $version  = defined( 'BHG_VERSION' ) ? BHG_VERSION : null;

			   $this->enqueue_profile_assets();

				wp_enqueue_style(
						'bhg-prizes',
						$base_url . 'assets/css/bhg-prizes.css',
						array( 'bhg-shortcodes' ),
						$version
				);

				wp_enqueue_script(
						'bhg-prizes',
						$base_url . 'assets/js/bhg-prizes.js',
						array(),
						$version,
						true
				);

				$this->prize_assets_enqueued = true;
		}

		/**
		 * Determine whether a profile section should be displayed.
		 *
		 * @param string $section Section key.
		 * @return bool
		 */
                private function is_profile_section_enabled( $section ) {
				if ( null === $this->profile_visibility_settings ) {
						$settings = get_option( 'bhg_plugin_settings', array() );
						$sections = array();

						if ( isset( $settings['profile_sections'] ) && is_array( $settings['profile_sections'] ) ) {
								foreach ( $settings['profile_sections'] as $key => $value ) {
										$sections[ $key ] = (int) $value;
								}
						}

						$this->profile_visibility_settings = $sections;
				}

				if ( '' === $section ) {
						return true;
				}

                                if ( ! is_array( $this->profile_visibility_settings ) ) {
                                                $this->profile_visibility_settings = array();
                                }

                                if ( empty( $this->profile_visibility_settings ) ) {
                                                return true;
                                }

                                if ( ! array_key_exists( $section, $this->profile_visibility_settings ) ) {
                                                return true;
                                }

				return (bool) $this->profile_visibility_settings[ $section ];
		}

/**
 * Render prizes section markup.
 *
 * @param array  $prizes  Prize rows.
 * @param string $layout  Layout keyword.
 * @param string $size    Image size keyword.
 * @param array  $display Display overrides.
 * @param array  $options Section-level options.
 * @return string
 */
private function render_prize_section( $prizes, $layout, $size, $display = array(), $options = array() ) {
if ( empty( $prizes ) || ! class_exists( 'BHG_Prizes' ) ) {
return '';
}

$layout = $this->normalize_prize_layout( $layout );
$size   = $this->normalize_prize_size( $size );

$this->enqueue_prize_assets();

$section_options = BHG_Prizes::prepare_section_options( $options );
$display_options = BHG_Prizes::prepare_display_overrides( $display );

$limit = isset( $section_options['limit'] ) ? (int) $section_options['limit'] : 0;
if ( $limit > 0 && count( $prizes ) > $limit ) {
$prizes = array_slice( $prizes, 0, $limit );
}

if ( empty( $prizes ) ) {
return '';
}

$count             = count( $prizes );
$heading_text      = isset( $section_options['heading_text'] ) ? $section_options['heading_text'] : '';
$hide_heading      = ! empty( $section_options['hide_heading'] );
$carousel_visible  = isset( $section_options['carousel_visible'] ) ? max( 1, (int) $section_options['carousel_visible'] ) : 1;
$carousel_autoplay = ! empty( $section_options['carousel_autoplay'] );
$carousel_interval = isset( $section_options['carousel_interval'] ) ? max( 1000, (int) $section_options['carousel_interval'] ) : 5000;
$carousel_pages    = max( 1, (int) ceil( $count / max( 1, $carousel_visible ) ) );

$visible_for_sizing = 'carousel' === $layout ? min( $carousel_visible, $count ) : ( $limit > 0 ? min( $limit, $count ) : $count );
$size               = $this->resolve_responsive_prize_size( $size, $layout, $visible_for_sizing, $count, $section_options );

$summary_enabled = ! empty( $section_options['show_summary'] );
$summary_label   = isset( $section_options['summary_heading'] ) ? (string) $section_options['summary_heading'] : bhg_t( 'prize_summary_heading', 'Prize Summary' );
$summary_entries = array();
if ( $summary_enabled ) {
$summary_entries = $this->build_prize_summary_entries( $prizes, $section_options );
if ( empty( $summary_entries ) ) {
$summary_label = '';
}
}

$view          = $this->get_view_path( 'prizes/section' );
$card_renderer = array( $this, 'render_prize_card' );
$context       = array(
'layout'            => $layout,
'carousel_visible'  => $carousel_visible,
'carousel_autoplay' => $carousel_autoplay,
'carousel_interval' => $carousel_interval,
'display_defaults'  => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_display_defaults() : array(),
);

if ( $view ) {
$view_layout        = $layout;
$view_size          = $size;
$view_prizes        = $prizes;
$view_display       = $display_options;
$view_context       = $context;
$view_title         = $hide_heading ? '' : $heading_text;
$view_count         = $count;
$view_pages         = $carousel_pages;
$view_card_renderer = $card_renderer;
$view_summary_label = $summary_label;
$view_summary_items = $summary_entries;

ob_start();
include $view;

return ob_get_clean();
}

ob_start();
echo '<div class="bhg-prizes-block bhg-prizes-layout-' . esc_attr( $layout ) . ' size-' . esc_attr( $size ) . '">';
if ( ! $hide_heading && '' !== $heading_text ) {
echo '<h4 class="bhg-prizes-title">' . esc_html( $heading_text ) . '</h4>';
}

if ( 'carousel' === $layout ) {
$show_nav = $carousel_pages > 1;
echo '<div class="bhg-prize-carousel" data-count="' . (int) $count . '" data-visible="' . esc_attr( $carousel_visible ) . '" data-pages="' . esc_attr( $carousel_pages ) . '" data-autoplay="' . ( $carousel_autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( $carousel_interval ) . '">';
if ( $show_nav ) {
echo '<button type="button" class="bhg-prize-nav bhg-prize-prev" aria-label="' . esc_attr( bhg_t( 'previous', 'Previous' ) ) . '"><span aria-hidden="true">&lsaquo;</span></button>';
}
echo '<div class="bhg-prize-track-wrapper"><div class="bhg-prize-track">';
foreach ( $prizes as $prize ) {
echo $this->render_prize_card( $prize, $size, $display_options, $context );
}
echo '</div></div>';
if ( $show_nav ) {
echo '<button type="button" class="bhg-prize-nav bhg-prize-next" aria-label="' . esc_attr( bhg_t( 'next', 'Next' ) ) . '"><span aria-hidden="true">&rsaquo;</span></button>';
echo '<div class="bhg-prize-dots" role="tablist">';
for ( $i = 0; $i < $carousel_pages; $i++ ) {
$active = 0 === $i ? ' active' : '';
echo '<button type="button" class="bhg-prize-dot' . esc_attr( $active ) . '" data-index="' . esc_attr( $i ) . '" aria-label="' . esc_attr( sprintf( bhg_t( 'prize_slide_label', 'Go to prize %d' ), $i + 1 ) ) . '"></button>';
}
echo '</div>';
}
echo '</div>';
} else {
echo '<div class="bhg-prizes-grid">';
foreach ( $prizes as $prize ) {
echo $this->render_prize_card( $prize, $size, $display_options, $context );
}
echo '</div>';
}

if ( ! empty( $summary_entries ) ) {
echo '<div class="bhg-prize-summary">';
if ( '' !== $summary_label ) {
echo '<h5 class="bhg-prize-summary-title">' . esc_html( $summary_label ) . '</h5>';
}
echo '<ol class="bhg-prize-summary-list">';
foreach ( $summary_entries as $entry ) {
$text = isset( $entry['text'] ) ? (string) $entry['text'] : '';
if ( '' === $text ) {
$text = bhg_t( 'label_emdash', '—' );
}
echo '<li>' . esc_html( $text ) . '</li>';
}
echo '</ol>';
echo '</div>';
}

echo '</div>';

return ob_get_clean();
}

private function render_prize_tabs( $tabs ) {
if ( empty( $tabs ) || ! is_array( $tabs ) ) {
return '';
}

$container_id = wp_unique_id( 'bhg-prize-tabset-' );
$tab_index    = 0;
$nav_markup   = '';
$panel_markup = '';

foreach ( $tabs as $tab ) {
$label       = isset( $tab['label'] ) ? (string) $tab['label'] : '';
$content     = isset( $tab['content'] ) ? (string) $tab['content'] : '';
$tab_id      = $container_id . '-tab-' . $tab_index;
$panel_id    = $container_id . '-panel-' . $tab_index;
$is_active   = 0 === $tab_index;
$active_attr = $is_active ? ' is-active' : '';
$hidden_attr = $is_active ? '' : ' hidden';

$nav_markup   .= '<button type="button" class="bhg-prize-tab' . esc_attr( $active_attr ) . '" id="' . esc_attr( $tab_id ) . '" role="tab" aria-controls="' . esc_attr( $panel_id ) . '" aria-selected="' . ( $is_active ? 'true' : 'false' ) . '">' . esc_html( $label ) . '</button>';
$panel_markup .= '<div class="bhg-prize-tab-panel' . esc_attr( $active_attr ) . '" id="' . esc_attr( $panel_id ) . '" role="tabpanel" aria-labelledby="' . esc_attr( $tab_id ) . '"' . $hidden_attr . '>' . wp_kses_post( $content ) . '</div>';

$tab_index++;
}

return '<div class="bhg-prize-tabset" data-bhg-prize-tabs="1"><div class="bhg-prize-tabs" role="tablist">' . $nav_markup . '</div><div class="bhg-prize-tab-panels">' . $panel_markup . '</div></div>';
}

       /**
        * Normalize username labels with consistent capitalization.
        *
        * @param string $label   Raw username label.
        * @param int    $user_id Optional user ID for fallback labels.
        *
        * @return string
        */
       private function format_username_label( $label, $user_id = 0 ) {
               $label = (string) $label;

               if ( '' === $label && $user_id > 0 ) {
                       /* translators: %d: user ID. */
                       $label = sprintf( bhg_t( 'label_user_hash', 'user#%d' ), $user_id );
               }

               if ( '' === $label ) {
                       return $label;
               }

               $charset = get_bloginfo( 'charset' );
               $charset = $charset ? $charset : 'UTF-8';

               if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strtoupper' ) ) {
                       $first = mb_substr( $label, 0, 1, $charset );
                       $rest  = mb_substr( $label, 1, null, $charset );
                       $label = mb_strtoupper( $first, $charset ) . $rest;
               } else {
                       $label = ucfirst( $label );
               }

               return $label;
       }

        /**
        * Retrieve bonus hunts the current user has participated in.
        *
        * @param int $user_id User identifier.
	* @param int $limit   Maximum number of hunts to return.
	* @return array[]
	*/
	private function get_user_bonus_hunt_rows( $user_id, $limit = 50 ) {
		global $wpdb;

		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
		return array();
	}

		$hunts_tbl   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
		$guesses_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
		$winners_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );

		if ( ! $hunts_tbl || ! $guesses_tbl ) {
		return array();
	}

		$limit = (int) $limit;
		if ( $limit <= 0 ) {
		$limit = 50;
	} elseif ( $limit > 200 ) {
		$limit = 200;
	}

		$order_expression = "COALESCE(h.closed_at, h.updated_at, h.created_at, g.created_at)";
		$select_columns   = "h.id, h.title, h.status, h.final_balance, h.closed_at, h.winners_count, g.guess, g.created_at AS guess_date";

		if ( $winners_tbl ) {
		$select_columns .= ', w.position, w.diff';
	} else {
		$select_columns .= ', NULL AS position, NULL AS diff';
	}

		$sql  = "SELECT {$select_columns} FROM {$hunts_tbl} h INNER JOIN {$guesses_tbl} g ON g.hunt_id = h.id AND g.user_id = %d";
		$args = array( $user_id );

				if ( $winners_tbl ) {
						$sql  .= " LEFT JOIN {$winners_tbl} w ON w.hunt_id = h.id AND w.user_id = %d AND w.eligible = 1";
						$args[] = $user_id;
				}

		$sql   .= ' WHERE g.user_id = %d ORDER BY ' . $order_expression . ' DESC, h.id DESC LIMIT %d';
		$args[] = $user_id;
		$args[] = $limit;

		$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $args ) );
		$rows     = $wpdb->get_results( $prepared );

		if ( empty( $rows ) ) {
		return array();
	}

		$formatted = array();

		foreach ( $rows as $row ) {
		$hunt_id       = isset( $row->id ) ? (int) $row->id : 0;
		$guess         = isset( $row->guess ) ? (float) $row->guess : 0.0;
		$final_balance = null;

		if ( isset( $row->final_balance ) && '' !== $row->final_balance && null !== $row->final_balance ) {
		$final_balance = (float) $row->final_balance;
	}

		$difference = null;
		if ( null !== $final_balance ) {
		$difference = abs( $final_balance - $guess );
	}

		$winners_limit = isset( $row->winners_count ) ? (int) $row->winners_count : 0;
		if ( $winners_limit <= 0 ) {
		$winners_limit = 1;
	}

		$placement  = isset( $row->position ) ? (int) $row->position : 0;
		$is_winner  = ( $placement > 0 && $placement <= $winners_limit );
		$guess_date = isset( $row->guess_date ) ? (string) $row->guess_date : '';

		$formatted[] = array(
		'hunt_id'       => $hunt_id,
		'title'         => isset( $row->title ) ? (string) $row->title : '',
		'status'        => isset( $row->status ) ? (string) $row->status : '',
		'guess'         => $guess,
		'final_balance' => $final_balance,
		'difference'    => $difference,
		'position'      => $placement,
		'winners_count' => $winners_limit,
		'is_winner'     => $is_winner,
		'closed_at'     => isset( $row->closed_at ) ? (string) $row->closed_at : '',
		'guess_date'    => $guess_date,
		);
	}

		return $formatted;
	}

	/**
	* Retrieve tournament standings for a user.
	*
	* @param int $user_id User identifier.
	* @return array[]
	*/
	private function get_user_tournament_rows( $user_id ) {
		global $wpdb;

		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
		return array();
	}

		$results_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
		$tours_tbl   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );

		if ( ! $results_tbl || ! $tours_tbl ) {
		return array();
	}

		$sql = "SELECT t.id, t.title, t.status, t.start_date, t.end_date, r.points, r.wins, r.last_win_date\n\t\t\tFROM {$results_tbl} r\n\t\t\tINNER JOIN {$tours_tbl} t ON t.id = r.tournament_id\n\t\t\tWHERE r.user_id = %d\n\t\t\tORDER BY r.points DESC, r.wins DESC, r.last_win_date ASC, t.title ASC";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

		if ( empty( $rows ) ) {
		return array();
	}

		$tournament_ids = array();
		foreach ( $rows as $row ) {
		$tid = isset( $row->id ) ? (int) $row->id : 0;
		if ( $tid > 0 ) {
		$tournament_ids[ $tid ] = $tid;
	}
	}

		$ranking_map = array();

		if ( ! empty( $tournament_ids ) ) {
		$placeholders = implode( ', ', array_fill( 0, count( $tournament_ids ), '%d' ) );
		$args         = array_values( $tournament_ids );
		$sql_ranks    = "SELECT tournament_id, user_id, points, wins, last_win_date\n\t\t\tFROM {$results_tbl}\n\t\t\tWHERE tournament_id IN ({$placeholders})\n\t\t\tORDER BY tournament_id ASC, points DESC, wins DESC, last_win_date ASC, user_id ASC";
		$prepared     = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql_ranks ), $args ) );
		$rank_rows    = $wpdb->get_results( $prepared );

		if ( $rank_rows ) {
		$current_tournament = null;
		$position_counter   = 0;
		$current_rank       = 0;
		$last_points        = null;
		$last_wins          = null;

		foreach ( $rank_rows as $rank_row ) {
		$tid = isset( $rank_row->tournament_id ) ? (int) $rank_row->tournament_id : 0;
		$uid = isset( $rank_row->user_id ) ? (int) $rank_row->user_id : 0;

		if ( $tid <= 0 || $uid <= 0 ) {
		continue;
	}

		if ( $tid !== $current_tournament ) {
		$current_tournament = $tid;
		$position_counter   = 0;
		$current_rank       = 0;
		$last_points        = null;
		$last_wins          = null;
	}

		++$position_counter;

		$points = isset( $rank_row->points ) ? (int) $rank_row->points : 0;
		$wins   = isset( $rank_row->wins ) ? (int) $rank_row->wins : 0;

		if ( null === $last_points || $points !== $last_points || $wins !== $last_wins ) {
		$current_rank = $position_counter;
		$last_points  = $points;
		$last_wins    = $wins;
	}

		if ( ! isset( $ranking_map[ $tid ] ) ) {
		$ranking_map[ $tid ] = array();
	}

		$ranking_map[ $tid ][ $uid ] = $current_rank;
	}
	}
	}

		$formatted = array();

		foreach ( $rows as $row ) {
		$tid = isset( $row->id ) ? (int) $row->id : 0;
		if ( $tid <= 0 ) {
		continue;
	}

		$rank = null;
		if ( isset( $ranking_map[ $tid ][ $user_id ] ) ) {
		$rank = (int) $ranking_map[ $tid ][ $user_id ];
	}

		$formatted[] = array(
		'tournament_id' => $tid,
		'title'         => isset( $row->title ) ? (string) $row->title : '',
		'status'        => isset( $row->status ) ? (string) $row->status : '',
		'points'        => isset( $row->points ) ? (int) $row->points : 0,
		'wins'          => isset( $row->wins ) ? (int) $row->wins : 0,
		'last_win_date' => isset( $row->last_win_date ) ? (string) $row->last_win_date : '',
		'rank'          => $rank,
		);
	}

		return $formatted;
	}

	/**
	* Retrieve prizes associated with hunts the user has won.
	*
	* @param int $user_id User identifier.
	* @return array[]
	*/
	private function get_user_prize_rows( $user_id ) {
		global $wpdb;

		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
		return array();
	}

		$winners_tbl  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
		$hunts_tbl    = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
		$relation_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_prizes' ) );
		$prizes_tbl   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_prizes' ) );

		if ( ! $winners_tbl || ! $hunts_tbl || ! $relation_tbl || ! $prizes_tbl ) {
		return array();
	}

				$sql = "SELECT DISTINCT p.id AS prize_id, p.title AS prize_title, p.category, h.title AS hunt_title, h.closed_at, w.position\n\t\t\tFROM {$winners_tbl} w\n\t\t\tINNER JOIN {$hunts_tbl} h ON h.id = w.hunt_id\n\t\t\tINNER JOIN {$relation_tbl} hp ON hp.hunt_id = w.hunt_id\n\t\t\tINNER JOIN {$prizes_tbl} p ON p.id = hp.prize_id\n\t\t\tWHERE w.user_id = %d AND w.eligible = 1\n\t\t\tORDER BY h.closed_at DESC, w.position ASC, p.title ASC";

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

		if ( empty( $rows ) ) {
		return array();
	}

		$formatted = array();

		foreach ( $rows as $row ) {
		$formatted[] = array(
		'prize_id'   => isset( $row->prize_id ) ? (int) $row->prize_id : 0,
		'title'      => isset( $row->prize_title ) ? (string) $row->prize_title : '',
		'category'   => isset( $row->category ) ? (string) $row->category : '',
		'hunt_title' => isset( $row->hunt_title ) ? (string) $row->hunt_title : '',
		'closed_at'  => isset( $row->closed_at ) ? (string) $row->closed_at : '',
		'position'   => isset( $row->position ) ? (int) $row->position : 0,
		);
	}

		return $formatted;
	}

/**
 * Render a single prize card.
 *
 * @param object $prize   Prize row.
 * @param string $size    Image size keyword.
 * @param array  $display Display overrides.
 * @param array  $context Additional context.
 * @return string
 */
private function render_prize_card( $prize, $size, $display = array(), $context = array() ) {
if ( ! class_exists( 'BHG_Prizes' ) ) {
return '';
}

$display  = is_array( $display ) ? $display : array();
$context  = is_array( $context ) ? $context : array();
$layout   = isset( $context['layout'] ) ? $context['layout'] : 'grid';
$defaults = array();

if ( isset( $context['display_defaults'] ) && is_array( $context['display_defaults'] ) ) {
$defaults = $context['display_defaults'];
}

$style_attr = BHG_Prizes::build_style_attr( $prize );
$image_url  = BHG_Prizes::get_image_url( $prize, $size );
$image_id   = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_image_id_for_size( $prize, $size ) : 0;
$category   = isset( $prize->category ) ? (string) $prize->category : '';
$wp_size    = 'medium';

if ( 'small' === $size ) {
$wp_size = 'thumbnail';
} elseif ( 'big' === $size ) {
$wp_size = 'bhg_prize_big';
}

$defaults         = is_array( $defaults ) ? $defaults : array();
$show_title       = BHG_Prizes::resolve_display_flag(
        isset( $display['show_title'] ) ? $display['show_title'] : null,
        isset( $prize->show_title ) ? $prize->show_title : 1,
        array_key_exists( 'show_title', $defaults ) ? $defaults['show_title'] : null
);
$show_description = BHG_Prizes::resolve_display_flag(
        isset( $display['show_description'] ) ? $display['show_description'] : null,
        isset( $prize->show_description ) ? $prize->show_description : 1,
        array_key_exists( 'show_description', $defaults ) ? $defaults['show_description'] : null
);
$show_category    = BHG_Prizes::resolve_display_flag(
        isset( $display['show_category'] ) ? $display['show_category'] : null,
        isset( $prize->show_category ) ? $prize->show_category : 1,
        array_key_exists( 'show_category', $defaults ) ? $defaults['show_category'] : null
);
$show_image       = BHG_Prizes::resolve_display_flag(
        isset( $display['show_image'] ) ? $display['show_image'] : null,
        isset( $prize->show_image ) ? $prize->show_image : 1,
        array_key_exists( 'show_image', $defaults ) ? $defaults['show_image'] : null
);

$action_override = isset( $display['click_action'] ) ? $display['click_action'] : 'inherit';
$prize_action    = isset( $prize->click_action ) ? $prize->click_action : 'link';
$default_click   = isset( $defaults['click_action'] ) ? $defaults['click_action'] : 'inherit';
$click_action    = BHG_Prizes::resolve_click_action( $action_override, $prize_action, $default_click );

$link_href = '';
$target    = '_self';
$rel_attr  = '';
if ( in_array( $click_action, array( 'link', 'new' ), true ) ) {
$link_href = BHG_Prizes::get_prize_link( $prize );
if ( '' === $link_href ) {
$click_action = 'none';
} else {
        if ( 'new' === $click_action ) {
            $target   = '_blank';
            $rel_attr = ' rel="noopener noreferrer"';
        } else {
            $default_link_target = isset( $defaults['link_target'] ) ? $defaults['link_target'] : 'inherit';
            $target              = BHG_Prizes::resolve_link_target(
                isset( $display['link_target'] ) ? $display['link_target'] : 'inherit',
                isset( $prize->link_target ) ? $prize->link_target : '_self',
                $default_link_target
            );
            $rel_attr = '_blank' === $target ? ' rel="noopener noreferrer"' : '';
        }
}
} elseif ( 'image' === $click_action ) {
$link_href = BHG_Prizes::get_full_image_url( $prize );
if ( '' === $link_href ) {
$click_action = 'none';
}
}

if ( 'image' === $click_action && ! $show_image ) {
$click_action = 'none';
}

$classes = array( 'bhg-prize-card' );
if ( $style_attr ) {
$classes[] = 'bhg-prize-card--custom';
}
if ( ! empty( $prize->css_border ) || ! empty( $prize->css_border_color ) ) {
$classes[] = 'bhg-prize-card--has-border';
}
if ( ! empty( $prize->css_background ) ) {
$classes[] = 'bhg-prize-card--has-background';
}
if ( $show_image && $image_url ) {
$classes[] = 'bhg-prize-card--has-image';
}
if ( 'none' !== $click_action && '' !== $link_href ) {
$classes[] = 'bhg-prize-card--linked';
if ( 'image' === $click_action ) {
$classes[] = 'bhg-prize-card--popup';
}
}

$classes    = array_map( 'sanitize_html_class', $classes );
$class_attr = trim( implode( ' ', array_filter( $classes ) ) );

$tag        = 'div';
$attributes = '';
if ( 'none' !== $click_action && '' !== $link_href ) {
$tag        = 'a';
$attributes = ' href="' . esc_url( $link_href ) . '"';
if ( '_blank' === $target ) {
$attributes .= ' target="_blank"' . $rel_attr;
} elseif ( '_self' !== $target ) {
$attributes .= ' target="' . esc_attr( $target ) . '"';
}
if ( 'image' === $click_action ) {
$attributes .= ' data-bhg-prize-popup="image" data-bhg-prize-alt="' . esc_attr( $prize->title ) . '"';
}
}

ob_start();
echo '<' . $tag . ' class="' . esc_attr( $class_attr ) . '"' . $style_attr . $attributes . '>';
if ( $show_image && $image_url ) {
$img_attr = '';
$srcset   = $image_id ? wp_get_attachment_image_srcset( $image_id, $wp_size ) : '';
if ( ! $srcset && 'bhg_prize_big' === $wp_size ) {
$srcset = $image_id ? wp_get_attachment_image_srcset( $image_id, 'large' ) : '';
}
$sizes = $image_id ? wp_get_attachment_image_sizes( $image_id, $wp_size ) : '';
if ( ! $sizes && 'bhg_prize_big' === $wp_size ) {
$sizes = $image_id ? wp_get_attachment_image_sizes( $image_id, 'large' ) : '';
}
if ( $srcset ) {
$img_attr .= ' srcset="' . esc_attr( $srcset ) . '"';
}
if ( $sizes ) {
$img_attr .= ' sizes="' . esc_attr( $sizes ) . '"';
} else {
$img_attr .= ' sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"';
}
echo '<div class="bhg-prize-image"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $prize->title ) . '"' . $img_attr . '></div>';
}
echo '<div class="bhg-prize-body">';
if ( $show_title ) {
echo '<h5 class="bhg-prize-title">' . esc_html( $prize->title ) . '</h5>';
}
if ( $show_category && $category ) {
$label_text = BHG_Prizes::get_category_label( $category );

if ( '' === $label_text ) {
$label_text = ucwords( str_replace( array( '_', '-' ), ' ', $category ) );
}

$category_label     = esc_html( $label_text );
$category_link_data = BHG_Prizes::get_category_link_data( $prize );

$link_allowed = BHG_Prizes::resolve_display_flag(
isset( $display['category_links'] ) ? $display['category_links'] : null,
$category_link_data['enabled'],
array_key_exists( 'category_links', $defaults ) ? $defaults['category_links'] : null
);

if ( $link_allowed && $category_link_data['enabled'] && $category_link_data['url'] ) {
$category_target = BHG_Prizes::resolve_link_target(
isset( $display['category_target'] ) ? $display['category_target'] : 'inherit',
$category_link_data['target'],
isset( $defaults['category_target'] ) ? $defaults['category_target'] : 'inherit'
);
$rel = '_blank' === $category_target ? ' rel="noopener noreferrer"' : '';
$category_label = '<a href="' . esc_url( $category_link_data['url'] ) . '" target="' . esc_attr( $category_target ) . '"' . $rel . '>' . $category_label . '</a>';
}

echo '<div class="bhg-prize-category">' . $category_label . '</div>';
}
if ( $show_description && ! empty( $prize->description ) ) {
$description = wp_kses_post( wpautop( $prize->description ) );
echo '<div class="bhg-prize-description">' . $description . '</div>';
}
echo '</div>';
echo '</' . $tag . '>';

return ob_get_clean();
}


               /**
                * Minimal login hint used by some themes.
                *
                * @param array $atts Shortcode attributes. Unused.
                * @return string HTML output.
                */
               public function login_hint_shortcode( $atts = array() ) {
                               unset( $atts ); // Parameter unused but kept for shortcode signature.

                               if ( is_user_logged_in() ) {
                                               $reason = 'BHG login notice suppressed: user already logged in.';

                                               if ( current_user_can( 'manage_options' ) ) {
                                                               return '<div class="bhg-shortcode-note bhg-shortcode-note--login">' . esc_html( $reason ) . '</div><!-- ' . esc_html( $reason ) . ' -->';
                                               }

                                               return '<!-- ' . esc_html( $reason ) . ' -->';
                               }
				$raw      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				$base     = wp_validate_redirect( $raw, home_url( '/' ) );
				$redirect = esc_url_raw( add_query_arg( array(), $base ) );

			$login_url = function_exists( 'bhg_get_login_url' ) ? bhg_get_login_url( $redirect ) : wp_login_url( $redirect );

			return '<p>' . esc_html( bhg_t( 'notice_login_to_continue', 'Please log in to continue.' ) ) . '</p>'
				. '<p><a class="button button-primary" href="' . esc_url( $login_url ) . '">' . esc_html( bhg_t( 'button_log_in', 'Log in' ) ) . '</a></p>';
		}

			/**
			 * Renders list of open hunts.
			 *
			 * @param array $atts Shortcode attributes.
			 * @return string HTML output.
			 */
			   public function active_hunt_shortcode( $atts ) {
						$atts = shortcode_atts(
								array(
										'prize_layout' => 'grid',
										'prize_size'   => 'medium',
								),
								$atts,
								'bhg_active_hunt'
						);

						$prize_layout = $this->normalize_prize_layout( isset( $atts['prize_layout'] ) ? $atts['prize_layout'] : 'grid' );
						$prize_size   = $this->normalize_prize_size( isset( $atts['prize_size'] ) ? $atts['prize_size'] : 'medium' );

				   global $wpdb;
				   $hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			   if ( ! $hunts_table ) {
					   return '';
			   }

				   $cache_key = 'bhg_active_hunts';
				   $hunts     = wp_cache_get( $cache_key, 'bhg' );
					   if ( false === $hunts ) {
							   $aff_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );

							   if ( $aff_table ) {
									   $sql = "SELECT h.*, aff.name AS affiliate_site_name FROM {$hunts_table} h LEFT JOIN {$aff_table} aff ON aff.id = h.affiliate_site_id WHERE h.status = %s ORDER BY h.created_at DESC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names sanitized above.
							   } else {
									   $sql = "SELECT * FROM {$hunts_table} WHERE status = %s ORDER BY created_at DESC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above.
							   }

					   $hunts = $wpdb->get_results(
							   $wpdb->prepare(
									   $sql,
									   'open'
							   )
					   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					   wp_cache_set( $cache_key, $hunts, 'bhg', 300 );
					   }

					   if ( empty( $hunts ) ) {
					   return '<div class="bhg-active-hunt"><p>' . esc_html( bhg_t( 'notice_no_active_hunts', 'No active bonus hunts at the moment.' ) ) . '</p></div>';
					   }

					   if ( ! class_exists( 'BHG_Bonus_Hunts' ) && defined( 'BHG_PLUGIN_DIR' ) ) {
							   $hunts_class = BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts.php';
							   if ( file_exists( $hunts_class ) ) {
									   require_once $hunts_class;
							   }
					   }

					   $hunts_map = array();
					   foreach ( $hunts as $hunt ) {
							   $hunts_map[ (int) $hunt->id ] = $hunt;
					   }

			   $selected_hunt_id = 0;
			   if ( isset( $_GET['bhg_hunt'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Viewing data.
				   $selected_hunt_id = absint( wp_unslash( $_GET['bhg_hunt'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			   }

			   if ( $selected_hunt_id <= 0 || ! isset( $hunts_map[ $selected_hunt_id ] ) ) {
				   $first_hunt       = reset( $hunts );
				   $selected_hunt_id = $first_hunt ? (int) $first_hunt->id : 0;
			   }

			   if ( $selected_hunt_id <= 0 ) {
				   return '';
			   }

					   $selected_hunt = $hunts_map[ $selected_hunt_id ];

					   $prize_sets = array(
							   'regular' => array(),
							   'premium' => array(),
					   );
					   if ( class_exists( 'BHG_Prizes' ) ) {
							   $prize_sets = BHG_Prizes::get_prizes_for_hunt(
									   $selected_hunt_id,
									   array(
											   'active_only' => true,
											   'grouped'     => true,
									   )
							   );
					   }

                                        $active_default = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 30 ) : 30;
                                        $per_page       = (int) apply_filters( 'bhg_active_hunt_per_page', $active_default );
                                        if ( $per_page <= 0 ) {
                                                        $per_page = $active_default;
                                        }

			   $current_page = 1;
			   if ( isset( $_GET['bhg_hunt_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Viewing data.
				   $current_page = max( 1, absint( wp_unslash( $_GET['bhg_hunt_page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			   }

				   $guesses_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
				   $users_table   = esc_sql( $this->sanitize_table( $wpdb->users ) );
			   if ( ! $guesses_table || ! $users_table ) {
					   return '';
			   }

			   $offset        = ( $current_page - 1 ) * $per_page;
			   $final_balance = isset( $selected_hunt->final_balance ) ? $selected_hunt->final_balance : null;
			   $final_balance = '' === $final_balance ? null : $final_balance;
			   $has_final     = null !== $final_balance;

			   if ( $has_final ) {
							   $sql = sprintf(
									   'SELECT g.id, g.user_id, g.guess, g.created_at, u.display_name, u.user_login, (%%f - g.guess) AS diff FROM %1$s g LEFT JOIN %2$s u ON u.ID = g.user_id WHERE g.hunt_id = %%d ORDER BY ABS(%%f - g.guess) ASC, g.id ASC LIMIT %%d OFFSET %%d',
									   $guesses_table,
									   $users_table
							   );
				   $rows = $wpdb->get_results(
					   $wpdb->prepare(
						   $sql,
						   (float) $final_balance,
						   $selected_hunt_id,
						   $per_page,
						   $offset
					   )
				   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			   } else {
							   $sql = sprintf(
									   'SELECT g.id, g.user_id, g.guess, g.created_at, u.display_name, u.user_login, NULL AS diff FROM %1$s g LEFT JOIN %2$s u ON u.ID = g.user_id WHERE g.hunt_id = %%d ORDER BY g.created_at ASC, g.id ASC LIMIT %%d OFFSET %%d',
									   $guesses_table,
									   $users_table
							   );
				   $rows = $wpdb->get_results(
					   $wpdb->prepare(
						   $sql,
						   $selected_hunt_id,
						   $per_page,
						   $offset
					   )
				   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			   }

			   $total_guesses = (int) $wpdb->get_var(
				   $wpdb->prepare(
					   "SELECT COUNT(*) FROM {$guesses_table} WHERE hunt_id = %d",
					   $selected_hunt_id
				   )
			   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

					   $total_pages = $total_guesses > 0 ? (int) ceil( $total_guesses / $per_page ) : 1;

					   $win_limit_notice = '';
					   if ( $has_final && method_exists( 'BHG_Bonus_Hunts', 'get_ineligible_winner_ids' ) && function_exists( 'bhg_get_win_limit_config' ) && function_exists( 'bhg_build_win_limit_notice' ) ) {
							   $skipped_ids = BHG_Bonus_Hunts::get_ineligible_winner_ids( $selected_hunt_id );
							   if ( ! empty( $skipped_ids ) ) {
									   $limit_config = bhg_get_win_limit_config( 'hunt' );
									   $limit_count  = isset( $limit_config['count'] ) ? (int) $limit_config['count'] : 0;
									   $limit_period = isset( $limit_config['period'] ) ? $limit_config['period'] : 'none';
									   $notice_text  = bhg_build_win_limit_notice( 'hunt', $limit_count, $limit_period, count( $skipped_ids ) );
									   if ( '' !== $notice_text ) {
											   $win_limit_notice = $notice_text;
									   }
							   }
					   }

				   wp_enqueue_style(
					   'bhg-shortcodes',
					   ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
					   array(),
					   defined( 'BHG_VERSION' ) ? BHG_VERSION : null
				   );
			   wp_enqueue_script(
				   'bhg-shortcodes-js',
				   ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/js/bhg-shortcodes.js',
				   array(),
				   defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
				   true
			   );

			   $hunt_site_id = isset( $selected_hunt->affiliate_site_id ) ? (int) $selected_hunt->affiliate_site_id : 0;

			   ob_start();
			   echo '<div class="bhg-active-hunt">';

			   if ( count( $hunts ) > 1 ) {
				   echo '<form class="bhg-hunt-selector" method="get">';
				   if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserving query vars.
					   foreach ( wp_unslash( $_GET ) as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						   if ( in_array( $key, array( 'bhg_hunt', 'bhg_hunt_page' ), true ) ) {
							   continue;
						   }
						   if ( is_array( $value ) ) {
							   continue;
						   }
						   echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '">';
				}
		}

				   echo '<label for="bhg-hunt-select">' . esc_html( bhg_t( 'label_choose_hunt', 'Choose a hunt:' ) ) . '</label>';
				   echo '<select id="bhg-hunt-select" class="bhg-hunt-select" name="bhg_hunt">';
				   foreach ( $hunts as $hunt ) {
					   $hunt_id = (int) $hunt->id;
					   echo '<option value="' . esc_attr( $hunt_id ) . '"' . selected( $hunt_id, $selected_hunt_id, false ) . '>' . esc_html( $hunt->title ) . '</option>';
				   }
				   echo '</select>';
				   echo '<noscript><button type="submit" class="button button-primary">' . esc_html( bhg_t( 'button_apply', 'Apply' ) ) . '</button></noscript>';
				   echo '</form>';
			   }

			   echo '<div class="bhg-hunt-card">';
			   echo '<h3>' . esc_html( $selected_hunt->title ) . '</h3>';
					   echo '<ul class="bhg-hunt-meta">';
					   echo '<li><strong>' . esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ) . ':</strong> ' . esc_html( bhg_format_money( (float) $selected_hunt->starting_balance ) ) . '</li>';
					   echo '<li><strong>' . esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ) . ':</strong> ' . (int) $selected_hunt->num_bonuses . '</li>';

					   $opened_at = isset( $selected_hunt->created_at ) ? (string) $selected_hunt->created_at : '';
					   if ( '' !== $opened_at && '0000-00-00 00:00:00' !== $opened_at ) {
							   $opened_label = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $opened_at, true );
							   echo '<li><strong>' . esc_html( bhg_t( 'label_opened', 'Opened' ) ) . ':</strong> ' . esc_html( $opened_label ) . '</li>';
					   }

					   $closed_at      = isset( $selected_hunt->closed_at ) ? (string) $selected_hunt->closed_at : '';
					   $closed_display = '-';
					   if ( '' !== $closed_at && '0000-00-00 00:00:00' !== $closed_at ) {
							   $closed_display = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $closed_at, true );
					   }
					   echo '<li><strong>' . esc_html( bhg_t( 'label_closed', 'Closed' ) ) . ':</strong> ' . esc_html( $closed_display ) . '</li>';

					   $affiliate_name = isset( $selected_hunt->affiliate_site_name ) ? $selected_hunt->affiliate_site_name : '';
					   if ( '' === $affiliate_name && isset( $selected_hunt->affiliate_site_id ) && (int) $selected_hunt->affiliate_site_id > 0 ) {
							   $sites_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
							   if ( $sites_table ) {
									   $affiliate_name = $wpdb->get_var(
											   $wpdb->prepare(
													   "SELECT name FROM {$sites_table} WHERE id = %d",
													   (int) $selected_hunt->affiliate_site_id
											   )
									   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							   }
					   }
					   $affiliate_display = '' !== $affiliate_name ? $affiliate_name : '-';
					   echo '<li><strong>' . esc_html( bhg_t( 'label_affiliate_site', 'Affiliate Site' ) ) . ':</strong> ' . esc_html( $affiliate_display ) . '</li>';
					   if ( ! empty( $selected_hunt->prizes ) ) {
							   echo '<li><strong>' . esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ) . ':</strong> ' . wp_kses_post( $selected_hunt->prizes ) . '</li>';
					   }
					   if ( $has_final ) {
							   echo '<li><strong>' . esc_html( bhg_t( 'label_final_balance', 'Final Balance' ) ) . ':</strong> ' . esc_html( bhg_format_money( (float) $final_balance ) ) . '</li>';
					   }
					   echo '</ul>';

					   if ( '' !== $win_limit_notice ) {
							   echo '<div class="bhg-win-limit-notice">' . esc_html( $win_limit_notice ) . '</div>';
					   }

					   $regular_prizes = isset( $prize_sets['regular'] ) && is_array( $prize_sets['regular'] ) ? $prize_sets['regular'] : array();
					   $premium_prizes = isset( $prize_sets['premium'] ) && is_array( $prize_sets['premium'] ) ? $prize_sets['premium'] : array();
					   $show_premium   = false;

					   if ( ! empty( $premium_prizes ) && is_user_logged_in() ) {
							   if ( $hunt_site_id > 0 && function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
									   $show_premium = bhg_is_user_affiliate_for_site( get_current_user_id(), $hunt_site_id );
							   } elseif ( function_exists( 'bhg_is_user_affiliate' ) ) {
									   $show_premium = bhg_is_user_affiliate( get_current_user_id() );
							   } else {
									   $show_premium = (bool) get_user_meta( get_current_user_id(), 'bhg_is_affiliate', true );
							   }
					   }

                                           $prize_section_options = array(
                                                           'show_summary' => true,
                                           );

                                           if ( $show_premium && ! empty( $premium_prizes ) ) {
                                                           $premium_options = $prize_section_options;
                                                           $premium_options['summary_heading'] = bhg_t( 'premium_prize_summary_heading', 'Premium Prize Summary' );

                                                           if ( ! empty( $regular_prizes ) ) {
                                                                           $regular_options = $prize_section_options;
                                                                           $regular_options['summary_heading'] = bhg_t( 'regular_prize_summary_heading', 'Regular Prize Summary' );

                                                                           $tabs = array(
                                                                                           array(
                                                                                                           'label'   => bhg_t( 'premium_prizes_heading', 'Premium Prizes' ),
                                                                                                           'content' => $this->render_prize_section( $premium_prizes, $prize_layout, $prize_size, array(), $premium_options ),
                                                                                           ),
                                                                                           array(
                                                                                                           'label'   => bhg_t( 'regular_prizes_heading', 'Regular Prizes' ),
                                                                                                           'content' => $this->render_prize_section( $regular_prizes, $prize_layout, $prize_size, array(), $regular_options ),
                                                                                           ),
                                                                           );

                                                                           echo '<div class="bhg-hunt-prizes bhg-hunt-prizes--tabbed">';
                                                                           echo wp_kses_post( $this->render_prize_tabs( $tabs ) );
                                                                           echo '</div>';
                                                           } else {
                                                                           echo '<div class="bhg-hunt-prizes bhg-hunt-prizes-premium">';
                                                                           echo '<h4 class="bhg-prize-heading bhg-prize-heading-premium">' . esc_html( bhg_t( 'premium_prizes_heading', 'Premium Prizes' ) ) . '</h4>';
                                                                           echo $this->render_prize_section( $premium_prizes, $prize_layout, $prize_size, array(), $premium_options );
                                                                           echo '</div>';
                                                           }
                                           } elseif ( ! empty( $regular_prizes ) ) {
                                                           $regular_options = $prize_section_options;
                                                           $regular_options['summary_heading'] = bhg_t( 'regular_prize_summary_heading', 'Regular Prize Summary' );

                                                           echo '<div class="bhg-hunt-prizes">';
                                                           echo $this->render_prize_section( $regular_prizes, $prize_layout, $prize_size, array(), $regular_options );
                                                           echo '</div>';
                                           }
                                           echo '</div>';

			   echo '<div class="bhg-table-wrapper">';
			   if ( empty( $rows ) ) {
				   echo '<p class="bhg-no-guesses">' . esc_html( bhg_t( 'notice_no_guesses_yet', 'No guesses have been submitted for this hunt yet.' ) ) . '</p>';
			   } else {
				   echo '<table class="bhg-leaderboard bhg-active-hunt-table">';
				   echo '<thead><tr>';
				   echo '<th scope="col">' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
				   echo '<th scope="col">' . esc_html( bhg_t( 'label_username', 'Username' ) ) . '</th>';
				   echo '<th scope="col">' . esc_html( bhg_t( 'label_guess', 'Guess' ) ) . '</th>';
				   if ( $has_final ) {
					   echo '<th scope="col">' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
				   }
				   echo '</tr></thead><tbody>';
				   foreach ( $rows as $index => $row ) {
					   $position   = $offset + $index + 1;
                                           $user_login = ! empty( $row->display_name ) ? $row->display_name : $row->user_login;
                                           $user_label = $user_login ? $this->format_username_label( $user_login ) : bhg_t( 'label_unknown_user', 'Unknown user' );
					   $aff_dot    = bhg_render_affiliate_dot( (int) $row->user_id, $hunt_site_id );

					   echo '<tr>';
					   echo '<td data-label="' . esc_attr( bhg_t( 'label_position', 'Position' ) ) . '">' . (int) $position . '</td>';
					   echo '<td data-label="' . esc_attr( bhg_t( 'label_username', 'Username' ) ) . '">' . esc_html( $user_label ) . ' ' . wp_kses_post( $aff_dot ) . '</td>';
					   echo '<td data-label="' . esc_attr( bhg_t( 'label_guess', 'Guess' ) ) . '">' . esc_html( bhg_format_money( (float) $row->guess ) ) . '</td>';
					   if ( $has_final ) {
						   $diff = isset( $row->diff ) ? (float) $row->diff : 0.0;
						   echo '<td data-label="' . esc_attr( bhg_t( 'label_difference', 'Difference' ) ) . '">' . esc_html( bhg_format_money( $diff ) ) . '</td>';
					   }
					   echo '</tr>';
				   }
				   echo '</tbody></table>';
			   }
			   echo '</div>';

					   if ( $total_pages > 1 ) {
							   $pagination_links = paginate_links(
									   array(
											   'base'      => esc_url_raw( add_query_arg( array( 'bhg_hunt_page' => '%#%', 'bhg_hunt' => $selected_hunt_id ) ) ),
											   'format'    => '',
						   'current'   => $current_page,
						   'total'     => $total_pages,
						   'type'      => 'array',
						   'prev_text' => esc_html__( '&laquo;', 'bonus-hunt-guesser' ),
						   'next_text' => esc_html__( '&raquo;', 'bonus-hunt-guesser' ),
					   )
				   );

				   if ( ! empty( $pagination_links ) ) {
					   echo '<nav class="bhg-pagination" aria-label="' . esc_attr( bhg_t( 'label_pagination', 'Pagination' ) ) . '">';
					   echo '<ul class="bhg-pagination-list">';
					   foreach ( $pagination_links as $link ) {
						   $class = false !== strpos( $link, 'current' ) ? ' class="bhg-current-page"' : '';
						   echo '<li' . $class . '>' . wp_kses_post( $link ) . '</li>';
					   }
					   echo '</ul>';
					   echo '</nav>';
				   }
			   }

			   echo '</div>';

			   return ob_get_clean();
		   }

					/**
					 * Renders the guess submission form.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
		public function guess_form_shortcode( $atts ) {
				$atts    = shortcode_atts( array( 'hunt_id' => 0 ), $atts, 'bhg_guess_form' );
				$hunt_id = (int) $atts['hunt_id'];

			if ( ! is_user_logged_in() ) {
				$raw      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				$base     = wp_validate_redirect( $raw, home_url( '/' ) );
				$redirect = esc_url_raw( add_query_arg( array(), $base ) );

			$login_url = function_exists( 'bhg_get_login_url' ) ? bhg_get_login_url( $redirect ) : wp_login_url( $redirect );

			return '<p>' . esc_html( bhg_t( 'notice_login_to_guess', 'Please log in to submit your guess.' ) ) . '</p>'
				. '<p><a class="button button-primary" href="' . esc_url( $login_url ) . '">' . esc_html( bhg_t( 'button_log_in', 'Log in' ) ) . '</a></p>';
			}

						global $wpdb;
												$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			if ( ! $hunts_table ) {
					return '';
			}

						$cache_key  = 'bhg_open_hunts';
						$open_hunts = wp_cache_get( $cache_key, 'bhg' );
			if ( false === $open_hunts ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
								$open_hunts = $wpdb->get_results(
									$wpdb->prepare(
											   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
										"SELECT id, title FROM {$hunts_table} WHERE status = %s AND guessing_enabled = %d ORDER BY created_at DESC",
										'open',
										1
									)
								); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					wp_cache_set( $cache_key, $open_hunts, 'bhg', 300 );
			}

			if ( $hunt_id <= 0 ) {
				if ( ! $open_hunts ) {
					return '<p>' . esc_html( bhg_t( 'notice_no_open_hunt', 'No open hunt found to guess.' ) ) . '</p>';
				}
				if ( count( $open_hunts ) === 1 ) {
					$hunt_id = (int) $open_hunts[0]->id;
				}
			}

						$user_id = get_current_user_id();
						$table   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
			if ( ! $table ) {
				return '';
			}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$existing_id = $hunt_id > 0 ? (int) $wpdb->get_var(
					$wpdb->prepare(
							   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
						"SELECT id FROM {$table} WHERE user_id = %d AND hunt_id = %d",
						$user_id,
						$hunt_id
					)
				) : 0; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$existing_guess = $existing_id ? (float) $wpdb->get_var(
					$wpdb->prepare(
							   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
						"SELECT guess FROM {$table} WHERE id = %d",
						$existing_id
					)
				) : ''; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

						$settings        = get_option( 'bhg_plugin_settings' );
						$min             = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
						$max             = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;
						$redirect_target = ! empty( $settings['post_submit_redirect'] ) ? wp_validate_redirect( $settings['post_submit_redirect'], '' ) : '';
						$button_label    = $existing_id ? bhg_t( 'button_edit_guess', 'Edit Guess' ) : bhg_t( 'button_submit_guess', 'Submit Guess' );

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start(); ?>
												<form class="bhg-guess-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
																															   <input type="hidden" name="action" value="bhg_submit_guess">
																												<?php wp_nonce_field( 'bhg_submit_guess', 'bhg_submit_guess_nonce' ); ?>
												<?php if ( $redirect_target ) : ?>
														<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_target ); ?>">
												<?php endif; ?>

					<?php if ( $open_hunts && count( $open_hunts ) > 1 ) : ?>
					<label for="bhg-hunt-select">
						<?php
						echo esc_html( bhg_t( 'label_choose_hunt', 'Choose a hunt:' ) );
						?>
</label>
					<select id="bhg-hunt-select" name="hunt_id" required>
						<option value="">
						<?php
						echo esc_html( bhg_t( 'label_select_hunt', 'Select a hunt' ) );
						?>
</option>
												<?php foreach ( $open_hunts as $oh ) : ?>
														<option value="<?php echo esc_attr( (int) $oh->id ); ?>" <?php selected( $hunt_id, (int) $oh->id ); ?>>
																<?php echo esc_html( $oh->title ); ?>
														</option>
												<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input type="hidden" name="hunt_id" value="<?php echo esc_attr( $hunt_id ); ?>">
				<?php endif; ?>

				<label for="bhg-guess" class="bhg-guess-label">
				<?php
				echo esc_html( bhg_t( 'label_guess_final_balance', 'Your guess (final balance):' ) );
				?>
</label>
				<input type="number" step="0.01" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
					id="bhg-guess" name="guess" value="<?php echo esc_attr( $existing_guess ); ?>" required>
				<div class="bhg-error-message"></div>
								<button type="submit" class="bhg-submit-btn button button-primary"><?php echo esc_html( $button_label ); ?></button>
			</form>
				<?php
				return ob_get_clean();
		}

					/**
					 * Displays a leaderboard for a hunt.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
		public function leaderboard_shortcode( $atts ) {
               if ( ! is_array( $atts ) ) {
                               $atts = array();
               }

               $a = shortcode_atts(
					array(
						'hunt_id'  => 0,
						'orderby'  => 'guess', // guess|user|position.
						'order'    => 'ASC',
											   'fields'   => 'position,user,guess',
											   'paged'    => 1,
											   'per_page' => 30,
											   'search'   => '',
					),
					$atts,
					'bhg_leaderboard'
				);

				global $wpdb;
			$hunt_id = (int) $a['hunt_id'];
			if ( $hunt_id <= 0 ) {
																$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
				if ( ! $hunts_table ) {
										return '';
				}
				$cache_key = 'bhg_latest_hunt_id';
				$hunt_id   = wp_cache_get( $cache_key, 'bhg' );
				if ( false === $hunt_id ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
												$hunt_id = (int) $wpdb->get_var(
													$wpdb->prepare(
															   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
														"SELECT id FROM {$hunts_table} ORDER BY created_at DESC LIMIT %d",
														1
													)
												); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						wp_cache_set( $cache_key, $hunt_id, 'bhg', 300 );
				}
				if ( $hunt_id <= 0 ) {
						return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
				}
			}

						$g = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
						$u = esc_sql( $this->sanitize_table( $wpdb->users ) );
			if ( ! $g || ! $u ) {
				return '';
			}

								$allowed_orders = array( 'ASC', 'DESC' );
								$order          = strtoupper( sanitize_key( $a['order'] ) );
						if ( ! in_array( $order, $allowed_orders, true ) ) {
								$order = 'ASC';
						}
								$direction_key = strtolower( $order );
								$allowed_orderby = array(
									'guess'    => 'g.guess',
									'user'     => 'u.user_login',
									'position' => 'g.id', // stable proxy.
								);
								$orderby_key     = sanitize_key( $a['orderby'] );
								if ( ! isset( $allowed_orderby[ $orderby_key ] ) ) {
												$orderby_key = 'guess';
								}
								$orderby = $allowed_orderby[ $orderby_key ];

                        $paged    = isset( $_GET['bhg_page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                                                                                                                          ? max( 1, absint( wp_unslash( $_GET['bhg_page'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                                                                                                                          : (int) $a['paged']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                                                                                                                          $paged    = max( 1, $paged );
                                                                                                                          $per_page_default = 30;
                                                                                                                          if ( function_exists( 'bhg_get_shortcode_rows_per_page' ) ) {
                                                                                                                          $per_page_default = bhg_get_shortcode_rows_per_page( $per_page_default );
                                                                                                                          }
                        $per_page = $per_page_default;
                        if ( is_array( $atts ) && array_key_exists( 'per_page', $atts ) ) {
                                $per_page_attr = (int) $a['per_page'];
                                if ( $per_page_attr > 0 ) {
                                        $per_page = $per_page_attr;
                                                                                                                          }
                                                                                                                          }
                                                                                                                          $per_page = max( 1, $per_page );
                                                                                                                          $offset   = ( $paged - 1 ) * $per_page;

															   $search = isset( $_GET['bhg_search'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   : (string) $a['search'];

																$fields_raw    = explode( ',', (string) $a['fields'] );
																$allowed_field = array( 'position', 'user', 'guess' );
																$fields        = array_values( array_intersect( $allowed_field, array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) ) ) );
																if ( empty( $fields ) ) {
																								$fields = $allowed_field;
																}

															   $where  = array( 'g.hunt_id = %d' );
															   $params = array( $hunt_id );
															   if ( '' !== $search ) {
																	   $where[]  = 'u.user_login LIKE %s';
																	   $params[] = '%' . $wpdb->esc_like( $search ) . '%';
															   }
															   $where_sql = implode( ' AND ', $where );

															   $total_cache = 'bhg_leaderboard_total_' . $hunt_id . '_' . md5( $search );
															   $total       = wp_cache_get( $total_cache, 'bhg' );
															   if ( false === $total ) {
																			   // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names sanitized above.
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
																	   $count_sql = "SELECT COUNT(*) FROM {$g} g LEFT JOIN {$u} u ON u.ID = g.user_id WHERE {$where_sql}";
																	   $total     = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
																	   wp_cache_set( $total_cache, $total, 'bhg', 300 );
															   }
															   if ( $total < 1 ) {
																					   return '<p>' . esc_html( bhg_t( 'notice_no_guesses_yet', 'No guesses yet.' ) ) . '</p>';
															   }

																								$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
															   if ( ! $hunts_table ) {
																								return '';
															   }
																								$order_by_clause = sprintf( '%s %s', $orderby, $order );
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
															   $sql  = "SELECT g.user_id, g.guess, u.user_login, h.affiliate_site_id FROM {$g} g LEFT JOIN {$u} u ON u.ID = g.user_id LEFT JOIN {$hunts_table} h ON h.id = g.hunt_id WHERE {$where_sql} ORDER BY {$order_by_clause} LIMIT %d OFFSET %d";
															   $rows = $wpdb->get_results( $wpdb->prepare( $sql, ...array_merge( $params, array( $per_page, $offset ) ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

															   $current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
															   $base_url    = remove_query_arg( array( 'bhg_page', 'bhg_orderby', 'bhg_order' ), $current_url );
															   if ( '' === $search ) {
																	   $base_url = remove_query_arg( 'bhg_search', $base_url );
															   }

															   $toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search ) {
																	   $dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
																	   $args = array(
																			   'bhg_orderby' => $field,
																			   'bhg_order'   => $dir,
																	   );
																	   if ( '' !== $search ) {
																			   $args['bhg_search'] = $search;
																	   }
																	   return add_query_arg( $args, $base_url );
															   };

                wp_enqueue_style(
                                'bhg-shortcodes',
                                ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
                                array(),
                                defined( 'BHG_VERSION' ) ? BHG_VERSION : null
                );

                $header_class = static function ( $key ) use ( $orderby_key, $direction_key ) {
                                $classes = array( 'sortable' );
                                if ( $orderby_key === $key ) {
                                                $classes[] = ( 'desc' === strtolower( (string) $direction_key ) ) ? 'desc' : 'asc';
                                }

                                return implode( ' ', $classes );
                };

                $sort_icon_markup = static function ( $field, $label_text ) use ( $orderby_key, $direction_key ) {
                                $state       = '';
                                $direction   = strtolower( (string) $direction_key );
                                if ( $orderby_key === $field ) {
                                                $state = ( 'desc' === $direction ) ? 'desc' : 'asc';
                                }

                                $icon        = '↕';
                                $sr_template = bhg_t( 'sort_state_none', 'Sortable column — %s' );

                                if ( 'asc' === $state ) {
                                                $icon        = '↑';
                                                $sr_template = bhg_t( 'sort_state_ascending', 'Sorted ascending — %s' );
                                } elseif ( 'desc' === $state ) {
                                                $icon        = '↓';
                                                $sr_template = bhg_t( 'sort_state_descending', 'Sorted descending — %s' );
                                }

                                $sr_text = sprintf( $sr_template, $label_text );

                                return '<span class="bhg-sort-icon" aria-hidden="true">' . esc_html( $icon ) . '</span><span class="screen-reader-text">' . esc_html( $sr_text ) . '</span>';
                };

                ob_start();
											   echo '<div class="bhg-leaderboard-wrapper">';
											   echo '<form method="get" class="bhg-search-form">';
											   foreach ( $_GET as $raw_key => $v ) {
													   $key = sanitize_key( wp_unslash( $raw_key ) );
													   if ( 'bhg_search' === $key ) {
															   continue;
													   }
													   echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
											   }
											   echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
											   echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
											   echo '</form>';
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-leaderboard">';
											   echo '<thead><tr>';
                                           foreach ( $fields as $field ) {
                                                                if ( 'position' === $field ) {
                                                                                $classes = array( 'sortable' );
                                                                                if ( 'position' === $orderby_key ) {
                                                                                                $classes[] = ( 'desc' === strtolower( $direction_key ) ) ? 'desc' : 'asc';
                                                                                }
                                                                                $label = bhg_t( 'sc_position', 'Position' );
                                                                                echo '<th class="' . esc_attr( implode( ' ', $classes ) ) . '" data-column="position"><a href="' . esc_url( $toggle( 'position' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'position', $label ) . '</a></th>';
                                                                } elseif ( 'user' === $field ) {
                                                                                $classes = array( 'sortable' );
                                                                                if ( 'user' === $orderby_key ) {
                                                                                                $classes[] = ( 'desc' === strtolower( $direction_key ) ) ? 'desc' : 'asc';
                                                                                }
                                                                                $label = bhg_t( 'sc_user', 'Username' );
                                                                                echo '<th class="' . esc_attr( implode( ' ', $classes ) ) . '" data-column="user"><a href="' . esc_url( $toggle( 'user' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'user', $label ) . '</a></th>';
                                                                } elseif ( 'guess' === $field ) {
                                                                                $classes = array( 'sortable' );
                                                                                if ( 'guess' === $orderby_key ) {
                                                                                                $classes[] = ( 'desc' === strtolower( $direction_key ) ) ? 'desc' : 'asc';
                                                                                }
                                                                                $label = bhg_t( 'sc_guess', 'Guess' );
                                                                                echo '<th class="' . esc_attr( implode( ' ', $classes ) ) . '" data-column="guess"><a href="' . esc_url( $toggle( 'guess' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'guess', $label ) . '</a></th>';
                                                                }
                                           }
						echo '</tr></thead><tbody>';

												$pos       = $offset + 1;
												$need_user = in_array( 'user', $fields, true );
			foreach ( $rows as $r ) {
				if ( $need_user ) {
					$site_id                         = isset( $r->affiliate_site_id ) ? (int) $r->affiliate_site_id : 0;
											$aff_dot = bhg_render_affiliate_dot( (int) $r->user_id, $site_id );
                                                                                        /* translators: %d: user ID. */
                                                                                        $user_label = $this->format_username_label( $r->user_login, (int) $r->user_id );
				}

				echo '<tr>';
				foreach ( $fields as $field ) {
					if ( 'position' === $field ) {
						echo '<td data-column="position">' . (int) $pos . '</td>';
					} elseif ( 'user' === $field ) {
																											echo '<td data-column="user">' . esc_html( $user_label ) . ' ' . wp_kses_post( $aff_dot ) . '</td>';
					} elseif ( 'guess' === $field ) {
						echo '<td data-column="guess">' . esc_html( bhg_format_money( (float) $r->guess ) ) . '</td>';
					}
				}
								echo '</tr>';
																++$pos;
			}
												echo '</tbody></table>';

																								$pages = (int) ceil( $total / $per_page );
						if ( $pages > 1 ) {
										$pagination = paginate_links(
												array(
														'base'     => add_query_arg( 'bhg_page', '%#%', $base_url ),
														'format'   => '',
														'current'  => $paged,
														'total'    => $pages,
														'add_args' => array_filter(
																array(
																		'bhg_search'  => $search,
																		'bhg_orderby' => $orderby_key,
																		'bhg_order'   => $direction_key,
																)
														),
												)
										);
										if ( $pagination ) {
												echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
										}
						}
											   echo '</div>';

												return ob_get_clean();
		}

					/**
					 * Renders a table of guesses for a user.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
				  // phpcs:disable
				  public function user_guesses_shortcode( $atts ) {
               if ( ! is_array( $atts ) ) {
                               $atts = array();
               }

               $a = shortcode_atts(
		  array(
				  'id'       => 0,
				  'aff'      => '',
				  'website'  => 0,
				  'status'   => '',
				  'timeline' => '',
		  'fields'   => 'hunt,user,guess,final',
				  'orderby'  => 'hunt',
				  'order'    => 'DESC',
				  'paged'    => 1,
				  'search'   => '',
		  ),
		  $atts,
		  'bhg_user_guesses'
		);

		$fields_raw    = explode( ',', (string) $a['fields'] );
		$allowed_field = array( 'hunt', 'guess', 'final', 'user', 'site' );
		$fields_arr    = array_values(
				array_unique(
						array_intersect(
								$allowed_field,
								array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) )
						)
				)
		);
		if ( empty( $fields_arr ) ) {
				$fields_arr = array( 'hunt', 'user', 'guess', 'final' );
		}

		$need_site  = in_array( 'site', $fields_arr, true );
		$need_users = in_array( 'user', $fields_arr, true );

		$paged               = isset( $_GET['bhg_paged'] ) ? max( 1, (int) wp_unslash( $_GET['bhg_paged'] ) ) : max( 1, (int) $a['paged'] );
		$search              = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
                $limit_default       = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 30 ) : 30;
                $limit               = $limit_default;
		$offset              = ( $paged - 1 ) * $limit;
		$has_orderby_query   = isset( $_GET['bhg_orderby'] );
		$orderby_request     = $has_orderby_query ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
		$has_order_query     = isset( $_GET['bhg_order'] );
		$order_request       = $has_order_query ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );
                        $has_order_attribute = is_array( $atts ) && array_key_exists( 'order', $atts );

$status_attr = sanitize_key( (string) $a['status'] );
if ( ! in_array( $status_attr, array( 'open', 'closed' ), true ) ) {
$status_attr = '';
}
$status_request = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : '';
$status_filter  = in_array( $status_request, array( 'open', 'closed' ), true ) ? $status_request : $status_attr;

						global $wpdb;

						$g  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
						$h  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
						$w  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
						$um = esc_sql( $this->sanitize_table( $wpdb->usermeta ) );
						$u  = esc_sql( $this->sanitize_table( $wpdb->users ) );
			if ( ! $g || ! $h ) {
				return '';
		}
						if ( $need_site && ! $w ) {
								return '';
						}

						if ( $need_users && ! $u ) {
								return '';
						}

			// Ensure hunts table has created_at column. If missing, inform admin to run upgrades manually.
	$has_created_at = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$h} LIKE %s", 'created_at' ) );
			if ( empty( $has_created_at ) ) {
								error_log( 'Bonus Hunt Guesser: missing required column created_at in table ' . $h );
								return '<p>' . esc_html( bhg_t( 'notice_db_update_required', 'Database upgrade required. Please run plugin upgrades.' ) ) . '</p>';
			}

						$order_column = 'id';
						if ( $has_created_at ) {
								$order_column = 'created_at';
						}

						$hunt_id = (int) $a['id'];
						if ( $hunt_id <= 0 ) {
								$hunt_id = (int) $wpdb->get_var(
										$wpdb->prepare(
												/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
												"SELECT id FROM {$h} WHERE status = %s ORDER BY {$order_column} DESC LIMIT 1",
												'open'
										)
								);
						}
						if ( $hunt_id <= 0 ) {
								$hunt_id = (int) $wpdb->get_var(
										/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
										"SELECT id FROM {$h} ORDER BY {$order_column} DESC LIMIT 1"
								);
						}
						if ( $hunt_id <= 0 ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
						}

		$where  = array( 'g.hunt_id = %d' );
		$params = array( $hunt_id );

		$hunt_context = $wpdb->get_row(
				$wpdb->prepare(
						/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
						"SELECT final_balance FROM {$h} WHERE id = %d",
						$hunt_id
				)
		);
		$hunt_has_final_balance = ( $hunt_context && null !== $hunt_context->final_balance );
		$is_open_hunt           = ! $hunt_has_final_balance;

                        $aff_raw    = ( is_array( $atts ) && array_key_exists( 'aff', $atts ) ) ? $atts['aff'] : '';
			$aff_filter = sanitize_key( (string) $aff_raw );
			if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
				$aff_filter = 'yes';
			} elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
				$aff_filter = 'no';
			} else {
				$aff_filter = '';
			}

			if ( '' !== $aff_filter && ! $um ) {
				return '';
			}

			$aff_yes_values = array( '1', 'yes', 'true', 'on' );
			$aff_yes_sql    = array();
			foreach ( $aff_yes_values as $val ) {
				$aff_yes_sql[] = "'" . esc_sql( $val ) . "'";
			}
			$aff_yes_list = implode( ',', $aff_yes_sql );

			$count_joins  = array( "INNER JOIN {$h} h ON h.id = g.hunt_id" );
			$select_joins = $count_joins;

                        if ( in_array( $status_filter, array( 'open', 'closed' ), true ) ) {
                                $where[]  = 'h.status = %s';
                                $params[] = $status_filter;
                        }

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'h.affiliate_site_id = %d';
				$params[] = $website;
			}

			if ( 'yes' === $aff_filter ) {
				$count_joins[]  = "INNER JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$select_joins[] = "INNER JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$where[]        = "CAST(um_aff.meta_value AS CHAR) IN ({$aff_yes_list})";
			} elseif ( 'no' === $aff_filter ) {
				$count_joins[]  = "LEFT JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$select_joins[] = "LEFT JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$where[]        = "(um_aff.user_id IS NULL OR CAST(um_aff.meta_value AS CHAR) = '' OR CAST(um_aff.meta_value AS CHAR) NOT IN ({$aff_yes_list}))";
			}

		// Timeline handling (explicit range).
						$timeline = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : sanitize_key( $a['timeline'] );
						$range    = $this->get_timeline_range( $timeline );
						if ( $range ) {
								$where[]  = 'g.created_at BETWEEN %s AND %s';
								$params[] = $range['start'];
								$params[] = $range['end'];
						}

						if ( '' !== $search ) {
								$where[]  = 'h.title LIKE %s';
								$params[] = '%' . $wpdb->esc_like( $search ) . '%';
						}

						$direction_map = array(
								'asc'  => 'ASC',
								'desc' => 'DESC',
						);
						$default_direction_key = strtolower( sanitize_key( $a['order'] ) );
						if ( ! isset( $direction_map[ $default_direction_key ] ) ) {
								$default_direction_key = 'desc';
						}
						$order_request_key = strtolower( $order_request );
						$direction_key     = isset( $direction_map[ $order_request_key ] ) ? $order_request_key : $default_direction_key;
						if ( $is_open_hunt && ! $has_order_query && ! $has_order_attribute ) {
								$direction_key = 'asc';
						}
						$direction = $direction_map[ $direction_key ];

		$orderby_map = array(
				'guess'      => 'g.guess',
				'hunt'       => $has_created_at ? 'h.created_at' : 'h.id',
				'final'      => 'h.final_balance',
				'time'       => 'g.created_at',
				'difference' => 'difference',
		);
		$default_orderby_key = sanitize_key( $a['orderby'] );
		if ( $is_open_hunt && ! $has_orderby_query ) {
				$default_orderby_key = 'time';
		}
		if ( ! isset( $orderby_map[ $default_orderby_key ] ) ) {
				$default_orderby_key = $is_open_hunt ? 'time' : 'hunt';
		}
		$orderby_request_key = sanitize_key( $orderby_request );
		$orderby_key         = isset( $orderby_map[ $orderby_request_key ] ) ? $orderby_request_key : $default_orderby_key;
		$orderby             = $orderby_map[ $orderby_key ];

		if ( $is_open_hunt ) {
				if ( 'difference' === $orderby_key || 'final' === $orderby_key || 'hunt' === $orderby_key ) {
						$order_sql = sprintf( ' ORDER BY g.created_at %s', $direction );
				} else {
						$order_sql = sprintf( ' ORDER BY %s %s', $orderby, $direction );
				}
		} elseif ( 'difference' === $orderby_key ) {
				$order_sql = sprintf(
						' ORDER BY CASE WHEN h.final_balance IS NULL THEN 1 ELSE 0 END ASC, CASE WHEN h.final_balance IS NULL THEN g.created_at END %1$s, ABS(h.final_balance - g.guess) %1$s',
						$direction
				);
		} else {
				$order_sql = sprintf( ' ORDER BY %s %s', $orderby, $direction );
		}

			$count_params    = $params;
			$count_join_sql  = $count_joins ? ' ' . implode( ' ', $count_joins ) . ' ' : ' ';
			$count_where_sql = implode( ' AND ', $where );
			$count_sql       = "SELECT COUNT(*) FROM {$g} g{$count_join_sql}WHERE {$count_where_sql}";
						$total        = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$count_params ) );

		if ( $need_site ) {
				$select_joins[] = "LEFT JOIN {$w} w ON w.id = h.affiliate_site_id";
		}

		if ( $need_users ) {
				$count_joins[]  = "LEFT JOIN {$u} u ON u.ID = g.user_id";
				$select_joins[] = "LEFT JOIN {$u} u ON u.ID = g.user_id";
		}

			$select_join_sql = $select_joins ? ' ' . implode( ' ', $select_joins ) . ' ' : ' ';
			$where_sql       = implode( ' AND ', $where );

		$sql = 'SELECT g.guess, g.created_at, g.user_id, h.title, h.final_balance, h.affiliate_site_id, CASE WHEN h.final_balance IS NOT NULL THEN (h.final_balance - g.guess) END AS difference';
		if ( $need_site ) {
				$sql .= ', w.name AS site_name';
		}
		if ( $need_users ) {
				$sql .= ', u.display_name AS user_display_name, u.user_login AS user_login';
		}
		$sql .= " FROM {$g} g{$select_join_sql}WHERE {$where_sql}{$order_sql} LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;
		$query    = $wpdb->prepare( $sql, ...$params );

						// db call ok; no-cache ok.
						$rows  = $wpdb->get_results( $query );
						$pages = (int) ceil( $total / $limit );

						$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
						$base_url    = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
						if ( '' === $search ) {
								$base_url = remove_query_arg( 'bhg_search', $base_url );
						}

						$toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search ) {
								$dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
								$args = array(
										'bhg_orderby' => $field,
										'bhg_order'   => $dir,
								);
								if ( '' !== $search ) {
										$args['bhg_search'] = $search;
								}
								return add_query_arg( $args, $base_url );
						};

						if ( ! $rows ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_guesses_found', 'No guesses found.' ) ) . '</p>';
						}

		$show_aff = $need_users;

                                                wp_enqueue_style(
                                                        'bhg-shortcodes',
                                                        ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
                                                        array(),
                                                        defined( 'BHG_VERSION' ) ? BHG_VERSION : null
                                                );

                                                $header_class = static function ( $key ) use ( $orderby_key, $direction_key ) {
                                                        $classes = array( 'sortable' );
                                                        if ( $orderby_key === $key ) {
                                                                $classes[] = ( 'desc' === strtolower( (string) $direction_key ) ) ? 'desc' : 'asc';
                                                        }

                                                        return implode( ' ', $classes );
                                                };

                                                ob_start();
                                                echo '<form method="get" class="bhg-search-form">';
		  foreach ( $_GET as $raw_key => $v ) {
				  $key = sanitize_key( wp_unslash( $raw_key ) );
				  if ( in_array( $key, array( 'bhg_search', 'bhg_timeline' ), true ) ) {
						  continue;
				  }
				  $value = is_array( $v ) ? reset( $v ) : $v;
				  echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( $value ) ) ) . '">';
		  }
		  echo '<label class="bhg-timeline-filter">';
		  echo '<span>' . esc_html( bhg_t( 'label_timeline_colon', 'Timeline:' ) ) . '</span> ';
  echo '<select name="bhg_timeline">';
  $timeline_options = array(
                  'all_time'     => bhg_t( 'label_all_time', 'Alltime' ),
                  'today'        => bhg_t( 'label_today', 'Today' ),
                  'this_week'    => bhg_t( 'label_this_week', 'This Week' ),
                  'this_month'   => bhg_t( 'label_this_month', 'This Month' ),
                  'this_quarter' => bhg_t( 'option_timeline_this_quarter', 'This Quarter' ),
                  'this_year'    => bhg_t( 'label_this_year', 'This Year' ),
                  'last_year'    => bhg_t( 'label_last_year', 'Last Year' ),
  );
  $selected_timeline = $timeline;
  if ( ! array_key_exists( $selected_timeline, $timeline_options ) ) {
                  $selected_timeline = 'all_time';
  }
  foreach ( $timeline_options as $key => $label ) {
                  echo '<option value="' . esc_attr( $key ) . '"' . selected( $selected_timeline, $key, false ) . '>' . esc_html( $label ) . '</option>';
  }
		  echo '</select>';
		  echo '</label>';
		  echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
		  echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
		  echo '</form>';

echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-user-guesses"><thead><tr>';
                echo '<th class="' . esc_attr( $header_class( 'hunt' ) ) . '"><a href="' . esc_url( $toggle( 'hunt' ) ) . '">' . esc_html( bhg_t( 'sc_hunt', 'Hunt' ) ) . '</a></th>';
                if ( $need_users ) {
                                echo '<th>' . esc_html( bhg_t( 'label_user', 'Username' ) ) . '</th>';
                }
                echo '<th class="' . esc_attr( $header_class( 'guess' ) ) . '"><a href="' . esc_url( $toggle( 'guess' ) ) . '">' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</a></th>';
                if ( $need_site ) {
                                echo '<th>' . esc_html( bhg_t( 'label_site', 'Site' ) ) . '</th>';
                }
                echo '<th class="' . esc_attr( $header_class( 'final' ) ) . '"><a href="' . esc_url( $toggle( 'final' ) ) . '">' . esc_html( bhg_t( 'sc_final', 'Final' ) ) . '</a></th>';
                echo '<th class="' . esc_attr( $header_class( 'difference' ) ) . '"><a href="' . esc_url( $toggle( 'difference' ) ) . '">' . esc_html( bhg_t( 'sc_difference', 'Difference' ) ) . '</a></th>';
		echo '</tr></thead><tbody>';

						foreach ( $rows as $row ) {
								echo '<tr>';
				echo '<td>' . esc_html( $row->title ) . '</td>';
				if ( $need_users ) {
						$user_display = '';
						if ( isset( $row->user_display_name ) && '' !== (string) $row->user_display_name ) {
								$user_display = (string) $row->user_display_name;
						} elseif ( isset( $row->user_login ) && '' !== (string) $row->user_login ) {
								$user_display = (string) $row->user_login;
						} else {
								$user_display = sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
						}

						$user_cell = '';
						if ( $show_aff ) {
								$user_cell .= bhg_render_affiliate_dot( (int) $row->user_id, (int) $row->affiliate_site_id ) . ' ';
						}
						$user_cell .= '<span class="bhg-user-name">' . esc_html( $user_display ) . '</span>';
						echo '<td>' . wp_kses_post( $user_cell ) . '</td>';
				}
				echo '<td>' . esc_html( bhg_format_money( (float) $row->guess ) ) . '</td>';
				if ( $need_site ) {
						echo '<td>' . esc_html( $row->site_name ? $row->site_name : bhg_t( 'label_emdash', '—' ) ) . '</td>';
				}
				echo '<td>' . ( isset( $row->final_balance ) ? esc_html( bhg_format_money( (float) $row->final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				echo '<td>' . ( isset( $row->difference ) ? esc_html( bhg_format_money( (float) $row->difference ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				echo '</tr>';
		}
		echo '</tbody></table>';

		  $pagination = paginate_links(
				  array(
						  'base'      => add_query_arg( 'bhg_paged', '%#%', $base_url ),
						  'format'    => '',
						  'current'   => $paged,
						  'total'     => max( 1, $pages ),
						  'add_args'  => array_filter(
								  array(
										  'bhg_orderby' => $orderby_key,
										  'bhg_order'   => $direction_key,
										  'bhg_search'  => $search,
										  'bhg_timeline'=> $timeline,
								  )
						  ),
				  )
		  );
                                                   if ( $pagination ) {
                                                                                   echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
                                                   }

                                                   return ob_get_clean();
                                    }



                               /**
                                * Render a condensed text list of the latest winners.
                                *
                                * @param array $atts Shortcode attributes.
                                * @return string
                                */
                               public function latest_winners_list_shortcode( $atts ) {
                                               global $wpdb;

                                               $atts = shortcode_atts(
                                                               array(
                                                                               'limit'  => 10,
                                                                               'fields' => 'date,username,prize,bonushunt,tournament',
                                                                               'empty'  => '',
                                                               ),
$atts,
'bhg_latest_winners_list'
);

                                               $limit = max( 1, min( 100, (int) $atts['limit'] ) );

                                               $raw_fields = array_map( 'trim', explode( ',', (string) $atts['fields'] ) );
                                               $allowed    = array( 'date', 'username', 'prize', 'bonushunt', 'tournament', 'position' );
                                               $fields     = array();
                                               foreach ( $raw_fields as $field ) {
                                                               $key = sanitize_key( $field );
                                                               if ( in_array( $key, $allowed, true ) ) {
                                                                               $fields[] = $key;
                                                               }
                                               }

                                               if ( empty( $fields ) ) {
                                                               $fields = array( 'date', 'username', 'prize', 'bonushunt' );
                                               }

                                               $show_date       = in_array( 'date', $fields, true );
                                               $show_username   = in_array( 'username', $fields, true );
                                               $show_prize      = in_array( 'prize', $fields, true );
                                               $show_hunt       = in_array( 'bonushunt', $fields, true );
                                               $show_tournament = in_array( 'tournament', $fields, true );
                                               $show_position   = in_array( 'position', $fields, true );

                                               $winners_tbl     = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
                                               $hunts_tbl       = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
                                               $users_tbl       = esc_sql( $this->sanitize_table( $wpdb->users ) );
                                               $relation_tbl    = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments_hunts' ) );
                                               $tournaments_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );

                                               if ( ! $winners_tbl || ! $hunts_tbl || ! $users_tbl ) {
                                                               return '';
                                               }

                                               $sql = 'SELECT w.id, w.hunt_id, w.user_id, w.position, w.created_at, '
                                                               . 'h.title AS hunt_title, h.affiliate_site_id, h.tournament_id AS legacy_tournament_id, '
                                                               . 'u.display_name, u.user_login '
                                                               . "FROM {$winners_tbl} w "
                                                               . "INNER JOIN {$hunts_tbl} h ON h.id = w.hunt_id "
                                                               . "LEFT JOIN {$users_tbl} u ON u.ID = w.user_id "
                                                               . 'WHERE w.eligible = 1 '
                                                               . 'ORDER BY w.created_at DESC, w.id DESC '
                                                               . 'LIMIT %d';

                                               $rows = $wpdb->get_results( $wpdb->prepare( $sql, $limit ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

                                               if ( empty( $rows ) ) {
                                                               $empty_text = '' !== $atts['empty'] ? $atts['empty'] : bhg_t( 'notice_no_winners_yet', 'There are no winners yet' );

                                                               return '<p class="bhg-latest-winners-empty">' . esc_html( $empty_text ) . '</p>';
                                               }

                                               $hunt_ids = array();
                                               foreach ( $rows as $row ) {
                                                               $hunt_ids[] = isset( $row->hunt_id ) ? (int) $row->hunt_id : 0;
                                               }
                                               $hunt_ids = array_values( array_unique( array_filter( $hunt_ids ) ) );

                                               $hunt_tournaments = array();
                                               if ( $relation_tbl && $tournaments_tbl && ! empty( $hunt_ids ) ) {
                                                               $placeholders = implode( ',', array_fill( 0, count( $hunt_ids ), '%d' ) );
                                                               $pairs        = $wpdb->get_results(
                                                                               $wpdb->prepare(
                                                                                               "SELECT hunt_id, tournament_id FROM {$relation_tbl} WHERE hunt_id IN ({$placeholders})",
                                                                                               ...$hunt_ids
                                                                               )
                                                               );

                                                               foreach ( (array) $pairs as $pair ) {
                                                                               $hid = isset( $pair->hunt_id ) ? (int) $pair->hunt_id : 0;
                                                                               $tid = isset( $pair->tournament_id ) ? (int) $pair->tournament_id : 0;
                                                                               if ( $hid > 0 && $tid > 0 ) {
                                                                                               if ( ! isset( $hunt_tournaments[ $hid ] ) ) {
                                                                                                               $hunt_tournaments[ $hid ] = array();
                                                                                               }
                                                                                               $hunt_tournaments[ $hid ][ $tid ] = $tid;
                                                                               }
                                                               }
                                               }

                                               foreach ( $rows as $row ) {
                                                               $hid = isset( $row->hunt_id ) ? (int) $row->hunt_id : 0;
                                                               $legacy_tid = isset( $row->legacy_tournament_id ) ? (int) $row->legacy_tournament_id : 0;
                                                               if ( $hid > 0 && $legacy_tid > 0 ) {
                                                                               if ( ! isset( $hunt_tournaments[ $hid ] ) ) {
                                                                                               $hunt_tournaments[ $hid ] = array();
                                                                               }
                                                                               $hunt_tournaments[ $hid ][ $legacy_tid ] = $legacy_tid;
                                                               }
                                               }

                                               $tournament_lookup_ids = array();
                                               foreach ( $hunt_tournaments as $ids ) {
                                                               foreach ( $ids as $tid ) {
                                                                               $tournament_lookup_ids[ $tid ] = $tid;
                                                               }
                                               }

                                               $tournament_titles = array();
                                               if ( $tournaments_tbl && ! empty( $tournament_lookup_ids ) ) {
                                                               $ids          = array_values( $tournament_lookup_ids );
                                                               $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
                                                               $results      = $wpdb->get_results(
                                                                               $wpdb->prepare(
                                                                                               "SELECT id, title FROM {$tournaments_tbl} WHERE id IN ({$placeholders})",
                                                                                               ...$ids
                                                                               )
                                                               );

                                                               foreach ( (array) $results as $result ) {
                                                                               $tournament_titles[ (int) $result->id ] = (string) $result->title;
                                                               }
                                               }

                                               $prize_cache = array();
                                               $items       = array();
                                               foreach ( $rows as $row ) {
                                                               $hunt_id     = isset( $row->hunt_id ) ? (int) $row->hunt_id : 0;
                                                               $user_id     = isset( $row->user_id ) ? (int) $row->user_id : 0;
                                                               $hunt_title  = isset( $row->hunt_title ) && '' !== (string) $row->hunt_title ? (string) $row->hunt_title : bhg_t( 'label_unnamed_hunt', 'Untitled hunt' );
                                                               $created_at  = isset( $row->created_at ) ? $row->created_at : '';
                                                               $date_output = $created_at ? mysql2date( get_option( 'date_format' ), $created_at ) : '';
                                                               $position    = isset( $row->position ) ? (int) $row->position : 0;

                                                               $user_label = '';
                                                               if ( isset( $row->display_name ) && '' !== (string) $row->display_name ) {
                                                                               $user_label = (string) $row->display_name;
                                                               } elseif ( isset( $row->user_login ) && '' !== (string) $row->user_login ) {
                                                                               $user_label = (string) $row->user_login;
                                                               } elseif ( $user_id > 0 ) {
                                                                               /* translators: %d: user ID. */
                                                                               $user_label = sprintf( bhg_t( 'label_user_hash', 'user#%d' ), $user_id );
                                                               }
                                                               $user_label = $this->format_username_label( $user_label, $user_id );

                                                               $prize_title = '';
                                                               if ( $show_prize && $hunt_id > 0 ) {
                                                                               if ( ! isset( $prize_cache[ $hunt_id ] ) ) {
                                                                                               $prize_cache[ $hunt_id ] = array(
                                                                                                               'regular' => array(),
                                                                                                               'premium' => array(),
                                                                                               );
                                                                                               if ( class_exists( 'BHG_Prizes' ) ) {
                                                                                                               $sets = BHG_Prizes::get_prizes_for_hunt( $hunt_id, array( 'grouped' => true ) );
                                                                                                               foreach ( array( 'regular', 'premium' ) as $set_key ) {
                                                                                                                               if ( ! empty( $sets[ $set_key ] ) && is_array( $sets[ $set_key ] ) ) {
                                                                                                                                               foreach ( $sets[ $set_key ] as $prize_row ) {
                                                                                                                                                               if ( isset( $prize_row->title ) && '' !== (string) $prize_row->title ) {
                                                                                                                                                                               $prize_cache[ $hunt_id ][ $set_key ][] = (string) $prize_row->title;
                                                                                                                                                               }
                                                                                                                                               }
                                                                                                                               }
                                                                                                               }
                                                                                               }
                                                                               }

                                                                               $site_id = isset( $row->affiliate_site_id ) ? (int) $row->affiliate_site_id : 0;
                                                                               $is_aff  = false;
                                                                               if ( $user_id > 0 ) {
                                                                                               if ( function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
                                                                                                               $is_aff = bhg_is_user_affiliate_for_site( $user_id, $site_id );
                                                                                               } elseif ( function_exists( 'bhg_is_user_affiliate' ) ) {
                                                                                                               $is_aff = bhg_is_user_affiliate( $user_id );
                                                                                               } else {
                                                                                                               $meta_val = get_user_meta( $user_id, 'bhg_is_affiliate', true );
                                                                                                               $is_aff   = in_array( (string) $meta_val, array( '1', 'yes', 'true' ), true );
                                                                                               }
                                                                               }

                                                                               $prizes_regular = $prize_cache[ $hunt_id ]['regular'];
                                                                               $prizes_premium = $prize_cache[ $hunt_id ]['premium'];
                                                                               $index          = max( 0, $position - 1 );

                                                                               if ( $is_aff && isset( $prizes_premium[ $index ] ) ) {
                                                                                               $prize_title = $prizes_premium[ $index ];
                                                                               } elseif ( isset( $prizes_regular[ $index ] ) ) {
                                                                                               $prize_title = $prizes_regular[ $index ];
                                                                               } elseif ( $is_aff && isset( $prizes_premium[0] ) ) {
                                                                                               $prize_title = $prizes_premium[0];
                                                                               }
                                                               }

                                                               $tournament_names = array();
                                                               if ( $show_tournament && $hunt_id > 0 && isset( $hunt_tournaments[ $hunt_id ] ) ) {
                                                                               foreach ( $hunt_tournaments[ $hunt_id ] as $tid ) {
                                                                                               if ( isset( $tournament_titles[ $tid ] ) ) {
                                                                                                               $tournament_names[] = $tournament_titles[ $tid ];
                                                                                               }
                                                                               }
                                                               }

                                                               $parts = array();
                                                               if ( $show_date ) {
                                                                               $parts[] = '<span class="bhg-winner-date">' . esc_html( $date_output ) . '</span>';
                                                               }
                                                               if ( $show_position && $position > 0 ) {
                                                                               $parts[] = '<span class="bhg-winner-position">#' . esc_html( number_format_i18n( $position ) ) . '</span>';
                                                               }
                                                               if ( $show_username ) {
                                                                               $parts[] = '<span class="bhg-winner-username">' . esc_html( $user_label ) . '</span>';
                                                               }
                                                               if ( $show_prize ) {
                                                                               $parts[] = '<span class="bhg-winner-prize">' . esc_html( '' !== $prize_title ? $prize_title : bhg_t( 'label_emdash', '—' ) ) . '</span>';
                                                               }
                                                               if ( $show_hunt ) {
                                                                               $parts[] = '<span class="bhg-winner-hunt">' . esc_html( $hunt_title ) . '</span>';
                                                               }
                                                               if ( $show_tournament ) {
                                                                               $parts[] = '<span class="bhg-winner-tournament">' . esc_html( ! empty( $tournament_names ) ? implode( ', ', $tournament_names ) : bhg_t( 'label_emdash', '—' ) ) . '</span>';
                                                               }

                                                               $parts = array_filter( $parts, static function ( $part ) {
                                                                               return '' !== $part;
                                                               } );

                                                               $items[] = '<li class="bhg-latest-winner-item">' . implode( ' <span class="bhg-separator">&mdash;</span> ', $parts ) . '</li>';
                                               }

                                               if ( empty( $items ) ) {
                                                               $empty_text = '' !== $atts['empty'] ? $atts['empty'] : bhg_t( 'notice_no_winners_yet', 'There are no winners yet' );

                                                               return '<p class="bhg-latest-winners-empty">' . esc_html( $empty_text ) . '</p>';
                                               }

                                               return '<ul class="bhg-latest-winners-list">' . implode( '', $items ) . '</ul>';
                               }

                               /**
                                * Render a compact leaderboard text list.
                                *
                                * @param array $atts Shortcode attributes.
                                * @return string
                                */
                               public function leaderboard_list_shortcode( $atts ) {
                                               $atts = shortcode_atts(
                                                               array(
                                                                               'limit'      => 5,
                                                                               'fields'     => 'position,username,times_won,avg_hunt,avg_tournament',
                                                                               'timeline'   => '',
'tournament' => '',
                                                                               'website'    => '',
                                                                               'aff'        => '',
                                                                               'orderby'    => 'wins',
                                                                               'order'      => 'DESC',
                                                                               'empty'      => '',
                                                               ),
$atts,
'bhg_leaderboard_list'
);

                                               $limit = max( 1, min( 100, (int) $atts['limit'] ) );

                                               $raw_fields = array_map( 'trim', explode( ',', (string) $atts['fields'] ) );
                                               $allowed    = array( 'position', 'username', 'times_won', 'avg_hunt', 'avg_tournament' );
                                               $fields     = array();
                                               foreach ( $raw_fields as $field ) {
                                                               $key = sanitize_key( $field );
                                                               if ( in_array( $key, $allowed, true ) ) {
                                                                               $fields[] = $key;
                                                               }
                                               }
                                               if ( empty( $fields ) ) {
                                                               $fields = array( 'position', 'username', 'times_won' );
                                               }

                                               $show_position       = in_array( 'position', $fields, true );
                                               $show_username       = in_array( 'username', $fields, true );
                                               $show_times_won      = in_array( 'times_won', $fields, true );
                                               $show_avg_hunt       = in_array( 'avg_hunt', $fields, true );
                                               $show_avg_tournament = in_array( 'avg_tournament', $fields, true );

                                               $timeline_key      = sanitize_key( (string) $atts['timeline'] );
                                               $allowed_timelines = array( 'all_time', 'today', 'this_week', 'this_month', 'this_quarter', 'this_year', 'last_year' );
                                               if ( ! in_array( $timeline_key, $allowed_timelines, true ) ) {
                                                               $timeline_key = 'all_time';
                                               }
                                               $timeline_map = array(
                                                               'all_time'     => 'all_time',
                                                               'today'        => 'day',
                                                               'this_week'    => 'week',
                                                               'this_month'   => 'month',
                                                               'this_quarter' => 'quarter',
                                                               'this_year'    => 'year',
                                                               'last_year'    => 'last_year',
                                               );
                                               $timeline = $timeline_map[ $timeline_key ];

                                               $tournament_id = isset( $atts['tournament'] ) ? (int) $atts['tournament'] : 0;
                                               $hunt_id       = isset( $atts['bonushunt'] ) ? (int) $atts['bonushunt'] : 0;
                                               $website_id    = isset( $atts['website'] ) ? (int) $atts['website'] : 0;
                                               $aff_filter    = sanitize_key( (string) $atts['aff'] );
                                               if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
                                                               $aff_filter = 'yes';
                                               } elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
                                                               $aff_filter = 'no';
                                               } else {
                                                               $aff_filter = '';
                                               }

                                               $orderby = sanitize_key( (string) $atts['orderby'] );
                                               if ( ! in_array( $orderby, array( 'wins', 'user', 'avg_hunt', 'avg_tournament' ), true ) ) {
                                                               $orderby = 'wins';
                                               }

                                               $order = strtolower( sanitize_key( (string) $atts['order'] ) );
                                               if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
                                                               $order = 'desc';
                                               }

                                               $query_result = $this->run_leaderboard_query(
                                                               array(
                                                                               'fields'               => array( 'pos', 'user', 'wins', 'avg_hunt', 'avg_tournament' ),
                                                                               'timeline'             => $timeline,
                                                                               'tournament_id'        => $tournament_id,
                                                                               'hunt_id'              => $hunt_id,
                                                                               'website_id'           => $website_id,
                                                                               'aff_filter'           => $aff_filter,
                                                                               'ranking_limit'        => $limit,
                                                                               'paged'                => 1,
                                                                               'per_page'             => $limit,
                                                                               'orderby'              => $orderby,
                                                                               'order'                => $order,
                                                                               'need_avg_hunt'        => $show_avg_hunt,
                                                                               'need_avg_tournament'  => $show_avg_tournament || 'avg_tournament' === $orderby,
                                                                               'need_site'            => false,
                                                                               'need_tournament_name' => false,
                                                                               'need_hunt_name'       => false,
                                                                               'need_aff'             => false,
                                                               )
                                               );

                                               if ( empty( $query_result['rows'] ) ) {
                                                               $empty_text = '' !== $atts['empty'] ? $atts['empty'] : bhg_t( 'notice_no_data_available', 'No data available.' );

                                                               return '<p class="bhg-leaderboard-list-empty">' . esc_html( $empty_text ) . '</p>';
                                               }

                                               $rows   = $query_result['rows'];
                                               $offset = isset( $query_result['offset'] ) ? (int) $query_result['offset'] : 0;

                                               $charset = get_bloginfo( 'charset' );
                                               $charset = $charset ? $charset : 'UTF-8';

                                               $items = array();
                                               $position_counter = $offset + 1;
                                               foreach ( $rows as $row ) {
                                                               $parts = array();

                                                               if ( $show_position ) {
                                                                               $parts[] = '<span class="bhg-leaderboard-pos">#' . esc_html( number_format_i18n( $position_counter ) ) . '</span>';
                                                               }

                                                               if ( $show_username ) {
                                                                               $label = '';
                                                                               if ( isset( $row->user_login ) && '' !== (string) $row->user_login ) {
                                                                                               $label = (string) $row->user_login;
                                                                               } elseif ( isset( $row->user_id ) ) {
                                                                                               /* translators: %d: user ID. */
                                                                                               $label = sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
                                                                               }
                                                                               if ( '' !== $label ) {
                                                                                               if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strtoupper' ) ) {
                                                                                                               $first = mb_substr( $label, 0, 1, $charset );
                                                                                                               $rest  = mb_substr( $label, 1, null, $charset );
                                                                                                               $label = mb_strtoupper( $first, $charset ) . $rest;
                                                                                               } else {
                                                                                                               $label = ucfirst( $label );
                                                                                               }
                                                                               }
                                                                               $parts[] = '<span class="bhg-leaderboard-user">' . esc_html( $label ) . '</span>';
                                                               }

                                                               if ( $show_times_won ) {
                                                                               $wins_value = isset( $row->total_wins ) ? (int) $row->total_wins : 0;
                                                                               $parts[]     = '<span class="bhg-leaderboard-wins">' . esc_html( number_format_i18n( $wins_value ) ) . '</span>';
                                                               }

                                                               if ( $show_avg_hunt ) {
                                                                               $avg_hunt = isset( $row->avg_hunt_pos ) ? number_format_i18n( (float) $row->avg_hunt_pos, 0 ) : bhg_t( 'label_emdash', '—' );
                                                                               $parts[]  = '<span class="bhg-leaderboard-avg-hunt">' . esc_html( $avg_hunt ) . '</span>';
                                                               }

                                                               if ( $show_avg_tournament ) {
                                                                               $avg_tournament = isset( $row->avg_tournament_pos ) ? number_format_i18n( (float) $row->avg_tournament_pos, 0 ) : bhg_t( 'label_emdash', '—' );
                                                                               $parts[]        = '<span class="bhg-leaderboard-avg-tournament">' . esc_html( $avg_tournament ) . '</span>';
                                                               }

                                                               $parts = array_filter( $parts, static function ( $part ) {
                                                                               return '' !== $part;
                                                               } );

                                                               $items[] = '<li class="bhg-leaderboard-list-item">' . implode( ' <span class="bhg-separator">&mdash;</span> ', $parts ) . '</li>';
                                                               ++$position_counter;
                                               }

                                               return '<ul class="bhg-leaderboard-list">' . implode( '', $items ) . '</ul>';
                               }

                               /**
                                * Render a text list of tournaments.
                                *
                                * @param array $atts Shortcode attributes.
                                * @return string
                                */
                               public function tournament_list_shortcode( $atts ) {
                                               global $wpdb;

                                               $atts = shortcode_atts(
                                                               array(
                                                                               'status' => 'active',
                                                                               'timeline' => '',
                                                                               'limit' => 5,
                                                                               'orderby' => 'start_date',
                                                                               'order' => 'desc',
                                                                               'fields' => 'name,start_date,end_date,status,details',
                                                                               'empty'  => '',
                                                               ),
$atts,
'bhg_tournament_list'
);

                                               $limit   = max( 1, min( 100, (int) $atts['limit'] ) );
                                               $orderby = sanitize_key( (string) $atts['orderby'] );
                                               if ( ! in_array( $orderby, array( 'start_date', 'end_date', 'title', 'status' ), true ) ) {
                                                               $orderby = 'start_date';
                                               }

                                               $order = strtolower( sanitize_key( (string) $atts['order'] ) );
                                               if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
                                                               $order = 'desc';
                                               }

                                               $raw_fields = array_map( 'trim', explode( ',', (string) $atts['fields'] ) );
                                               $allowed    = array( 'name', 'start_date', 'end_date', 'status', 'details' );
                                               $fields     = array();
                                               foreach ( $raw_fields as $field ) {
                                                               $key = sanitize_key( $field );
                                                               if ( in_array( $key, $allowed, true ) ) {
                                                                               $fields[] = $key;
                                                               }
                                               }
                                               if ( empty( $fields ) ) {
                                                               $fields = array( 'name', 'start_date', 'status' );
                                               }

                                               $show_name    = in_array( 'name', $fields, true );
                                               $show_start   = in_array( 'start_date', $fields, true );
                                               $show_end     = in_array( 'end_date', $fields, true );
                                               $show_status  = in_array( 'status', $fields, true );
                                               $show_details = in_array( 'details', $fields, true );

                                               $status_filter = sanitize_key( (string) $atts['status'] );
                                               if ( ! in_array( $status_filter, array( 'active', 'closed', 'all' ), true ) ) {
                                                               $status_filter = 'active';
                                               }

                                               $timeline_key      = sanitize_key( (string) $atts['timeline'] );
                                               $allowed_timelines = array( 'all_time', 'today', 'this_week', 'this_month', 'this_quarter', 'this_year', 'last_year' );
                                               if ( ! in_array( $timeline_key, $allowed_timelines, true ) ) {
                                                               $timeline_key = 'all_time';
                                               }
                                               $timeline_map = array(
                                                               'all_time'     => 'all_time',
                                                               'today'        => 'day',
                                                               'this_week'    => 'week',
                                                               'this_month'   => 'month',
                                                               'this_quarter' => 'quarter',
                                                               'this_year'    => 'year',
                                                               'last_year'    => 'last_year',
                                               );
                                               $timeline = $timeline_map[ $timeline_key ];

                                               $table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
                                               if ( ! $table ) {
                                                               return '';
                                               }

                                               $where  = array();
                                               $params = array();

                                               if ( in_array( $status_filter, array( 'active', 'closed' ), true ) ) {
                                                               $where[]  = 'status = %s';
                                                               $params[] = $status_filter;
                                               }

                                               $range = $this->get_timeline_range( $timeline );
                                               if ( $range ) {
                                                               $start = substr( $range['start'], 0, 10 );
                                                               $end   = substr( $range['end'], 0, 10 );
                                                               $where[]  = '( (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s) )';
                                                               $params[] = $end;
                                                               $params[] = $start;
                                               }

                                               $where_sql = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';

                                               $sql = 'SELECT id, title, start_date, end_date, status FROM ' . $table . $where_sql . ' ORDER BY ' . $orderby . ' ' . strtoupper( $order ) . ' LIMIT %d'; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                                               $query_args = array_merge( $params, array( $limit ) );
                                               $rows       = $wpdb->get_results( $wpdb->prepare( $sql, ...$query_args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

                                               if ( empty( $rows ) ) {
                                                               $empty_text = '' !== $atts['empty'] ? $atts['empty'] : bhg_t( 'notice_no_tournaments_found', 'No tournaments found.' );

                                                               return '<p class="bhg-tournament-list-empty">' . esc_html( $empty_text ) . '</p>';
                                               }

                                               $details_base = function_exists( 'bhg_get_core_page_url' ) ? bhg_get_core_page_url( 'tournaments' ) : '';

                                               $items = array();
                                               foreach ( $rows as $row ) {
                                                               $parts = array();

                                                               if ( $show_name ) {
                                                                               $title = isset( $row->title ) && '' !== (string) $row->title ? (string) $row->title : bhg_t( 'label_unnamed_tournament', 'Untitled tournament' );
                                                                               $parts[] = '<span class="bhg-tournament-name">' . esc_html( $title ) . '</span>';
                                                               }

                                                               if ( $show_start ) {
                                                                               $start_value = isset( $row->start_date ) && $row->start_date ? mysql2date( get_option( 'date_format' ), $row->start_date ) : bhg_t( 'label_emdash', '—' );
                                                                               $parts[]      = '<span class="bhg-tournament-start">' . esc_html( $start_value ) . '</span>';
                                                               }

                                                               if ( $show_end ) {
                                                                               $end_value = isset( $row->end_date ) && $row->end_date ? mysql2date( get_option( 'date_format' ), $row->end_date ) : bhg_t( 'label_emdash', '—' );
                                                                               $parts[]    = '<span class="bhg-tournament-end">' . esc_html( $end_value ) . '</span>';
                                                               }

                                                               if ( $show_status ) {
                                                                               $status_key = isset( $row->status ) ? strtolower( (string) $row->status ) : 'unknown';
                                                                               $parts[]    = '<span class="bhg-tournament-status">' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</span>';
                                                               }

                                                               if ( $show_details ) {
                                                                               $details_url = '';
                                                                               if ( '' !== $details_base ) {
                                                                                               $details_url = add_query_arg( 'bhg_tournament_id', (int) $row->id, $details_base );
                                                                               }
                                                                               $details_text = '' !== $details_url ? '<a href="' . esc_url( $details_url ) . '">' . esc_html( bhg_t( 'label_show_details', 'Show details' ) ) . '</a>' : esc_html( bhg_t( 'label_emdash', '—' ) );
                                                                               $parts[]      = '<span class="bhg-tournament-details">' . $details_text . '</span>';
                                                               }

                                                               $parts = array_filter( $parts, static function ( $part ) {
                                                                               return '' !== $part;
                                                               } );

                                                               $items[] = '<li class="bhg-tournament-list-item">' . implode( ' <span class="bhg-separator">&mdash;</span> ', $parts ) . '</li>';
                                               }

                                               return '<ul class="bhg-tournament-list">' . implode( '', $items ) . '</ul>';
                               }

                               /**
                                * Render a text list of bonus hunts.
                                *
                                * @param array $atts Shortcode attributes.
                                * @return string
                                */
                               public function bonushunt_list_shortcode( $atts ) {
                                               global $wpdb;

                                               $atts = shortcode_atts(
                                                               array(
                                                                               'status' => 'open',
                                                                               'timeline' => '',
                                                                               'limit' => 5,
                                                                               'orderby' => 'created_at',
                                                                               'order' => 'desc',
                                                                               'fields' => 'title,start_balance,final_balance,winners,status,details',
                                                                               'empty'  => '',
                                                               ),
$atts,
'bhg_bonushunt_list'
);

                                               $limit   = max( 1, min( 100, (int) $atts['limit'] ) );
                                               $orderby = sanitize_key( (string) $atts['orderby'] );
                                               if ( ! in_array( $orderby, array( 'created_at', 'title', 'start_balance', 'final_balance', 'status' ), true ) ) {
                                                               $orderby = 'created_at';
                                               }
                                               $order = strtolower( sanitize_key( (string) $atts['order'] ) );
                                               if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
                                                               $order = 'desc';
                                               }

                                               $raw_fields = array_map( 'trim', explode( ',', (string) $atts['fields'] ) );
                                               $allowed    = array( 'title', 'start_balance', 'final_balance', 'winners', 'status', 'details' );
                                               $fields     = array();
                                               foreach ( $raw_fields as $field ) {
                                                               $key = sanitize_key( $field );
                                                               if ( in_array( $key, $allowed, true ) ) {
                                                                               $fields[] = $key;
                                                               }
                                               }
                                               if ( empty( $fields ) ) {
                                                               $fields = array( 'title', 'status', 'details' );
                                               }

                                               $show_title   = in_array( 'title', $fields, true );
                                               $show_start   = in_array( 'start_balance', $fields, true );
                                               $show_final   = in_array( 'final_balance', $fields, true );
                                               $show_winners = in_array( 'winners', $fields, true );
                                               $show_status  = in_array( 'status', $fields, true );
                                               $show_details = in_array( 'details', $fields, true );

                                               $status_filter = sanitize_key( (string) $atts['status'] );
                                               if ( ! in_array( $status_filter, array( 'open', 'closed', 'all' ), true ) ) {
                                                               $status_filter = 'open';
                                               }

                                               $timeline_key      = sanitize_key( (string) $atts['timeline'] );
                                               $allowed_timelines = array( 'all_time', 'today', 'this_week', 'this_month', 'this_quarter', 'this_year', 'last_year' );
                                               if ( ! in_array( $timeline_key, $allowed_timelines, true ) ) {
                                                               $timeline_key = 'all_time';
                                               }
                                               $timeline_map = array(
                                                               'all_time'     => 'all_time',
                                                               'today'        => 'day',
                                                               'this_week'    => 'week',
                                                               'this_month'   => 'month',
                                                               'this_quarter' => 'quarter',
                                                               'this_year'    => 'year',
                                                               'last_year'    => 'last_year',
                                               );
                                               $timeline = $timeline_map[ $timeline_key ];

                                               $table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
                                               if ( ! $table ) {
                                                               return '';
                                               }

                                               $where  = array();
                                               $params = array();

                                               if ( in_array( $status_filter, array( 'open', 'closed' ), true ) ) {
                                                               $where[]  = 'status = %s';
                                                               $params[] = $status_filter;
                                               }

                                               $range = $this->get_timeline_range( $timeline );
                                               if ( $range ) {
                                                               $where[]  = 'created_at BETWEEN %s AND %s';
                                                               $params[] = $range['start'];
                                                               $params[] = $range['end'];
                                               }

                                               $where_sql = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';

                                               $sql = 'SELECT id, title, starting_balance, final_balance, winners_count, status, guessing_enabled FROM ' . $table . $where_sql . ' ORDER BY ' . $orderby . ' ' . strtoupper( $order ) . ' LIMIT %d'; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                                               $query_args = array_merge( $params, array( $limit ) );
                                               $rows       = $wpdb->get_results( $wpdb->prepare( $sql, ...$query_args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

                                               if ( empty( $rows ) ) {
                                                               $empty_text = '' !== $atts['empty'] ? $atts['empty'] : bhg_t( 'notice_no_hunts_found', 'No hunts found.' );

                                                               return '<p class="bhg-bonushunt-list-empty">' . esc_html( $empty_text ) . '</p>';
                                               }

                                               $items = array();
                                               foreach ( $rows as $row ) {
                                                               $parts = array();

                                                               if ( $show_title ) {
                                                                               $title = isset( $row->title ) && '' !== (string) $row->title ? (string) $row->title : bhg_t( 'label_unnamed_hunt', 'Untitled hunt' );
                                                                               $parts[] = '<span class="bhg-hunt-title">' . esc_html( $title ) . '</span>';
                                                               }

                                                               if ( $show_start ) {
                                                                               $start_balance = isset( $row->starting_balance ) ? bhg_format_money( (float) $row->starting_balance ) : bhg_t( 'label_emdash', '—' );
                                                                               $parts[]        = '<span class="bhg-hunt-start">' . esc_html( $start_balance ) . '</span>';
                                                               }

                                                               if ( $show_final ) {
                                                                               $final_balance = isset( $row->final_balance ) && '' !== $row->final_balance ? bhg_format_money( (float) $row->final_balance ) : bhg_t( 'label_emdash', '—' );
                                                                               $parts[]        = '<span class="bhg-hunt-final">' . esc_html( $final_balance ) . '</span>';
                                                               }

                                                               if ( $show_winners ) {
                                                                               $winners_count = isset( $row->winners_count ) ? (int) $row->winners_count : 0;
                                                                               $parts[]       = '<span class="bhg-hunt-winners">' . esc_html( number_format_i18n( $winners_count ) ) . '</span>';
                                                               }

                                                               if ( $show_status ) {
                                                                               $status_key = isset( $row->status ) ? strtolower( (string) $row->status ) : 'unknown';
                                                                               $parts[]    = '<span class="bhg-hunt-status">' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</span>';
                                                               }

                                                               if ( $show_details ) {
                                                                               $details_markup = esc_html( bhg_t( 'label_emdash', '—' ) );
                                                                               $hunt_id        = isset( $row->id ) ? (int) $row->id : 0;
                                                                               if ( $hunt_id > 0 ) {
                                                                                               $status = isset( $row->status ) ? strtolower( (string) $row->status ) : 'open';
                                                                                               if ( 'closed' === $status && function_exists( 'bhg_get_hunt_results_url' ) ) {
                                                                                                               $results_url   = bhg_get_hunt_results_url( $hunt_id );
                                                                                                               $details_markup = '' !== $results_url ? '<a href="' . esc_url( $results_url ) . '">' . esc_html( bhg_t( 'link_show_results', 'Show Results' ) ) . '</a>' : $details_markup;
                                                                                               } elseif ( 'open' === $status && function_exists( 'bhg_get_guess_submission_url' ) ) {
                                                                                                               $guess_enabled = isset( $row->guessing_enabled ) ? (int) $row->guessing_enabled : 1;
                                                                                                               if ( $guess_enabled > 0 ) {
                                                                                                                               $guess_url      = bhg_get_guess_submission_url( $hunt_id );
                                                                                                                               $details_markup = '' !== $guess_url ? '<a href="' . esc_url( $guess_url ) . '">' . esc_html( bhg_t( 'link_guess_now', 'Guess Now' ) ) . '</a>' : $details_markup;
                                                                                                               }
                                                                                               }
                                                                               }
                                                                               $parts[] = '<span class="bhg-hunt-details">' . $details_markup . '</span>';
                                                               }

                                                               $parts = array_filter( $parts, static function ( $part ) {
                                                                               return '' !== $part;
                                                               } );

                                                               $items[] = '<li class="bhg-bonushunt-list-item">' . implode( ' <span class="bhg-separator">&mdash;</span> ', $parts ) . '</li>';
                                               }

                                               return '<ul class="bhg-bonushunt-list">' . implode( '', $items ) . '</ul>';
                               }

					/**
					 * Displays a list of hunts.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
				public function hunts_shortcode( $atts ) {
						$a = shortcode_atts(
                                                                array(
                                                                                'id'          => 0,
                                                                                'aff'         => 'no',
                                                                                'website'     => 0,
                                                                                'status'      => '',
                                                                                'timeline'    => '',
                                                                                'fields'      => 'title,start,final,status',
                                                                                'orderby'     => 'created',
                                                                                'order'       => 'DESC',
                                                                                'paged'       => 1,
                                                                                'search'      => '',
                                                                                'show_search' => 'yes',
                                                                ),
                                                                $atts,
                                                                'bhg_hunts'
                                                );

						$fields_raw    = explode( ',', (string) $a['fields'] );
						$allowed_field = array( 'title', 'start', 'final', 'winners', 'status', 'user', 'site' );
						$fields_arr    = array_values(
				array_unique(
					array_intersect(
						$allowed_field,
						array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) )
					)
				)
			);
                                               if ( empty( $fields_arr ) ) {
                                                                $fields_arr = array( 'title', 'start', 'final', 'status' );
                                               }

                                               $status_attr = sanitize_key( (string) $a['status'] );
                                               if ( ! in_array( $status_attr, array( 'open', 'closed' ), true ) ) {
                                                               $status_attr = '';
                                               }

                                               $status_request = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : '';
                                               $status_filter  = in_array( $status_request, array( 'open', 'closed' ), true ) ? $status_request : $status_attr;

                                               $allowed_timeline_ui = array( 'all_time', 'today', 'this_week', 'this_month', 'this_quarter', 'this_year', 'last_year' );
                                               $timeline_attr       = sanitize_key( (string) $a['timeline'] );
                                               $timeline_default    = in_array( $timeline_attr, $allowed_timeline_ui, true ) ? $timeline_attr : 'all_time';
                                               $timeline_request    = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : '';
                                               $timeline_ui_value   = in_array( $timeline_request, $allowed_timeline_ui, true ) ? $timeline_request : $timeline_default;

                                               $timeline_query_map = array(
                                                               'all_time'     => 'all_time',
                                                               'today'        => 'day',
                                                               'this_week'    => 'week',
                                                               'this_month'   => 'month',
                                                               'this_quarter' => 'quarter',
                                                               'this_year'    => 'year',
                                                               'last_year'    => 'last_year',
                                               );

                                               $timeline_query_value = $timeline_query_map[ $timeline_ui_value ];

						$need_site_field = in_array( 'site', $fields_arr, true );

						$paged           = isset( $_GET['bhg_paged'] ) ? max( 1, (int) wp_unslash( $_GET['bhg_paged'] ) ) : max( 1, (int) $a['paged'] );
                                                $search          = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
                                               $show_search_form = $this->attribute_to_bool( isset( $a['show_search'] ) ? $a['show_search'] : 'yes' );
                                                $limit_default   = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 30 ) : 30;
                                                $limit           = $limit_default;
						$offset          = ( $paged - 1 ) * $limit;
						$orderby_request = isset( $_GET['bhg_orderby'] ) ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
						$order_request   = isset( $_GET['bhg_order'] ) ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );

												global $wpdb;
												$h         = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
												$aff_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
			if ( ! $h || ! $aff_table ) {
				return '';
			}

			$where  = array();
			$params = array();

			$id = (int) $a['id'];
			if ( $id > 0 ) {
				$where[]  = 'h.id = %d';
				$params[] = $id;
			}

			if ( in_array( $status_filter, array( 'open', 'closed' ), true ) ) {
				$where[]  = 'h.status = %s';
				$params[] = $status_filter;
			}

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'h.affiliate_site_id = %d';
				$params[] = $website;
			}

											// Timeline handling.
                                                $timeline_for_range = ( 'all_time' === $timeline_query_value ) ? '' : $timeline_query_value;
                                                $range              = $this->get_timeline_range( $timeline_for_range );
						if ( $range ) {
								$where[]  = 'h.created_at BETWEEN %s AND %s';
								$params[] = $range['start'];
								$params[] = $range['end'];
						}

						if ( '' !== $search ) {
								$where[]  = 'h.title LIKE %s';
								$params[] = '%' . $wpdb->esc_like( $search ) . '%';
						}

						$direction_map = array(
								'asc'  => 'ASC',
								'desc' => 'DESC',
						);
						$direction_key = strtolower( $order_request );
						if ( ! isset( $direction_map[ $direction_key ] ) ) {
								$direction_key = strtolower( sanitize_key( $a['order'] ) );
						}
						if ( ! isset( $direction_map[ $direction_key ] ) ) {
								$direction_key = 'desc';
						}
						$direction = $direction_map[ $direction_key ];

						$orderby_map = array(
								'title'   => 'h.title',
								'start'   => 'h.starting_balance',
								'final'   => 'h.final_balance',
								'winners' => 'h.winners_count',
								'status'  => 'h.status',
								'created' => 'h.created_at',
						);
						$default_orderby = sanitize_key( $a['orderby'] );
						if ( '' === $default_orderby || ! isset( $orderby_map[ $default_orderby ] ) ) {
								$default_orderby = 'created';
						}
						if ( '' === $orderby_request ) {
								$orderby_request = $default_orderby;
						}
						$orderby_key = isset( $orderby_map[ $orderby_request ] ) ? $orderby_request : $default_orderby;
						$orderby     = $orderby_map[ $orderby_key ];
						$order_sql   = sprintf( ' ORDER BY %s %s', $orderby, $direction );

						$count_sql = "SELECT COUNT(*) FROM {$h} h";
						if ( $where ) {
								$count_sql .= ' WHERE ' . implode( ' AND ', $where );
						}
						$total = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ) : $wpdb->get_var( $count_sql ) );

						$select = "SELECT h.id, h.title, h.starting_balance, h.final_balance, h.winners_count, h.status, h.created_at, h.closed_at, h.guessing_enabled";
						$join   = '';
						if ( $need_site_field ) {
								$select .= ', a.name AS site_name';
								$join    = " LEFT JOIN {$aff_table} a ON a.id = h.affiliate_site_id";
						}
						$sql = $select . " FROM {$h} h" . $join;
						if ( $where ) {
								$sql .= ' WHERE ' . implode( ' AND ', $where );
						}
						$sql     .= $order_sql . ' LIMIT %d OFFSET %d';
						$params[] = $limit;
						$params[] = $offset;

						// db call ok; no-cache ok.
						$sql  = $wpdb->prepare( $sql, ...$params );
						$rows  = $wpdb->get_results( $sql );
						$pages = (int) ceil( $total / $limit );

						$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
						$base_url    = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
                                                if ( '' === $search ) {
                                                                $base_url = remove_query_arg( 'bhg_search', $base_url );
                                                }
                                                if ( 'all_time' === $timeline_ui_value ) {
                                                                $base_url = remove_query_arg( 'bhg_timeline', $base_url );
                                                }
                                                if ( '' === $status_filter ) {
                                                                $base_url = remove_query_arg( 'bhg_status', $base_url );
                                                }
                                                $toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search, $timeline_ui_value, $status_filter ) {
                                                                $dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
                                                                $args = array(
                                                                                'bhg_orderby' => $field,
                                                                                'bhg_order'   => $dir,
                                                                );
                                                                if ( '' !== $search ) {
                                                                                $args['bhg_search'] = $search;
                                                                }
                                                                if ( 'all_time' !== $timeline_ui_value ) {
                                                                                $args['bhg_timeline'] = $timeline_ui_value;
                                                                }
                                                                if ( '' !== $status_filter ) {
                                                                                $args['bhg_status'] = $status_filter;
                                                                }
                                                                return add_query_arg( $args, $base_url );
                                                };

						if ( ! $rows ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
						}

						$show_site = $need_site_field;

                                                wp_enqueue_style(
                                                                'bhg-shortcodes',
                                                                ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
                                                                array(),
                                                                defined( 'BHG_VERSION' ) ? BHG_VERSION : null
                                                );

                                                $header_class = static function ( $key ) use ( $orderby_key, $direction_key ) {
                                                                $classes = array( 'sortable' );
                                                                if ( $orderby_key === $key ) {
                                                                                $classes[] = ( 'desc' === strtolower( (string) $direction_key ) ) ? 'desc' : 'asc';
                                                                }

                                                                return implode( ' ', $classes );
                                                };

                                                $sort_icon_markup = static function ( $field, $label_text ) use ( $orderby_key, $direction_key ) {
                                                                $state       = '';
                                                                $direction   = strtolower( (string) $direction_key );
                                                                if ( $orderby_key === $field ) {
                                                                                $state = ( 'desc' === $direction ) ? 'desc' : 'asc';
                                                                }

                                                                $icon        = '↕';
                                                                $sr_template = bhg_t( 'sort_state_none', 'Sortable column — %s' );

                                                                if ( 'asc' === $state ) {
                                                                                $icon        = '↑';
                                                                                $sr_template = bhg_t( 'sort_state_ascending', 'Sorted ascending — %s' );
                                                                } elseif ( 'desc' === $state ) {
                                                                                $icon        = '↓';
                                                                                $sr_template = bhg_t( 'sort_state_descending', 'Sorted descending — %s' );
                                                                }

                                                                $sr_text = sprintf( $sr_template, $label_text );

                                                                return '<span class="bhg-sort-icon" aria-hidden="true">' . esc_html( $icon ) . '</span><span class="screen-reader-text">' . esc_html( $sr_text ) . '</span>';
                                                };

                                                ob_start();
                                                echo '<form method="get" class="bhg-search-form">';
                                                foreach ( $_GET as $raw_key => $v ) {
                                                                $key = sanitize_key( wp_unslash( $raw_key ) );
                                                                if ( in_array( $key, array( 'bhg_search', 'bhg_timeline', 'bhg_status' ), true ) ) {
                                                                                continue;
                                                                }
                                                                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
                                                }

                                               if ( ! $show_search_form ) {
                                                               if ( '' !== $search ) {
                                                                               echo '<input type="hidden" name="bhg_search" value="' . esc_attr( $search ) . '">';
                                                               }
                                                               if ( 'all_time' !== $timeline_ui_value ) {
                                                                               echo '<input type="hidden" name="bhg_timeline" value="' . esc_attr( $timeline_ui_value ) . '">';
                                                               }
                                                               if ( '' !== $status_filter ) {
                                                                               echo '<input type="hidden" name="bhg_status" value="' . esc_attr( $status_filter ) . '">';
                                                               }
                                               }

echo '<div class="bhg-search-controls">';

if ( $show_search_form ) {
echo '<div class="bhg-search-control bhg-search-control--text">';
echo '<label for="bhg_hunts_search" class="screen-reader-text">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</label>';
echo '<input type="text" id="bhg_hunts_search" name="bhg_search" value="' . esc_attr( $search ) . '">';
echo '</div>';
}

echo '<div class="bhg-search-control bhg-search-control--submit">';
echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
echo '</div>';
echo '</div>';

$timeline_options = array(
'all_time'     => bhg_t( 'label_all_time', 'Alltime' ),
'today'        => bhg_t( 'label_today', 'Today' ),
'this_week'    => bhg_t( 'label_this_week', 'This Week' ),
'this_month'   => bhg_t( 'label_this_month', 'This Month' ),
'this_quarter' => bhg_t( 'option_timeline_this_quarter', 'This Quarter' ),
'this_year'    => bhg_t( 'label_this_year', 'This Year' ),
'last_year'    => bhg_t( 'label_last_year', 'Last Year' ),
);

$status_options = array(
''       => bhg_t( 'option_status_all', 'All Statuses' ),
'open'   => bhg_t( 'status_open', 'Open' ),
'closed' => bhg_t( 'status_closed', 'Closed' ),
);

echo '<div class="bhg-filter-controls">';
echo '<label class="bhg-filter-label" for="bhg_hunts_timeline">' . esc_html( bhg_t( 'label_timeline', 'Timeline' ) ) . '</label>';
echo '<select name="bhg_timeline" id="bhg_hunts_timeline" class="bhg-filter-select">';
foreach ( $timeline_options as $value => $label ) {
echo '<option value="' . esc_attr( $value ) . '"' . selected( $timeline_ui_value, $value, false ) . '>' . esc_html( $label ) . '</option>';
}
echo '</select>';

echo '<label class="bhg-filter-label" for="bhg_hunts_status">' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . '</label>';
echo '<select name="bhg_status" id="bhg_hunts_status" class="bhg-filter-select">';
foreach ( $status_options as $value => $label ) {
$selected_status = ( '' === $value ) ? '' : $value;
echo '<option value="' . esc_attr( $value ) . '"' . selected( $status_filter, $selected_status, false ) . '>' . esc_html( $label ) . '</option>';
}
echo '</select>';
echo '</div>';

echo '</form>';

echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-hunts"><thead><tr>';
                                                $title_label   = bhg_t( 'sc_title', 'Title' );
                                                $start_label   = bhg_t( 'sc_start_balance', 'Start Balance' );
                                                $final_label   = bhg_t( 'sc_final_balance', 'Final Balance' );
                                                $winners_label = bhg_t( 'sc_winners', 'Winners' );
                                                $status_label  = bhg_t( 'sc_status', 'Status' );

                                                echo '<th class="' . esc_attr( $header_class( 'title' ) ) . '"><a href="' . esc_url( $toggle( 'title' ) ) . '">' . esc_html( $title_label ) . $sort_icon_markup( 'title', $title_label ) . '</a></th>';
                                                echo '<th class="' . esc_attr( $header_class( 'start' ) ) . '"><a href="' . esc_url( $toggle( 'start' ) ) . '">' . esc_html( $start_label ) . $sort_icon_markup( 'start', $start_label ) . '</a></th>';
                                                echo '<th class="' . esc_attr( $header_class( 'final' ) ) . '"><a href="' . esc_url( $toggle( 'final' ) ) . '">' . esc_html( $final_label ) . $sort_icon_markup( 'final', $final_label ) . '</a></th>';
                                                echo '<th class="' . esc_attr( $header_class( 'winners' ) ) . '"><a href="' . esc_url( $toggle( 'winners' ) ) . '">' . esc_html( $winners_label ) . $sort_icon_markup( 'winners', $winners_label ) . '</a></th>';
                                                echo '<th class="' . esc_attr( $header_class( 'status' ) ) . '"><a href="' . esc_url( $toggle( 'status' ) ) . '">' . esc_html( $status_label ) . $sort_icon_markup( 'status', $status_label ) . '</a></th>';
						echo '<th>' . esc_html( bhg_t( 'label_details', 'Details' ) ) . '</th>';
						if ( $show_site ) {
								echo '<th>' . esc_html( bhg_t( 'label_site', 'Site' ) ) . '</th>';
						}
						echo '</tr></thead><tbody>';

			foreach ( $rows as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->title ) . '</td>';
							echo '<td>' . esc_html( bhg_format_money( (float) $row->starting_balance ) ) . '</td>';
								echo '<td>' . ( isset( $row->final_balance ) ? esc_html( bhg_format_money( (float) $row->final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
								$winners_display = isset( $row->winners_count ) ? number_format_i18n( (int) $row->winners_count ) : bhg_t( 'label_emdash', '—' );
								echo '<td>' . esc_html( $winners_display ) . '</td>';
								$status_key = strtolower( (string) $row->status );
								echo '<td>' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</td>';
								$details_html = esc_html( bhg_t( 'label_emdash', '—' ) );
								if ( 'closed' === $status_key ) {
										$results_url = function_exists( 'bhg_get_hunt_results_url' ) ? bhg_get_hunt_results_url( (int) $row->id ) : '';
										if ( '' !== $results_url ) {
												$details_html = '<a class="bhg-hunt-link" href="' . esc_url( $results_url ) . '">' . esc_html( bhg_t( 'link_show_results', 'Show Results' ) ) . '</a>';
										}
								} elseif ( 'open' === $status_key ) {
										$guessing_enabled = isset( $row->guessing_enabled ) ? (int) $row->guessing_enabled : 1;
										if ( $guessing_enabled > 0 ) {
												$guess_url = function_exists( 'bhg_get_guess_submission_url' ) ? bhg_get_guess_submission_url( (int) $row->id ) : '';
												if ( '' !== $guess_url ) {
														$details_html = '<a class="bhg-hunt-link" href="' . esc_url( $guess_url ) . '">' . esc_html( bhg_t( 'link_guess_now', 'Guess Now' ) ) . '</a>';
												}
										} else {
												$details_html = esc_html( bhg_t( 'label_guessing_closed', 'Guessing Closed' ) );
										}
								}
								echo '<td>' . $details_html . '</td>';
								if ( $show_site ) {
										echo '<td>' . ( $row->site_name ? esc_html( $row->site_name ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
								}
				echo '</tr>';
			}
						echo '</tbody></table>';

                                                $pagination = paginate_links(
                                                                array(
                                                                                'base'     => add_query_arg( 'bhg_paged', '%#%', $base_url ),
                                                                                'format'   => '',
                                                                                'current'  => $paged,
                                                                                'total'    => max( 1, $pages ),
                                                                                'add_args' => array_filter(
                                                                                                array(
                                                                                                                'bhg_orderby' => $orderby_key,
                                                                                                                'bhg_order'   => $direction_key,
                                                                                                                'bhg_search'  => $search,
                                                                                                                'bhg_timeline'=> 'all_time' === $timeline_ui_value ? '' : $timeline_ui_value,
                                                                                                                'bhg_status'  => $status_filter,
                                                                                                )
                                                                                ),
                                                                )
                                                );
						if ( $pagination ) {
								echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
						}

						return ob_get_clean();
		}

					/**
					 * Displays overall wins leaderboards.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
public function leaderboards_shortcode( $atts ) {
if ( ! is_array( $atts ) ) {
$atts = array();
}

$atts = (array) $atts;
$leaderboard_defaults = array(
'fields'             => 'pos,user,wins,avg_hunt,avg_tournament,aff',
'ranking'            => 0,
'timeline'           => '',
'orderby'            => 'wins',
'order'              => 'DESC',
'search'             => '',
'tournament'         => '',
'bonushunt'          => '',
'website'            => '',
'aff'                => '',
'filters'            => 'timeline,tournament,affiliate_site,affiliate_status',
'per_page'           => 25,
'paged'              => 1,
'show_search'        => 'yes',
'show_prize_summary' => 'auto',
'show_prizes'        => 'yes',
);

                                               $a = shortcode_atts(
                                                               $leaderboard_defaults,
                                                               $atts,
                                                               'bhg_leaderboards'
                                               );

                                               $summary_preference = strtolower( trim( (string) $a['show_prize_summary'] ) );
                                               $show_prizes_attr   = isset( $a['show_prizes'] ) ? $a['show_prizes'] : 'yes';
                                               $show_prizes        = $this->attribute_to_bool( $show_prizes_attr, true );

                                               $raw_fields     = array_map( 'trim', explode( ',', (string) $a['fields'] ) );
						$allowed_fields = array( 'pos', 'user', 'wins', 'avg', 'avg_hunt', 'avg_tournament', 'aff', 'site', 'hunt', 'tournament' );
						$normalized     = array();
						foreach ( $raw_fields as $field ) {
								$key = sanitize_key( $field );
								if ( 'avg' === $key ) {
										$key = 'avg_hunt';
								}
								if ( in_array( $key, $allowed_fields, true ) ) {
										$normalized[] = $key;
								}
						}
                               $fields_arr = array_values( array_unique( $normalized ) );
                               if ( empty( $fields_arr ) ) {
                                               $fields_arr = array( 'pos', 'user', 'wins', 'avg_hunt', 'avg_tournament', 'aff' );
                               }

$default_filters    = self::LEADERBOARD_DEFAULT_FILTERS;
$enabled_filters    = $default_filters;
$filters_input      = ( is_array( $atts ) && array_key_exists( 'filters', $atts ) ) ? $atts['filters'] : null;
			$normalized_filters = $this->normalize_leaderboard_filters( $filters_input );
			if ( null !== $normalized_filters ) {
				$enabled_filters = $normalized_filters;
			}

			$filter_timeline_enabled   = in_array( 'timeline', $enabled_filters, true );
			$filter_tournament_enabled = in_array( 'tournament', $enabled_filters, true );
			$filter_site_enabled       = in_array( 'site', $enabled_filters, true );
			$filter_affiliate_enabled  = in_array( 'affiliate', $enabled_filters, true );

						global $wpdb;

                                                $search             = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
                                               $show_search_control = $this->attribute_to_bool( isset( $a['show_search'] ) ? $a['show_search'] : 'yes' );

						$attr_timeline = sanitize_key( $a['timeline'] );
						if ( '' === $attr_timeline ) {
								$attr_timeline = 'all_time';
						}

			$timeline_request = ( $filter_timeline_enabled && isset( $_GET['bhg_timeline'] ) ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : '';
			$timeline         = '' !== $timeline_request ? $timeline_request : $attr_timeline;

$allowed_timelines = array( 'all_time', 'today', 'this_week', 'this_month', 'this_quarter', 'this_year', 'last_year' );
if ( ! in_array( $timeline, $allowed_timelines, true ) ) {
$timeline = 'all_time';
}

						$timeline_filter = ( 'all_time' === $timeline ) ? '' : $timeline;

                                               $ranking_raw = trim( (string) $a['ranking'] );
                                               $ranking_limit = 0;
                                               if ( '' !== $ranking_raw ) {
                                                               if ( preg_match( '/^(?:\d+\s*-\s*)?(\d+)$/', $ranking_raw, $matches ) ) {
                                                                               $ranking_limit = (int) $matches[1];
                                                               } else {
                                                                               $ranking_limit = (int) $ranking_raw;
                                                               }
                                               }
                                               $ranking_limit = max( 0, $ranking_limit );

$paged = isset( $_GET['bhg_paged'] ) ? max( 1, absint( wp_unslash( $_GET['bhg_paged'] ) ) ) : (int) $a['paged'];
if ( is_array( $atts ) && array_key_exists( 'paged', $atts ) ) {
$paged_attr = (int) $atts['paged'];
if ( $paged_attr > 0 ) {
$paged = $paged_attr;
}
}
                                               $paged = max( 1, $paged );

$default_per_page = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 25 ) : 25;
$per_page         = $default_per_page;
if ( is_array( $atts ) && array_key_exists( 'per_page', $atts ) ) {
$per_page_attr = (int) $atts['per_page'];
if ( $per_page_attr > 0 ) {
$per_page = $per_page_attr;
}
}
                                               $per_page = (int) apply_filters( 'bhg_leaderboards_per_page', $per_page, $atts );
                                               if ( $per_page <= 0 ) {
                                                               $per_page = $default_per_page;
                                               }
                                               $offset = ( $paged - 1 ) * $per_page;

						$orderby_request = isset( $_GET['bhg_orderby'] ) ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
						$order_request   = isset( $_GET['bhg_order'] ) ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );

$shortcode_tournament = isset( $a['tournament'] ) ? $a['tournament'] : '';
$shortcode_bonushunt  = isset( $a['bonushunt'] ) ? $a['bonushunt'] : '';
$shortcode_site       = isset( $a['website'] ) ? $a['website'] : '';
$shortcode_aff        = isset( $a['aff'] ) ? $a['aff'] : '';

$raw_tournament = $shortcode_tournament;
if ( isset( $_GET['bhg_tournament'] ) ) {
$raw_tournament = wp_unslash( $_GET['bhg_tournament'] );
} elseif ( isset( $_GET['bhg_tournament_id'] ) ) {
$raw_tournament = wp_unslash( $_GET['bhg_tournament_id'] );
}
$raw_bonushunt = $shortcode_bonushunt;
if ( isset( $_GET['bhg_bonushunt'] ) ) {
$raw_bonushunt = wp_unslash( $_GET['bhg_bonushunt'] );
}
$raw_site = ( $filter_site_enabled && isset( $_GET['bhg_site'] ) ) ? wp_unslash( $_GET['bhg_site'] ) : $shortcode_site;
$raw_aff  = ( $filter_affiliate_enabled && isset( $_GET['bhg_aff'] ) ) ? wp_unslash( $_GET['bhg_aff'] ) : $shortcode_aff;

$tournament_id = max( 0, absint( $raw_tournament ) );
$bonushunt_id  = max( 0, absint( $raw_bonushunt ) );
$website_id    = max( 0, absint( $raw_site ) );

                                                $tournament_filter_active = $tournament_id > 0;

                                                $show_prize_summary = false;
                                                if ( in_array( $summary_preference, array( 'yes', 'true', '1' ), true ) ) {
                                                                $show_prize_summary = true;
                                                } elseif ( in_array( $summary_preference, array( 'auto', '' ), true ) ) {
                                                                $show_prize_summary = $tournament_filter_active;
                                                }

                                                $aff_filter = sanitize_key( (string) $raw_aff );
                                                if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
                                                                $aff_filter = 'yes';
                                                } elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
								$aff_filter = 'no';
						} else {
								$aff_filter = '';
						}

// Preload dropdown data for filters.
$tournaments                = array();
$sites                      = array();
$leaderboard_prizes_markup = '';
$selected_tournament_row    = null;
$selected_bonushunt_row     = null;

$tournaments_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
$sites_table       = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
$hunts_table       = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );

if ( $tournaments_table ) {
                                                                $sql = "SELECT id, title FROM {$tournaments_table} ORDER BY created_at DESC, id DESC";
                                                                // db call ok; limited columns.
                                                                $tournaments = $wpdb->get_results( $sql );

                                                                if ( $tournament_filter_active ) {
                                                                               $has_selected = false;
                                                                               foreach ( $tournaments as $tournament ) {
                                                                                               if ( (int) $tournament->id === $tournament_id ) {
                                                                                                               $has_selected               = true;
                                                                                                               $selected_tournament_row = $tournament;
                                                                                                               break;
                                                                                               }
                                                                               }
                                                                               if ( ! $has_selected ) {
                                                                                               $sql          = $wpdb->prepare( "SELECT id, title FROM {$tournaments_table} WHERE id = %d", $tournament_id );
                                                                                               $selected_row = $wpdb->get_row( $sql );
                                                                                               if ( $selected_row ) {
                                                                                                               $tournaments[]           = $selected_row;
                                                                                                               $selected_tournament_row = $selected_row;
                                                                                               }
                                                                               }
                                                                                 if ( $tournament_filter_active && $show_prizes && class_exists( 'BHG_Prizes' ) && method_exists( 'BHG_Prizes', 'get_prizes_by_ids' ) ) {
                                                                                               $tournament_meta = $wpdb->get_row(
                                                                                                               $wpdb->prepare(
                                                                                                                               "SELECT status, prizes, winners_count FROM {$tournaments_table} WHERE id = %d",
                                                                                                                               $tournament_id
                                                                                                               )
                                                                                               );
                                                                                               if ( $tournament_meta && ! empty( $tournament_meta->prizes ) ) {
                                                                                                               $status = strtolower( (string) $tournament_meta->status );
                                                                                                               if ( 'active' === $status ) {
                                                                                                                               $prize_maps = $this->normalize_prize_maps_from_storage( $tournament_meta->prizes, isset( $tournament_meta->winners_count ) ? (int) $tournament_meta->winners_count : 0 );
                                                                                                                               $prizes     = $this->load_prize_sets_from_maps( $prize_maps );

                                                                                                                               if ( ! empty( $prizes['regular'] ) || ! empty( $prizes['premium'] ) ) {
                                                                                                                                               $section_html = $this->render_prize_sets_tabs(
                                                                                                                                                       $prizes,
                                                                                                                                                       array(
                                                                                                                                                               'show_summary' => $show_prize_summary,
                                                                                                                                                       ),
                                                                                                                                                       array(),
                                                                                                                                                       'carousel',
                                                                                                                                                       'medium'
                                                                                                                                               );

                                                                                                                                               if ( '' !== $section_html ) {

                                                                                                                                                               $leaderboard_prizes_markup  = '<div class="bhg-tournament-prizes bhg-tournament-prizes--leaderboard">';

                                                                                                                                                               $leaderboard_prizes_markup .= wp_kses_post( $section_html );

                                                                                                                                                               $leaderboard_prizes_markup .= '</div>';

                                                                                                                                               }

                                                                                                                               }

                                                                                                               }
                                                                                               }
                                                                               }
                                                               }

if ( $hunts_table && $bonushunt_id > 0 ) {
                                                               $selected_bonushunt_row = $wpdb->get_row(
                                                                               $wpdb->prepare(
                                                                                               "SELECT id, title FROM {$hunts_table} WHERE id = %d",
                                                                                               $bonushunt_id
                                                                               )
                                                               );
                                               }

                                               if ( $sites_table ) {
                                                               $site_limits = array();
                                                               if ( '' !== $shortcode_site && '0' !== (string) $shortcode_site ) {
                                                                               $site_limits[] = absint( $shortcode_site );
                                                               }
                                                               if ( ! empty( $site_limits ) && $website_id > 0 && ! in_array( $website_id, $site_limits, true ) ) {
                                                                               $site_limits[] = $website_id;
                                                               }
                                                               $site_limits = array_values( array_unique( array_filter( $site_limits ) ) );

                                                               if ( ! empty( $site_limits ) ) {
                                                                               $placeholders = implode( ',', array_fill( 0, count( $site_limits ), '%d' ) );
                                                                               $sql          = "SELECT id, name FROM {$sites_table} WHERE id IN ({$placeholders}) ORDER BY name ASC";
                                                                               $sites        = $wpdb->get_results( $wpdb->prepare( $sql, ...$site_limits ) );
                                                               } else {
                                                                               $sql   = "SELECT id, name FROM {$sites_table} ORDER BY name ASC";
                                                                               $sites = $wpdb->get_results( $sql );
                                                               }

                                                               if ( $website_id > 0 ) {
                                                                               $has_selected_site = false;
                                                                               foreach ( $sites as $site ) {
                                                                                               if ( (int) $site->id === $website_id ) {
                                                                                                               $has_selected_site = true;
                                                                                                               break;
                                                                                               }
                                                                               }
                                                                               if ( ! $has_selected_site ) {
                                                                                               $sql           = $wpdb->prepare( "SELECT id, name FROM {$sites_table} WHERE id = %d", $website_id );
                                                                                               $selected_site = $wpdb->get_row( $sql );
                                                                                               if ( $selected_site ) {
                                                                                                               $sites[] = $selected_site;
                                                                                               }
                                                                               }
                                                               }
                                               }

						if ( '' === $orderby_request ) {
								$orderby_request = 'wins';
						}
						if ( 'avg' === $orderby_request ) {
								$orderby_request = 'avg_hunt';
						}
						$direction_key = strtolower( $order_request );
						if ( ! in_array( $direction_key, array( 'asc', 'desc' ), true ) ) {
								$direction_key = strtolower( sanitize_key( $a['order'] ) );
								if ( ! in_array( $direction_key, array( 'asc', 'desc' ), true ) ) {
										$direction_key = 'desc';
								}
						}

                                               $need_avg_hunt        = in_array( 'avg_hunt', $fields_arr, true );
                                               $need_avg_tournament  = in_array( 'avg_tournament', $fields_arr, true );
                                               $need_site            = in_array( 'site', $fields_arr, true );
                                               $need_tournament_name = in_array( 'tournament', $fields_arr, true );
                                               $need_hunt_name       = in_array( 'hunt', $fields_arr, true );
                                               $need_aff             = in_array( 'aff', $fields_arr, true );


$query_result = $this->run_leaderboard_query(
array(
'fields'               => $fields_arr,
'timeline'             => $timeline,
'search'               => $search,
'tournament_id'        => $tournament_id,
'hunt_id'              => $bonushunt_id,
'website_id'           => $website_id,
'aff_filter'           => $aff_filter,
                                                                               'ranking_limit'        => $ranking_limit,
                                                                               'paged'                => $paged,
                                                                               'per_page'             => $per_page,
                                                                               'orderby'              => $orderby_request,
                                                                               'order'                => $direction_key,
                                                                               'need_avg_hunt'        => $need_avg_hunt,
                                                                               'need_avg_tournament'  => $need_avg_tournament,
                                                                               'need_site'            => $need_site,
                                                                               'need_tournament_name' => $need_tournament_name,
                                                                               'need_hunt_name'       => $need_hunt_name,
                                                                               'need_aff'             => $need_aff,
                                                               )
                                               );

                                               if ( '' !== $query_result['error'] && empty( $query_result['rows'] ) ) {
                                                               return '<p>' . esc_html( $query_result['error'] ) . '</p>';
                                               }

                                               $rows          = isset( $query_result['rows'] ) ? $query_result['rows'] : array();
                                               $total         = isset( $query_result['total'] ) ? (int) $query_result['total'] : 0;
                                               $per_page      = isset( $query_result['per_page'] ) ? (int) $query_result['per_page'] : $per_page;
                                               $paged         = isset( $query_result['paged'] ) ? (int) $query_result['paged'] : $paged;
                                               $offset        = isset( $query_result['offset'] ) ? (int) $query_result['offset'] : $offset;
                                               $total_pages   = isset( $query_result['total_pages'] ) ? (int) $query_result['total_pages'] : 1;
                                               $orderby_key   = isset( $query_result['orderby_key'] ) ? sanitize_key( $query_result['orderby_key'] ) : $orderby_request;
                                               $direction_key = isset( $query_result['direction_key'] ) ? sanitize_key( $query_result['direction_key'] ) : $direction_key;

                                               $current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
                                               $base_url    = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
                                               if ( '' === $search ) {
                                                               $base_url = remove_query_arg( 'bhg_search', $base_url );
                                               }
$toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search, $tournament_id, $aff_filter, $website_id, $timeline, $bonushunt_id ) {
$dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
$args = array(
'bhg_orderby' => $field,
                                                                               'bhg_order'   => $dir,
                                                                               'bhg_paged'   => false,
                                                               );
                                                               if ( '' !== $search ) {
                                                                               $args['bhg_search'] = $search;
                                                               }
if ( $tournament_id > 0 ) {
$args['bhg_tournament'] = $tournament_id;
}

if ( $bonushunt_id > 0 ) {
$args['bhg_bonushunt'] = $bonushunt_id;
}
if ( '' !== $aff_filter ) {
$args['bhg_aff'] = $aff_filter;
}
                                                               if ( $website_id > 0 ) {
                                                                               $args['bhg_site'] = $website_id;
                                                               }
                                                               if ( '' !== $timeline ) {
                                                                               $args['bhg_timeline'] = $timeline;
                                                               }
                                                               return add_query_arg( $args, $base_url );
                                               };

                                               wp_enqueue_style(
                                                               'bhg-shortcodes',
                                                               ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
                                                               array(),
                                                               defined( 'BHG_VERSION' ) ? BHG_VERSION : null
                                               );

                                               $show_timeline_filter   = in_array( 'timeline', $enabled_filters, true );
                                               $show_tournament_filter = in_array( 'tournament', $enabled_filters, true ) && ! empty( $tournaments );
                                               $show_site_filter       = in_array( 'site', $enabled_filters, true ) && ! empty( $sites );
                                               $show_affiliate_filter  = in_array( 'affiliate', $enabled_filters, true );
                                               $has_filter_dropdowns   = $show_timeline_filter || $show_tournament_filter || $show_site_filter || $show_affiliate_filter;

                                                $sort_icon_markup = static function ( $field, $label_text ) use ( $orderby_key, $direction_key ) {
                                                                $state       = '';
                                                                $direction   = strtolower( (string) $direction_key );
                                                                if ( $orderby_key === $field ) {
                                                                                $state = ( 'desc' === $direction ) ? 'desc' : 'asc';
                                                                }

                                                                $icon        = '↕';
                                                                $sr_template = bhg_t( 'sort_state_none', 'Sortable column — %s' );

                                                                if ( 'asc' === $state ) {
                                                                                $icon        = '↑';
                                                                                $sr_template = bhg_t( 'sort_state_ascending', 'Sorted ascending — %s' );
                                                                } elseif ( 'desc' === $state ) {
                                                                                $icon        = '↓';
                                                                                $sr_template = bhg_t( 'sort_state_descending', 'Sorted descending — %s' );
                                                                }

                                                                $sr_text = sprintf( $sr_template, $label_text );

                                                                return '<span class="bhg-sort-icon" aria-hidden="true">' . esc_html( $icon ) . '</span><span class="screen-reader-text">' . esc_html( $sr_text ) . '</span>';
                                                };

                                                ob_start();
						echo '<form method="get" class="bhg-search-form">';
                                               foreach ( $_GET as $raw_key => $v ) {
                                                               $key = sanitize_key( wp_unslash( $raw_key ) );
                                                               if ( in_array( $key, array( 'bhg_search', 'bhg_tournament', 'bhg_tournament_id', 'bhg_site', 'bhg_aff', 'bhg_timeline' ), true ) ) {
                                                                               continue;
                                                               }
                                                               echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
                                               }

                                               if ( ! $show_search_control && '' !== $search ) {
                                                               echo '<input type="hidden" name="bhg_search" value="' . esc_attr( $search ) . '">';
                                               }

if ( $has_filter_dropdowns ) {
echo '<div class="bhg-filter-controls">';

if ( $show_timeline_filter ) {
$timeline_options = array(
'all_time'     => bhg_t( 'option_timeline_all_time', 'Alltime' ),
'today'        => bhg_t( 'option_timeline_today', 'Today' ),
'this_week'    => bhg_t( 'option_timeline_this_week', 'This Week' ),
'this_month'   => bhg_t( 'option_timeline_this_month', 'This Month' ),
'this_quarter' => bhg_t( 'option_timeline_this_quarter', 'This Quarter' ),
'this_year'    => bhg_t( 'option_timeline_this_year', 'This Year' ),
'last_year'    => bhg_t( 'option_timeline_last_year', 'Last Year' ),
);

echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_timeline', 'Timeline' ) );
echo '<select name="bhg_timeline" class="bhg-filter-select">';
foreach ( $timeline_options as $value => $label ) {
echo '<option value="' . esc_attr( $value ) . '"' . selected( $timeline, $value, false ) . '>' . esc_html( $label ) . '</option>';
}
echo '</select></label>';
}

                                                                if ( $show_tournament_filter ) {
                                                                                echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_tournament', 'Tournament' ) );
                                                                                echo '<select name="bhg_tournament" class="bhg-filter-select">';
                                                                                echo '<option value="">' . esc_html( bhg_t( 'option_all_tournaments', 'All tournaments' ) ) . '</option>';
                                                                                foreach ( $tournaments as $tournament ) {
                                                                                                echo '<option value="' . (int) $tournament->id . '"' . selected( $tournament_id, (int) $tournament->id, false ) . '>' . esc_html( $tournament->title ) . '</option>';
                                                                                }
                                                                                echo '</select></label>';
                                                                }

                                                                if ( $show_site_filter ) {
                                                                                echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_site', 'Affiliate Site' ) );
                                                                                echo '<select name="bhg_site" class="bhg-filter-select">';
                                                                                echo '<option value="">' . esc_html( bhg_t( 'option_all_sites', 'All affiliate sites' ) ) . '</option>';
                                                                                foreach ( $sites as $site ) {
                                                                                                echo '<option value="' . (int) $site->id . '"' . selected( $website_id, (int) $site->id, false ) . '>' . esc_html( $site->name ) . '</option>';
                                                                                }
                                                                                echo '</select></label>';
                                                                }

                                                                if ( $show_affiliate_filter ) {
                                                                                echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_affiliate', 'Affiliate Status' ) );
                                                                                echo '<select name="bhg_aff" class="bhg-filter-select">';
                                                                                echo '<option value="">' . esc_html( bhg_t( 'option_aff_all', 'All users' ) ) . '</option>';
                                                                                echo '<option value="yes"' . selected( $aff_filter, 'yes', false ) . '>' . esc_html( bhg_t( 'option_aff_only', 'Affiliates only' ) ) . '</option>';
                                                                                echo '<option value="no"' . selected( $aff_filter, 'no', false ) . '>' . esc_html( bhg_t( 'option_aff_none', 'Non-affiliates only' ) ) . '</option>';
                                                                                echo '</select></label>';
                                                                }

                                                                echo '</div>';
                                                }

                                               if ( $show_search_control ) {
                                                               echo '<div class="bhg-search-control">';
                                                               echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
                                                               echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
                                                               echo '</div>';
                                               }

                                                echo '</form>';

$heading_parts = array();
if ( $selected_tournament_row && ! empty( $selected_tournament_row->title ) ) {
$heading_parts[] = '<h2 class="bhg-leaderboard-heading bhg-leaderboard-heading--tournament">' . esc_html( $selected_tournament_row->title ) . '</h2>';
}
if ( $selected_bonushunt_row && ! empty( $selected_bonushunt_row->title ) ) {
$heading_parts[] = '<h2 class="bhg-leaderboard-heading bhg-leaderboard-heading--bonushunt">' . esc_html( $selected_bonushunt_row->title ) . '</h2>';
}
if ( ! empty( $heading_parts ) ) {
echo '<div class="bhg-leaderboard-headings">' . implode( '', $heading_parts ) . '</div>';
}

                                                if ( $tournament_filter_active && '' !== $leaderboard_prizes_markup ) {
                                                                echo $leaderboard_prizes_markup;
                                                }

                                                echo '<table class="bhg-leaderboard">';
                                                echo '<thead><tr>';
                                               foreach ( $fields_arr as $field ) {
                                                               if ( 'pos' === $field ) {
                                                                               $label = bhg_t( 'sc_position', 'Position' );
                                                                               echo '<th class="sortable"><a href="' . esc_url( $toggle( 'pos' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'pos', $label ) . '</a></th>';
                                                               } elseif ( 'user' === $field ) {
                                                                               $label = bhg_t( 'sc_user', 'Username' );
                                                                               echo '<th class="sortable"><a href="' . esc_url( $toggle( 'user' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'user', $label ) . '</a></th>';
                                                               } elseif ( 'wins' === $field ) {
                                                                                $label = bhg_t( 'label_times_won', 'Times Won' );
                                                                                echo '<th class="sortable"><a href="' . esc_url( $toggle( 'wins' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'wins', $label ) . '</a></th>';
                                                                } elseif ( 'avg_hunt' === $field ) {
                                                                                $label = bhg_t( 'sc_avg_rank', 'Avg Hunt Pos' );
                                                                                echo '<th class="sortable"><a href="' . esc_url( $toggle( 'avg_hunt' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'avg_hunt', $label ) . '</a></th>';
                                                                } elseif ( 'avg_tournament' === $field ) {
                                                                                $label = bhg_t( 'sc_avg_tournament_pos', 'Avg Tournament Pos' );
                                                                                echo '<th class="sortable"><a href="' . esc_url( $toggle( 'avg_tournament' ) ) . '">' . esc_html( $label ) . $sort_icon_markup( 'avg_tournament', $label ) . '</a></th>';
                                                               } elseif ( 'aff' === $field ) {
                                                                               echo '<th>' . esc_html( bhg_t( 'label_affiliate_status', 'Affiliate' ) ) . '</th>';
                                                               } elseif ( 'site' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_site', 'Site' ) ) . '</th>';
								} elseif ( 'hunt' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_hunt', 'Hunt' ) ) . '</th>';
								} elseif ( 'tournament' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
								}
                                                }
                                                echo '</tr></thead><tbody>';

                                                echo $this->render_leaderboard_rows( $rows, $fields_arr, $offset, $need_aff );
                                                echo '</tbody></table>';

                                                if ( $total_pages > 1 ) {
                                                                $add_args = array(
                                                                                'bhg_orderby' => $orderby_key,
                                                                                'bhg_order'   => $direction_key,
                                                                );
                                                                if ( '' !== $search ) {
                                                                                $add_args['bhg_search'] = $search;
                                                                }
                                                                if ( '' !== $timeline && 'all_time' !== $timeline ) {
                                                                                $add_args['bhg_timeline'] = $timeline;
                                                                }
if ( $tournament_id > 0 ) {
$add_args['bhg_tournament'] = $tournament_id;
}
if ( $bonushunt_id > 0 ) {
$add_args['bhg_bonushunt'] = $bonushunt_id;
}
if ( '' !== $aff_filter ) {
$add_args['bhg_aff'] = $aff_filter;
}
                                                                if ( $website_id > 0 ) {
                                                                                $add_args['bhg_site'] = $website_id;
                                                                }

                                                                $pagination = paginate_links(
                                                                                array(
                                                                                                'base'      => add_query_arg( 'bhg_paged', '%#%', $base_url ),
                                                                                                'format'    => '',
                                                                                                'current'   => $paged,
                                                                                                'total'     => max( 1, $total_pages ),
                                                                                                'add_args'  => $add_args,
                                                                                )
                                                                );

                                                if ( $pagination ) {
                                                                                echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
                                                }
                                                }

                                                return ob_get_clean();
                                }

                }

               /**
                * Render leaderboard rows for the main leaderboard shortcode.
                *
                * @param array $rows       Result rows.
                * @param array $fields_arr Ordered list of fields to render.
                * @param int   $offset     Pagination offset used for position numbering.
                * @param bool  $need_aff   Whether to render affiliate indicators.
                *
                * @return string HTML table rows.
                */
               private function render_leaderboard_rows( $rows, $fields_arr, $offset, $need_aff ) {
                               $pos = $offset + 1;

                               ob_start();

                               foreach ( $rows as $row ) {
                                               $aff = '';

                                               if ( $need_aff ) {
                                                               $site_id = isset( $row->site_id ) ? (int) $row->site_id : 0;
                                                               $aff     = bhg_render_affiliate_dot( (int) $row->user_id, $site_id );
                                               }

                                               /* translators: %d: user ID. */
                                               $user_label = $this->format_username_label( $row->user_login, (int) $row->user_id );

                                               echo '<tr>';
                                               foreach ( $fields_arr as $field ) {
                                                               if ( 'pos' === $field ) {
                                                                               echo '<td>' . (int) $pos . '</td>';
                                                               } elseif ( 'user' === $field ) {
                                                                               echo '<td>' . esc_html( $user_label ) . '</td>';
                                                               } elseif ( 'wins' === $field ) {
                                                                               echo '<td>' . (int) $row->total_wins . '</td>';
                                                               } elseif ( 'avg_hunt' === $field ) {
                                                                               echo '<td>' . ( isset( $row->avg_hunt_pos ) ? esc_html( number_format_i18n( (float) $row->avg_hunt_pos, 0 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
                                                               } elseif ( 'avg_tournament' === $field ) {
                                                                               echo '<td>' . ( isset( $row->avg_tournament_pos ) ? esc_html( number_format_i18n( (float) $row->avg_tournament_pos, 0 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
                                                               } elseif ( 'aff' === $field ) {
                                                                               echo '<td>' . wp_kses_post( $aff ) . '</td>';
                                                               } elseif ( 'site' === $field ) {
                                                                               $site_label = ! empty( $row->site_name ) ? $row->site_name : bhg_t( 'label_emdash', '—' );
                                                                               echo '<td>' . esc_html( $site_label ) . '</td>';
                                                               } elseif ( 'hunt' === $field ) {
                                                                               $hunt_label = ! empty( $row->hunt_title ) ? $row->hunt_title : bhg_t( 'label_emdash', '—' );
                                                                               echo '<td>' . esc_html( $hunt_label ) . '</td>';
                                                               } elseif ( 'tournament' === $field ) {
                                                                               $tournament_label = ! empty( $row->tournament_title ) ? $row->tournament_title : bhg_t( 'label_emdash', '—' );
                                                                               echo '<td>' . esc_html( $tournament_label ) . '</td>';
                                                               }
                                               }
                                               echo '</tr>';
                                               ++$pos;
                               }

                               return ob_get_clean();
               }

                                        /**
                                         * Lists tournaments or shows tournament details.
                                         *
                                         * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
                public function tournaments_shortcode( $atts ) {
                        global $wpdb;

                        wp_enqueue_style(
                                'bhg-shortcodes',
                                ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
                                array(),
                                defined( 'BHG_VERSION' ) ? BHG_VERSION : null
                        );

                        $show_prize_summary_attr    = isset( $atts['show_prize_summary'] ) ? $atts['show_prize_summary'] : 'yes';
                        $show_prize_summary_detail = $this->attribute_to_bool( $show_prize_summary_attr, true );
                        $show_prizes_attr           = isset( $atts['show_prizes'] ) ? $atts['show_prizes'] : 'yes';
                        $show_prizes                = $this->attribute_to_bool( $show_prizes_attr, true );

                        // Details screen.
                        $details_id = 0;
                        if ( isset( $atts['bhg_tournament_id'] ) ) {
                                $details_id = absint( $atts['bhg_tournament_id'] );
                        } elseif ( isset( $atts['tournament_id'] ) ) {
                                $details_id = absint( $atts['tournament_id'] );
                        }

                        if ( $details_id <= 0 && isset( $_GET['bhg_tournament_id'] ) ) {
                                $details_id = absint( wp_unslash( $_GET['bhg_tournament_id'] ) );
                        }
						if ( $details_id > 0 ) {
								$t = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
								$r = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
								$u = esc_sql( $this->sanitize_table( $wpdb->users ) );
								$s = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
								if ( ! $t || ! $r || ! $u || ! $s ) {
										return '';
								}

								$column_parts = array(
										't.id',
										't.title',
										't.description',
										't.start_date',
										't.end_date',
										't.status',
                                                                                't.prizes',
                                                                                't.winners_count',
										't.affiliate_site_id',
										't.affiliate_website',
										't.affiliate_url_visible',
										's.name AS affiliate_site_name',
										's.url AS affiliate_site_url',
								);
								$columns = implode( ', ', $column_parts );

								// db call ok; no-cache ok.
								$tournament = $wpdb->get_row(
										$wpdb->prepare(
												"SELECT {$columns} FROM {$t} t LEFT JOIN {$s} s ON s.id = t.affiliate_site_id WHERE t.id = %d",
												$details_id
										)
								);
								if ( ! $tournament ) {
										return '<p>' . esc_html( bhg_t( 'notice_tournament_not_found', 'Tournament not found.' ) ) . '</p>';
								}

                                                                $prize_maps = $this->normalize_prize_maps_from_storage( $tournament->prizes, isset( $tournament->winners_count ) ? (int) $tournament->winners_count : 0 );
                                                                $prizes     = $this->load_prize_sets_from_maps( $prize_maps );

                                                                $default_per_page = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 25 ) : 25;
                                                                $per_page         = $default_per_page;
                                                                if ( isset( $_GET['bhg_tr_per_page'] ) ) {
                                                                                $per_page_override = max( 1, absint( wp_unslash( $_GET['bhg_tr_per_page'] ) ) );
                                                                                if ( $per_page_override > 0 ) {
                                                                                                $per_page = $per_page_override;
                                                                                }
                                                                }
                                                                $per_page = (int) apply_filters( 'bhg_tournament_leaderboard_per_page', $per_page, $tournament );
                                                                if ( $per_page <= 0 ) {
                                                                                $per_page = $default_per_page;
                                                                }

$orderby        = isset( $_GET['orderby'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ) : 'position';
$allowed_orders = array( 'asc', 'desc' );
$order          = isset( $_GET['order'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'asc';

$allowed = array(
'position'    => 'position',
'wins'        => 'r.wins',
'username'    => 'u.user_login',
'last_win_at' => 'r.last_win_date',
);
if ( ! isset( $allowed[ $orderby ] ) ) {
$orderby = 'position';
}
if ( ! in_array( $order, $allowed_orders, true ) ) {
$order = 'asc';
}
$order = strtoupper( $order );

$current_page = isset( $_GET['bhg_tr_paged'] ) ? max( 1, absint( wp_unslash( $_GET['bhg_tr_paged'] ) ) ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

$total = (int) $wpdb->get_var(
        $wpdb->prepare(
                "SELECT COUNT(*) FROM {$r} WHERE tournament_id = %d",
                $tournament->id
        )
); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

if ( $total <= 0 ) {
        echo '<p>' . esc_html( bhg_t( 'notice_no_results_yet', 'No results yet.' ) ) . '</p>';
        echo '</div>';
        return ob_get_clean();
}

$total_pages = max( 1, (int) ceil( $total / $per_page ) );
if ( $current_page > $total_pages ) {
        $current_page = $total_pages;
        $offset       = ( $current_page - 1 ) * $per_page;
}

$order_parts = array();
switch ( $orderby ) {
case 'position':
$points_dir = ( 'ASC' === $order ) ? 'DESC' : 'ASC';
$wins_dir   = $points_dir;
$last_dir   = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
$order_parts[] = 'r.points ' . $points_dir;
$order_parts[] = 'r.wins ' . $wins_dir;
$order_parts[] = 'r.last_win_date ' . $last_dir;
break;
case 'wins':
$order_parts[] = 'r.wins ' . $order;
$order_parts[] = 'r.points ' . ( 'DESC' === $order ? 'DESC' : 'ASC' );
$order_parts[] = 'r.last_win_date ' . ( 'DESC' === $order ? 'ASC' : 'DESC' );
break;
case 'username':
$order_parts[] = 'u.user_login ' . $order;
$order_parts[] = 'r.points DESC';
$order_parts[] = 'r.wins DESC';
break;
case 'last_win_at':
default:
$order_parts[] = 'r.last_win_date ' . $order;
$order_parts[] = 'r.points DESC';
$order_parts[] = 'r.wins DESC';
break;
}
$order_parts[] = 'r.user_id ASC';
$order_sql     = implode( ', ', $order_parts );

$query = $wpdb->prepare(
"SELECT r.user_id, r.wins, r.points, r.last_win_date, u.user_login FROM {$r} r INNER JOIN {$u} u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY {$order_sql} LIMIT %d OFFSET %d",
$tournament->id,
$per_page,
$offset
);
$rows = $wpdb->get_results( $query );

$last_win_map = array();
if ( $rows ) {
$hw_table  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
$hunts_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
$rel_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments_hunts' ) );
if ( $hw_table && $hunts_tbl && $rel_table ) {
$user_ids = array();
foreach ( $rows as $row ) {
$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;
if ( $user_id > 0 ) {
$user_ids[ $user_id ] = $user_id;
}
}
if ( ! empty( $user_ids ) ) {
$placeholders = implode( ', ', array_fill( 0, count( $user_ids ), '%d' ) );
$last_win_sql = "SELECT hw.user_id, MAX(COALESCE(hw.created_at, h.closed_at, h.updated_at, h.created_at)) AS last_win_date FROM {$hw_table} hw INNER JOIN {$hunts_tbl} h ON h.id = hw.hunt_id LEFT JOIN {$rel_table} ht ON ht.hunt_id = h.id WHERE hw.user_id IN ({$placeholders}) AND hw.eligible = 1 AND (ht.tournament_id = %d OR (ht.tournament_id IS NULL AND h.tournament_id = %d)) GROUP BY hw.user_id"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table names sanitized above.
$last_win_args = array_merge( array_values( $user_ids ), array( $tournament->id, $tournament->id ) );
$win_rows      = $wpdb->get_results( $wpdb->prepare( $last_win_sql, ...$last_win_args ) );
if ( $win_rows ) {
foreach ( $win_rows as $win_row ) {
$uid = isset( $win_row->user_id ) ? (int) $win_row->user_id : 0;
if ( $uid > 0 && ! empty( $win_row->last_win_date ) ) {
$last_win_map[ $uid ] = $win_row->last_win_date;
}
}
}
}
}
}

$base = remove_query_arg( array( 'orderby', 'order', 'bhg_tr_paged' ) );
$toggle = function ( $key ) use ( $orderby, $order, $base ) {
$next = ( $orderby === $key && 'ASC' === strtoupper( (string) $order ) ) ? 'desc' : 'asc';

return add_query_arg(
array(
'orderby'      => $key,
'order'        => $next,
'bhg_tr_paged' => false,
),
$base
);
};

                                                                ob_start();
								echo '<div class="bhg-tournament-details">';
								echo '<p><a href="' . esc_url( remove_query_arg( 'bhg_tournament_id' ) ) . '">&larr; ' . esc_html( bhg_t( 'label_back_to_tournaments', 'Back to tournaments' ) ) . '</a></p>';
								$heading = $tournament->title ? $tournament->title : bhg_t( 'label_tournament', 'Tournament' );
								echo '<h3>' . esc_html( $heading ) . '</h3>';
                                                                echo '<p><strong>' . esc_html( bhg_t( 'sc_start', 'Start' ) ) . ':</strong> ' . esc_html( mysql2date( get_option( 'date_format' ), $tournament->start_date ) ) . ' &nbsp; ';
                                                                echo '<strong>' . esc_html( bhg_t( 'sc_end', 'End' ) ) . ':</strong> ' . esc_html( mysql2date( get_option( 'date_format' ), $tournament->end_date ) ) . ' &nbsp; ';
                                                                $status_key = strtolower( (string) $tournament->status );
                                                                echo '<strong>' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . ':</strong> ' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</p>';

                                                                $days_remaining = 0;
                                                                if ( 'active' === $status_key && ! empty( $tournament->end_date ) ) {
                                                                                try {
                                                                                                $tz            = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
                                                                                                $now           = new DateTimeImmutable( 'now', $tz );
                                                                                                $end_date_obj  = new DateTimeImmutable( $tournament->end_date, $tz );
                                                                                                $end_of_period = $end_date_obj->setTime( 23, 59, 59 );
                                                                                                $seconds_left  = $end_of_period->getTimestamp() - $now->getTimestamp();
                                                                                                if ( $seconds_left >= 0 ) {
                                                                                                                $days_remaining = (int) ceil( $seconds_left / DAY_IN_SECONDS );
                                                                                                }
                                                                                } catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
                                                                                                // Gracefully ignore parsing issues.
                                                                                }
                                                                }

                                                                if ( $days_remaining > 0 ) {
                                                                                $days_label = 1 === $days_remaining
                                                                                                ? bhg_t( 'tournament_closes_in_one_day', 'This tournament will close in 1 day.' )
                                                                                                : sprintf(
                                                                                                                bhg_t( 'tournament_closes_in_days', 'This tournament will close in %s days.' ),
                                                                                                                number_format_i18n( $days_remaining )
                                                                                                );
                                                                                echo '<div class="bhg-tournament-countdown">' . esc_html( $days_label ) . '</div>';
                                                                }

                                                                if ( ! empty( $tournament->description ) ) {
                                                                                echo '<div class="bhg-tournament-description">' . wp_kses_post( wpautop( $tournament->description ) ) . '</div>';
								}

								$affiliate_name = isset( $tournament->affiliate_site_name ) ? (string) $tournament->affiliate_site_name : '';
								$affiliate_url  = isset( $tournament->affiliate_website ) ? (string) $tournament->affiliate_website : '';
								if ( '' === $affiliate_url && isset( $tournament->affiliate_site_url ) ) {
										$affiliate_url = (string) $tournament->affiliate_site_url;
								}
								$show_affiliate_url = isset( $tournament->affiliate_url_visible ) ? (int) $tournament->affiliate_url_visible : 0;

								if ( '' !== $affiliate_name || '' !== $affiliate_url ) {
										echo '<p class="bhg-tournament-affiliate"><strong>' . esc_html( bhg_t( 'label_affiliate', 'Affiliate' ) ) . ':</strong> ';
										if ( $show_affiliate_url && '' !== $affiliate_url ) {
												$link_text = '' !== $affiliate_name ? $affiliate_name : $affiliate_url;
												echo '<a href="' . esc_url( $affiliate_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link_text ) . '</a>';
												if ( '' !== $affiliate_name && $link_text !== $affiliate_name ) {
														echo ' (' . esc_html( $affiliate_name ) . ')';
												}
										} elseif ( '' !== $affiliate_name ) {
												echo esc_html( $affiliate_name );
										} else {
												echo esc_html( $affiliate_url );
										}
										echo '</p>';
								}

                                if ( $show_prizes ) {
                                                $prize_markup = $this->render_prize_sets_tabs( $prizes, array( 'show_summary' => $show_prize_summary_detail ), array(), 'carousel', 'medium' );
                                                if ( '' !== $prize_markup ) {
                                                                echo '<div class="bhg-tournament-prizes">' . wp_kses_post( $prize_markup ) . '</div>';
                                                }
                                }

$default_rows = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 25 ) : 25;
$per_page     = (int) apply_filters( 'bhg_tournament_results_per_page', $default_rows, $tournament );
if ( $per_page <= 0 ) {
$per_page = $default_rows;
}
$per_page = max( 1, $per_page );

$current_page = isset( $_GET['bhg_tr_paged'] ) ? max( 1, absint( wp_unslash( $_GET['bhg_tr_paged'] ) ) ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

								if ( ! $rows ) {
										echo '<p>' . esc_html( bhg_t( 'notice_no_results_yet', 'No results yet.' ) ) . '</p>';
										echo '</div>';
										return ob_get_clean();
								}

                                                                $sort_icon_markup = static function ( $field, $label_text ) use ( $orderby, $order ) {
                                                                                $state = '';
                                                                                if ( $orderby === $field ) {
                                                                                                $state = ( 'DESC' === strtoupper( (string) $order ) ) ? 'desc' : 'asc';
                                                                                }

                                                                                $icon        = '↕';
                                                                                $sr_template = bhg_t( 'sort_state_none', 'Sortable column — %s' );

                                                                                if ( 'asc' === $state ) {
                                                                                                $icon        = '↑';
                                                                                                $sr_template = bhg_t( 'sort_state_ascending', 'Sorted ascending — %s' );
                                                                                } elseif ( 'desc' === $state ) {
                                                                                                $icon        = '↓';
                                                                                                $sr_template = bhg_t( 'sort_state_descending', 'Sorted descending — %s' );
                                                                                }

                                                                                $sr_text = sprintf( $sr_template, $label_text );

                                                                                return '<span class="bhg-sort-icon" aria-hidden="true">' . esc_html( $icon ) . '</span><span class="screen-reader-text">' . esc_html( $sr_text ) . '</span>';
                                                                };

echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-leaderboard bhg-leaderboard--tournament">';
echo '<thead><tr>';
$position_label = bhg_t( 'label_position', 'Position' );
$username_label = bhg_t( 'label_username', 'Username' );
$wins_label     = bhg_t( 'sc_wins', 'Times Won' );
$last_win_label = bhg_t( 'label_last_win', 'Last win' );
echo '<th scope="col" class="sortable"><a href="' . esc_url( $toggle( 'position' ) ) . '">' . esc_html( $position_label ) . $sort_icon_markup( 'position', $position_label ) . '</a></th>';
echo '<th scope="col" class="sortable"><a href="' . esc_url( $toggle( 'username' ) ) . '">' . esc_html( $username_label ) . $sort_icon_markup( 'username', $username_label ) . '</a></th>';
echo '<th scope="col" class="sortable"><a href="' . esc_url( $toggle( 'wins' ) ) . '">' . esc_html( $wins_label ) . $sort_icon_markup( 'wins', $wins_label ) . '</a></th>';
echo '<th scope="col" class="sortable"><a href="' . esc_url( $toggle( 'last_win_at' ) ) . '">' . esc_html( $last_win_label ) . $sort_icon_markup( 'last_win_at', $last_win_label ) . '</a></th>';
echo '</tr></thead><tbody>';

                                               foreach ( $rows as $index => $row ) {
                                                               $position_number = $offset + $index + 1;
                                                               $row_classes     = array( 'bhg-tournament-row' );
                                                               if ( isset( $row->wins ) && (int) $row->wins > 0 ) {
                                                                               $row_classes[] = 'bhg-tournament-row--winner';
                                                               }
                                                               if ( $position_number <= 3 ) {
                                                                               $row_classes[] = 'bhg-tournament-row--top-three';
                                                               }
                                                               if ( 1 === $position_number ) {
                                                                               $row_classes[] = 'bhg-tournament-row--first';
                                                               }
                                                               $class_attr = ' class="' . esc_attr( implode( ' ', $row_classes ) ) . '"';

                                                               $user_label = $this->format_username_label(
                                                                               $row->user_login,
                                                                               (int) $row->user_id
                                                               );

$resolved_last_win = '';
if ( isset( $row->user_id ) && isset( $last_win_map[ (int) $row->user_id ] ) ) {
$resolved_last_win = $last_win_map[ (int) $row->user_id ];
} elseif ( ! empty( $row->last_win_date ) ) {
$resolved_last_win = $row->last_win_date;
}

echo '<tr' . $class_attr . '>';
echo '<td data-label="' . esc_attr( $position_label ) . '">' . (int) $position_number . '</td>';
echo '<td data-label="' . esc_attr( $username_label ) . '">' . esc_html( $user_label ) . '</td>';
echo '<td data-label="' . esc_attr( $wins_label ) . '">' . (int) $row->wins . '</td>';
echo '<td data-label="' . esc_attr( $last_win_label ) . '">';
echo $resolved_last_win ? esc_html( mysql2date( get_option( 'date_format' ), $resolved_last_win ) ) : esc_html( bhg_t( 'label_emdash', '—' ) );
echo '</td>';
echo '</tr>';
}
echo '</tbody></table>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

 $total = isset( $total ) ? (int) $total : 0;

 $total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
if ( $total_pages > 1 ) {
$preserved_args = array();
if ( ! empty( $_GET ) ) {
foreach ( $_GET as $raw_key => $value ) {
$key = sanitize_key( wp_unslash( $raw_key ) );
if ( in_array( $key, array( 'orderby', 'order', 'bhg_tr_paged' ), true ) ) {
continue;
}
if ( is_array( $value ) ) {
continue;
}

$preserved_args[ $key ] = sanitize_text_field( wp_unslash( $value ) );
}
}

$pagination_args = array_merge(
$preserved_args,
array(
'orderby' => $orderby,
'order'   => strtolower( $order ),
)
);

$pagination_links = paginate_links(
array(
'base'      => esc_url_raw( add_query_arg( array( 'bhg_tr_paged' => '%#%' ), $base ) ),
'format'    => '',
'current'   => $current_page,
'total'     => $total_pages,
'type'      => 'array',
'add_args'  => $pagination_args,
'prev_text' => esc_html__( '&laquo;', 'bonus-hunt-guesser' ),
'next_text' => esc_html__( '&raquo;', 'bonus-hunt-guesser' ),
)
);

if ( ! empty( $pagination_links ) ) {
echo '<nav class="bhg-pagination" aria-label="' . esc_attr( bhg_t( 'label_pagination', 'Pagination' ) ) . '">';
echo '<ul class="bhg-pagination-list">';
foreach ( $pagination_links as $link ) {
$class = false !== strpos( $link, 'current' ) ? ' class="bhg-current-page"' : '';
echo '<li' . $class . '>' . wp_kses_post( $link ) . '</li>';
}
echo '</ul>';
echo '</nav>';
}
}
echo '</div>';

return ob_get_clean();
						}

						// List view with filters.
                                           $a = shortcode_atts(
                                                           array(
                                                                           'status'      => 'active',
                                                                           'tournament'  => 0,
                                                                           'website'     => 0,
                                                                           'timeline'    => '',
                                                                           'paged'       => 1,
                                                                           'orderby'     => 'start_date',
                                                                           'order'       => 'desc',
                                                                           'search'      => '',
                                                                           'show_search' => 'yes',
                                                                           'show_prize_summary' => $show_prize_summary_attr,
                                                           ),
                                                           $atts,
                                                           'bhg_tournaments'
                                           );

											   $t = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
					   if ( ! $t ) {
									   return '';
					   }
					   $where  = array();
					   $params = array();

					   $status     = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : sanitize_key( $a['status'] );
                                           $timeline   = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : sanitize_key( $a['timeline'] );
                                           $tournament = absint( $a['tournament'] );
                                           $website    = absint( $a['website'] );
                                           $paged      = isset( $_GET['bhg_paged'] ) ? max( 1, (int) wp_unslash( $_GET['bhg_paged'] ) ) : max( 1, (int) $a['paged'] );
                                           $search     = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
                                           $show_search_control = $this->attribute_to_bool( isset( $a['show_search'] ) ? $a['show_search'] : 'yes' );
                                           $limit_default = function_exists( 'bhg_get_shortcode_rows_per_page' ) ? bhg_get_shortcode_rows_per_page( 30 ) : 30;
                                           $limit      = $limit_default;
                                           $offset     = ( $paged - 1 ) * $limit;

					   $orderby_param = isset( $_GET['bhg_orderby'] ) ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
					   $order_param   = isset( $_GET['bhg_order'] ) ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );
					   $allowed_orderby = array(
							   'title'      => 'title',
							   'start_date' => 'start_date',
							   'end_date'   => 'end_date',
							   'status'     => 'status',
					   );
					   $orderby_column = isset( $allowed_orderby[ $orderby_param ] ) ? $allowed_orderby[ $orderby_param ] : 'start_date';
					   $order_param    = in_array( strtolower( $order_param ), array( 'asc', 'desc' ), true ) ? strtoupper( $order_param ) : 'DESC';

					   if ( $tournament > 0 ) {
							   $where[]  = 'id = %d';
							   $params[] = $tournament;
					   }
					   if ( in_array( $status, array( 'active', 'closed' ), true ) ) {
							   $where[]  = 'status = %s';
							   $params[] = $status;
					   }

                                           $timeline_ui        = $timeline;
                                           $timeline_map       = array(
                                                           'all_time'     => 'all_time',
                                                           'today'        => 'day',
                                                           'this_week'    => 'week',
                                                           'this_month'   => 'month',
                                                           'this_quarter' => 'quarter',
                                                           'this_year'    => 'year',
                                                           'last_year'    => 'last_year',
                                           );
                                           $allowed_timelines = array_keys( $timeline_map );
                                           if ( ! in_array( $timeline_ui, $allowed_timelines, true ) ) {
                                                           $timeline_ui = 'all_time';
                                           }
                                           $timeline = $timeline_map[ $timeline_ui ];

                                           // Accept explicit time window or derive range from timeline keyword.
                                           $range = $this->get_timeline_range( $timeline );
                                           if ( $range ) {
                                                           $range_start = substr( $range['start'], 0, 10 );
							   $range_end   = substr( $range['end'], 0, 10 );
							   $where[]     = '( (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s) )';
							   $params[]    = $range_end;
							   $params[]    = $range_start;
					   }

					   if ( $website > 0 ) {
							   $where[]  = 'affiliate_site_id = %d';
							   $params[] = $website;
					   }

					   if ( '' !== $search ) {
							   $where[]  = 'title LIKE %s';
							   $params[] = '%' . $wpdb->esc_like( $search ) . '%';
					   }

					   $where_sql = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';

					   $count_sql = "SELECT COUNT(*) FROM {$t}{$where_sql}";
					   $total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ) : $wpdb->get_var( $count_sql ) );

					   $sql         = 'SELECT * FROM ' . $t . $where_sql . ' ORDER BY ' . $orderby_column . ' ' . $order_param . ' LIMIT %d OFFSET %d'; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Order by clause sanitized via whitelist.
					   $query_args  = array_merge( $params, array( $limit, $offset ) );
					   $rows        = $wpdb->get_results( $wpdb->prepare( $sql, ...$query_args ) ); // db call ok; no-cache ok.
					   if ( ! $rows ) {
							   return '<p>' . esc_html( bhg_t( 'notice_no_tournaments_found', 'No tournaments found.' ) ) . '</p>';
					   }

					   $current_url = isset( $_SERVER['REQUEST_URI'] )
					   ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) )
					   : home_url( '/' );

					   $base_url = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
					   if ( '' === $search ) {
							   $base_url = remove_query_arg( 'bhg_search', $base_url );
					   }

                                           ob_start();
                                           echo '<form method="get" class="bhg-tournament-filters">';
											   // Keep other query args.
                                           foreach ( $_GET as $raw_key => $v ) {
                                                           $key = sanitize_key( wp_unslash( $raw_key ) );
                                                           if ( in_array( $key, array( 'bhg_timeline', 'bhg_status', 'bhg_tournament_id', 'bhg_search', 'bhg_paged' ), true ) ) {
                                                                           continue;
                                                           }
                                                           echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
                                           }

                                           if ( ! $show_search_control && '' !== $search ) {
                                                           echo '<input type="hidden" name="bhg_search" value="' . esc_attr( $search ) . '">';
                                           }

                                           echo '<label class="bhg-tournament-label">' . esc_html( bhg_t( 'label_timeline_colon', 'Timeline:' ) ) . ' ';
                                           echo '<select name="bhg_timeline">';
                                               $timeline_options = array(
                                                               'all_time'     => bhg_t( 'label_all_time', 'Alltime' ),
                                                               'today'        => bhg_t( 'label_today', 'Today' ),
                                                               'this_week'    => bhg_t( 'label_this_week', 'This Week' ),
                                                               'this_month'   => bhg_t( 'label_this_month', 'This Month' ),
                                                               'this_quarter' => bhg_t( 'option_timeline_this_quarter', 'This Quarter' ),
                                                               'this_year'    => bhg_t( 'label_this_year', 'This Year' ),
                                                               'last_year'    => bhg_t( 'label_last_year', 'Last Year' ),
                                               );
                        $timeline_key = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : $timeline_ui;
                        if ( ! array_key_exists( $timeline_key, $timeline_options ) ) {
                                $timeline_key = 'all_time';
                        }
                        foreach ( $timeline_options as $key => $label ) {
                                echo '<option value="' . esc_attr( $key ) . '"' . selected( $timeline_key, $key, false ) . '>' . esc_html( $label ) . '</option>';
                        }
                        echo '</select></label>';

			echo '<label>' . esc_html( bhg_t( 'status', 'Status:' ) ) . ' ';
			echo '<select name="bhg_status">';
			$statuses   = array(
				'active' => bhg_t( 'label_active', 'Active' ),
				'closed' => bhg_t( 'label_closed', 'Closed' ),
				'all'    => bhg_t( 'label_all', 'All' ),
			);
			$status_key = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : $status;
			foreach ( $statuses as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $status_key, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
					   echo '</select></label> ';

                                           if ( $show_search_control ) {
                                                           echo '<label>' . esc_html( bhg_t( 'label_search', 'Search' ) ) . ' <input type="text" name="bhg_search" value="' . esc_attr( $search ) . '"></label> ';
                                           }

					   echo '<button class="button bhg-filter-button" type="submit">' . esc_html( bhg_t( 'button_filter', 'Filter' ) ) . '</button>';
					   echo '</form>';

					   $toggle = function ( $key ) use ( $orderby_param, $order_param, $base_url, $search ) {
							   $next = ( $orderby_param === $key && 'ASC' === $order_param ) ? 'desc' : 'asc';
							   $args = array(
									   'bhg_orderby' => $key,
									   'bhg_order'   => $next,
							   );
							   if ( '' !== $search ) {
									   $args['bhg_search'] = $search;
							   }
							   return add_query_arg( $args, $base_url );
					   };

                        $header_class = static function ( $key ) use ( $orderby_param, $order_param ) {
                                        $classes = array( 'sortable' );
                                        if ( $orderby_param === $key ) {
                                                        $classes[] = ( 'DESC' === strtoupper( (string) $order_param ) ) ? 'desc' : 'asc';
                                        }

                                        return implode( ' ', $classes );
                        };

                        $sort_icon_markup = static function ( $field, $label_text ) use ( $orderby_param, $order_param ) {
                                        $state = '';
                                        if ( $orderby_param === $field ) {
                                                        $state = ( 'DESC' === strtoupper( (string) $order_param ) ) ? 'desc' : 'asc';
                                        }

                                        $icon        = '↕';
                                        $sr_template = bhg_t( 'sort_state_none', 'Sortable column — %s' );

                                        if ( 'asc' === $state ) {
                                                        $icon        = '↑';
                                                        $sr_template = bhg_t( 'sort_state_ascending', 'Sorted ascending — %s' );
                                        } elseif ( 'desc' === $state ) {
                                                        $icon        = '↓';
                                                        $sr_template = bhg_t( 'sort_state_descending', 'Sorted descending — %s' );
                                        }

                                        $sr_text = sprintf( $sr_template, $label_text );

                                        return '<span class="bhg-sort-icon" aria-hidden="true">' . esc_html( $icon ) . '</span><span class="screen-reader-text">' . esc_html( $sr_text ) . '</span>';
                        };

echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-tournaments">';
                        echo '<thead><tr>';
                        $name_label   = bhg_t( 'label_name', 'Name' );
                        $start_label  = bhg_t( 'sc_start', 'Start' );
                        $end_label    = bhg_t( 'sc_end', 'End' );
                        $status_label = bhg_t( 'sc_status', 'Status' );
                        echo '<th class="' . esc_attr( $header_class( 'title' ) ) . '"><a href="' . esc_url( $toggle( 'title' ) ) . '">' . esc_html( $name_label ) . $sort_icon_markup( 'title', $name_label ) . '</a></th>';
                        echo '<th class="' . esc_attr( $header_class( 'start_date' ) ) . '"><a href="' . esc_url( $toggle( 'start_date' ) ) . '">' . esc_html( $start_label ) . $sort_icon_markup( 'start_date', $start_label ) . '</a></th>';
                        echo '<th class="' . esc_attr( $header_class( 'end_date' ) ) . '"><a href="' . esc_url( $toggle( 'end_date' ) ) . '">' . esc_html( $end_label ) . $sort_icon_markup( 'end_date', $end_label ) . '</a></th>';
                        echo '<th class="' . esc_attr( $header_class( 'status' ) ) . '"><a href="' . esc_url( $toggle( 'status' ) ) . '">' . esc_html( $status_label ) . $sort_icon_markup( 'status', $status_label ) . '</a></th>';
			echo '<th>' . esc_html( bhg_t( 'label_details', 'Details' ) ) . '</th>';
			echo '</tr></thead><tbody>';

			foreach ( $rows as $row ) {
				$detail_url = add_query_arg( 'bhg_tournament_id', (int) $row->id, remove_query_arg( array( 'orderby', 'order' ), $current_url ) );
				echo '<tr>';
				echo '<td data-label="' . esc_attr( bhg_t( 'label_name', 'Name' ) ) . '"><a href="' . esc_url( $detail_url ) . '">' . esc_html( $row->title ? $row->title : bhg_t( 'label_unnamed_tournament', 'Untitled tournament' ) ) . '</a></td>';
				echo '<td data-label="' . esc_attr( bhg_t( 'sc_start', 'Start' ) ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $row->start_date ) ) . '</td>';
				echo '<td data-label="' . esc_attr( bhg_t( 'sc_end', 'End' ) ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $row->end_date ) ) . '</td>';
				$status_key = strtolower( (string) $row->status );
				echo '<td data-label="' . esc_attr( bhg_t( 'sc_status', 'Status' ) ) . '">' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</td>';
				echo '<td data-label="' . esc_attr( bhg_t( 'label_details', 'Details' ) ) . '"><a href="' . esc_url( $detail_url ) . '">' . esc_html( bhg_t( 'label_show_details', 'Show details' ) ) . '</a></td>';
				echo '</tr>';
			}

					   echo '</tbody></table>';

					   $pages = (int) ceil( $total / $limit );
					   if ( $pages > 1 ) {
							   $pagination = paginate_links(
									   array(
											   'base'      => add_query_arg( 'bhg_paged', '%#%', $base_url ),
											   'format'    => '',
											   'current'   => $paged,
											   'total'     => $pages,
											   'add_args'  => array_filter(
													   array(
															   'bhg_orderby' => $orderby_param,
															   'bhg_order'   => strtolower( $order_param ),
															   'bhg_search'  => $search,
													   )
											   ),
									   )
							   );
							   if ( $pagination ) {
									   echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
							   }
					   }

					   return ob_get_clean();
			   }

				/**
				 * Render standalone prizes shortcode.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
				public function prizes_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Prizes' ) ) {
								return '';
						}

$atts = shortcode_atts(
array(
'category'         => '',
'design'           => 'grid',
'size'             => 'medium',
'active'           => 'yes',
'visible'          => '',
'limit'            => '',
'total'            => '',
'autoplay'         => '',
'interval'         => '',
'hide_heading'     => '',
'heading'          => '',
'heading_text'     => '',
'show_summary'     => 'no',
'summary_title'    => '',
'show_title'       => '',
'show_description' => '',
'show_category'    => '',
'show_image'       => '',
'category_links'   => '',
'click_action'     => '',
'link_target'      => '',
'category_target'  => '',
),
$atts,
'bhg_prizes'
);

						$args = array();

						$category = isset( $atts['category'] ) ? sanitize_key( $atts['category'] ) : '';
                                                if ( $category && BHG_Prizes::category_exists( $category ) ) {
                                                                $args['category'] = $category;
                                                }

						$active = isset( $atts['active'] ) ? strtolower( (string) $atts['active'] ) : 'yes';
						if ( in_array( $active, array( 'yes', 'no', '1', '0' ), true ) ) {
								$args['active'] = in_array( $active, array( 'yes', '1' ), true ) ? 1 : 0;
						}

						$layout = $this->normalize_prize_layout( isset( $atts['design'] ) ? $atts['design'] : 'grid' );
						$size   = $this->normalize_prize_size( isset( $atts['size'] ) ? $atts['size'] : 'medium' );

$prizes = BHG_Prizes::get_prizes( $args );

if ( empty( $prizes ) ) {
return '<div class="bhg-prizes-shortcode"><p>' . esc_html( bhg_t( 'no_prizes_yet', 'No prizes found.' ) ) . '</p></div>';
}

wp_enqueue_style(
'bhg-shortcodes',
( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
array(),
defined( 'BHG_VERSION' ) ? BHG_VERSION : null
);

$section_options = array();
$display_overrides = array();

if ( '' !== $atts['visible'] ) {
$section_options['carousel_visible'] = max( 1, absint( $atts['visible'] ) );
}

$limit_attr = '' !== $atts['limit'] ? absint( $atts['limit'] ) : ( '' !== $atts['total'] ? absint( $atts['total'] ) : 0 );
if ( $limit_attr > 0 ) {
$section_options['carousel_total'] = $limit_attr;
$section_options['limit']          = $limit_attr;
}

if ( '' !== $atts['autoplay'] ) {
$auto = $this->normalize_yes_no_attr( $atts['autoplay'], false );
$section_options['carousel_autoplay'] = '1' === $auto;
}

if ( '' !== $atts['interval'] ) {
$section_options['carousel_interval'] = max( 1000, absint( $atts['interval'] ) );
}

if ( '' !== $atts['hide_heading'] ) {
$hide = $this->normalize_yes_no_attr( $atts['hide_heading'], false );
$section_options['hide_heading'] = '1' === $hide;
}

$heading_attr = '' !== $atts['heading'] ? $atts['heading'] : $atts['heading_text'];
if ( '' !== $heading_attr ) {
$section_options['heading_text'] = sanitize_text_field( wp_unslash( $heading_attr ) );
}

$summary_attr = $this->normalize_yes_no_attr( $atts['show_summary'], false );
$section_options['show_summary'] = ( '1' === $summary_attr );

if ( '' !== $atts['summary_title'] ) {
$section_options['summary_heading'] = sanitize_text_field( wp_unslash( $atts['summary_title'] ) );
}

foreach ( array( 'show_title', 'show_description', 'show_category', 'show_image', 'category_links' ) as $key ) {
if ( '' !== $atts[ $key ] ) {
$display_overrides[ $key ] = $this->normalize_yes_no_attr( $atts[ $key ] );
}
}

if ( '' !== $atts['click_action'] ) {
$display_overrides['click_action'] = $this->normalize_click_action_attr( $atts['click_action'] );
}

if ( '' !== $atts['link_target'] ) {
$display_overrides['link_target'] = $this->normalize_link_target_attr( $atts['link_target'] );
}

if ( '' !== $atts['category_target'] ) {
$display_overrides['category_target'] = $this->normalize_link_target_attr( $atts['category_target'] );
}

$content = $this->render_prize_section( $prizes, $layout, $size, $display_overrides, $section_options );

						if ( '' === $content ) {
								return '';
						}

						return '<div class="bhg-prizes-shortcode">' . $content . '</div>';
				}

					/**
					 * Minimal winners widget: latest closed hunts.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
public function winner_notifications_shortcode( $atts ) {
global $wpdb;

			$a = shortcode_atts(
				array( 'limit' => 5 ),
				$atts,
				'bhg_winner_notifications'
			);

						$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			if ( ! $hunts_table ) {
					return '';
			}
						// db call ok; no-cache ok.
						$sql                   = $wpdb->prepare(
							"SELECT id, title, final_balance, winners_count, closed_at FROM {$hunts_table} WHERE status = 'closed' ORDER BY closed_at DESC LIMIT %d",
							(int) $a['limit']
						);
										$hunts = $wpdb->get_results( $sql );

			if ( ! $hunts ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ) . '</p>';
			}

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start();
			echo '<div class="bhg-winner-notifications">';
			foreach ( $hunts as $hunt ) {
				$winners = function_exists( 'bhg_get_top_winners_for_hunt' )
				? bhg_get_top_winners_for_hunt( $hunt->id, (int) $hunt->winners_count )
				: array();

				echo '<div class="bhg-winner">';
				echo '<p><strong>' . esc_html( $hunt->title ) . '</strong></p>';
				if ( null !== $hunt->final_balance ) {
					echo '<p><em>' . esc_html( bhg_t( 'sc_final', 'Final' ) ) . ':</em> ' . esc_html( bhg_format_money( (float) $hunt->final_balance ) ) . '</p>';
				}

				if ( $winners ) {
					echo '<ul class="bhg-winner-list">';
					foreach ( $winners as $w ) {
						$u  = get_userdata( (int) $w->user_id );
						$nm = $u ? $u->user_login : sprintf( bhg_t( 'label_user_number', 'User #%d' ), (int) $w->user_id );
											echo '<li>' . esc_html( $nm ) . ' ' . esc_html( bhg_t( 'label_emdash', '—' ) ) . ' ' . esc_html( bhg_format_money( (float) $w->guess ) ) . ' (' . esc_html( bhg_format_money( (float) $w->diff ) ) . ')</li>';
					}
					echo '</ul>';
				}

				echo '</div>';
			}
			echo '</div>';
return ob_get_clean();
}

/**
 * Render the current user's bonus hunts list.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_bonushunts_shortcode( $atts ) {
$atts = shortcode_atts(
array(
'limit' => 50,
),
$atts,
'my_bonushunts'
);

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_bonushunts' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id = get_current_user_id();
$limit   = max( 1, min( 200, (int) $atts['limit'] ) );
$rows    = $this->get_user_bonus_hunt_rows( $user_id, $limit );

$currency_callback = function_exists( 'bhg_format_money' ) ? 'bhg_format_money' : static function( $value ) {
return number_format_i18n( $value, 2 );
};

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--hunts">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_bonushunts', 'My Bonus Hunts' ) ) . '</h2>';

if ( empty( $rows ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_hunts_user', 'You have not participated in any bonus hunts yet.' ) ) . '</p>';
} else {
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_guess', 'Guess' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_final_balance', 'Final Balance' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $rows as $row ) {
$guess_display = call_user_func( $currency_callback, $row['guess'] );
$final_display = null === $row['final_balance'] ? null : call_user_func( $currency_callback, $row['final_balance'] );
$diff_display  = null === $row['difference'] ? null : call_user_func( $currency_callback, $row['difference'] );
$closed_at     = $row['closed_at'] ? mysql2date( get_option( 'date_format' ), $row['closed_at'] ) : null;
$classes       = array( 'bhg-profile-row' );

if ( ! empty( $row['is_winner'] ) ) {
$classes[] = 'bhg-profile-row--winner';
}

            $final_cell   = null === $final_display ? '&mdash;' : esc_html( $final_display );
            $diff_cell    = null === $diff_display ? '&mdash;' : esc_html( $diff_display );
            $position_cell = $row['position'] > 0 ? esc_html( (string) (int) $row['position'] ) : '&mdash;';
            $closed_cell  = $closed_at ? esc_html( $closed_at ) : '&mdash;';

            echo '<tr class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ) . '">';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '">' . esc_html( $row['title'] ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_guess', 'Guess' ) ) . '">' . esc_html( $guess_display ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_final_balance', 'Final Balance' ) ) . '">' . $final_cell . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_difference', 'Difference' ) ) . '">' . $diff_cell . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_position', 'Position' ) ) . '">' . $position_cell . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_closed_at', 'Closed At' ) ) . '">' . $closed_cell . '</td>';
            echo '</tr>';
}

echo '</tbody></table>';
echo '</div>';
echo '</div>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Render the current user's tournament standings.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_tournaments_shortcode( $atts ) {
unset( $atts );

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_tournaments' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id     = get_current_user_id();
$tournaments = $this->get_user_tournament_rows( $user_id );

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--tournaments">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_tournaments', 'My Tournaments' ) ) . '</h2>';

if ( empty( $tournaments ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_tournaments_user', 'You have not participated in any tournaments yet.' ) ) . '</p>';
} else {
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_wins', 'Times Won' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_last_win', 'Last win' ) ) . '</th>';
echo '</tr></thead><tbody>';

        foreach ( $tournaments as $row ) {
            $last_win     = $row['last_win_date'] ? mysql2date( get_option( 'date_format' ), $row['last_win_date'] ) : '';
            $rank_cell    = null === $row['rank'] ? '&mdash;' : esc_html( (string) (int) $row['rank'] );
            $last_win_cell = $last_win ? esc_html( $last_win ) : '&mdash;';

            echo '<tr>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_tournament', 'Tournament' ) ) . '">' . esc_html( $row['title'] ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_points', 'Points' ) ) . '">' . esc_html( (int) $row['points'] ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_wins', 'Times Won' ) ) . '">' . esc_html( (int) $row['wins'] ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_rank', 'Rank' ) ) . '">' . $rank_cell . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_last_win', 'Last win' ) ) . '">' . $last_win_cell . '</td>';
            echo '</tr>';
        }

echo '</tbody></table>';
echo '</div>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Render the current user's prize history.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_prizes_shortcode( $atts ) {
unset( $atts );

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_prizes' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id = get_current_user_id();
$prizes  = $this->get_user_prize_rows( $user_id );

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--prizes">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_prizes', 'My Prizes' ) ) . '</h2>';

if ( empty( $prizes ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_prizes_user', 'You have not won any prizes yet.' ) ) . '</p>';
} else {
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_prize', 'Prize' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_category', 'Category' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

        foreach ( $prizes as $row ) {
            $closed_at      = $row['closed_at'] ? mysql2date( get_option( 'date_format' ), $row['closed_at'] ) : '';
            $category_label = BHG_Prizes::get_category_label( isset( $row['category'] ) ? $row['category'] : '' );
            if ( '' === $category_label && isset( $row['category'] ) ) {
                $category_label = ucwords( str_replace( '_', ' ', $row['category'] ) );
            }

            $position_cell = $row['position'] > 0 ? esc_html( (string) (int) $row['position'] ) : '&mdash;';
            $closed_cell   = $closed_at ? esc_html( $closed_at ) : '&mdash;';

            echo '<tr>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_prize', 'Prize' ) ) . '">' . esc_html( $row['title'] ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_category', 'Category' ) ) . '">' . esc_html( $category_label ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '">' . esc_html( $row['hunt_title'] ) . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_position', 'Position' ) ) . '">' . $position_cell . '</td>';
            echo '<td data-label="' . esc_attr( bhg_t( 'label_closed_at', 'Closed At' ) ) . '">' . $closed_cell . '</td>';
            echo '</tr>';
        }

echo '</tbody></table>';
echo '</div>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Render the current user's ranking summary.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_rankings_shortcode( $atts ) {
unset( $atts );

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_rankings' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id = get_current_user_id();
$hunts   = $this->get_user_bonus_hunt_rows( $user_id, 200 );
$wins    = array_filter(
$hunts,
static function( $row ) {
return ! empty( $row['is_winner'] );
}
);
$tournaments = $this->get_user_tournament_rows( $user_id );

$total_points = 0;
$best_rank    = null;

foreach ( $tournaments as $row ) {
$total_points += (int) $row['points'];
if ( isset( $row['rank'] ) && $row['rank'] && ( null === $best_rank || (int) $row['rank'] < $best_rank ) ) {
$best_rank = (int) $row['rank'];
}
}

$currency_callback = function_exists( 'bhg_format_money' ) ? 'bhg_format_money' : static function( $value ) {
return number_format_i18n( $value, 2 );
};

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--rankings">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_rankings', 'My Rankings' ) ) . '</h2>';

echo '<ul class="bhg-profile-summary">';
echo '<li>' . esc_html( bhg_t( 'label_total_hunt_wins', 'Total hunt wins' ) ) . ': ' . esc_html( (string) count( $wins ) ) . '</li>';
echo '<li>' . esc_html( bhg_t( 'label_total_tournament_points', 'Total tournament points' ) ) . ': ' . esc_html( (string) $total_points ) . '</li>';
echo '<li>' . esc_html( bhg_t( 'label_best_tournament_rank', 'Best tournament rank' ) ) . ': ' . ( null === $best_rank ? '&mdash;' : esc_html( (string) $best_rank ) ) . '</li>';
echo '<li>' . esc_html( bhg_t( 'label_tournaments_played', 'Tournaments played' ) ) . ': ' . esc_html( (string) count( $tournaments ) ) . '</li>';
echo '</ul>';

if ( empty( $wins ) && empty( $tournaments ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_rankings_user', 'No ranking data is available yet.' ) ) . '</p>';
} else {
if ( ! empty( $wins ) ) {
echo '<h3>' . esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ) . '</h3>';
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

            foreach ( $wins as $row ) {
                $diff_display  = null === $row['difference'] ? null : call_user_func( $currency_callback, $row['difference'] );
                $closed_at     = $row['closed_at'] ? mysql2date( get_option( 'date_format' ), $row['closed_at'] ) : '';
                $diff_cell     = null === $diff_display ? '&mdash;' : esc_html( $diff_display );
                $closed_cell   = $closed_at ? esc_html( $closed_at ) : '&mdash;';

                echo '<tr>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '">' . esc_html( $row['title'] ) . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_position', 'Position' ) ) . '">' . esc_html( (string) (int) $row['position'] ) . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_difference', 'Difference' ) ) . '">' . $diff_cell . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_closed_at', 'Closed At' ) ) . '">' . $closed_cell . '</td>';
                echo '</tr>';
            }

echo '</tbody></table>';
echo '</div>';
}

if ( ! empty( $tournaments ) ) {
echo '<h3>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</h3>';
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_wins', 'Times Won' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_last_win', 'Last win' ) ) . '</th>';
echo '</tr></thead><tbody>';

            foreach ( $tournaments as $row ) {
                $last_win     = $row['last_win_date'] ? mysql2date( get_option( 'date_format' ), $row['last_win_date'] ) : '';
                $rank_cell    = null === $row['rank'] ? '&mdash;' : esc_html( (string) (int) $row['rank'] );
                $last_win_cell = $last_win ? esc_html( $last_win ) : '&mdash;';

                echo '<tr>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_tournament', 'Tournament' ) ) . '">' . esc_html( $row['title'] ) . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_points', 'Points' ) ) . '">' . esc_html( (string) (int) $row['points'] ) . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_wins', 'Times Won' ) ) . '">' . esc_html( (string) (int) $row['wins'] ) . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_rank', 'Rank' ) ) . '">' . $rank_cell . '</td>';
                echo '<td data-label="' . esc_attr( bhg_t( 'label_last_win', 'Last win' ) ) . '">' . $last_win_cell . '</td>';
                echo '</tr>';
            }

echo '</tbody></table>';
echo '</div>';
}
}

echo '</div>';

return ob_get_clean();
}

					/**
					 * Minimal profile view: affiliate status badge.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
			   public function user_profile_shortcode( $atts ) {
					   unset( $atts ); // Parameter unused but kept for shortcode signature.
					   if ( ! is_user_logged_in() ) {
							   return '<p>' . esc_html( bhg_t( 'notice_login_view_content', 'Please log in to view this content.' ) ) . '</p>';
					   }
					   wp_enqueue_style(
							   'bhg-shortcodes',
							   ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
							   array(),
							   defined( 'BHG_VERSION' ) ? BHG_VERSION : null
					   );
					  $user        = wp_get_current_user();
					  $user_id     = $user->ID;
					  $real_name   = trim( (string) get_user_meta( $user_id, 'bhg_real_name', true ) );
					  if ( '' === $real_name ) {
							  $real_name = trim( $user->get( 'first_name' ) . ' ' . $user->get( 'last_name' ) );
					  }
					  if ( '' === $real_name ) {
							  $real_name = (string) $user->display_name;
					  }
					   $username    = $user->user_login;
					   $email       = $user->user_email;
					   $is_affiliate = (int) get_user_meta( $user_id, 'bhg_is_affiliate', true );
					   $badge       = $is_affiliate ? '<span class="bhg-aff-green" aria-hidden="true"></span>' : '<span class="bhg-aff-red" aria-hidden="true"></span>';
					   $aff_text    = $is_affiliate ? bhg_t( 'label_affiliate', 'Affiliate' ) : bhg_t( 'label_not_affiliate', 'Not Affiliate' );
					   $edit_link   = '';
					   if ( current_user_can( 'edit_user', $user_id ) ) {
							   $edit_link = get_edit_user_link( $user_id );
					   }
$output  = '<div class="bhg-user-profile"><div class="bhg-table-wrapper"><table class="bhg-user-profile-table">';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_name', 'Name' ) ) . '</th><td>' . esc_html( $real_name ) . '</td></tr>';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_username', 'Username' ) ) . '</th><td>' . esc_html( $username ) . '</td></tr>';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_email', 'Email' ) ) . '</th><td>' . esc_html( $email ) . '</td></tr>';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_affiliate_status', 'Affiliate Status' ) ) . '</th><td>' . wp_kses_post( $badge ) . ' ' . esc_html( $aff_text ) . '</td></tr>';

					  $site_rows = array();
					  if ( function_exists( 'bhg_get_user_affiliate_websites' ) ) {
							  $site_ids = array_filter( array_map( 'absint', (array) bhg_get_user_affiliate_websites( (int) $user_id ) ) );
							  if ( $site_ids ) {
									  global $wpdb;
									  $sites_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
									  if ( $sites_table ) {
											  $placeholders = implode( ',', array_fill( 0, count( $site_ids ), '%d' ) );
											  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $placeholders created using prepare-safe values.
											  $query = "SELECT id, name FROM {$sites_table} WHERE id IN ({$placeholders}) ORDER BY id ASC";
											  // db call ok; no-cache ok.
											  $site_rows = $wpdb->get_results( $wpdb->prepare( $query, $site_ids ) );
									  }
							  }
					  }

					  if ( $site_rows ) {
							  $count = 1;
							  foreach ( $site_rows as $site_row ) {
									  $label = sprintf( bhg_t( 'label_affiliate_website_number', 'Affiliate Website %d' ), $count );
									  $output .= '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $site_row->name ) . '</td></tr>';
									  $count++;
							  }
					  }

$output .= '</table></div>';
					   if ( $edit_link ) {
							   $output .= '<p><a href="' . esc_url( $edit_link ) . '">' . esc_html( bhg_t( 'link_edit_profile', 'Edit Profile' ) ) . '</a></p>';
					   }
					   $output .= '</div>';
					   return $output;
			   }

				/**
				 * Render the current jackpot amount.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
public function jackpot_current_shortcode( $atts ) {
if ( ! class_exists( 'BHG_Jackpots' ) ) {
return $this->shortcode_notice( 'BHG jackpot current suppressed: jackpots module missing.' );
}

						$atts = shortcode_atts(
								array(
										'id' => 0,
								),
								$atts,
								'bhg_jackpot_current'
						);

$jackpot_id = absint( $atts['id'] );

if ( $jackpot_id <= 0 ) {
return $this->shortcode_notice( 'BHG jackpot current suppressed: provide id="" attribute.' );
}

$amount = BHG_Jackpots::instance()->get_formatted_amount( $jackpot_id );

if ( '' === $amount ) {
return $this->shortcode_notice( 'BHG jackpot current suppressed: jackpot not found.' );
}

						return '<span class="bhg-jackpot-amount" data-jackpot-id="' . esc_attr( $jackpot_id ) . '">' . esc_html( $amount ) . '</span>';
				}

                                /**
                                 * Render a ticker of jackpots or winners.
                                 *
                                 * @param array $atts Shortcode attributes.
                                 * @return string
				 */
				public function jackpot_ticker_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Jackpots' ) ) {
								return '';
						}

                                                $atts = shortcode_atts(
                                                                array(
                                                                                'mode'   => 'amount',
                                                                                'status' => 'active',
                                                                                'design' => 'fade',
                                                                ),
                                                                $atts,
                                                                'bhg_jackpot_ticker'
                                                );

                                                $mode    = sanitize_key( $atts['mode'] );
                                                $status  = in_array( sanitize_key( $atts['status'] ), array( 'active', 'closed', 'all' ), true ) ? sanitize_key( $atts['status'] ) : 'active';
                                                $design  = in_array( sanitize_key( $atts['design'] ), array( 'fade', 'scroll' ), true ) ? sanitize_key( $atts['design'] ) : 'fade';
                                                $jackets = BHG_Jackpots::instance()->get_ticker_items( $mode, $status );

                                                if ( empty( $jackets ) ) {
                                                                return '';
                                                }

$fade_interval = max( 1, (int) get_option( 'bhg_jackpot_ticker_interval', 5 ) );
$scroll_speed  = max( 1, (int) get_option( 'bhg_jackpot_ticker_scroll_speed', 25 ) );
$separator     = (string) get_option( 'bhg_jackpot_ticker_separator', '-' );
$padding       = max( 0, (int) get_option( 'bhg_jackpot_ticker_padding', 0 ) );

						$format_amount = static function ( $amount ) {
								if ( function_exists( 'bhg_format_money' ) ) {
										return bhg_format_money( $amount );
								}

								return number_format_i18n( (float) $amount, 2 );
						};

                                                ob_start();
$classes = array(
'bhg-jackpot-ticker',
'bhg-jackpot-ticker--' . $mode,
'bhg-jackpot-ticker--design-' . $design,
);
$style_attr = $padding > 0 ? sprintf( ' style="--bhg-ticker-padding:%dpx;"', $padding ) : '';
echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-interval="' . esc_attr( $fade_interval ) . '" data-speed="' . esc_attr( $scroll_speed ) . '" data-separator="' . esc_attr( $separator ) . '" data-padding="' . esc_attr( $padding ) . '"' . $style_attr . '><ul class="bhg-jackpot-ticker__list">';
                                                if ( 'winners' === $mode ) {
                                                                $total = count( $jackets );
                                                                $index = 0;
                                                                foreach ( $jackets as $row ) {
                                                                                ++$index;
                                                                                $user_label = '';
                                                                                $user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
                                                                                if ( $user_id ) {
                                                                                                $user = get_userdata( $user_id );
                                                                                                if ( $user ) {
                                                                                                                $user_label = $user->display_name;
                                                                                                }
                                                                                }
                                                                                $user_label = $this->format_username_label( $user_label, $user_id );
                                                                                $amount = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
                                                                                $title  = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
echo '<li class="bhg-jackpot-ticker__item">';
                                                                                echo '<span class="bhg-ticker-winner-name">' . esc_html( $user_label ) . '</span> ';
                                                                                echo '<span class="bhg-ticker-amount">' . esc_html( $format_amount( $amount ) ) . '</span> ';
                                                                                if ( $title ) {
                                                                                                echo '<span class="bhg-ticker-jackpot">' . esc_html( $title ) . '</span>';
                                                                                }
                                                                                if ( 'scroll' === $design && $separator && $index < $total ) {
                                                                                                echo '<span class="bhg-ticker-separator" aria-hidden="true">' . esc_html( $separator ) . '</span>';
                                                                                }
                                                                                echo '</li>';
                                                                }
                                                } else {
                                                                $total = count( $jackets );
                                                                $index = 0;
                                                                foreach ( $jackets as $row ) {
                                                                                ++$index;
                                                                                $title  = isset( $row['title'] ) ? $row['title'] : '';
                                                                                $amount = isset( $row['current_amount'] ) ? (float) $row['current_amount'] : 0.0;
echo '<li class="bhg-jackpot-ticker__item">';
                                                                                if ( $title ) {
                                                                                                echo '<span class="bhg-ticker-jackpot">' . esc_html( $title ) . '</span> ';
                                                                                }
                                                                                echo '<span class="bhg-ticker-amount">' . esc_html( $format_amount( $amount ) ) . '</span>';
                                                                                if ( 'scroll' === $design && $separator && $index < $total ) {
                                                                                                echo ' <span class="bhg-ticker-separator" aria-hidden="true">' . esc_html( $separator ) . '</span>';
                                                                                }
                                                                                echo '</li>';
                                                                }
                                                }
                                                echo '</ul></div>';

						return ob_get_clean();
				}

				/**
				 * Render a winners list/table for jackpots.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
                                public function jackpot_winners_shortcode( $atts ) {
                                                if ( ! class_exists( 'BHG_Jackpots' ) ) {
                                                                return $this->shortcode_notice( 'BHG jackpot winners suppressed: jackpots module missing.' );
                                                }

                                                $atts = shortcode_atts(
                                                                array(
                                                                                'layout'    => 'table',
                                                                                'limit'     => 10,
                                                                                'affiliate' => 0,
                                                                                'year'      => 0,
                                                                                'empty'     => '',
                                                                                'show_title'     => 'show',
                                                                                'show_amount'    => 'show',
                                                                                'show_username'  => 'show',
                                                                                'show_affiliate' => 'show',
                                                                                'show_date'      => 'show',
                                                                                'strong'         => '',
                                                                ),
                                                                $atts,
                                                                'bhg_jackpot_winners'
                                                );

						$args = array( 'limit' => max( 1, absint( $atts['limit'] ) ) );

						if ( ! empty( $atts['affiliate'] ) ) {
								$args['affiliate'] = absint( $atts['affiliate'] );
						}

						if ( ! empty( $atts['year'] ) ) {
								$args['year'] = absint( $atts['year'] );
						}

$rows = BHG_Jackpots::instance()->get_winner_rows( $args );

if ( empty( $rows ) ) {
if ( $atts['empty'] ) {
return '<div class="bhg-jackpot-winners-empty">' . esc_html( $atts['empty'] ) . '</div>';
}

return $this->shortcode_notice( 'BHG jackpot winners suppressed: no winners found for filters.' );
}

                                                $layout         = sanitize_key( $atts['layout'] );
                                                $visibility     = static function ( $value ) {
                                                                $value = strtolower( (string) $value );

                                                                return ! in_array( $value, array( 'hide', '0', 'false', 'no' ), true );
                                                };
                                                $strong_fields  = array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( ',', (string) $atts['strong'] ) ) ) );
                                                $maybe_strong   = static function ( $field, $text ) use ( $strong_fields ) {
                                                                if ( '' === $text ) {
                                                                                return '';
                                                                }

                                                                $escaped = esc_html( $text );

                                                                return in_array( $field, $strong_fields, true ) ? '<strong>' . $escaped . '</strong>' : $escaped;
                                                };
                                                $format_amount  = static function ( $amount ) {
                                                                if ( function_exists( 'bhg_format_money' ) ) {
                                                                                return bhg_format_money( $amount );
                                                                }

								return number_format_i18n( (float) $amount, 2 );
						};

                                                if ( 'list' === $layout ) {
                                                                ob_start();
                                                                echo '<ul class="bhg-jackpot-winners">';
                                                                foreach ( $rows as $row ) {
										$title      = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
										$amount     = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
										$created_at = isset( $row['created_at'] ) ? $row['created_at'] : '';
										$user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
                                                                                $affiliate  = isset( $row['affiliate_site_name'] ) ? $row['affiliate_site_name'] : '';
                                                                                $user_name  = '';
                                                                                if ( $user_id ) {
                                                                                                $user = get_userdata( $user_id );
												if ( $user ) {
														$user_name = $user->display_name;
												}
										}
                                                                                echo '<li class="bhg-jackpot-winner">';
                                                                                if ( $visibility( $atts['show_username'] ) && $user_name ) {
                                                                                                echo '<span class="bhg-jackpot-winner-name">' . $maybe_strong( 'username', $user_name ) . '</span> ';
                                                                                }
                                                                                if ( $visibility( $atts['show_amount'] ) ) {
                                                                                                echo '<span class="bhg-jackpot-amount">' . $maybe_strong( 'amount', $format_amount( $amount ) ) . '</span> ';
                                                                                }
                                                                                if ( $visibility( $atts['show_title'] ) && $title ) {
                                                                                                echo '<span class="bhg-jackpot-name">' . $maybe_strong( 'title', $title ) . '</span> ';
                                                                                }
                                                                                if ( $visibility( $atts['show_affiliate'] ) && $affiliate ) {
                                                                                                echo '<span class="bhg-jackpot-affiliate">' . $maybe_strong( 'affiliate', $affiliate ) . '</span> ';
                                                                                }
                                                                                if ( $visibility( $atts['show_date'] ) && $created_at ) {
                                                                                                echo '<time datetime="' . esc_attr( $created_at ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $created_at ) ) . '</time>';
                                                                                }
                                                                                echo '</li>';
                                                                }
                                                                echo '</ul>';
                                                                return ob_get_clean();
                                                }

ob_start();
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-jackpot-winners-table"><thead><tr>';
                                                if ( $visibility( $atts['show_username'] ) ) {
                                                                echo '<th>' . esc_html( bhg_t( 'sc_user', 'Username' ) ) . '</th>';
                                                }
                                                if ( $visibility( $atts['show_amount'] ) ) {
                                                                echo '<th>' . esc_html( bhg_t( 'label_amount', 'Amount' ) ) . '</th>';
                                                }
                                                if ( $visibility( $atts['show_title'] ) ) {
                                                                echo '<th>' . esc_html( bhg_t( 'label_title', 'Title' ) ) . '</th>';
                                                }
                                                if ( $visibility( $atts['show_affiliate'] ) ) {
                                                                echo '<th>' . esc_html( bhg_t( 'label_affiliate_website', 'Affiliate Website' ) ) . '</th>';
                                                }
                                                if ( $visibility( $atts['show_date'] ) ) {
                                                                echo '<th>' . esc_html( bhg_t( 'label_date', 'Date' ) ) . '</th>';
                                                }
echo '</tr></thead><tbody>';
                                                foreach ( $rows as $row ) {
                                                                $title      = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
                                                                $amount     = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
                                                                $created_at = isset( $row['created_at'] ) ? $row['created_at'] : '';
                                                                $user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
                                                                $affiliate  = isset( $row['affiliate_site_name'] ) ? $row['affiliate_site_name'] : '';
                                                                $user_name  = '';
                                                                if ( $user_id ) {
                                                                                $user = get_userdata( $user_id );
										if ( $user ) {
												$user_name = $user->display_name;
										}
								}
            echo '<tr>';
            if ( $visibility( $atts['show_username'] ) ) {
                    echo '<td data-label="' . esc_attr( bhg_t( 'sc_user', 'Username' ) ) . '">' . $maybe_strong( 'username', $user_name ) . '</td>';
            }
            if ( $visibility( $atts['show_amount'] ) ) {
                    echo '<td data-label="' . esc_attr( bhg_t( 'label_amount', 'Amount' ) ) . '">' . $maybe_strong( 'amount', $format_amount( $amount ) ) . '</td>';
            }
            if ( $visibility( $atts['show_title'] ) ) {
                    echo '<td data-label="' . esc_attr( bhg_t( 'label_title', 'Title' ) ) . '">' . $maybe_strong( 'title', $title ) . '</td>';
            }
            if ( $visibility( $atts['show_affiliate'] ) ) {
                    echo '<td data-label="' . esc_attr( bhg_t( 'label_affiliate_website', 'Affiliate Website' ) ) . '">' . $maybe_strong( 'affiliate', $affiliate ) . '</td>';
            }
            if ( $visibility( $atts['show_date'] ) ) {
                    echo '<td data-label="' . esc_attr( bhg_t( 'label_date', 'Date' ) ) . '">' . esc_html( $created_at ? mysql2date( get_option( 'date_format' ), $created_at ) : '' ) . '</td>';
            }
            echo '</tr>';
                                                }
echo '</tbody></table>';
echo '</div>';

						return ob_get_clean();
				}

										/**
										 * Simple wins leaderboard with tabs.
										 *
										 * @param array $atts Shortcode attributes.
										 * @return string HTML output.
										 */
		public function best_guessers_shortcode( $atts ) {
			global $wpdb;

						$wins_tbl  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
						$tours_tbl = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
						$users_tbl = esc_sql( $wpdb->users );

			$now_ts        = time();
			$current_month = wp_date( 'Y-m', $now_ts );
			$current_year  = wp_date( 'Y', $now_ts );

			$periods = array(
								'overall' => array(
										'label' => esc_html( bhg_t( 'label_overall', 'Overall' ) ),
										'start' => '',
										'end'   => '',
								),
								'monthly' => array(
										'label' => esc_html( bhg_t( 'label_monthly', 'Monthly' ) ),
										'start' => $current_month . '-01',
										'end'   => wp_date( 'Y-m-t', strtotime( $current_month . '-01', $now_ts ) ),
								),
								'yearly'  => array(
										'label' => esc_html( bhg_t( 'label_yearly', 'Yearly' ) ),
										'start' => $current_year . '-01-01',
										'end'   => $current_year . '-12-31',
								),
								'alltime' => array(
										'label' => esc_html( bhg_t( 'label_all_time', 'Alltime' ) ),
										'start' => '',
										'end'   => '',
								),
			);

			$results = array();


			foreach ( $periods as $key => $info ) {
				$where_clauses = array();
				$params        = array();

				if ( ! empty( $info['start'] ) && ! empty( $info['end'] ) ) {
					$where_clauses[] = 't.start_date IS NOT NULL';
					$where_clauses[] = 't.end_date IS NOT NULL';
					$where_clauses[] = 't.start_date >= %s';
					$where_clauses[] = 't.end_date <= %s';
					$params[]        = $info['start'];
					$params[]        = $info['end'];
				} elseif ( 'alltime' === $key ) {
					$where_clauses[] = '(t.start_date IS NULL OR t.start_date = "0000-00-00")';
					$where_clauses[] = '(t.end_date IS NULL OR t.end_date = "0000-00-00")';
				}

				$sql = 'SELECT u.ID as user_id, u.user_login, SUM(r.wins) as total_wins'
					. " FROM {$wins_tbl} r"
					. " INNER JOIN {$users_tbl} u ON u.ID = r.user_id"
					. " INNER JOIN {$tours_tbl} t ON t.id = r.tournament_id";

				if ( ! empty( $where_clauses ) ) {
					$sql .= ' WHERE ' . implode( ' AND ', $where_clauses );
				}

				$sql .= "
											GROUP BY u.ID, u.user_login";

				if ( ! empty( $params ) ) {
					$sql = $wpdb->prepare( $sql, ...$params );
				}

				$sql               .= ' ORDER BY total_wins DESC, u.user_login ASC LIMIT 50';
				$results[ $key ] = $wpdb->get_results( $sql );
			}

						$hunts_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			if ( ! $hunts_tbl ) {
					return '';
			}
						$hunts_sql             = "SELECT id, title FROM {$hunts_tbl} WHERE status = 'closed' ORDER BY created_at DESC LIMIT 50";
										$hunts = $wpdb->get_results( $hunts_sql );

				wp_enqueue_style(
					'bhg-shortcodes',
					( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
					array(),
					defined( 'BHG_VERSION' ) ? BHG_VERSION : null
				);
			wp_enqueue_script(
				'bhg-shortcodes-js',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/js/bhg-shortcodes.js',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
				true
			);

				ob_start();
				echo '<ul class="bhg-tabs">';
				$first = true;
			foreach ( $periods as $key => $info ) {
				if ( $first ) {
					echo '<li class="active"><a href="#bhg-tab-' . esc_html( $key ) . '">' . esc_html( $info['label'] ) . '</a></li>';
					$first = false;
				} else {
						echo '<li><a href="#bhg-tab-' . esc_html( $key ) . '">' . esc_html( $info['label'] ) . '</a></li>';
				}
			}
			if ( $hunts ) {
					echo '<li><a href="#bhg-tab-hunts">' . esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ) . '</a></li>';
			}
				echo '</ul>';

				$first = true;
			foreach ( $periods as $key => $info ) {
					$classes = 'bhg-tab-pane';
				if ( $first ) {
						$classes .= ' active';
						$first    = false;
				}
					echo '<div id="bhg-tab-' . esc_attr( $key ) . '" class="' . esc_attr( $classes ) . '">';
					$rows = isset( $results[ $key ] ) ? $results[ $key ] : array();
if ( ! $rows ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_data_yet', 'No data yet.' ) ) . '</p>';
} else {
echo '<div class="bhg-table-wrapper">';
echo '<table class="bhg-leaderboard"><thead><tr><th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th><th>' . esc_html( bhg_t( 'sc_user', 'Username' ) ) . '</th><th>' . esc_html( bhg_t( 'sc_wins', 'Times Won' ) ) . '</th></tr></thead><tbody>';
						$pos = 1;
					foreach ( $rows as $r ) {
                                                       /* translators: %d: user ID. */
                                                       $user_label = $this->format_username_label( $r->user_login, (int) $r->user_id );
							echo '<tr><td>' . (int) $pos . '</td><td>' . esc_html( $user_label ) . '</td><td>' . (int) $r->total_wins . '</td></tr>';
							++$pos;
					}
echo '</tbody></table>';
echo '</div>';
				}
					echo '</div>';
			}

			if ( $hunts ) {
				$raw  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				$base = esc_url_raw( remove_query_arg( 'hunt_id', wp_validate_redirect( $raw, home_url( '/' ) ) ) );
				echo '<div id="bhg-tab-hunts" class="bhg-tab-pane">';
				echo '<ul class="bhg-hunt-history">';
				foreach ( $hunts as $hunt ) {
						$url = add_query_arg( 'hunt_id', (int) $hunt->id, $base );
						echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $hunt->title ) . '</a></li>';
				}
				echo '</ul>';
				echo '</div>';
			}

				return ob_get_clean();
                }

        }
}

  // Register once on init even if no other bootstrap instantiates the class.
if ( ! function_exists( 'bhg_register_shortcodes_once' ) ) {
		/**
		 * Register shortcodes once on init.
		 *
		 * @return void
		 */
	function bhg_register_shortcodes_once() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed
			static $done = false;
		if ( $done ) {
				return;
		}
			$done = true;
		if ( class_exists( 'BHG_Shortcodes' ) ) {
				new BHG_Shortcodes();
		}
	}
		add_action( 'init', 'bhg_register_shortcodes_once', 20 );
}
