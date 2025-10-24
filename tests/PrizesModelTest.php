<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PrizesModelTest extends TestCase {
    public function test_categories_match_customer_request(): void {
        $expected = array(
            'cash_money',
            'casino_money',
            'coupons',
            'merchandise',
            'various',
        );

        $this->assertSame($expected, BHG_Prizes::get_categories());
    }

    public function test_default_css_settings_expose_required_keys(): void {
        $defaults = BHG_Prizes::default_css_settings();

        $this->assertSame(
            array(
                'border',
                'border_color',
                'padding',
                'margin',
                'background',
            ),
            array_keys($defaults)
        );

        foreach ($defaults as $value) {
            $this->assertSame('', $value);
        }
    }
}
