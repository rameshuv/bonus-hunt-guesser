<?php
/**
 * Notification helpers for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! function_exists( 'bhg_get_notification_section_defaults' ) ) {
        /**
         * Default values for a single notification section.
         *
         * @return array
         */
        function bhg_get_notification_section_defaults() {
                return array(
                        'enabled'     => 0,
                        'title'       => '',
                        'description' => '',
                        'bcc'         => array(),
                );
        }
}

if ( ! function_exists( 'bhg_get_notification_defaults' ) ) {
        /**
         * Default values for all notification sections.
         *
         * @return array
         */
        function bhg_get_notification_defaults() {
                return array(
                        'winners'    => bhg_get_notification_section_defaults(),
                        'tournament' => bhg_get_notification_section_defaults(),
                        'bonus_hunt' => bhg_get_notification_section_defaults(),
                );
        }
}

if ( ! function_exists( 'bhg_prepare_notification_section' ) ) {
        /**
         * Sanitize and normalize a notification section payload.
         *
         * @param array $section Raw section data.
         * @return array Normalized section data.
         */
        function bhg_prepare_notification_section( $section ) {
                $defaults = bhg_get_notification_section_defaults();
                $section  = is_array( $section ) ? $section : array();
                $section  = wp_parse_args( $section, $defaults );

                $section['enabled'] = ! empty( $section['enabled'] ) ? 1 : 0;

                $title = isset( $section['title'] ) ? $section['title'] : '';
                if ( is_array( $title ) ) {
                        $title = '';
                }
                $section['title'] = sanitize_text_field( wp_unslash( (string) $title ) );

                $description = isset( $section['description'] ) ? $section['description'] : '';
                if ( is_array( $description ) ) {
                        $description = '';
                }
                $description              = wp_unslash( (string) $description );
                $section['description']   = '' === $description ? '' : wp_kses_post( $description );

                $bcc_raw = isset( $section['bcc'] ) ? $section['bcc'] : array();
                if ( is_string( $bcc_raw ) ) {
                        $bcc_raw = preg_split( '/[\s,]+/', $bcc_raw );
                }

                if ( ! is_array( $bcc_raw ) ) {
                        $bcc_raw = array();
                }

                $bcc = array();
                foreach ( $bcc_raw as $email ) {
                        if ( ! is_string( $email ) && ! is_numeric( $email ) ) {
                                continue;
                        }

                        $email = sanitize_email( wp_unslash( (string) $email ) );
                        if ( $email && is_email( $email ) ) {
                                $bcc[ $email ] = $email;
                        }
                }

                $section['bcc'] = array_values( $bcc );

                return $section;
        }
}

if ( ! function_exists( 'bhg_normalize_notifications_settings' ) ) {
        /**
         * Merge notification settings with defaults.
         *
         * @param array $settings Raw settings data.
         * @return array Normalized settings.
         */
        function bhg_normalize_notifications_settings( $settings ) {
                $defaults = bhg_get_notification_defaults();
                $settings = is_array( $settings ) ? $settings : array();

                foreach ( $defaults as $key => $default ) {
                        $current           = isset( $settings[ $key ] ) ? $settings[ $key ] : array();
                        $defaults[ $key ] = bhg_prepare_notification_section( $current );
                }

                return $defaults;
        }
}

if ( ! function_exists( 'bhg_get_notifications_settings' ) ) {
        /**
         * Retrieve stored notification settings combined with defaults.
         *
         * @return array
         */
        function bhg_get_notifications_settings() {
                $option = get_option( 'bhg_plugin_settings', array() );

                $notifications = array();
                if ( is_array( $option ) && isset( $option['notifications'] ) && is_array( $option['notifications'] ) ) {
                        $notifications = $option['notifications'];
                }

                return bhg_normalize_notifications_settings( $notifications );
        }
}

if ( ! function_exists( 'bhg_render_notification_template' ) ) {
        /**
         * Replace placeholders within a notification template.
         *
         * @param string $template    Template string.
         * @param array  $replacements Placeholder replacements.
         * @return string
         */
        function bhg_render_notification_template( $template, array $replacements ) {
                if ( '' === $template ) {
                        return '';
                }

                return strtr( $template, $replacements );
        }
}

if ( ! function_exists( 'bhg_get_notification_tokens' ) ) {
        /**
         * Return the supported notification placeholders.
         *
         * @return string[]
         */
        function bhg_get_notification_tokens() {
                return array( '{{username}}', '{{hunt}}', '{{final}}', '{{winner}}', '{{winners}}' );
        }
}
