<?php

use PHPUnit\Framework\TestCase;

final class LeaderboardAverageRenderingTest extends TestCase {
        /**
         * @var BHG_Shortcodes
         */
        private $shortcodes;

        /**
         * @var ReflectionMethod
         */
        private $renderRows;

        protected function setUp(): void {
                if ( ! function_exists( 'bhg_t' ) ) {
                        require_once __DIR__ . '/../includes/helpers.php';
                }

                if ( ! class_exists( 'BHG_Shortcodes' ) ) {
                        require_once __DIR__ . '/../includes/class-bhg-shortcodes.php';
                }

                $this->shortcodes = new BHG_Shortcodes();
                $this->renderRows = new ReflectionMethod( $this->shortcodes, 'render_leaderboard_rows' );
                $this->renderRows->setAccessible( true );
        }

        public function test_leaderboard_rows_use_zero_decimal_averages(): void {
                $rows = array(
                        (object) array(
                                'user_id'            => 42,
                                'user_login'         => 'decimal_player',
                                'total_wins'         => 7,
                                'avg_hunt_pos'       => 1.75,
                                'avg_tournament_pos' => 3.2,
                        ),
                );

                $html = $this->renderRows->invoke(
                        $this->shortcodes,
                        $rows,
                        array( 'pos', 'user', 'wins', 'avg_hunt', 'avg_tournament' ),
                        0,
                        false
                );

                $this->assertStringContainsString( '<td>1</td>', $html );
                $this->assertStringContainsString( '<td>Decimal_player</td>', $html );
                $this->assertStringContainsString( '<td>7</td>', $html );
                $this->assertStringContainsString( '<td>2</td>', $html );
                $this->assertStringContainsString( '<td>3</td>', $html );
        }
}
