<?php

use PHPUnit\Framework\TestCase;

final class LeaderboardFiltersTest extends TestCase {
        private $shortcodes;
        private $normalizer;
        private $defaultFilters;

        protected function setUp(): void {
                if ( ! class_exists( 'BHG_Shortcodes' ) ) {
                        require_once __DIR__ . '/../includes/class-bhg-shortcodes.php';
                }

                $this->shortcodes = new BHG_Shortcodes();

                $ref_class            = new ReflectionClass( $this->shortcodes );
                $this->normalizer     = $ref_class->getMethod( 'normalize_leaderboard_filters' );
                $this->defaultFilters = $ref_class->getConstant( 'LEADERBOARD_DEFAULT_FILTERS' );

                $this->normalizer->setAccessible( true );
        }

        private function normalize( $value ) {
                return $this->normalizer->invoke( $this->shortcodes, $value );
        }

        public function test_null_returns_null(): void {
                $this->assertNull( $this->normalize( null ) );
        }

        public function test_empty_string_disables_filters(): void {
                $this->assertSame( array(), $this->normalize( '' ) );
        }

        public function test_none_keyword_disables_filters(): void {
                $this->assertSame( array(), $this->normalize( 'none' ) );
                $this->assertSame( array(), $this->normalize( 'false' ) );
        }

        public function test_default_keyword_restores_defaults(): void {
                $this->assertSame( $this->defaultFilters, $this->normalize( 'default' ) );
                $this->assertSame( $this->defaultFilters, $this->normalize( 'all' ) );
        }

        public function test_normalizes_affiliate_variants(): void {
                $expected = array( 'timeline', 'affiliate', 'site' );
                $this->assertSame( $expected, $this->normalize( 'timeline, affiliate status, affiliate-site' ) );
        }

        public function test_normalizes_array_input(): void {
                $expected = array( 'tournament', 'timeline' );
                $this->assertSame( $expected, $this->normalize( array( 'tournaments', 'timeline' ) ) );
        }
}
