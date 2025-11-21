<?php

use PHPUnit\Framework\TestCase;

final class LeaderboardTimelineTest extends TestCase {
        /** @var MockWPDB */
        private $wpdb;
        /** @var BHG_Shortcodes */
        private $shortcodes;
        /** @var ReflectionMethod */
        private $leaderboard;
        /** @var ReflectionMethod */
        private $tournamentLeaderboard;

        protected function setUp(): void {
                global $wpdb;

                if ( ! class_exists( 'MockWPDB' ) ) {
                        require_once __DIR__ . '/support/class-mock-wpdb.php';
                }

                if ( ! function_exists( 'bhg_log' ) ) {
                        require_once __DIR__ . '/../includes/helpers.php';
                }

                if ( ! class_exists( 'BHG_Shortcodes' ) ) {
                        require_once __DIR__ . '/../includes/class-bhg-shortcodes.php';
                }

                $this->wpdb  = new MockWPDB();
                $wpdb        = $this->wpdb;
                $this->shortcodes = new BHG_Shortcodes();

                $this->leaderboard = new ReflectionMethod( $this->shortcodes, 'run_leaderboard_query' );
                $this->leaderboard->setAccessible( true );

                $this->tournamentLeaderboard = new ReflectionMethod( $this->shortcodes, 'run_tournament_results_leaderboard' );
                $this->tournamentLeaderboard->setAccessible( true );
        }

        public function test_timeline_filters_apply_for_specific_tournament(): void {
                global $wpdb;

                $current_year  = (int) gmdate( 'Y' );
                $previous_year = $current_year - 1;

                $wpdb->users_data[101] = array( 'ID' => 101, 'user_login' => 'timeline-winner' );

                $wpdb->bonus_hunts[1] = array(
                        'id'            => 1,
                        'status'        => 'closed',
                        'winners_count' => 1,
                        'closed_at'     => sprintf( '%d-02-01 00:00:00', $current_year ),
                        'created_at'    => sprintf( '%d-01-01 00:00:00', $current_year ),
                );
                $wpdb->bonus_hunts[2] = array(
                        'id'            => 2,
                        'status'        => 'closed',
                        'winners_count' => 1,
                        'closed_at'     => sprintf( '%d-03-01 00:00:00', $previous_year ),
                        'created_at'    => sprintf( '%d-01-01 00:00:00', $previous_year ),
                );

                $wpdb->tournaments_hunts[] = array( 'hunt_id' => 1, 'tournament_id' => 5 );
                $wpdb->tournaments_hunts[] = array( 'hunt_id' => 2, 'tournament_id' => 5 );

                $wpdb->hunt_winners[] = array(
                        'id'         => 1,
                        'hunt_id'    => 1,
                        'user_id'    => 101,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => sprintf( '%d-02-02 12:00:00', $current_year ),
                );
                $wpdb->hunt_winners[] = array(
                        'id'         => 2,
                        'hunt_id'    => 2,
                        'user_id'    => 101,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => sprintf( '%d-03-02 12:00:00', $previous_year ),
                );

                $result = $this->leaderboard->invoke( $this->shortcodes, array(
                        'tournament_id' => 5,
                        'timeline'      => 'this_year',
                        'ranking_limit' => 10,
                ) );

                $this->assertSame( 1, $result['total'] );
                $this->assertSame( 1, $result['rows'][0]->total_wins );
        }

        public function test_all_time_still_includes_full_tournament_history(): void {
                global $wpdb;

                $current_year  = (int) gmdate( 'Y' );
                $previous_year = $current_year - 1;

                $wpdb->users_data[102] = array( 'ID' => 102, 'user_login' => 'alltime-winner' );

                $wpdb->bonus_hunts[3] = array(
                        'id'            => 3,
                        'status'        => 'closed',
                        'winners_count' => 1,
                        'closed_at'     => sprintf( '%d-02-01 00:00:00', $current_year ),
                        'created_at'    => sprintf( '%d-01-01 00:00:00', $current_year ),
                );
                $wpdb->bonus_hunts[4] = array(
                        'id'            => 4,
                        'status'        => 'closed',
                        'winners_count' => 1,
                        'closed_at'     => sprintf( '%d-01-01 00:00:00', $previous_year ),
                        'created_at'    => sprintf( '%d-01-01 00:00:00', $previous_year ),
                );

                $wpdb->tournaments_hunts[] = array( 'hunt_id' => 3, 'tournament_id' => 6 );
                $wpdb->tournaments_hunts[] = array( 'hunt_id' => 4, 'tournament_id' => 6 );

                $wpdb->hunt_winners[] = array(
                        'id'         => 3,
                        'hunt_id'    => 3,
                        'user_id'    => 102,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => sprintf( '%d-02-02 12:00:00', $current_year ),
                );
                $wpdb->hunt_winners[] = array(
                        'id'         => 4,
                        'hunt_id'    => 4,
                        'user_id'    => 102,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => sprintf( '%d-01-02 12:00:00', $previous_year ),
                );

                $result = $this->leaderboard->invoke( $this->shortcodes, array(
                        'tournament_id' => 6,
                        'timeline'      => 'all_time',
                        'ranking_limit' => 10,
                ) );

                $this->assertSame( 1, $result['total'] );
                $this->assertSame( 2, $result['rows'][0]->total_wins );
        }

        public function test_tournament_results_respect_timeline_filter(): void {
                global $wpdb;

                $current_year  = (int) gmdate( 'Y' );
                $previous_year = $current_year - 1;

                $wpdb->users_data[201] = array( 'ID' => 201, 'user_login' => 'ranked-current' );
                $wpdb->users_data[202] = array( 'ID' => 202, 'user_login' => 'ranked-previous' );

                $wpdb->tournament_results[] = array(
                        'id'             => 1,
                        'tournament_id'  => 7,
                        'user_id'        => 201,
                        'wins'           => 3,
                        'last_win_date'  => sprintf( '%d-02-10 00:00:00', $current_year ),
                        'points'         => 90,
                );

                $wpdb->tournament_results[] = array(
                        'id'             => 2,
                        'tournament_id'  => 7,
                        'user_id'        => 202,
                        'wins'           => 4,
                        'last_win_date'  => sprintf( '%d-12-31 23:59:59', $previous_year ),
                        'points'         => 120,
                );

                $result = $this->tournamentLeaderboard->invoke( $this->shortcodes, array(
                        'tournament_id' => 7,
                        'timeline'      => 'this_year',
                        'ranking_limit' => 10,
                ) );

                $this->assertSame( 1, $result['total'] );
                $this->assertSame( 'ranked-current', $result['rows'][0]->user_login );
                $this->assertSame( 3, $result['rows'][0]->total_wins );
        }
}

