<?php

use PHPUnit\Framework\TestCase;

final class LeaderboardAffiliateSiteResolutionTest extends TestCase {
        private $shortcodes;
        private $resolver;

        protected function setUp(): void {
                global $wpdb;

                if ( ! class_exists( 'MockWPDB' ) ) {
                        require_once __DIR__ . '/support/class-mock-wpdb.php';
                }

                if ( ! class_exists( 'BHG_Shortcodes' ) ) {
                        require_once __DIR__ . '/../includes/class-bhg-shortcodes.php';
                }

                $wpdb = new MockWPDB();
                $wpdb->set_table_exists( 'wp_bhg_affiliate_websites' );
                $wpdb->affiliate_websites[] = array(
                        'id'   => 7,
                        'slug' => 'moderators',
                        'name' => 'Moderators',
                );

                $this->shortcodes = new BHG_Shortcodes();
                $ref_class        = new ReflectionClass( $this->shortcodes );
                $this->resolver   = $ref_class->getMethod( 'resolve_affiliate_site_id' );
                $this->resolver->setAccessible( true );
        }

        public function test_slug_resolution_falls_back_to_name_lookup(): void {
                $resolved = $this->resolver->invoke( $this->shortcodes, 'moderators' );

                $this->assertSame( 7, $resolved );
        }
}
