<?php
/**
 * Admin functionality for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin screens and actions for the plugin.
 */
class BHG_Admin {

	/**
	 * Initialize admin hooks and actions.
	 */
	public function __construct() {
		// Menus.
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		// Handlers.
                add_action( 'admin_post_bhg_delete_guess', array( $this, 'handle_delete_guess' ) );
                add_action( 'admin_post_bhg_save_hunt', array( $this, 'handle_save_hunt' ) );
                add_action( 'admin_post_bhg_close_hunt', array( $this, 'handle_close_hunt' ) );
                add_action( 'admin_post_bhg_delete_hunt', array( $this, 'handle_delete_hunt' ) );
                add_action( 'admin_post_bhg_toggle_guessing', array( $this, 'handle_toggle_guessing' ) );
                add_action( 'admin_post_bhg_save_ad', array( $this, 'handle_save_ad' ) );
                add_action( 'admin_post_bhg_delete_ad', array( $this, 'handle_delete_ad' ) );
                                add_action( 'admin_post_bhg_tournament_save', array( $this, 'handle_save_tournament' ) );
                                add_action( 'admin_post_bhg_tournament_delete', array( $this, 'handle_delete_tournament' ) );
               add_action( 'admin_post_bhg_tournament_close', array( $this, 'handle_close_tournament' ) );
                add_action( 'admin_post_bhg_save_affiliate', array( $this, 'handle_save_affiliate' ) );
                add_action( 'admin_post_bhg_delete_affiliate', array( $this, 'handle_delete_affiliate' ) );
                add_action( 'admin_post_bhg_save_user_meta', array( $this, 'handle_save_user_meta' ) );
                add_action( 'admin_post_bhg_tools_action', array( $this, 'handle_tools_action' ) );
        }

	/**
	 * Register admin menus and pages.
	 */
	public function menu() {
		$cap  = 'manage_options';
		$slug = 'bhg';

		add_menu_page(
			bhg_t( 'bonus_hunt', 'Bonus Hunt' ),
			bhg_t( 'bonus_hunt', 'Bonus Hunt' ),
			$cap,
			$slug,
			array( $this, 'dashboard' ),
			'dashicons-awards',
			55
		);

		add_submenu_page( $slug, bhg_t( 'menu_dashboard', 'Dashboard' ), bhg_t( 'menu_dashboard', 'Dashboard' ), $cap, $slug, array( $this, 'dashboard' ) );
		add_submenu_page( $slug, bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ), bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ), $cap, 'bhg-bonus-hunts', array( $this, 'bonus_hunts' ) );
		add_submenu_page( $slug, bhg_t( 'button_results', 'Results' ), bhg_t( 'button_results', 'Results' ), $cap, 'bhg-bonus-hunts-results', array( $this, 'bonus_hunts_results' ) );
		add_submenu_page( $slug, bhg_t( 'menu_tournaments', 'Tournaments' ), bhg_t( 'menu_tournaments', 'Tournaments' ), $cap, 'bhg-tournaments', array( $this, 'tournaments' ) );
		add_submenu_page( $slug, bhg_t( 'menu_users', 'Users' ), bhg_t( 'menu_users', 'Users' ), $cap, 'bhg-users', array( $this, 'users' ) );
		add_submenu_page( $slug, bhg_t( 'menu_affiliates', 'Affiliates' ), bhg_t( 'menu_affiliates', 'Affiliates' ), $cap, 'bhg-affiliates', array( $this, 'affiliates' ) );
		add_submenu_page( $slug, bhg_t( 'menu_advertising', 'Advertising' ), bhg_t( 'menu_advertising', 'Advertising' ), $cap, 'bhg-ads', array( $this, 'advertising' ) );
		add_submenu_page( $slug, bhg_t( 'menu_translations', 'Translations' ), bhg_t( 'menu_translations', 'Translations' ), $cap, 'bhg-translations', array( $this, 'translations' ) );
		add_submenu_page( $slug, bhg_t( 'database', 'Database' ), bhg_t( 'database', 'Database' ), $cap, 'bhg-database', array( $this, 'database' ) );
		add_submenu_page( $slug, bhg_t( 'settings', 'Settings' ), bhg_t( 'settings', 'Settings' ), $cap, 'bhg-settings', array( $this, 'settings' ) );
		add_submenu_page(
			$slug,
			bhg_t( 'bhg_tools', 'BHG Tools' ),
			bhg_t( 'bhg_tools', 'BHG Tools' ),
			$cap,
			'bhg-tools',
			array( $this, 'bhg_tools_page' )
		);

		// NOTE: By default, WordPress adds a submenu item that duplicates the
		// top-level “Bonus Hunt” menu. The previous `remove_submenu_page()`
		// call removed this submenu, but it also inadvertently removed our
		// custom “Dashboard” submenu. Removing the call ensures the Dashboard
		// item remains visible under the "Bonus Hunt" menu.
	}

		/**
		 * Enqueue admin assets on BHG screens.
		 *
		 * @param string $hook Current admin page hook.
		 */
	public function assets( $hook ) {
		if ( false !== strpos( $hook, 'bhg' ) ) {
			wp_enqueue_style(
				'bhg-admin',
				BHG_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);
						$script_path = BHG_PLUGIN_DIR . 'assets/js/admin.js';
			if ( file_exists( $script_path ) && filesize( $script_path ) > 0 ) {
					wp_enqueue_script(
						'bhg-admin',
						BHG_PLUGIN_URL . 'assets/js/admin.js',
						array( 'jquery' ),
						defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
						true
					);
			}

                       if ( false !== strpos( $hook, 'bhg-bonus-hunts-results' ) ) {
                                       wp_enqueue_script(
                                               'bhg-admin-results',
                                               BHG_PLUGIN_URL . 'assets/js/admin-results.js',
                                               array(),
                                               BHG_VERSION,
                                               true
                                       );
                                       wp_localize_script(
                                               'bhg-admin-results',
                                               'bhgResults',
                                               array(
                                                       'base_url' => admin_url( 'admin.php?page=bhg-bonus-hunts-results' ),
                                               )
                                       );
                       }
               }
       }

	// -------------------- Views --------------------
	/**
	 * Render the dashboard page.
	 */
	public function dashboard() {
		require BHG_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render the bonus hunts page.
	 */
	public function bonus_hunts() {
		require BHG_PLUGIN_DIR . 'admin/views/bonus-hunts.php';
	}

	/**
	 * Render the bonus hunts results page.
	 */
	public function bonus_hunts_results() {
		require BHG_PLUGIN_DIR . 'admin/views/bonus-hunts-results.php';
	}

	/**
	 * Render the tournaments page.
	 */
	public function tournaments() {
		require BHG_PLUGIN_DIR . 'admin/views/tournaments.php';
	}

	/**
	 * Render the users page.
	 */
	public function users() {
		require BHG_PLUGIN_DIR . 'admin/views/users.php';
	}

	/**
	 * Render the affiliates management page.
	 */
	public function affiliates() {
		$view = BHG_PLUGIN_DIR . 'admin/views/affiliate-websites.php';
		if ( file_exists( $view ) ) {
			require $view; } else {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'menu_affiliates', 'Affiliates' ) ) . '</h1><p>' . esc_html( bhg_t( 'affiliate_management_ui_not_provided_yet', 'Affiliate management UI not provided yet.' ) ) . '</p></div>'; }
	}
	/**
	 * Render the advertising page.
	 */
	public function advertising() {
		require BHG_PLUGIN_DIR . 'admin/views/advertising.php';
	}

	/**
	 * Render the translations page.
	 */
	public function translations() {
		$view = BHG_PLUGIN_DIR . 'admin/views/translations.php';
		if ( file_exists( $view ) ) {
			require $view; } else {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'menu_translations', 'Translations' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_translations_ui_found', 'No translations UI found.' ) ) . '</p></div>'; }
	}
        /**
         * Render the database maintenance page.
         */
        public function database() {
                require_once BHG_PLUGIN_DIR . 'includes/admin-database-tools.php';

                $view = BHG_PLUGIN_DIR . 'admin/views/database.php';
                if ( file_exists( $view ) ) {
                        require $view;
                } else {
                        echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'database', 'Database' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_database_ui_found', 'No database UI found.' ) ) . '</p></div>';
                }
        }
	/**
	 * Render the settings page.
	 */
	public function settings() {
		$view = BHG_PLUGIN_DIR . 'admin/views/settings.php';
		if ( file_exists( $view ) ) {
			require $view; } else {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'settings', 'Settings' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_settings_ui_found', 'No settings UI found.' ) ) . '</p></div>'; }
	}
		/**
		 * Render the tools page.
		 */
	public function bhg_tools_page() {
			$view = BHG_PLUGIN_DIR . 'admin/views/tools.php';
		if ( file_exists( $view ) ) {
				require $view;
		} else {
					echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'bhg_tools', 'BHG Tools' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_tools_ui_found', 'No tools UI found.' ) ) . '</p></div>';
		}
	}

	// -------------------- Handlers --------------------

	/**
	 * Handle deletion of a guess from the admin screen.
	 */
	public function handle_delete_guess() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_delete_guess', 'bhg_delete_guess_nonce' );
		global $wpdb;
                                                                                                                        $guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$guess_id      = isset( $_POST['guess_id'] ) ? absint( wp_unslash( $_POST['guess_id'] ) ) : 0;
		if ( $guess_id ) {
			$wpdb->delete( $guesses_table, array( 'id' => $guess_id ), array( '%d' ) );
		}
				$referer = wp_get_referer();
								wp_safe_redirect( $referer ? $referer : BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) );
		exit;
	}

	/**
	 * Handle creation and updating of a bonus hunt.
	 */
	public function handle_save_hunt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_save_hunt', 'bhg_save_hunt_nonce' );
		global $wpdb;
                $hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

		$id                    = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$title                 = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$starting              = isset( $_POST['starting_balance'] ) ? floatval( wp_unslash( $_POST['starting_balance'] ) ) : 0;
		$num_bonuses           = isset( $_POST['num_bonuses'] ) ? absint( wp_unslash( $_POST['num_bonuses'] ) ) : 0;
		$prizes                = isset( $_POST['prizes'] ) ? wp_kses_post( wp_unslash( $_POST['prizes'] ) ) : '';
		$affiliate_site        = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;
		$tournament_id         = isset( $_POST['tournament_id'] ) ? bhg_sanitize_tournament_id( wp_unslash( $_POST['tournament_id'] ) ) : 0;
		$winners_count         = isset( $_POST['winners_count'] ) ? max( 1, absint( wp_unslash( $_POST['winners_count'] ) ) ) : 3;
		$guessing_enabled      = isset( $_POST['guessing_enabled'] ) ? 1 : 0;
				$final_balance = ( isset( $_POST['final_balance'] ) && '' !== $_POST['final_balance'] ) ? floatval( wp_unslash( $_POST['final_balance'] ) ) : null;
				$status        = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'open';
				if ( ! in_array( $status, array( 'open', 'closed' ), true ) ) {
					$status = 'open';
				}

				$data = array(
					'title'             => $title,
					'starting_balance'  => $starting,
					'num_bonuses'       => $num_bonuses,
					'prizes'            => $prizes,
					'affiliate_site_id' => $affiliate_site,
					'tournament_id'     => $tournament_id,
					'winners_count'     => $winners_count,
					'guessing_enabled'  => $guessing_enabled,
				);

				$format = array( '%s', '%f', '%d', '%s', '%d', '%d', '%d', '%d' );

				if ( null !== $final_balance ) {
								$data['final_balance'] = $final_balance;
								// Use a float format to match the stored value.
								$format[] = '%f';
				}

				$data['status']     = $status;
				$data['updated_at'] = current_time( 'mysql' );
				$format[]           = '%s';
				$format[]           = '%s';
				if ( $id ) {
					$wpdb->update( $hunts_table, $data, array( 'id' => $id ), $format, array( '%d' ) );
				} else {
					$data['created_at'] = current_time( 'mysql' );
					$format[]           = '%s';
					$wpdb->insert( $hunts_table, $data, $format );
					$id = (int) $wpdb->insert_id;
				}

				if ( 'closed' === $status && null !== $final_balance ) {
					$winners = BHG_Models::close_hunt( $id, $final_balance );

					$emails_enabled = (int) get_option( 'bhg_email_enabled', 1 );
					if ( $emails_enabled ) {
                                                                                                                        $guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );

                                                                                                                        $rows = $wpdb->get_results(
                                                                                                                        $wpdb->prepare(
                                                                                                                        "SELECT DISTINCT user_id FROM {$guesses_table} WHERE hunt_id = %d",
                                                                                                                        $id
                                                                                                                        )
                                                                                                                        );

						$template = get_option(
							'bhg_email_template',
							'Hi {{username}},\nThe Bonus Hunt "{{hunt}}" is closed. Final balance: €{{final}}. Winners: {{winners}}. Thanks for playing!'
						);

                                                                                                                        $hunt_title = (string) $wpdb->get_var(
                                                                                                                        $wpdb->prepare(
                                                                                                                        "SELECT title FROM {$hunts_table} WHERE id = %d",
                                                                                                                        $id
                                                                                                                        )
                                                                                                                        );

						$winner_names = array();
						foreach ( (array) $winners as $winner_id ) {
							$wu = get_userdata( (int) $winner_id );
							if ( $wu ) {
								$winner_names[] = $wu->user_login;
							}
						}
								$winner_first = $winner_names ? $winner_names[0] : esc_html( bhg_t( 'label_emdash', '—' ) );
								$winner_list  = $winner_names ? implode( ', ', $winner_names ) : esc_html( bhg_t( 'label_emdash', '—' ) );

						foreach ( $rows as $r ) {
							$u = get_userdata( (int) $r->user_id );
							if ( ! $u ) {
								continue;
							}
										$username   = sanitize_text_field( $u->user_login );
										$hunt_title = sanitize_text_field( $hunt_title );

										$body = strtr(
											$template,
											array(
												'{{username}}' => esc_html( $username ),
												'{{hunt}}' => esc_html( $hunt_title ),
												'{{final}}' => number_format( $final_balance, 2 ),
												'{{winner}}' => $winner_first,
												'{{winners}}' => $winner_list,
											)
										);

										$headers = array( 'From: ' . BHG_Utils::get_email_from() );
										wp_mail(
											$u->user_email,
											sprintf(
											/* translators: %s: bonus hunt title. */
												bhg_t( 'results_for_s', 'Results for %s' ),
												$hunt_title ? $hunt_title : bhg_t( 'bonus_hunt', 'Bonus Hunt' )
											),
											$body,
											$headers
										);
						}
					}
				}

				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) );
				exit;
	}

	/**
	 * Close an active bonus hunt.
	 */
	public function handle_close_hunt() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
			check_admin_referer( 'bhg_close_hunt', 'bhg_close_hunt_nonce' );

		$hunt_id           = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
		$final_balance_raw = isset( $_POST['final_balance'] ) ? wp_unslash( $_POST['final_balance'] ) : '';

		$final_balance = function_exists( 'bhg_parse_amount' ) ? bhg_parse_amount( $final_balance_raw ) : null;

		if ( null === $final_balance ) {
			wp_safe_redirect( add_query_arg( 'bhg_msg', 'invalid_final_balance', BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) ) );
			exit;
		}

		$final_balance = (float) $final_balance;

		if ( $final_balance < 0 ) {
			wp_safe_redirect( add_query_arg( 'bhg_msg', 'invalid_final_balance', BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) ) );
			exit;
		}

                if ( $hunt_id ) {
                                $result = BHG_Models::close_hunt( $hunt_id, $final_balance );
                                if ( false === $result ) {
                                                wp_safe_redirect(
                                                                add_query_arg(
                                                                                'bhg_msg',
                                                                                'close_failed',
                                                                                BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' )
                                                                )
                                                );
                                                exit;
                                }
                }

                                $redirect_url = add_query_arg(
                                                'closed',
                                                1,
                                                BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' )
                                );
                                wp_safe_redirect( $redirect_url );
                exit;
        }

	/**
	 * Delete a bonus hunt and its guesses.
	 */
	public function handle_delete_hunt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
		check_admin_referer( 'bhg_delete_hunt', 'bhg_delete_hunt_nonce' );

               global $wpdb;
$hunts_table    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$guesses_table  = esc_sql( $wpdb->prefix . 'bhg_guesses' );
$winners_table  = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
$results_table  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
                        $hunt_id        = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;

if ( $hunt_id ) {
$wpdb->delete( $hunts_table, array( 'id' => $hunt_id ), array( '%d' ) );
$wpdb->delete( $guesses_table, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
$wpdb->delete( $winners_table, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
$wpdb->delete( $results_table, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
}

wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts&bhg_msg=hunt_deleted' ) );
exit;
	}

	/**
	 * Toggle guessing for a hunt.
	 */
	public function handle_toggle_guessing() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
		check_admin_referer( 'bhg_toggle_guessing', 'bhg_toggle_guessing_nonce' );

		global $wpdb;
                $hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$hunt_id     = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
		$new_state   = isset( $_POST['guessing_enabled'] ) ? absint( wp_unslash( $_POST['guessing_enabled'] ) ) : 0;

		if ( $hunt_id ) {
			$wpdb->update(
				$hunts_table,
				array(
					'guessing_enabled' => $new_state,
					'updated_at'       => current_time( 'mysql' ),
				),
				array( 'id' => $hunt_id ),
				array( '%d', '%s' ),
				array( '%d' )
			);
		}

				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) );
		exit;
	}

		/**
		 * Handle deletion of advertising entries.
		 */
	public function handle_delete_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
			check_admin_referer( 'bhg_delete_ad', 'bhg_delete_ad_nonce' );
                        global $wpdb;
                        $ads_table   = esc_sql( $wpdb->prefix . 'bhg_ads' );
			$ad_id       = isset( $_POST['ad_id'] ) ? absint( wp_unslash( $_POST['ad_id'] ) ) : 0;
			$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';
			$bulk_ad_ids = isset( $_POST['ad_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ad_ids'] ) ) : array();

		if ( $ad_id ) {
                                $wpdb->query(
                                        $wpdb->prepare(
                                                "DELETE FROM {$ads_table} WHERE id = %d",
                                                $ad_id
                                        )
                                );
		} elseif ( 'delete' === $bulk_action && ! empty( $bulk_ad_ids ) ) {
                                $placeholders = implode( ', ', array_fill( 0, count( $bulk_ad_ids ), '%d' ) );
                                                                $query = $wpdb->prepare(
                                                                           // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                                                                        "DELETE FROM {$ads_table} WHERE id IN ($placeholders)",
                                                                        ...$bulk_ad_ids
                                                                );

                                                               // phpcs:ignore WordPress.DB.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
                                                                $wpdb->query( $query );
		}

			$referer = wp_get_referer();
						wp_safe_redirect( $referer ? $referer : BHG_Utils::admin_url( 'admin.php?page=bhg-ads' ) );
			exit;
	}

		/**
		 * Save or update an advertising entry.
		 */
	public function handle_save_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
							check_admin_referer( 'bhg_save_ad', 'bhg_save_ad_nonce' );
		global $wpdb;
		$table = $wpdb->prefix . 'bhg_ads';

		$id      = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
		$link    = isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '';
		$place   = isset( $_POST['placement'] ) ? sanitize_text_field( wp_unslash( $_POST['placement'] ) ) : 'none';
		$visible = isset( $_POST['visible_to'] ) ? sanitize_text_field( wp_unslash( $_POST['visible_to'] ) ) : 'all';
		$targets = isset( $_POST['target_pages'] ) ? sanitize_text_field( wp_unslash( $_POST['target_pages'] ) ) : '';
		$active  = isset( $_POST['active'] ) ? 1 : 0;

		$data = array(
			'title'        => $title,
			'content'      => $content,
			'link_url'     => $link,
			'placement'    => $place,
			'visible_to'   => $visible,
			'target_pages' => $targets,
			'active'       => $active,
			'updated_at'   => current_time( 'mysql' ),
		);

		$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' );
		if ( $id ) {
			$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
		} else {
			$data['created_at'] = current_time( 'mysql' );
			$format[]           = '%s';
			$wpdb->insert( $table, $data, $format );
		}

				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-ads' ) );
		exit;
	}

	/**
	 * Save a tournament record.
	 */
	public function handle_save_tournament() {
		if ( ! current_user_can( 'manage_options' ) ) {
						wp_safe_redirect( add_query_arg( 'bhg_msg', 'noaccess', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
			exit;
		}
		if ( ! check_admin_referer( 'bhg_tournament_save_action', 'bhg_tournament_save_nonce' ) ) {
						wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
			exit;
		}
		global $wpdb;
		$t                     = $wpdb->prefix . 'bhg_tournaments';
		$id                    = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
			$participants_mode = isset( $_POST['participants_mode'] ) ? sanitize_key( wp_unslash( $_POST['participants_mode'] ) ) : 'winners';
		if ( ! in_array( $participants_mode, array( 'winners', 'all' ), true ) ) {
				$participants_mode = 'winners';
		}

			$data = array(
				'title'             => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
				'description'       => isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '',
				'type'              => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'weekly',
				'participants_mode' => $participants_mode,
				'start_date'        => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : null,
				'end_date'          => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : null,
				'status'            => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active',
				'updated_at'        => current_time( 'mysql' ),
			);
			if ( ! in_array( $data['status'], array( 'active', 'inactive' ), true ) ) {
				$data['status'] = 'active';
			}
			try {
					$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
				if ( $id > 0 ) {
						$wpdb->update( $t, $data, array( 'id' => $id ), $format, array( '%d' ) );
				} else {
						$data['created_at'] = current_time( 'mysql' );
						$format[]           = '%s';
						$wpdb->insert( $t, $data, $format );
				}
									wp_safe_redirect( add_query_arg( 'bhg_msg', 't_saved', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
					exit;
			} catch ( Throwable $e ) {
				if ( function_exists( 'error_log' ) ) {
					error_log( '[BHG] tournament save error: ' . $e->getMessage() );
				}
							wp_safe_redirect( add_query_arg( 'bhg_msg', 't_error', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
				exit;
			}
	}

		/**
		 * Delete a tournament.
		 */
        public function handle_delete_tournament() {
                if ( ! current_user_can( 'manage_options' ) ) {
                                                        wp_safe_redirect( add_query_arg( 'bhg_msg', 'noaccess', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
                                exit;
                }
                if ( ! isset( $_POST['bhg_tournament_delete_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['bhg_tournament_delete_nonce'] ), 'bhg_tournament_delete_action' ) ) {
                                                        wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
                                exit;
                }
                        global $wpdb;
                        $table = $wpdb->prefix . 'bhg_tournaments';
                        $id    = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
                if ( $id ) {
                                $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
                                                        wp_safe_redirect( add_query_arg( 'bhg_msg', 't_deleted', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
                                exit;
                }
                                                wp_safe_redirect( add_query_arg( 'bhg_msg', 't_error', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
                        exit;
        }

       /**
        * Close a tournament by setting its status to closed.
        */
       public function handle_close_tournament() {
               if ( ! current_user_can( 'manage_options' ) ) {
                       wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
               }

               check_admin_referer( 'bhg_tournament_close', 'bhg_tournament_close_nonce' );

               $id = isset( $_POST['tournament_id'] ) ? absint( wp_unslash( $_POST['tournament_id'] ) ) : 0;

               if ( $id ) {
                       global $wpdb;
                       $table = $wpdb->prefix . 'bhg_tournaments';
                       $wpdb->update(
                               $table,
                               array(
                                       'status'     => 'closed',
                                       'updated_at' => current_time( 'mysql' ),
                               ),
                               array( 'id' => $id ),
                               array( '%s', '%s' ),
                               array( '%d' )
                       );
               }

               wp_safe_redirect( add_query_arg( 'bhg_msg', 't_closed', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
               exit;
       }

		/**
		 * Save or update an affiliate website record.
		 */
	public function handle_save_affiliate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
				check_admin_referer( 'bhg_save_affiliate', 'bhg_save_affiliate_nonce' );
			global $wpdb;
			$table  = $wpdb->prefix . 'bhg_affiliate_websites';
			$id     = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
			$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$slug   = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : sanitize_title( $name );
			$url    = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
			$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active';
			if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
				$status = 'active';
			}

		$data       = array(
			'name'       => $name,
			'slug'       => $slug,
			'url'        => $url,
			'status'     => $status,
			'updated_at' => current_time( 'mysql' ),
		);
			$format = array( '%s', '%s', '%s', '%s', '%s' );
		if ( $id ) {
				$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
		} else {
				$data['created_at'] = current_time( 'mysql' );
				$format[]           = '%s';
				$wpdb->insert( $table, $data, $format );
		}
						wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-affiliates' ) );
			exit;
	}

		/**
		 * Delete an affiliate website.
		 */
	public function handle_delete_affiliate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_delete_affiliate', 'bhg_delete_affiliate_nonce' );
				global $wpdb;
				$table = $wpdb->prefix . 'bhg_affiliate_websites';
		$id            = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( $id ) {
			$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		}
				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-affiliates' ) );
		exit;
	}

	/**
	 * Save custom user metadata from the admin screen.
	 */
	public function handle_save_user_meta() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_save_user_meta', 'bhg_save_user_meta_nonce' );
		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
		if ( $user_id ) {
			$real_name    = isset( $_POST['bhg_real_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bhg_real_name'] ) ) : '';
			$is_affiliate = isset( $_POST['bhg_is_affiliate'] ) ? 1 : 0;
			update_user_meta( $user_id, 'bhg_real_name', $real_name );
			update_user_meta( $user_id, 'bhg_is_affiliate', $is_affiliate );
		}
				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-users' ) );
		exit;
	}

		/**
		 * Handle submission of the Tools page.
		 */
	public function handle_tools_action() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

			// Verify nonce for tools action submission.
			check_admin_referer( 'bhg_tools_action', 'bhg_tools_nonce' );

			global $wpdb;

                        $hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                        $tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

						// Remove existing demo data.
                                $wpdb->query(
                                        $wpdb->prepare(
                                                "DELETE FROM {$hunts_table} WHERE title LIKE %s",
                                                '%(Demo)%'
                                        )
                                );

                                $wpdb->query(
                                        $wpdb->prepare(
                                                "DELETE FROM {$tournaments_table} WHERE title LIKE %s",
                                                '%(Demo)%'
                                        )
                                );

			// Seed demo hunt.
			$wpdb->insert(
				$hunts_table,
				array(
					'title'            => 'Sample Hunt (Demo)',
					'starting_balance' => 1000,
					'num_bonuses'      => 5,
					'status'           => 'open',
				)
			);

			// Seed demo tournament.
			$wpdb->insert(
				$tournaments_table,
				array(
					'title'  => 'August Tournament (Demo)',
					'status' => 'active',
				)
			);

			// Redirect back to the tools page with a success message.
						wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-tools&bhg_msg=tools_success' ) );
			exit;
	}

	/**
	 * Display admin notices for tournament actions.
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_GET['bhg_msg'] ) ) {
			return;
		}
		$msg   = sanitize_text_field( wp_unslash( $_GET['bhg_msg'] ) );
		$map   = array(
                        't_saved'               => bhg_t( 'tournament_saved', 'Tournament saved.' ),
                        't_error'               => bhg_t( 'could_not_save_tournament_check_logs', 'Could not save tournament. Check logs.' ),
                        't_deleted'             => bhg_t( 'tournament_deleted', 'Tournament deleted.' ),
                       't_closed'              => bhg_t( 'tournament_closed', 'Tournament closed.' ),
                        'nonce'                 => bhg_t( 'security_check_failed_please_retry', 'Security check failed. Please retry.' ),
                       'noaccess'              => bhg_t( 'you_do_not_have_permission_to_do_that', 'You do not have permission to do that.' ),
                       'tools_success'         => bhg_t( 'tools_action_completed', 'Tools action completed.' ),
               );
		$class = ( strpos( $msg, 'error' ) !== false || 'nonce' === $msg || 'noaccess' === $msg ) ? 'notice notice-error' : 'notice notice-success';
		$text  = isset( $map[ $msg ] ) ? $map[ $msg ] : esc_html( $msg );
		echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $text ) . '</p></div>';
	}
}
