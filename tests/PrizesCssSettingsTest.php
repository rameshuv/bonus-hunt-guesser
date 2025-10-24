<?php

use PHPUnit\Framework\TestCase;

final class PrizesCssSettingsTest extends TestCase {
    public function test_sanitize_css_settings_allows_keywords_and_functions(): void {
        $settings = BHG_Prizes::sanitize_css_settings(
            array(
                'border'       => '1px solid #FFF',
                'border_color' => 'transparent',
                'padding'      => '10px 5px',
                'margin'       => '0 auto',
                'background'   => 'rgba(255, 0, 0, 0.5)',
            )
        );

        $this->assertSame(
            array(
                'border'       => '1px solid #FFF',
                'border_color' => 'transparent',
                'padding'      => '10px 5px',
                'margin'       => '0 auto',
                'background'   => 'rgba(255,0,0,0.5)',
            ),
            $settings
        );
    }

    public function test_sanitize_css_settings_allows_css_variables(): void {
        $settings = BHG_Prizes::sanitize_css_settings(
            array(
                'background' => 'var(--Accent)',
                'border_color' => 'var(--Accent)',
            )
        );

        $this->assertSame('var(--Accent)', $settings['background']);
        $this->assertSame('var(--Accent)', $settings['border_color']);
    }

    public function test_sanitize_css_settings_rejects_invalid_values(): void {
        $settings = BHG_Prizes::sanitize_css_settings(
            array(
                'background'   => 'url(javascript:alert(1))',
                'border_color' => '<script>alert(1)</script>',
            )
        );

        $this->assertSame('', $settings['background']);
        $this->assertSame('', $settings['border_color']);
    }

    public function test_build_style_attr_uses_sanitized_colors(): void {
        $prize = (object) array(
            'css_border'        => '2px solid #ABCDEF',
            'css_border_color'  => 'rgba(0, 0, 0, 0.25)',
            'css_padding'       => '12px',
            'css_margin'        => '<script>0</script>',
            'css_background'    => 'var(--primary)',
        );

        $style = BHG_Prizes::build_style_attr( $prize );

        $this->assertStringContainsString('border:2px solid #ABCDEF', $style);
        $this->assertStringContainsString('border-color:rgba(0,0,0,0.25)', $style);
        $this->assertStringNotContainsString('script', $style);
        $this->assertStringContainsString('background-color:var(--primary)', $style);
    }
}
