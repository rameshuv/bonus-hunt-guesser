<?php

use PHPUnit\Framework\TestCase;

final class ShortcodesRegistrationTest extends TestCase {
        protected function setUp(): void {
                global $shortcode_tags, $wpdb, $bhg_test_actions_done;

                $shortcode_tags        = array();
                $bhg_test_actions_done = array();

                if ( ! class_exists( 'MockWPDB' ) ) {
                        require_once __DIR__ . '/support/class-mock-wpdb.php';
                }

                $wpdb = new MockWPDB();
                $wpdb->set_table_exists( 'wp_usermeta' );
        }

        public function test_homepage_list_shortcodes_are_registered(): void {
                if ( ! class_exists( 'BHG_Shortcodes' ) ) {
                        require_once __DIR__ . '/../includes/class-bhg-shortcodes.php';
                }

                $instance = new BHG_Shortcodes();
                $instance->register_shortcodes();

                $expected = array(
                        'latest-winners-list',
                        'leaderboard-list',
                        'tournament-list',
                        'bonushunt-list',
                );

                foreach ( $expected as $tag ) {
                        $this->assertTrue(
                                shortcode_exists( $tag ),
                                sprintf( 'Shortcode [%s] should be registered.', $tag )
                        );
                }
        }
}
