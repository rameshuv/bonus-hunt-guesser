<?php

use PHPUnit\Framework\TestCase;

final class NotificationsSettingsTest extends TestCase {
    protected function setUp(): void {
        global $bhg_test_options;

        $bhg_test_options = array();
    }

    public function test_prepare_notification_section_sanitizes_data(): void {
        $input = array(
            'enabled'     => 'on',
            'title'       => " Results <script>alert(1)</script> ",
            'description' => '<p>Line<script>alert(1)</script></p>',
            'bcc'         => 'first@example.com, invalid-email, Second@Example.com  ',
        );

        $section = bhg_prepare_notification_section( $input );

        $this->assertSame( 1, $section['enabled'] );
        $this->assertSame( 'Results alert(1)', $section['title'] );
        $this->assertSame( '<p>Line</p>', $section['description'] );
        $this->assertSame( array( 'first@example.com', 'second@example.com' ), $section['bcc'] );
    }

    public function test_get_notifications_settings_merges_defaults(): void {
        global $bhg_test_options;

        $bhg_test_options['bhg_plugin_settings'] = array(
            'notifications' => array(
                'bonus_hunt' => array(
                    'enabled'     => 1,
                    'title'       => 'Congrats {{username}}',
                    'description' => '<p>Test</p>',
                    'bcc'         => array( 'ADMIN@EXAMPLE.COM', 'bad-email', 'admin@example.com' ),
                ),
            ),
        );

        $settings = bhg_get_notifications_settings();

        $this->assertSame( 1, $settings['bonus_hunt']['enabled'] );
        $this->assertSame( 'Congrats {{username}}', $settings['bonus_hunt']['title'] );
        $this->assertSame( '<p>Test</p>', $settings['bonus_hunt']['description'] );
        $this->assertSame( array( 'admin@example.com' ), $settings['bonus_hunt']['bcc'] );
        $this->assertArrayHasKey( 'winners', $settings );
        $this->assertSame( 0, $settings['winners']['enabled'] );
    }
}
