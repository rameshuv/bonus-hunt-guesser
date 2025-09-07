<?php
/**
 * Shortcodes for Bonus Hunt Guesser
 *
 * PHP 7.4 safe, WP 6.3.5 compatible.
 * Registers all shortcodes on init (once) and avoids parse errors.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Shortcodes' ) ) {

	class BHG_Shortcodes {

		public function __construct() {
			// Core shortcodes
			add_shortcode( 'bhg_active_hunt', array( $this, 'active_hunt_shortcode' ) );
			add_shortcode( 'bhg_guess_form', array( $this, 'guess_form_shortcode' ) );
			add_shortcode( 'bhg_leaderboard', array( $this, 'leaderboard_shortcode' ) );
			add_shortcode( 'bhg_tournaments', array( $this, 'tournaments_shortcode' ) );
			add_shortcode( 'bhg_winner_notifications', array( $this, 'winner_notifications_shortcode' ) );
			add_shortcode( 'bhg_user_profile', array( $this, 'user_profile_shortcode' ) );

			// Addons
			add_shortcode( 'bhg_best_guessers', array( $this, 'best_guessers_shortcode' ) );
			add_shortcode( 'bhg_user_guesses', array( $this, 'user_guesses_shortcode' ) );
			add_shortcode( 'bhg_hunts', array( $this, 'hunts_shortcode' ) );
			add_shortcode( 'bhg_leaderboards', array( $this, 'leaderboards_shortcode' ) );

			// Legacy/aliases
			add_shortcode( 'bonus_hunt_leaderboard', array( $this, 'leaderboard_shortcode' ) );
			add_shortcode( 'bonus_hunt_login', array( $this, 'login_hint_shortcode' ) );
			add_shortcode( 'bhg_active', array( $this, 'active_hunt_shortcode' ) );
		}

		private function sanitize_table( $table ) {
			global $wpdb;
			$allowed = array(
				$wpdb->prefix . 'bhg_bonus_hunts',
				$wpdb->prefix . 'bhg_guesses',
				$wpdb->prefix . 'bhg_tournaments',
				$wpdb->prefix . 'bhg_tournament_results',
				$wpdb->prefix . 'bhg_affiliates',
				$wpdb->users,
			);
			return in_array( $table, $allowed, true ) ? esc_sql( $table ) : '';
		}

			/** Minimal login hint used by some themes */
		public function login_hint_shortcode( $atts = array() ) {
			if ( is_user_logged_in() ) {
				return '';
			}
				$raw      = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : home_url( '/' );
				$base     = wp_validate_redirect( $raw, home_url( '/' ) );
				$redirect = esc_url_raw( add_query_arg( array(), $base ) );

				return '<p>' . esc_html( bhg_t( 'notice_login_to_continue', 'Please log in to continue.' ) ) . '</p>'
				. '<p><a class="button button-primary" href="' . esc_url( wp_login_url( $redirect ) ) . '">' . esc_html( bhg_t( 'button_log_in', 'Log in' ) ) . '</a></p>';
		}

			/** [bhg_active_hunt] — list all open hunts */
		public function active_hunt_shortcode( $atts ) {
			global $wpdb;
			$hunts_table = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
					// db call ok; no-cache ok.
											$hunts = $wpdb->get_results(
												$wpdb->prepare(
													'SELECT * FROM %i WHERE status = %s ORDER BY created_at DESC',
													$hunts_table,
													'open'
												)
											);

			if ( ! $hunts ) {
				return '<div class="bhg-active-hunt"><p>' . esc_html( bhg_t( 'notice_no_active_hunts', 'No active bonus hunts at the moment.' ) ) . '</p></div>';
			}

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start();
			echo '<div class="bhg-active-hunts">';
			foreach ( $hunts as $hunt ) {
				echo '<div class="bhg-hunt-card">';
				echo '<h3>' . esc_html( $hunt->title ) . '</h3>';
				echo '<ul class="bhg-hunt-meta">';
				echo '<li><strong>' . esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ) . ':</strong> ' . esc_html( number_format_i18n( (float) $hunt->starting_balance, 2 ) ) . '</li>';
				echo '<li><strong>' . esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ) . ':</strong> ' . (int) $hunt->num_bonuses . '</li>';
				if ( ! empty( $hunt->prizes ) ) {
					echo '<li><strong>' . esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ) . ':</strong> ' . wp_kses_post( $hunt->prizes ) . '</li>';
				}
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
			return ob_get_clean();
		}

			/** [bhg_guess_form hunt_id=""] */
		public function guess_form_shortcode( $atts ) {
			$atts    = shortcode_atts( array( 'hunt_id' => 0 ), $atts, 'bhg_guess_form' );
			$hunt_id = (int) $atts['hunt_id'];

			if ( ! is_user_logged_in() ) {
				$raw      = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : home_url( '/' );
				$base     = wp_validate_redirect( $raw, home_url( '/' ) );
				$redirect = esc_url_raw( add_query_arg( array(), $base ) );

				return '<p>' . esc_html( bhg_t( 'notice_login_to_guess', 'Please log in to submit your guess.' ) ) . '</p>'
				. '<p><a class="button button-primary" href="' . esc_url( wp_login_url( $redirect ) ) . '">' . esc_html( bhg_t( 'button_log_in', 'Log in' ) ) . '</a></p>';
			}

					global $wpdb;
					$hunts_table = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
					// db call ok; no-cache ok.
											$open_hunts = $wpdb->get_results(
												$wpdb->prepare(
													'SELECT id, title FROM %i WHERE status = %s ORDER BY created_at DESC',
													$hunts_table,
													'open'
												)
											);

			if ( $hunt_id <= 0 ) {
				if ( ! $open_hunts ) {
					return '<p>' . esc_html( bhg_t( 'notice_no_open_hunt', 'No open hunt found to guess.' ) ) . '</p>';
				}
				if ( count( $open_hunts ) === 1 ) {
					$hunt_id = (int) $open_hunts[0]->id;
				}
			}

					$user_id = get_current_user_id();
					$table   = $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' );
					// db call ok; no-cache ok.
											$existing_id = $hunt_id > 0 ? (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM %i WHERE user_id = %d AND hunt_id = %d', $table, $user_id, $hunt_id ) ) : 0;
											// db call ok; no-cache ok.
											$existing_guess = $existing_id ? (float) $wpdb->get_var( $wpdb->prepare( 'SELECT guess FROM %i WHERE id = %d', $table, $existing_id ) ) : '';

			$settings = get_option( 'bhg_plugin_settings' );
			$min      = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
			$max      = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start(); ?>
						<form class="bhg-guess-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<input type="hidden" name="action" value="bhg_submit_guess">
								<?php wp_nonce_field( 'bhg_submit_guess' ); ?>

					<?php if ( $open_hunts && count( $open_hunts ) > 1 ) : ?>
					<label for="bhg-hunt-select">
						<?php
						echo esc_html( bhg_t( 'label_choose_hunt', 'Choose a hunt:' ) );
						?>
</label>
					<select id="bhg-hunt-select" name="hunt_id" required>
						<option value="">
						<?php
						echo esc_html( bhg_t( 'label_select_hunt', 'Select a hunt' ) );
						?>
</option>
						<?php foreach ( $open_hunts as $oh ) : ?>
							<option value="<?php echo (int) $oh->id; ?>" <?php selected( $hunt_id, (int) $oh->id ); ?>>
								<?php echo esc_html( $oh->title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input type="hidden" name="hunt_id" value="<?php echo esc_attr( $hunt_id ); ?>">
				<?php endif; ?>

				<label for="bhg-guess" class="bhg-guess-label">
				<?php
				echo esc_html( bhg_t( 'label_guess_final_balance', 'Your guess (final balance):' ) );
				?>
</label>
				<input type="number" step="0.01" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
					id="bhg-guess" name="guess" value="<?php echo esc_attr( $existing_guess ); ?>" required>
				<div class="bhg-error-message"></div>
				<button type="submit" class="bhg-submit-btn button button-primary"><?php echo esc_html( bhg_t( 'button_submit_guess', 'Submit Guess' ) ); ?></button>
			</form>
				<?php
				return ob_get_clean();
		}

			/**
			 * [bhg_leaderboard]
			 * Supports ranking (1-10) and fields (comma-separated list).
			 */
		public function leaderboard_shortcode( $atts ) {
					$a = shortcode_atts(
						array(
							'hunt_id'  => 0,
							'orderby'  => 'guess', // guess|user|position
							'order'    => 'ASC',
							'page'     => 1,
							'per_page' => 20,
							'ranking'  => 0, // top N results
							'fields'   => 'position,user,guess',
						),
						$atts,
						'bhg_leaderboard'
					);

			global $wpdb;
			$hunt_id = (int) $a['hunt_id'];
			if ( $hunt_id <= 0 ) {
					$hunts_table = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
					// db call ok; no-cache ok.
													$hunt_id = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM %i ORDER BY created_at DESC LIMIT 1', $hunts_table ) );
				if ( $hunt_id <= 0 ) {
							return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
				}
			}

			$g = $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' );
			$u = $this->sanitize_table( $wpdb->users );

					$order       = strtoupper( $a['order'] );
					$order       = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC';
					$map         = array(
						'guess'    => 'g.guess',
						'user'     => 'u.user_login',
						'position' => 'g.id', // stable proxy
					);
					$orderby_key = array_key_exists( $a['orderby'], $map ) ? $a['orderby'] : 'guess';
					$orderby     = $map[ $orderby_key ];
					$page        = max( 1, (int) $a['page'] );
					$per         = max( 1, (int) $a['per_page'] );
					$offset      = ( $page - 1 ) * $per;

					$ranking = max( 0, min( 10, (int) $a['ranking'] ) );
					if ( $ranking > 0 ) {
							$per    = $ranking;
							$page   = 1;
							$offset = 0;
					}

					$fields_raw    = explode( ',', (string) $a['fields'] );
					$allowed_field = array( 'position', 'user', 'guess' );
					$fields        = array_values( array_intersect( $allowed_field, array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) ) ) );
					if ( empty( $fields ) ) {
							$fields = $allowed_field;
					}

					// db call ok; no-cache ok.
											$total = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE hunt_id = %d', $g, $hunt_id ) );
					if ( $total < 1 ) {
							return '<p>' . esc_html( bhg_t( 'notice_no_guesses_yet', 'No guesses yet.' ) ) . '</p>';
					}
					if ( $ranking > 0 && $total > $ranking ) {
							$total = $ranking;
					}

										$hunts_table = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
										// db call ok; no-cache ok.
                                                                               $sql             = 'SELECT g.user_id, g.guess, u.user_login, h.affiliate_site_id
                                                                                      FROM %i g
                                                                                      LEFT JOIN %i u ON u.ID = g.user_id
                                                                                      LEFT JOIN %i h ON h.id = g.hunt_id
                                                                                      WHERE g.hunt_id = %d ORDER BY %i ' . $order . ' LIMIT %d OFFSET %d';
                                                                               $allowed_orderby = array( 'g.guess', 'u.user_login', 'g.id' );
                                       if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
                                                                       $orderby = 'g.guess';
                                       }
                                                                               $rows = $wpdb->get_results(
                                                                                        $wpdb->prepare(
                                                                                                $sql,
                                                                                                $g,
                                                                                                $u,
                                                                                                  $hunts_table,
                                                                                                  $hunt_id,
                                                                                                  $orderby,
                                                                                                  $per,
                                                                                                  $offset
                                                                                        )
                                                                               );

					wp_enqueue_style(
						'bhg-shortcodes',
						( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
						array(),
						defined( 'BHG_VERSION' ) ? BHG_VERSION : null
					);

					ob_start();
					echo '<table class="bhg-leaderboard">';
					echo '<thead><tr>';
			foreach ( $fields as $field ) {
				if ( 'position' === $field ) {
							echo '<th class="sortable" data-column="position">' . esc_html( bhg_t( 'sc_position', 'Position' ) ) . '</th>';
				} elseif ( 'user' === $field ) {
					echo '<th class="sortable" data-column="user">' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</th>';
				} elseif ( 'guess' === $field ) {
					echo '<th class="sortable" data-column="guess">' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</th>';
				}
			}
				echo '</tr></thead><tbody>';

				$pos       = $offset + 1;
				$need_user = in_array( 'user', $fields, true );
			foreach ( $rows as $r ) {
				if ( $need_user ) {
					$site_id = isset( $r->affiliate_site_id ) ? (int) $r->affiliate_site_id : 0;
					$is_aff  = $site_id > 0
					? (int) get_user_meta( (int) $r->user_id, 'bhg_affiliate_website_' . $site_id, true )
					: (int) get_user_meta( (int) $r->user_id, 'bhg_is_affiliate', true );
					$aff     = $is_aff ? 'green' : 'red';
					/* translators: %d: user ID. */
					$user_label = $r->user_login ? $r->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $r->user_id );
				}

					echo '<tr>';
				foreach ( $fields as $field ) {
					if ( 'position' === $field ) {
						echo '<td data-column="position">' . (int) $pos . '</td>';
					} elseif ( 'user' === $field ) {
							echo '<td data-column="user">' . esc_html( $user_label ) . ' ' . $this->render_affiliate_dot( $aff ) . '</td>';
					} elseif ( 'guess' === $field ) {
							echo '<td data-column="guess">' . esc_html( number_format_i18n( (float) $r->guess, 2 ) ) . '</td>';
					}
				}
						echo '</tr>';
						++$pos;
			}
				echo '</tbody></table>';

				$pages = (int) ceil( $total / $per );
			if ( $pages > 1 && 0 === $ranking ) {
				$raw  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : home_url( '/' );
				$base = esc_url_raw( remove_query_arg( 'page', wp_validate_redirect( $raw, home_url( '/' ) ) ) );
				echo '<div class="bhg-pagination">';
				for ( $p = 1; $p <= $pages; $p++ ) {
					$class = $p === $page ? ' class="bhg-current-page"' : '';
					echo '<a' . $class . ' href="' . esc_url( add_query_arg( array( 'page' => $p ), $base ) ) . '">' . (int) $p . '</a> ';
				}
				echo '</div>';
			}

				return ob_get_clean();
		}

			/**
			 * [bhg_user_guesses]
			 * id (user), aff (yes/no), website (affiliate id), status (open|closed),
			 * timeline: '' | 'recent' (limit 10) | relative period: day|week|month|year
			 * fields: comma-separated list (hunt,guess,final,user)
			 * orderby: hunt|guess ; order: ASC|DESC
			 */
		public function user_guesses_shortcode( $atts ) {
			$a = shortcode_atts(
				array(
					'id'       => 0,
					'aff'      => 'yes',
					'website'  => 0,
					'status'   => '',
					'timeline' => '',
					'fields'   => 'hunt,guess,final',
					'orderby'  => 'hunt',
					'order'    => 'DESC',
				),
				$atts,
				'bhg_user_guesses'
			);

			$fields_raw    = explode( ',', (string) $a['fields'] );
			$allowed_field = array( 'hunt', 'guess', 'final', 'user' );
			$fields_arr    = array_values(
				array_unique(
					array_intersect(
						$allowed_field,
						array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) )
					)
				)
			);
			if ( empty( $fields_arr ) ) {
					$fields_arr = array( 'hunt', 'guess', 'final' );
			}

			global $wpdb;

			$user_id = (int) $a['id'];
			if ( $user_id <= 0 ) {
				$user_id = get_current_user_id();
			}
			if ( $user_id <= 0 ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_user_specified', 'No user specified.' ) ) . '</p>';
			}

			$g = $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' );
			$h = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );

						// Ensure hunts table has created_at column. If missing, attempt migration and fall back.
						$has_created_at = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', $h, 'created_at' ) );
			if ( empty( $has_created_at ) && class_exists( 'BHG_DB' ) ) {
					BHG_DB::migrate();
					$has_created_at = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', $h, 'created_at' ) );
			}

			$where  = array( 'g.user_id = %d' );
			$params = array( $user_id );

			if ( in_array( $a['status'], array( 'open', 'closed' ), true ) ) {
				$where[]  = 'h.status = %s';
				$params[] = $a['status'];
			}

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'h.affiliate_site_id = %d';
				$params[] = $website;
			}

			// Timeline handling (relative time window)
			$timeline  = sanitize_key( $a['timeline'] );
			$intervals = array(
				'day'   => '-1 day',
				'week'  => '-1 week',
				'month' => '-1 month',
				'year'  => '-1 year',
			);
			if ( isset( $intervals[ $timeline ] ) ) {
				$since    = wp_date( 'Y-m-d H:i:s', strtotime( $intervals[ $timeline ], current_time( 'timestamp' ) ) );
				$where[]  = 'g.created_at >= %s';
				$params[] = $since;
			}

			$order       = strtoupper( $a['order'] );
			$order       = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';
			$orderby_map = array(
				'guess' => 'g.guess',
				'hunt'  => $has_created_at ? 'h.created_at' : 'h.id',
			);
			$orderby_key = isset( $orderby_map[ $a['orderby'] ] ) ? $a['orderby'] : 'hunt';
			$orderby     = $orderby_map[ $orderby_key ];

                                               $sql = 'SELECT g.guess, h.title, h.final_balance, h.affiliate_site_id FROM %i g INNER JOIN %i h ON h.id = g.hunt_id WHERE ' . implode( ' AND ', $where ) . ' ORDER BY %i ' . $order;
                                               if ( 'recent' === strtolower( $a['timeline'] ) ) {
                                                       $sql .= ' LIMIT 10';
                                               }

                                               // db call ok; no-cache ok.
                                               $params = array_merge( array( $g, $h ), $params, array( $orderby ) );
                                               $rows   = $wpdb->get_results(
                                                       $wpdb->prepare( $sql, ...$params )
                                               );
			if ( ! $rows ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_guesses_found', 'No guesses found.' ) ) . '</p>';
			}

			$show_aff = in_array( 'user', $fields_arr, true ) && in_array( strtolower( (string) $a['aff'] ), array( 'yes', '1', 'true' ), true );

			ob_start();
			echo '<table class="bhg-user-guesses"><thead><tr>';
			echo '<th>' . esc_html( bhg_t( 'sc_hunt', 'Hunt' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_final', 'Final' ) ) . '</th>';
			echo '</tr></thead><tbody>';

			$current_user_id = $user_id; // for aff dot.
			foreach ( $rows as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->title ) . '</td>';
				$guess_cell = esc_html( number_format_i18n( (float) $row->guess, 2 ) );
				if ( $show_aff ) {
					$dot        = $this->render_affiliate_dot(
						( get_user_meta( (int) $current_user_id, 'bhg_affiliate_website_' . (int) $row->affiliate_site_id, true )
						|| get_user_meta( (int) $current_user_id, 'bhg_is_affiliate', true ) ) ? 'green' : 'red'
					);
					$guess_cell = $dot . $guess_cell;
				}
				echo '<td>' . $guess_cell . '</td>';
				echo '<td>' . ( isset( $row->final_balance ) ? esc_html( number_format_i18n( (float) $row->final_balance, 2 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
			return ob_get_clean();
		}

			/**
			 * [bhg_hunts]
			 * id (hunt), status (open|closed), website (affiliate id),
			 * timeline: '' | 'recent' (limit 10) | relative period: day|week|month|year
			 * fields: comma-separated list (title,start,final,status,user)
			 * aff: yes/no to show affiliate column
			 */
		public function hunts_shortcode( $atts ) {
			$a = shortcode_atts(
				array(
					'id'       => 0,
					'aff'      => 'no',
					'website'  => 0,
					'status'   => '',
					'timeline' => '',
					'fields'   => 'title,start,final,status',
				),
				$atts,
				'bhg_hunts'
			);

			$fields_raw    = explode( ',', (string) $a['fields'] );
			$allowed_field = array( 'title', 'start', 'final', 'status', 'user' );
			$fields_arr    = array_values(
				array_unique(
					array_intersect(
						$allowed_field,
						array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) )
					)
				)
			);
			if ( empty( $fields_arr ) ) {
					$fields_arr = array( 'title', 'start', 'final', 'status' );
			}

			global $wpdb;
			$h         = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
			$aff_table = $this->sanitize_table( $wpdb->prefix . 'bhg_affiliates' );

			$where  = array();
			$params = array();

			$id = (int) $a['id'];
			if ( $id > 0 ) {
				$where[]  = 'h.id = %d';
				$params[] = $id;
			}

			if ( in_array( $a['status'], array( 'open', 'closed' ), true ) ) {
				$where[]  = 'h.status = %s';
				$params[] = $a['status'];
			}

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'h.affiliate_site_id = %d';
				$params[] = $website;
			}

			// Timeline handling
			$timeline  = sanitize_key( $a['timeline'] );
			$intervals = array(
				'day'   => '-1 day',
				'week'  => '-1 week',
				'month' => '-1 month',
				'year'  => '-1 year',
			);
			if ( isset( $intervals[ $timeline ] ) ) {
				$since    = wp_date( 'Y-m-d H:i:s', strtotime( $intervals[ $timeline ], current_time( 'timestamp' ) ) );
				$where[]  = 'h.created_at >= %s';
				$params[] = $since;
			}

					$sql = 'SELECT h.id, h.title, h.starting_balance, h.final_balance, h.status, h.created_at, h.closed_at, a.name AS aff_name
                                        FROM %i h
                                        LEFT JOIN %i a ON a.id = h.affiliate_site_id';
			if ( $where ) {
					$sql .= ' WHERE ' . implode( ' AND ', $where );
			}
					$sql .= ' ORDER BY h.created_at DESC';

			if ( 'recent' === strtolower( $a['timeline'] ) ) {
				$sql .= ' LIMIT 10';
			}

						// db call ok; no-cache ok.
			if ( $params ) {
							$rows = $wpdb->get_results( $wpdb->prepare( $sql, $h, $aff_table, ...$params ) );
			} else {
							$rows = $wpdb->get_results( $wpdb->prepare( $sql, $h, $aff_table ) );
			}
			if ( ! $rows ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
			}

			$show_aff = in_array( 'user', $fields_arr, true ) && in_array( strtolower( (string) $a['aff'] ), array( 'yes', '1', 'true' ), true );

			ob_start();
			echo '<table class="bhg-hunts"><thead><tr>';
			echo '<th>' . esc_html( bhg_t( 'sc_title', 'Title' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . '</th>';
			if ( $show_aff ) {
				echo '<th>' . esc_html( bhg_t( 'affiliate_user', 'Affiliate' ) ) . '</th>';
			}
			echo '</tr></thead><tbody>';

			foreach ( $rows as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->title ) . '</td>';
				echo '<td>' . esc_html( number_format_i18n( (float) $row->starting_balance, 2 ) ) . '</td>';
				echo '<td>' . ( isset( $row->final_balance ) ? esc_html( number_format_i18n( (float) $row->final_balance, 2 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				echo '<td>' . esc_html( $row->status ) . '</td>';
				if ( $show_aff ) {
					echo '<td>' . ( $row->aff_name ? esc_html( $row->aff_name ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
			return ob_get_clean();
		}

			/**
			 * [bhg_leaderboards] — show multiple hunts’ leaderboards.
			 * id (hunt), status, website, timeline (''|'recent'|day|week|month|year)
			 * aff yes/no (render affiliate dots)
			 * ranking 1-10, fields (comma-separated list)
			 */
		public function leaderboards_shortcode( $atts ) {
			$a = shortcode_atts(
				array(
					'id'       => 0,
					'aff'      => 'yes',
					'website'  => 0,
					'status'   => '',
					'timeline' => '',
					'ranking'  => 0,
					'fields'   => 'position,user,guess',
				),
				$atts,
				'bhg_leaderboards'
			);

						global $wpdb;
										$h       = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
										$ranking = min( 10, max( 0, (int) $a['ranking'] ) );

										$raw_fields    = array_map( 'trim', explode( ',', (string) $a['fields'] ) );
										$allowed_field = array( 'position', 'user', 'guess' );
										$fields_arr    = array_values( array_unique( array_intersect( $allowed_field, array_map( 'sanitize_key', $raw_fields ) ) ) );
			if ( empty( $fields_arr ) ) {
							$fields_arr = $allowed_field;
			}
										$fields_str = implode( ',', $fields_arr );

			$where  = array();
			$params = array();

			$id = (int) $a['id'];
			if ( $id > 0 ) {
				$where[]  = 'id = %d';
				$params[] = $id;
			}

			if ( in_array( $a['status'], array( 'open', 'closed' ), true ) ) {
				$where[]  = 'status = %s';
				$params[] = $a['status'];
			}

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'affiliate_site_id = %d';
				$params[] = $website;
			}

			// Timeline handling
			$timeline  = sanitize_key( $a['timeline'] );
			$intervals = array(
				'day'   => '-1 day',
				'week'  => '-1 week',
				'month' => '-1 month',
				'year'  => '-1 year',
			);
			if ( isset( $intervals[ $timeline ] ) ) {
				$since    = wp_date( 'Y-m-d H:i:s', strtotime( $intervals[ $timeline ], current_time( 'timestamp' ) ) );
				$where[]  = 'created_at >= %s';
				$params[] = $since;
			}

					$sql = 'SELECT id, title FROM %i';
			if ( $where ) {
					$sql .= ' WHERE ' . implode( ' AND ', $where );
			}
					$sql .= ' ORDER BY created_at DESC';

			if ( 'recent' === strtolower( $a['timeline'] ) ) {
					$sql .= ' LIMIT 5';
			}

						// db call ok; no-cache ok.
			if ( $params ) {
							$hunts = $wpdb->get_results( $wpdb->prepare( $sql, $h, ...$params ) );
			} else {
							$hunts = $wpdb->get_results( $wpdb->prepare( $sql, $h ) );
			}
			if ( ! $hunts ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
			}

			$show_aff = in_array( 'user', $fields_arr, true ) && in_array( strtolower( (string) $a['aff'] ), array( 'yes', '1', 'true' ), true );

			ob_start();
			foreach ( $hunts as $hunt ) {
				echo '<div class="bhg-leaderboard-wrap">';
				echo '<h3>' . esc_html( $hunt->title ) . '</h3>';
				$html = $this->leaderboard_shortcode(
					array(
						'hunt_id' => (int) $hunt->id,
						'ranking' => $ranking,
						'fields'  => $fields_str,
					)
				);
				if ( in_array( 'user', $fields_arr, true ) && ! $show_aff ) {
					// Strip affiliate dot spans if requested.
					$html = preg_replace( '/<span class="bhg-aff-dot[^>]*><\/span>/', '', $html );
				}
				echo $html;
				echo '</div>';
			}
			return ob_get_clean();
		}

			/**
			 * [bhg_tournaments]
			 * Filters:
			 *   status (active|closed), tournament (id), website (affiliate id),
			 *   timeline:
			 *     - relative date window: day|week|month|year
			 *     - or tournament type: weekly|monthly|yearly|quarterly|alltime
			 * Details view via ?bhg_tournament_id=ID
			 */
		public function tournaments_shortcode( $atts ) {
			global $wpdb;

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			// Details screen
			$details_id = isset( $_GET['bhg_tournament_id'] ) ? absint( wp_unslash( $_GET['bhg_tournament_id'] ) ) : 0;
			if ( $details_id > 0 ) {
					$t = $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' );
					$r = $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' );
					$u = $this->sanitize_table( $wpdb->users );

							// db call ok; no-cache ok.
											$tournament = $wpdb->get_row(
												$wpdb->prepare(
													'SELECT id, type, start_date, end_date, status FROM %i WHERE id = %d',
													$t,
													$details_id
												)
											);
				if ( ! $tournament ) {
					return '<p>' . esc_html( bhg_t( 'notice_tournament_not_found', 'Tournament not found.' ) ) . '</p>';
				}

				$orderby = isset( $_GET['orderby'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ) : 'wins';
				$order   = isset( $_GET['order'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'desc';

				$allowed = array(
					'wins'        => 'r.wins',
					'username'    => 'u.user_login',
					'last_win_at' => 'r.last_win_date',
				);
				if ( ! isset( $allowed[ $orderby ] ) ) {
					$orderby = 'wins';
				}
				if ( 'asc' !== $order && 'desc' !== $order ) {
					$order = 'desc';
				}
								$orderby_column = $allowed[ $orderby ];
								$order          = strtoupper( $order );

								// db call ok; no-cache ok.
								$sql  = 'SELECT r.user_id, r.wins, r.last_win_date, u.user_login
                                                                                                      FROM %i r
                                                                                                      INNER JOIN %i u ON u.ID = r.user_id
                                                                                                      WHERE r.tournament_id = %d
                                                                                                      ORDER BY %i %s, r.user_id ASC';
								$rows = $wpdb->get_results(
									$wpdb->prepare(
										$sql,
										$r,
										$u,
										$tournament->id,
										$orderby_column,
										$order
									)
								);

				$base   = remove_query_arg( array( 'orderby', 'order' ) );
				$toggle = function ( $key ) use ( $orderby, $order, $base ) {
					$next = ( $orderby === $key && strtolower( $order ) === 'asc' ) ? 'desc' : 'asc';
					return esc_url(
						add_query_arg(
							array(
								'orderby' => $key,
								'order'   => $next,
							),
							$base
						)
					);
				};

					ob_start();
					echo '<div class="bhg-tournament-details">';
					echo '<p><a href="' . esc_url( remove_query_arg( 'bhg_tournament_id' ) ) . '">&larr; ' . esc_html( bhg_t( 'label_back_to_tournaments', 'Back to tournaments' ) ) . '</a></p>';
					echo '<h3>' . esc_html( ucfirst( $tournament->type ) ) . '</h3>';
					echo '<p><strong>' . esc_html( bhg_t( 'sc_start', 'Start' ) ) . ':</strong> ' . esc_html( mysql2date( get_option( 'date_format' ), $tournament->start_date ) ) . ' &nbsp; ';
					echo '<strong>' . esc_html( bhg_t( 'sc_end', 'End' ) ) . ':</strong> ' . esc_html( mysql2date( get_option( 'date_format' ), $tournament->end_date ) ) . ' &nbsp; ';
					echo '<strong>' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . ':</strong> ' . esc_html( $tournament->status ) . '</p>';

				if ( ! $rows ) {
					echo '<p>' . esc_html( bhg_t( 'notice_no_results_yet', 'No results yet.' ) ) . '</p>';
					echo '</div>';
					return ob_get_clean();
				}

					echo '<table class="bhg-leaderboard">';
					echo '<thead><tr>';
					echo '<th>#</th>';
					echo '<th><a href="' . $toggle( 'username' ) . '">' . esc_html( bhg_t( 'label_username', 'Username' ) ) . '</a></th>';
					echo '<th><a href="' . $toggle( 'wins' ) . '">' . esc_html( bhg_t( 'sc_wins', 'Wins' ) ) . '</a></th>';
					echo '<th><a href="' . $toggle( 'last_win_at' ) . '">' . esc_html( bhg_t( 'label_last_win', 'Last win' ) ) . '</a></th>';
					echo '</tr></thead><tbody>';

					$pos = 1;
				foreach ( $rows as $row ) {
					echo '<tr>';
					echo '<td>' . (int) $pos++ . '</td>';
					echo '<td>' . esc_html(
						$row->user_login ?: sprintf(
						/* translators: %d: user ID. */
							bhg_t( 'label_user_hash', 'user#%d' ),
							(int) $row->user_id
						)
					) . '</td>';
					echo '<td>' . (int) $row->wins . '</td>';
					echo '<td>' . ( $row->last_win_date ? esc_html( mysql2date( get_option( 'date_format' ), $row->last_win_date ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
					echo '</tr>';
				}
					echo '</tbody></table>';
					echo '</div>';

					return ob_get_clean();
			}

			// List view with filters
			$a = shortcode_atts(
				array(
					'status'     => 'active',
					'tournament' => 0,
					'website'    => 0,
					'timeline'   => '',
				),
				$atts,
				'bhg_tournaments'
			);

			$t     = $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' );
			$where = array();
			$args  = array();

			$status     = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : sanitize_key( $a['status'] );
			$timeline   = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : sanitize_key( $a['timeline'] );
			$tournament = absint( $a['tournament'] );
			$website    = absint( $a['website'] );

			if ( $tournament > 0 ) {
				$where[] = 'id = %d';
				$args[]  = $tournament;
			}
			if ( in_array( $status, array( 'active', 'closed' ), true ) ) {
				$where[] = 'status = %s';
				$args[]  = $status;
			}

			// Accept either relative time window OR explicit type
			if ( in_array( $timeline, array( 'day', 'week', 'month', 'year' ), true ) ) {
				$map     = array(
					'day'   => '-1 day',
					'week'  => '-1 week',
					'month' => '-1 month',
					'year'  => '-1 year',
				);
				$since   = wp_date( 'Y-m-d H:i:s', strtotime( $map[ $timeline ], current_time( 'timestamp' ) ) );
				$where[] = 'created_at >= %s';
				$args[]  = $since;
			} elseif ( in_array( $timeline, array( 'weekly', 'monthly', 'yearly', 'quarterly', 'alltime' ), true ) ) {
				$where[] = 'type = %s';
				$args[]  = $timeline;
			}

			if ( $website > 0 ) {
				$where[] = 'affiliate_site_id = %d';
				$args[]  = $website;
			}

					$sql = 'SELECT * FROM %i';
			if ( $where ) {
					$sql .= ' WHERE ' . implode( ' AND ', $where );
			}
											$sql .= ' ORDER BY start_date DESC, id DESC';

											// db call ok; no-cache ok.
			if ( $args ) {
							$rows = $wpdb->get_results( $wpdb->prepare( $sql, $t, ...$args ) );
			} else {
							$rows = $wpdb->get_results( $wpdb->prepare( $sql, $t ) );
			}
			if ( ! $rows ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_tournaments_found', 'No tournaments found.' ) ) . '</p>';
			}

			$current_url = isset( $_SERVER['REQUEST_URI'] )
			? esc_url_raw( wp_validate_redirect( wp_unslash( $_SERVER['REQUEST_URI'] ), home_url( '/' ) ) )
			: home_url( '/' );

			ob_start();
			echo '<form method="get" class="bhg-tournament-filters">';
			// keep other query args
			foreach ( $_GET as $raw_key => $v ) {
				$key = sanitize_key( wp_unslash( $raw_key ) );
				if ( in_array( $key, array( 'bhg_timeline', 'bhg_status', 'bhg_tournament_id' ), true ) ) {
					continue;
				}
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
			}

			echo '<label class="bhg-tournament-label">' . esc_html( bhg_t( 'label_timeline_colon', 'Timeline:' ) ) . ' ';
			echo '<select name="bhg_timeline">';
			$timelines    = array(
				'all'       => bhg_t( 'label_all', 'All' ),
				'weekly'    => bhg_t( 'label_weekly', 'Weekly' ),
				'monthly'   => bhg_t( 'label_monthly', 'Monthly' ),
				'yearly'    => bhg_t( 'label_yearly', 'Yearly' ),
				'quarterly' => bhg_t( 'label_quarterly', 'Quarterly' ),
				'alltime'   => bhg_t( 'label_all_time', 'All-Time' ),
				'day'       => bhg_t( 'label_last_day', 'Last day' ),
				'week'      => bhg_t( 'label_last_week', 'Last week' ),
				'month'     => bhg_t( 'label_last_month', 'Last month' ),
				'year'      => bhg_t( 'label_last_year', 'Last year' ),
			);
			$timeline_key = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : $timeline;
			foreach ( $timelines as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $timeline_key, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select></label>';

			echo '<label>' . esc_html( bhg_t( 'status', 'Status:' ) ) . ' ';
			echo '<select name="bhg_status">';
			$statuses   = array(
				'active' => bhg_t( 'label_active', 'Active' ),
				'closed' => bhg_t( 'label_closed', 'Closed' ),
				'all'    => bhg_t( 'label_all', 'All' ),
			);
			$status_key = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : $status;
			foreach ( $statuses as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $status_key, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select></label> ';

			echo '<button class="button bhg-filter-button" type="submit">' . esc_html( bhg_t( 'button_filter', 'Filter' ) ) . '</button>';
			echo '</form>';

			echo '<table class="bhg-tournaments">';
			echo '<thead><tr>';
			echo '<th>' . esc_html( bhg_t( 'label_type', 'Type' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_start', 'Start' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_end', 'End' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . '</th>';
			echo '<th>' . esc_html( bhg_t( 'label_details', 'Details' ) ) . '</th>';
			echo '</tr></thead><tbody>';

			foreach ( $rows as $row ) {
				$detail_url = esc_url( add_query_arg( 'bhg_tournament_id', (int) $row->id, remove_query_arg( array( 'orderby', 'order' ), $current_url ) ) );
				echo '<tr>';
				echo '<td>' . esc_html( ucfirst( $row->type ) ) . '</td>';
				echo '<td>' . esc_html( mysql2date( get_option( 'date_format' ), $row->start_date ) ) . '</td>';
				echo '<td>' . esc_html( mysql2date( get_option( 'date_format' ), $row->end_date ) ) . '</td>';
				echo '<td>' . esc_html( $row->status ) . '</td>';
				echo '<td><a href="' . $detail_url . '">' . esc_html( bhg_t( 'label_show_details', 'Show details' ) ) . '</a></td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
			return ob_get_clean();
		}

			/** Minimal winners widget: latest closed hunts */
		public function winner_notifications_shortcode( $atts ) {
			global $wpdb;

			$a = shortcode_atts(
				array( 'limit' => 5 ),
				$atts,
				'bhg_winner_notifications'
			);

			$hunts_table = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
			// db call ok; no-cache ok.
			$hunts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id, title, final_balance, winners_count, closed_at
        FROM %i
        WHERE status = %s ORDER BY closed_at DESC LIMIT %d',
					$hunts_table,
					'closed',
					(int) $a['limit']
				)
			);

			if ( ! $hunts ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ) . '</p>';
			}

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start();
			echo '<div class="bhg-winner-notifications">';
			foreach ( $hunts as $hunt ) {
				$winners = function_exists( 'bhg_get_top_winners_for_hunt' )
				? bhg_get_top_winners_for_hunt( $hunt->id, (int) $hunt->winners_count )
				: array();

				echo '<div class="bhg-winner">';
				echo '<p><strong>' . esc_html( $hunt->title ) . '</strong></p>';
				if ( null !== $hunt->final_balance ) {
					echo '<p><em>' . esc_html( bhg_t( 'sc_final', 'Final' ) ) . ':</em> ' . esc_html( number_format_i18n( (float) $hunt->final_balance, 2 ) ) . '</p>';
				}

				if ( $winners ) {
					echo '<ul class="bhg-winner-list">';
					foreach ( $winners as $w ) {
						$u  = get_userdata( (int) $w->user_id );
						$nm = $u ? $u->user_login : sprintf( bhg_t( 'label_user_number', 'User #%d' ), (int) $w->user_id );
						echo '<li>' . esc_html( $nm ) . ' ' . esc_html( bhg_t( 'label_emdash', '—' ) ) . ' ' . esc_html( number_format_i18n( (float) $w->guess, 2 ) ) . ' (' . esc_html( number_format_i18n( (float) $w->diff, 2 ) ) . ')</li>';
					}
					echo '</ul>';
				}

				echo '</div>';
			}
			echo '</div>';
			return ob_get_clean();
		}

			/** Minimal profile view: affiliate status badge */
		public function user_profile_shortcode( $atts ) {
			if ( ! is_user_logged_in() ) {
				return '<p>' . esc_html( bhg_t( 'notice_login_view_content', 'Please log in to view this content.' ) ) . '</p>';
			}
			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);
			$user_id      = get_current_user_id();
			$is_affiliate = (int) get_user_meta( $user_id, 'bhg_is_affiliate', true );
			$badge        = $is_affiliate ? '<span class="bhg-aff-green" aria-hidden="true"></span>' : '<span class="bhg-aff-red" aria-hidden="true"></span>';
			return '<div class="bhg-user-profile">' . $badge . ' ' . esc_html( wp_get_current_user()->display_name ) . '</div>';
		}

			/** [bhg_best_guessers] — simple wins leaderboard with tabs */
		public function best_guessers_shortcode( $atts ) {
			global $wpdb;

			$wins_tbl  = $wpdb->prefix . 'bhg_tournament_results';
			$tours_tbl = $wpdb->prefix . 'bhg_tournaments';
			$users_tbl = $wpdb->users;

			$now_ts        = current_time( 'timestamp' );
			$current_month = wp_date( 'Y-m', $now_ts );
			$current_year  = wp_date( 'Y', $now_ts );

			$periods = array(
				'overall' => array(
					'label' => esc_html( bhg_t( 'label_overall', 'Overall' ) ),
					'type'  => '',
					'start' => '',
					'end'   => '',
				),
				'monthly' => array(
					'label' => esc_html( bhg_t( 'label_monthly', 'Monthly' ) ),
					'type'  => 'monthly',
					'start' => $current_month . '-01',
					'end'   => wp_date( 'Y-m-t', strtotime( $current_month . '-01', $now_ts ) ),
				),
				'yearly'  => array(
					'label' => esc_html( bhg_t( 'label_yearly', 'Yearly' ) ),
					'type'  => 'yearly',
					'start' => $current_year . '-01-01',
					'end'   => $current_year . '-12-31',
				),
				'alltime' => array(
					'label' => esc_html( bhg_t( 'label_all_time', 'All-Time' ) ),
					'type'  => 'alltime',
					'start' => '',
					'end'   => '',
				),
			);

			$results = array();
			foreach ( $periods as $key => $info ) {
				if ( $info['type'] ) {
					$where  = 't.type = %s';
					$params = array( $info['type'] );
					if ( ! empty( $info['start'] ) && ! empty( $info['end'] ) ) {
						$where   .= ' AND t.start_date >= %s AND t.end_date <= %s';
						$params[] = $info['start'];
						$params[] = $info['end'];
					}
																		$sql = 'SELECT u.ID as user_id, u.user_login, SUM(r.wins) as total_wins
                                                       FROM %i r
                                                       INNER JOIN %i u ON u.ID = r.user_id
                                                       INNER JOIN %i t ON t.id = r.tournament_id
                                                       WHERE ' . $where . '
                                                       GROUP BY u.ID, u.user_login
                                                       ORDER BY total_wins DESC, u.user_login ASC
                                                       LIMIT 50';
																		// db call ok; no-cache ok.
																		$results[ $key ] = $wpdb->get_results(
																			$wpdb->prepare( $sql, $wins_tbl, $users_tbl, $tours_tbl, ...$params )
																		);
				} else {
									$sql = 'SELECT u.ID as user_id, u.user_login, SUM(r.wins) as total_wins
                                                                                                                FROM %i r
                                                                                                                INNER JOIN %i u ON u.ID = r.user_id
                                                                                                                GROUP BY u.ID, u.user_login
                                                                                                                ORDER BY total_wins DESC, u.user_login ASC
                                                                                                                LIMIT 50';
																			// db call ok; no-cache ok.
																			$results[ $key ] = $wpdb->get_results( $wpdb->prepare( $sql, $wins_tbl, $users_tbl ) );
				}
			}

			$hunts_tbl = $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' );
			$hunts     = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id, title FROM %i WHERE status = %s ORDER BY created_at DESC LIMIT %d',
					$hunts_tbl,
					'closed',
					50
				)
			);

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);
			wp_enqueue_script(
				'bhg-shortcodes-js',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/js/bhg-shortcodes.js',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
				true
			);

			ob_start();
			echo '<ul class="bhg-tabs">';
			$first = true;
			foreach ( $periods as $key => $info ) {
				$active = $first ? ' class="active"' : '';
				echo '<li' . $active . '><a href="#bhg-tab-' . esc_html( $key ) . '">' . esc_html( $info['label'] ) . '</a></li>';
				$first = false;
			}
			if ( $hunts ) {
				echo '<li><a href="#bhg-tab-hunts">' . esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ) . '</a></li>';
			}
			echo '</ul>';

			$first = true;
			foreach ( $periods as $key => $info ) {
				$active = $first ? ' active' : '';
				echo '<div id="bhg-tab-' . esc_attr( $key ) . '" class="bhg-tab-pane' . $active . '">';
				$rows = isset( $results[ $key ] ) ? $results[ $key ] : array();
				if ( ! $rows ) {
					echo '<p>' . esc_html( bhg_t( 'notice_no_data_yet', 'No data yet.' ) ) . '</p>';
				} else {
					echo '<table class="bhg-leaderboard"><thead><tr><th>#</th><th>' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</th><th>' . esc_html( bhg_t( 'sc_wins', 'Wins' ) ) . '</th></tr></thead><tbody>';
					$pos = 1;
					foreach ( $rows as $r ) {
						/* translators: %d: user ID. */
						$user_label = $r->user_login ? $r->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $r->user_id );
						echo '<tr><td>' . (int) $pos++ . '</td><td>' . esc_html( $user_label ) . '</td><td>' . (int) $r->total_wins . '</td></tr>';
					}
					echo '</tbody></table>';
				}
				echo '</div>';
				$first = false;
			}

			if ( $hunts ) {
				$raw  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : home_url( '/' );
				$base = esc_url_raw( remove_query_arg( 'hunt_id', wp_validate_redirect( $raw, home_url( '/' ) ) ) );
				echo '<div id="bhg-tab-hunts" class="bhg-tab-pane">';
				echo '<ul class="bhg-hunt-history">';
				foreach ( $hunts as $hunt ) {
					$url = esc_url( add_query_arg( 'hunt_id', (int) $hunt->id, $base ) );
					echo '<li><a href="' . $url . '">' . esc_html( $hunt->title ) . '</a></li>';
				}
				echo '</ul>';
				echo '</div>';
			}

			return ob_get_clean();
		}

			/** Private: render affiliate dot span */
		private function render_affiliate_dot( $color ) {
			$c = ( 'green' === $color ) ? 'green' : 'red';
			return '<span class="bhg-aff-dot bhg-aff-' . esc_attr( $c ) . '" aria-hidden="true"></span> ';
		}
	}
}

// Register once on init even if no other bootstrap instantiates the class
if ( ! function_exists( 'bhg_register_shortcodes_once' ) ) {
	function bhg_register_shortcodes_once() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;
		if ( class_exists( 'BHG_Shortcodes' ) ) {
			new BHG_Shortcodes();
		}
	}
	add_action( 'init', 'bhg_register_shortcodes_once', 20 );
}
