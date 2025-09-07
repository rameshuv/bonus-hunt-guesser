<?php
/**
 * Dashboard: Latest Hunts overview.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' )
		);
}

if ( ! function_exists( 'bhg_get_latest_closed_hunts' ) ) {
		wp_die(
			esc_html__(
				'Helper function bhg_get_latest_closed_hunts() missing. Please include class-bhg-bonus-hunts.php helpers.',
				'bonus-hunt-guesser'
			)
		);
}

global $wpdb;

$hunts_count       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bhg_bonus_hunts" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$tournaments_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bhg_tournaments" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

$user_counts = count_users();
$users_count = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;

$hunts = bhg_get_latest_closed_hunts( 3 ); // Expect: id, title, starting_balance, final_balance, winners_count, closed_at.
?>
<div class="wrap bhg-dashboard">
		<h1><?php esc_html_e( 'Dashboard', 'bonus-hunt-guesser' ); ?></h1>

		<div class="dashboard-widgets-wrap">
				<div class="dashboard-widgets">
						<div class="postbox bhg-dashboard-card">
								<h2 class="hndle"><span><?php esc_html_e( 'Summary', 'bonus-hunt-guesser' ); ?></span></h2>
								<div class="inside">
										<ul class="bhg-dashboard-meta">
												<li><span class="dashicons dashicons-book-alt"></span> <strong><?php esc_html_e( 'Hunts:', 'bonus-hunt-guesser' ); ?></strong> <?php echo esc_html( number_format_i18n( $hunts_count ) ); ?></li>
												<li><span class="dashicons dashicons-groups"></span> <strong><?php esc_html_e( 'Users:', 'bonus-hunt-guesser' ); ?></strong> <?php echo esc_html( number_format_i18n( $users_count ) ); ?></li>
												<li><span class="dashicons dashicons-awards"></span> <strong><?php esc_html_e( 'Tournaments:', 'bonus-hunt-guesser' ); ?></strong> <?php echo esc_html( number_format_i18n( $tournaments_count ) ); ?></li>
										</ul>
								</div>
						</div>

						<div class="postbox bhg-dashboard-card">
								<h2 class="hndle"><span><?php esc_html_e( 'Latest Hunts', 'bonus-hunt-guesser' ); ?></span></h2>
								<div class="inside">
										<div class="bhg-dashboard-table-wrapper">
												<table class="wp-list-table widefat striped">
														<thead>
																<tr>
																		<th><?php esc_html_e( 'Bonushunt', 'bonus-hunt-guesser' ); ?></th>
																		<th><?php esc_html_e( 'All Winners', 'bonus-hunt-guesser' ); ?></th>
																		<th><?php esc_html_e( 'Start Balance', 'bonus-hunt-guesser' ); ?></th>
																		<th><?php esc_html_e( 'Final Balance', 'bonus-hunt-guesser' ); ?></th>
																		<th><?php esc_html_e( 'Closed At', 'bonus-hunt-guesser' ); ?></th>
																</tr>
														</thead>
														<tbody>
																<?php if ( ! empty( $hunts ) && is_array( $hunts ) ) : ?>
																		<?php foreach ( $hunts as $h ) : ?>
																				<?php
																				$hunt_id       = isset( $h->id ) ? (int) $h->id : 0;
																				$winners_count = isset( $h->winners_count ) ? (int) $h->winners_count : 0;
																				$winners       = array();

																				if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
																						$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_count );
																					if ( ! is_array( $winners ) ) {
																							$winners = array();
																					}
																				}

																				$hunt_title = isset( $h->title ) ? (string) $h->title : '';
																				$start      = isset( $h->starting_balance ) ? (float) $h->starting_balance : 0.0;
																				?>
																				<tr>
		<td><?php echo '' !== $hunt_title ? esc_html( $hunt_title ) : esc_html__( '(untitled)', 'bonus-hunt-guesser' ); ?></td>
																						<td>
																								<?php
																								if ( ! empty( $winners ) ) {
																										$out = array();
																									foreach ( $winners as $w ) {
																											$user_id = isset( $w->user_id ) ? (int) $w->user_id : 0;
																											$guess   = isset( $w->guess ) ? (float) $w->guess : 0.0;
																											$diff    = isset( $w->diff ) ? (float) $w->diff : 0.0;

																											$u  = $user_id ? get_userdata( $user_id ) : false;
																											$nm = $u ? $u->user_login : sprintf(
																													/* translators: %d: user ID. */
																												esc_html__( 'User #%d', 'bonus-hunt-guesser' ),
																												$user_id
																											);

																											// Compose: "name — 1,234.00 (diff 12.34)".
																											$out[] = sprintf(
																												'%1$s %2$s %3$s (%4$s %5$s)',
																												esc_html( $nm ),
																												esc_html_x( '—', 'name/guess separator', 'bonus-hunt-guesser' ),
																												esc_html( number_format_i18n( $guess, 2 ) ),
																												esc_html__( 'diff', 'bonus-hunt-guesser' ),
																												esc_html( number_format_i18n( $diff, 2 ) )
																											);
																									}
																										// Implode to a single, safely-escaped string separated by dots.
																										echo esc_html( implode( ' • ', $out ) );
																								} else {
																										esc_html_e( 'No winners yet', 'bonus-hunt-guesser' );
																								}
																								?>
																						</td>
																						<td><?php echo esc_html( number_format_i18n( $start, 2 ) ); ?></td>
																						<td>
																								<?php
																								if ( isset( $h->final_balance ) && null !== $h->final_balance ) {
																										echo esc_html( number_format_i18n( (float) $h->final_balance, 2 ) );
																								} else {
																										esc_html_e( '—', 'bonus-hunt-guesser' );
																								}
																								?>
																						</td>
																						<td>
																								<?php
																								if ( ! empty( $h->closed_at ) ) {
																										$ts = strtotime( (string) $h->closed_at );
																										echo esc_html(
																											false !== $ts
																														? date_i18n(
																															get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
																															$ts
																														)
																														: (string) $h->closed_at
																										);
																								} else {
																										esc_html_e( '—', 'bonus-hunt-guesser' );
																								}
																								?>
																						</td>
																				</tr>
																		<?php endforeach; ?>
																<?php else : ?>
																		<tr>
																				<td colspan="5"><?php esc_html_e( 'No closed hunts yet.', 'bonus-hunt-guesser' ); ?></td>
																		</tr>
																<?php endif; ?>
														</tbody>
												</table>
										</div>
								</div>
						</div>
				</div>
		</div>
</div>
