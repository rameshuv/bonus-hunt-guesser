<?php

use PHPUnit\Framework\TestCase;

final class PrizesShortcodeRenderingTest extends TestCase {
    /**
     * Instance under test.
     *
     * @var BHG_Shortcodes
     */
    private $shortcodes;

    protected function setUp(): void {
        $this->shortcodes = new BHG_Shortcodes();
    }

    public function test_render_prize_section_grid_outputs_cards(): void {
        $method = new ReflectionMethod( BHG_Shortcodes::class, 'render_prize_section' );
        $method->setAccessible( true );

        $prize = (object) array(
            'title'          => 'Test Prize',
            'description'    => 'Line one.' . "\n\n" . 'Line two.',
            'category'       => 'cash_money',
            'css_border'     => '1px solid #000',
            'css_border_color' => '#000000',
            'css_padding'    => '10px',
            'css_margin'     => '5px',
            'css_background' => '#ffffff',
            'image_small'    => 0,
            'image_medium'   => 0,
            'image_large'    => 0,
        );

        $output = $method->invoke( $this->shortcodes, array( $prize ), 'grid', 'medium' );

        $this->assertStringContainsString( 'bhg-prizes-layout-grid', $output );
        $this->assertStringContainsString( 'bhg-prize-card', $output );
        $this->assertStringContainsString( 'bhg-prize-no-image', $output );
        $this->assertStringContainsString( '<p>Line two.</p>', $output );
    }

    public function test_render_prize_section_carousel_outputs_navigation(): void {
        $method = new ReflectionMethod( BHG_Shortcodes::class, 'render_prize_section' );
        $method->setAccessible( true );

        $prizes = array(
            (object) array(
                'title'        => 'First',
                'description'  => '',
                'category'     => 'various',
                'css_border'   => '',
                'css_border_color' => '',
                'css_padding'  => '',
                'css_margin'   => '',
                'css_background' => '',
                'image_small'  => 0,
                'image_medium' => 0,
                'image_large'  => 0,
            ),
            (object) array(
                'title'        => 'Second',
                'description'  => '',
                'category'     => 'various',
                'css_border'   => '',
                'css_border_color' => '',
                'css_padding'  => '',
                'css_margin'   => '',
                'css_background' => '',
                'image_small'  => 0,
                'image_medium' => 0,
                'image_large'  => 0,
            ),
        );

        $output = $method->invoke( $this->shortcodes, $prizes, 'carousel', 'small' );

        $this->assertStringContainsString( 'bhg-prizes-layout-carousel', $output );
        $this->assertStringContainsString( 'bhg-prize-prev', $output );
        $this->assertStringContainsString( 'bhg-prize-next', $output );
        $this->assertStringContainsString( 'bhg-prize-dots', $output );
        $this->assertSame( 2, substr_count( $output, 'bhg-prize-card' ) );
    }
}
