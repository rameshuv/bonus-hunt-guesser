<?php

use PHPUnit\Framework\TestCase;

final class RankingResultsTest extends TestCase {
        /**
         * @var MockWPDB
         */
        private $wpdb;

        protected function setUp(): void {
                global $wpdb;

                if ( ! class_exists( 'MockWPDB' ) ) {
                        require_once __DIR__ . '/support/class-mock-wpdb.php';
                }

                if ( ! function_exists( 'bhg_log' ) ) {
                        require_once __DIR__ . '/../includes/helpers.php';
                }

                if ( ! class_exists( 'BHG_Models' ) ) {
                        require_once __DIR__ . '/../includes/class-bhg-models.php';
                }

                $this->wpdb = new MockWPDB();
                $wpdb       = $this->wpdb;
        }

        public function test_recalculate_tournament_results_tracks_points_and_wins(): void {
                global $wpdb;

                $wpdb->tournaments[1] = array(
                        'id'                => 1,
                        'participants_mode' => 'winners',
                        'ranking_scope'     => 'all',
                        'points_map'        => wp_json_encode( array( 1 => 25, 2 => 15, 3 => 10 ) ),
                );

                $wpdb->bonus_hunts[1] = array(
                        'id'            => 1,
                        'status'        => 'closed',
                        'winners_count' => 2,
                        'closed_at'     => '2024-02-01 00:00:00',
                        'updated_at'    => null,
                        'created_at'    => '2024-01-10 00:00:00',
                        'tournament_id' => 1,
                );
                $wpdb->bonus_hunts[2] = array(
                        'id'            => 2,
                        'status'        => 'closed',
                        'winners_count' => 2,
                        'closed_at'     => '2024-03-01 00:00:00',
                        'updated_at'    => null,
                        'created_at'    => '2024-02-10 00:00:00',
                        'tournament_id' => 1,
                );

                $wpdb->tournaments_hunts[] = array(
                        'hunt_id'       => 1,
                        'tournament_id' => 1,
                );
                $wpdb->tournaments_hunts[] = array(
                        'hunt_id'       => 2,
                        'tournament_id' => 1,
                );

                $wpdb->hunt_winners[] = array(
                        'id'         => 1,
                        'hunt_id'    => 1,
                        'user_id'    => 10,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => '2024-02-01 10:00:00',
                );
                $wpdb->hunt_winners[] = array(
                        'id'         => 2,
                        'hunt_id'    => 1,
                        'user_id'    => 11,
                        'position'   => 2,
                        'eligible'   => 1,
                        'created_at' => '2024-02-01 11:00:00',
                );
                // Beyond winners_count and should be ignored under winners mode.
                $wpdb->hunt_winners[] = array(
                        'id'         => 3,
                        'hunt_id'    => 1,
                        'user_id'    => 12,
                        'position'   => 3,
                        'eligible'   => 1,
                        'created_at' => '2024-02-01 12:00:00',
                );

                $wpdb->hunt_winners[] = array(
                        'id'         => 4,
                        'hunt_id'    => 2,
                        'user_id'    => 10,
                        'position'   => 2,
                        'eligible'   => 1,
                        'created_at' => '2024-03-01 09:00:00',
                );
                $wpdb->hunt_winners[] = array(
                        'id'         => 5,
                        'hunt_id'    => 2,
                        'user_id'    => 13,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => '2024-03-01 08:00:00',
                );

                BHG_Models::recalculate_tournament_results( array( 1 ) );

                $this->assertCount( 3, $wpdb->tournament_results );

                $first = $wpdb->tournament_results[0];
                $second = $wpdb->tournament_results[1];
                $third = $wpdb->tournament_results[2];

                $this->assertSame( 10, $first['user_id'] );
                $this->assertSame( 2, $first['wins'] );
                $this->assertSame( 40, $first['points'] );
                $this->assertSame( '2024-03-01 09:00:00', $first['last_win_date'] );

                $this->assertSame( 13, $second['user_id'] );
                $this->assertSame( 1, $second['wins'] );
                $this->assertSame( 25, $second['points'] );

                $this->assertSame( 11, $third['user_id'] );
                $this->assertSame( 1, $third['wins'] );
                $this->assertSame( 15, $third['points'] );
        }

        public function test_ranking_scope_closed_excludes_open_hunts(): void {
                global $wpdb;

                $wpdb->tournaments[2] = array(
                        'id'                => 2,
                        'participants_mode' => 'winners',
                        'ranking_scope'     => 'closed',
                        'points_map'        => wp_json_encode( array( 1 => 25, 2 => 15 ) ),
                );

                $wpdb->bonus_hunts[3] = array(
                        'id'            => 3,
                        'status'        => 'open',
                        'winners_count' => 2,
                        'closed_at'     => null,
                        'updated_at'    => null,
                        'created_at'    => '2024-04-01 00:00:00',
                        'tournament_id' => 2,
                );
                $wpdb->bonus_hunts[4] = array(
                        'id'            => 4,
                        'status'        => 'closed',
                        'winners_count' => 2,
                        'closed_at'     => '2024-04-05 00:00:00',
                        'updated_at'    => null,
                        'created_at'    => '2024-03-15 00:00:00',
                        'tournament_id' => 2,
                );

                $wpdb->tournaments_hunts[] = array(
                        'hunt_id'       => 3,
                        'tournament_id' => 2,
                );
                $wpdb->tournaments_hunts[] = array(
                        'hunt_id'       => 4,
                        'tournament_id' => 2,
                );

                $wpdb->hunt_winners[] = array(
                        'id'         => 6,
                        'hunt_id'    => 3,
                        'user_id'    => 20,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => '2024-04-02 10:00:00',
                );
                $wpdb->hunt_winners[] = array(
                        'id'         => 7,
                        'hunt_id'    => 4,
                        'user_id'    => 21,
                        'position'   => 1,
                        'eligible'   => 1,
                        'created_at' => '2024-04-05 11:00:00',
                );

                BHG_Models::recalculate_tournament_results( array( 2 ) );

                $this->assertCount( 1, $wpdb->tournament_results );
                $this->assertSame( 21, $wpdb->tournament_results[0]['user_id'] );
                $this->assertSame( 25, $wpdb->tournament_results[0]['points'] );
        }
}
