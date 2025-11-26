<?php

use PHPUnit\Framework\TestCase;

final class LeaderboardDistinctWinsTest extends TestCase {
        /**
         * @var MockWPDB
         */
        private $wpdb;

        /**
         * @var BHG_Shortcodes
         */
        private $shortcodes;

        /**
         * @var ReflectionMethod
         */
        private $leaderboard;

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

                $this->wpdb = new MockWPDB();
                $wpdb       = $this->wpdb;

                $this->shortcodes = new BHG_Shortcodes();
                $ref              = new ReflectionClass( $this->shortcodes );
                $this->leaderboard = $ref->getMethod( 'run_leaderboard_query' );
                $this->leaderboard->setAccessible( true );
        }

        public function test_wins_are_counted_distinct_per_hunt(): void {
                global $wpdb;

                $wpdb->bonus_hunts[1] = array(
                        'id'            => 1,
                        'status'        => 'closed',
                        'affiliate_site_id' => 3,
                        'created_at'    => '2025-01-01 00:00:00',
                        'closed_at'     => '2025-01-02 00:00:00',
                );

                $wpdb->tournaments[5] = array(
                        'id'     => 5,
                        'status' => 'active',
                );

                $wpdb->tournaments_hunts[] = array(
                        'hunt_id'       => 1,
                        'tournament_id' => 5,
                );
                // Duplicate mapping that previously doubled counts without DISTINCT.
                $wpdb->tournaments_hunts[] = array(
                        'hunt_id'       => 1,
                        'tournament_id' => 5,
                );

                $wpdb->hunt_winners[] = array(
                        'id'         => 10,
                        'hunt_id'    => 1,
                        'user_id'    => 50,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => '2025-01-02 10:00:00',
                );

                $wpdb->users_data = array(
                        50 => array( 'user_login' => 'distinct_winner' ),
                );

                $result = $this->leaderboard->invoke(
                        $this->shortcodes,
                        array(
                                'fields'      => array( 'wins' ),
                                'timeline'    => 'all_time',
                                'tournament_id' => 5,
                                'per_page'    => 5,
                                'paged'       => 1,
                                'orderby'     => 'wins',
                                'order'       => 'desc',
                        )
                );

                $this->assertNotEmpty( $result );
                $this->assertArrayHasKey( 'rows', $result );
                $this->assertNotEmpty( $result['rows'] );
                $this->assertSame( 1, (int) $result['rows'][0]->total_wins );
        }
}
