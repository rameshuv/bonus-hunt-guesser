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

$hunts_table       = $wpdb->prefix . 'bhg_bonus_hunts';
$hunts_sql         = "SELECT COUNT(*) FROM {$hunts_table}";
$hunts_count       = (int) $wpdb->get_var( $hunts_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Counting total hunts for dashboard summary.
$tournaments_table = $wpdb->prefix . 'bhg_tournaments';
$tournaments_sql   = "SELECT COUNT(*) FROM {$tournaments_table}";
$tournaments_count = (int) $wpdb->get_var( $tournaments_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Counting total tournaments for dashboard summary.

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
																	<?php
																	foreach ( $hunts as $hunt ) :
																		$hunt_id       = isset( $hunt->id ) ? (int) $hunt->id : 0;
																		$winners_count = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : 0;
																		$winners       = array();

																		if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
																			$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_count );
																			if ( ! is_array( $winners ) ) {
																																			$winners = array();
																			}
																		}

																		$hunt_title     = isset( $hunt->title ) ? (string) $hunt->title : '';
																		$start_balance  = isset( $hunt->starting_balance ) ? (float) $hunt->starting_balance : 0.0;
																		$final_balance  = isset( $hunt->final_balance ) && null !== $hunt->final_balance ? (float) $hunt->final_balance : null;
																		$closed_display = '';

																		if ( ! empty( $hunt->closed_at ) ) {
																			$closed_timestamp = strtotime( (string) $hunt->closed_at );
																			$closed_display   = false !== $closed_timestamp
																			? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $closed_timestamp )
																			: (string) $hunt->closed_at;
																		}

																		$winner_rows       = array();
																		$winner_row_count  = max( count( $winners ), 1 );
																		$hunt_title_output = '' !== $hunt_title ? esc_html( $hunt_title ) : esc_html( bhg_t( 'label_untitled', '(untitled)' ) );

																		if ( ! empty( $winners ) ) {
																			foreach ( $winners as $winner_row ) {
																				$user_id = isset( $winner_row->user_id ) ? (int) $winner_row->user_id : 0;
																				$guess   = isset( $winner_row->guess ) ? (float) $winner_row->guess : 0.0;
																				$diff    = isset( $winner_row->diff ) ? (float) $winner_row->diff : 0.0;

																				$user         = $user_id ? get_userdata( $user_id ) : false;
																				$display_name = $user ? $user->user_login : sprintf(
																				/* translators: %d: user ID. */
																					esc_html( bhg_t( 'label_user_number', 'User #%d' ) ),
																					$user_id
																				);
																				$winner_rows[] = sprintf(
																					'<strong class="bhg-dashboard-winner-name">%1$s</strong> <span class="bhg-dashboard-winner-separator">%2$s</span> <span class="bhg-dashboard-winner-guess">%3$s</span> <span class="bhg-dashboard-winner-diff">(%4$s %5$s)</span>',
																					esc_html( $display_name ),
																					esc_html_x( '—', 'name/guess separator', 'bonus-hunt-guesser' ),
																					esc_html( bhg_format_currency( $guess ) ),
																					esc_html( bhg_t( 'label_diff', 'diff' ) ),
																					esc_html( bhg_format_currency( $diff ) )
																				);
																			}
																		} else {
																			$winner_rows[] = esc_html( bhg_t( 'no_winners_yet', 'No winners yet' ) );
																		}

																		$first_winner = array_shift( $winner_rows );
																		?>
<tr>
<td rowspan="<?php echo esc_attr( $winner_row_count ); ?>"><?php echo $hunt_title_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above. ?></td>
<td class="bhg-dashboard-winner-cell"><?php echo wp_kses_post( $first_winner ); ?></td>
<td rowspan="<?php echo esc_attr( $winner_row_count ); ?>"><?php echo esc_html( bhg_format_currency( $start_balance ) ); ?></td>
<td rowspan="<?php echo esc_attr( $winner_row_count ); ?>"><?php echo null !== $final_balance ? esc_html( bhg_format_currency( $final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
<td rowspan="<?php echo esc_attr( $winner_row_count ); ?>"><?php echo '' !== $closed_display ? esc_html( $closed_display ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
</tr>
																		<?php foreach ( $winner_rows as $winner_html ) : ?>
<tr>
<td class="bhg-dashboard-winner-cell"><?php echo wp_kses_post( $winner_html ); ?></td>
</tr>
																		<?php endforeach; // End winner rows. ?>
<?php endforeach; // End hunts loop. ?>
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
