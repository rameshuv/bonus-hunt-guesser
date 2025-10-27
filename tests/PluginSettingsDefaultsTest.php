<?php

use PHPUnit\Framework\TestCase;

final class PluginSettingsDefaultsTest extends TestCase {
    protected function setUp(): void {
        global $bhg_test_options;
        $bhg_test_options = array();
        delete_option( 'bhg_plugin_settings' );
    }

    public function test_defaults_returned_when_option_missing(): void {
        delete_option( 'bhg_plugin_settings' );

        $settings = bhg_get_plugin_settings();

        $this->assertSame( 'monthly', $settings['default_tournament_period'] );
        $this->assertSame( 'grid', $settings['prize_layout'] );
        $this->assertSame( 100000, $settings['max_guess_amount'] );
        $this->assertSame( 'admin@example.com', $settings['email_from'] );
        $this->assertSame( bhg_get_default_tournament_points_map(), $settings['tournament_points'] );
    }

    public function test_existing_values_preserved_and_missing_keys_added(): void {
        update_option(
            'bhg_plugin_settings',
            array(
                'min_guess_amount' => 25,
                'prize_layout'     => 'carousel',
                'email_from'       => 'not-an-email',
            )
        );

        $settings = bhg_get_plugin_settings();

        $this->assertSame( 25.0, (float) $settings['min_guess_amount'] );
        $this->assertSame( 'carousel', $settings['prize_layout'] );
        $this->assertSame( 'admin@example.com', $settings['email_from'] );
        $this->assertSame( bhg_get_default_tournament_points_map(), $settings['tournament_points'] );
        $this->assertArrayHasKey( 'ads_enabled', $settings );
    }
}
