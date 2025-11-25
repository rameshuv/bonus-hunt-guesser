<?php

use PHPUnit\Framework\TestCase;

final class ShortcodeListRenderingTest extends TestCase {
        /** @var MockWPDB */
        private $wpdb;

        /** @var BHG_Shortcodes */
        private $shortcodes;

        protected function setUp(): void {
                global $wpdb, $shortcode_tags;

                $shortcode_tags = array();

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

                $this->wpdb->set_table_exists( 'wp_bhg_hunt_winners' );
                $this->wpdb->set_table_exists( 'wp_bhg_bonus_hunts' );
                $this->wpdb->set_table_exists( 'wp_bhg_tournaments' );
                $this->wpdb->set_table_exists( 'wp_bhg_tournaments_hunts' );

                $this->shortcodes = new BHG_Shortcodes();
        }

        public function test_latest_winners_empty_state_renders_message(): void {
                $output = $this->shortcodes->latest_winners_list_shortcode( array( 'empty' => 'Nothing yet' ) );

                $this->assertStringContainsString( 'bhg-latest-winners-empty', $output );
                $this->assertStringContainsString( 'Nothing yet', $output );
        }

        public function test_tournament_list_renders_rows(): void {
                $this->wpdb->tournaments = array(
                        1 => array(
                                'id'         => 1,
                                'title'      => 'Spring Cup',
                                'start_date' => '2024-01-10',
                                'end_date'   => '2024-01-20',
                                'status'     => 'active',
                        ),
                        2 => array(
                                'id'         => 2,
                                'title'      => 'Winter Cup',
                                'start_date' => '2023-12-01',
                                'end_date'   => '2023-12-10',
                                'status'     => 'closed',
                        ),
                );

                $output = $this->shortcodes->tournament_list_shortcode(
                        array(
                                'limit'  => 2,
                                'status' => 'all',
                                'order'  => 'asc',
                        )
                );

                $this->assertStringContainsString( 'bhg-tournament-list', $output );
                $this->assertStringContainsString( 'Spring Cup', $output );
                $this->assertStringContainsString( 'Winter Cup', $output );
        }

        public function test_bonushunt_list_renders_rows(): void {
                $this->wpdb->bonus_hunts = array(
                        1 => array(
                                'id'               => 1,
                                'title'            => 'High Roller',
                                'starting_balance' => 1000,
                                'final_balance'    => 1500,
                                'winners_count'    => 2,
                                'status'           => 'open',
                                'created_at'       => '2024-02-01 00:00:00',
                        ),
                        2 => array(
                                'id'               => 2,
                                'title'            => 'Finale',
                                'starting_balance' => 500,
                                'final_balance'    => 750,
                                'winners_count'    => 1,
                                'status'           => 'closed',
                                'created_at'       => '2024-01-01 00:00:00',
                        ),
                );

                $output = $this->shortcodes->bonushunt_list_shortcode(
                        array(
                                'limit'  => 2,
                                'status' => 'all',
                                'order'  => 'asc',
                        )
                );

                $this->assertStringContainsString( 'bhg-bonushunt-list', $output );
                $this->assertStringContainsString( 'High Roller', $output );
                $this->assertStringContainsString( 'Finale', $output );
        }
}
