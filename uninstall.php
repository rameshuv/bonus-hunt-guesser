<?php
			/**
			 * Uninstall script for Bonus Hunt Guesser.
			 *
			 * @package Bonus_Hunt_Guesser
			 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

			// Delete plugin options.
                        delete_option( 'bhg_version' );
                        delete_option( 'bhg_plugin_settings' );
                        delete_option( 'bhg_notifications_settings' );
                        delete_option( 'bhg_tables_created' );
                        delete_option( 'bhg_last_migrated_version' );
                        delete_option( 'bhg_cache_versions' );
                        delete_option( 'bhg_demo_notice' );

                        delete_site_option( 'bhg_version' );
                        delete_site_option( 'bhg_plugin_settings' );
                        delete_site_option( 'bhg_notifications_settings' );
                        delete_site_option( 'bhg_tables_created' );
                        delete_site_option( 'bhg_last_migrated_version' );
                        delete_site_option( 'bhg_cache_versions' );
                        delete_site_option( 'bhg_demo_notice' );

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
                                'bhg_hunt_tournaments',
                                'bhg_hunt_winners',
                        );

			foreach ( $tables as $table ) {
					$table_name = esc_sql( $wpdb->prefix . $table );
					$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // db call ok; no-cache ok.
			}
