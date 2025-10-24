<?php

use PHPUnit\Framework\TestCase;

final class PrizesShortcodeNormalizationTest extends TestCase {
    /**
     * Instance under test.
     *
     * @var BHG_Shortcodes
     */
    private $shortcodes;

    protected function setUp(): void {
        $this->shortcodes = new BHG_Shortcodes();
    }

    /**
     * @dataProvider layoutProvider
     */
    public function test_normalize_prize_layout_aliases( string $input, string $expected ): void {
        $method = new ReflectionMethod( BHG_Shortcodes::class, 'normalize_prize_layout' );
        $method->setAccessible( true );

        $this->assertSame( $expected, $method->invoke( $this->shortcodes, $input ) );
    }

    /**
     * @return array<string, array{0:string,1:string}>
     */
    public function layoutProvider(): array {
        return array(
            'legacy caroussel spelling' => array( 'caroussel', 'carousel' ),
            'horizontal keyword'        => array( 'horizontal', 'carousel' ),
            'grid alias list'           => array( 'list', 'grid' ),
            'fallback to default'       => array( 'unknown', 'grid' ),
        );
    }

    /**
     * @dataProvider sizeProvider
     */
    public function test_normalize_prize_size_aliases( string $input, string $expected ): void {
        $method = new ReflectionMethod( BHG_Shortcodes::class, 'normalize_prize_size' );
        $method->setAccessible( true );

        $this->assertSame( $expected, $method->invoke( $this->shortcodes, $input ) );
    }

    /**
     * @return array<string, array{0:string,1:string}>
     */
    public function sizeProvider(): array {
        return array(
            'thumbnail alias' => array( 'thumbnail', 'small' ),
            'short small'     => array( 'sm', 'small' ),
            'large alias'     => array( 'lg', 'big' ),
            'fallback'        => array( 'huge', 'medium' ),
        );
    }
}
