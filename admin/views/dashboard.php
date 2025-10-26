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
		esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) )
	);
}

if ( ! function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	wp_die(
		esc_html( bhg_t( 'helper_function_bhggetlatestclosedhunts_missing_please_include_classbhgbonushuntsphp_helpers', 'Helper function bhg_get_latest_closed_hunts() missing. Please include class-bhg-bonus-hunts.php helpers.' ) )
	);
}

global $wpdb;

$hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$hunts_count       = (int) $wpdb->get_var( /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name sanitized via esc_sql above. */ 'SELECT COUNT(*) FROM ' . $hunts_table );
$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tournaments_count = (int) $wpdb->get_var( /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name sanitized via esc_sql above. */ 'SELECT COUNT(*) FROM ' . $tournaments_table );

$user_counts = count_users();
$users_count = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;

$hunts = bhg_get_latest_closed_hunts( 3 ); // Expect: id, title, starting_balance, final_balance, winners_count, closed_at.
// Output dashboard with full-width cards.
?>
<div class="wrap bhg-admin bhg-wrap bhg-dashboard">
				<h1 class="bhg-dashboard-heading"><?php echo esc_html( bhg_t( 'menu_dashboard', 'Dashboard' ) ); ?></h1>

				<main class="bhg-dashboard-cards">
								<section class="bhg-dashboard-card" aria-labelledby="bhg-dashboard-summary-title" role="region">
												<header class="bhg-card-header">
																<h2 id="bhg-dashboard-summary-title" class="bhg-card-title"><?php echo esc_html( bhg_t( 'summary', 'Summary' ) ); ?></h2>
												</header>
												<div class="bhg-card-content">
																<table class="bhg-dashboard-table bhg-summary-table">
																				<thead>
																								<tr>
																												<th><span class="dashicons dashicons-book-alt"></span> <?php echo esc_html( bhg_t( 'hunts', 'Hunts' ) ); ?></th>
																												<th><span class="dashicons dashicons-groups"></span> <?php echo esc_html( bhg_t( 'users', 'Users' ) ); ?></th>
																												<th><span class="dashicons dashicons-awards"></span> <?php echo esc_html( bhg_t( 'tournaments', 'Tournaments' ) ); ?></th>
																								</tr>
																				</thead>
																				<tbody>
																								<tr>
																												<td><?php echo esc_html( number_format_i18n( $hunts_count ) ); ?></td>
																												<td><?php echo esc_html( number_format_i18n( $users_count ) ); ?></td>
																												<td><?php echo esc_html( number_format_i18n( $tournaments_count ) ); ?></td>
																								</tr>
																				</tbody>
																</table>
												</div>
								</section>

								<section class="bhg-dashboard-card" aria-labelledby="bhg-dashboard-latest-title" role="region">
												<header class="bhg-card-header">
																<h2 id="bhg-dashboard-latest-title" class="bhg-card-title"><?php echo esc_html( bhg_t( 'label_latest_hunts', 'Latest Hunts' ) ); ?></h2>
												</header>
												<div class="bhg-card-content">
																<?php if ( ! empty( $hunts ) && is_array( $hunts ) ) : ?>
																<table class="bhg-dashboard-table bhg-latest-hunts-table">
																		<thead>
																				<tr>
																						<th><?php echo esc_html( bhg_t( 'label_bonushunt', 'Bonushunt' ) ); ?></th>
																						<th><?php echo esc_html( bhg_t( 'label_all_winners', 'All Winners' ) ); ?></th>
																						<th><?php echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ); ?></th>
																						<th><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></th>
																						<th><?php echo esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ); ?></th>
																				</tr>
																		</thead>
																		<tbody>
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

																			$hunt_title  = isset( $h->title ) ? (string) $h->title : '';
																			$start       = isset( $h->starting_balance ) ? (float) $h->starting_balance : 0.0;
																			$winners_out = '';

																			if ( ! empty( $winners ) ) {
																				$items = array();
																				foreach ( $winners as $w ) {
																					$user_id = isset( $w->user_id ) ? (int) $w->user_id : 0;
																					$guess   = isset( $w->guess ) ? (float) $w->guess : 0.0;
																					$diff    = isset( $w->diff ) ? (float) $w->diff : 0.0;

																					$u  = $user_id ? get_userdata( $user_id ) : false;
																					$nm = $u ? $u->user_login : sprintf(
																					/* translators: %d: user ID. */
																						esc_html( bhg_t( 'label_user_number', 'User #%d' ) ),
																						$user_id
																					);

																					$items[] = sprintf(
																						'<li class="bhg-dashboard-winner"><strong class="bhg-dashboard-winner-name">%1$s</strong> <span class="bhg-dashboard-winner-meta">%2$s %3$s</span></li>',
																						esc_html( $nm ),
																						esc_html( bhg_format_currency( $guess ) ),
																						esc_html(
																							sprintf(
																							/* translators: %s: formatted currency value. */
																								bhg_t( 'diff_value', '(%s diff)' ),
																								bhg_format_currency( $diff )
																							)
																						)
																					);
																				}
																				$winners_out = $items ? '<ul class="bhg-dashboard-winners">' . implode( '', $items ) . '</ul>' : '';
																			} else {
																																						$winners_out = '';
																			}

																			?>
																				<tr>
																						<td><?php echo '' !== $hunt_title ? esc_html( $hunt_title ) : esc_html( bhg_t( 'label_untitled', '(untitled)' ) ); ?></td>
																						<td>
																			<?php
																			if ( '' !== $winners_out ) {
																					echo wp_kses_post( $winners_out );
																			} else {
																					echo esc_html( bhg_t( 'no_winners_yet', 'No winners yet' ) );
																			}
																			?>
																			</td>
																																<td><?php echo esc_html( bhg_format_currency( $start ) ); ?></td>
																						<td>
																						<?php
																						if ( isset( $h->final_balance ) && null !== $h->final_balance ) {
																																echo esc_html( bhg_format_currency( (float) $h->final_balance ) );
																						} else {
																								echo esc_html( bhg_t( 'label_emdash', '—' ) );
																						}
																						?>
																						</td>
																						<td>
																						<?php
																						if ( ! empty( $h->closed_at ) ) {
																								$ts = strtotime( (string) $h->closed_at );
																								echo esc_html(
																									false !== $ts
																												? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts )
																												: (string) $h->closed_at
																								);
																						} else {
																								echo esc_html( bhg_t( 'label_emdash', '—' ) );
																						}
																						?>
																						</td>
																				</tr>
																		<?php endforeach; ?>
																		</tbody>
																</table>
																<?php else : ?>
																<p><?php echo esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ); ?></p>
																<?php endif; ?>
																<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts' ) ); ?>" class="button button-primary bhg-dashboard-button"><?php echo esc_html( bhg_t( 'view_all_hunts', 'View All Hunts' ) ); ?></a></p>
												</div>
								</section>
				</main>
</div>
