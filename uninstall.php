<?php
			/**
			 * Uninstall script for Bonus Hunt Guesser.
			 *
			 * @package Bonus_Hunt_Guesser
			 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        exit;
}

                        $settings    = get_option( 'bhg_plugin_settings', array() );
                        $remove_data = isset( $settings['remove_data_on_uninstall'] ) && (int) $settings['remove_data_on_uninstall'] === 1;

                        if ( ! $remove_data ) {
                                return;
                        }

                        // Delete plugin options.
                        delete_option( 'bhg_version' );
                        delete_option( 'bhg_plugin_settings' );

                        delete_site_option( 'bhg_version' );
                        delete_site_option( 'bhg_plugin_settings' );

			global $wpdb;

                        $tables = array(
                                'bhg_bonus_hunts',
                                'bhg_guesses',
                                'bhg_tournaments',
                                'bhg_tournament_results',
                                'bhg_ads',
                                'bhg_translations',
                                'bhg_affiliate_websites',
                                'bhg_prizes',
                                'bhg_hunt_prizes',
                        );

			foreach ( $tables as $table ) {
					$table_name = esc_sql( $wpdb->prefix . $table );
					$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // db call ok; no-cache ok.
			}
