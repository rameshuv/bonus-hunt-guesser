<?php

use PHPUnit\Framework\TestCase;

final class LeaderboardResultsPaginationTest extends TestCase {
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

                $this->wpdb = new MockWPDB();
                $wpdb       = $this->wpdb;

                $this->shortcodes = new BHG_Shortcodes();
                $ref_class        = new ReflectionClass( $this->shortcodes );
                $this->tournamentLeaderboard = $ref_class->getMethod( 'run_tournament_results_leaderboard' );
                $this->tournamentLeaderboard->setAccessible( true );
        }

        public function test_tournament_leaderboard_returns_multiple_rows(): void {
                global $wpdb;

                $wpdb->tournament_results = array(
                        array(
                                'user_id'       => 501,
                                'tournament_id' => 77,
                                'wins'          => 5,
                                'last_win_date' => '2024-01-05 00:00:00',
                        ),
                        array(
                                'user_id'       => 502,
                                'tournament_id' => 77,
                                'wins'          => 3,
                                'last_win_date' => '2024-01-04 00:00:00',
                        ),
                        array(
                                'user_id'       => 503,
                                'tournament_id' => 77,
                                'wins'          => 2,
                                'last_win_date' => '2024-01-03 00:00:00',
                        ),
                );

                $wpdb->users_data = array(
                        501 => array( 'user_login' => 'alpha_player' ),
                        502 => array( 'user_login' => 'bravo_player' ),
                        503 => array( 'user_login' => 'charlie_player' ),
                );

                $result = $this->tournamentLeaderboard->invoke(
                        $this->shortcodes,
                        array(
                                'tournament_id' => 77,
                                'per_page'      => 10,
                                'paged'         => 1,
                                'orderby'       => 'wins',
                                'order'         => 'desc',
                        )
                );

                $this->assertNotNull( $result );
                $this->assertArrayHasKey( 'rows', $result );
                $this->assertGreaterThanOrEqual( 3, $result['total'] );
                $this->assertCount( 3, $result['rows'] );
        }
}
