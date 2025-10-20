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
$hunts_count       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$hunts_table}" );
$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tournaments_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tournaments_table}" );

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
                                                                                                                               <div class="bhg-latest-hunts-list">
                                                                                                                               <?php foreach ( $hunts as $h ) : ?>
                                                                                                                               <?php
                                                                                                                               $hunt_id        = isset( $h->id ) ? (int) $h->id : 0;
                                                                                                                               $winners_count  = isset( $h->winners_count ) ? (int) $h->winners_count : 0;
                                                                                                                               $winners_limit  = $winners_count > 0 ? $winners_count : 25;
                                                                                                                               $hunt_title     = isset( $h->title ) ? (string) $h->title : '';
                                                                                                                               $start_balance  = isset( $h->starting_balance ) ? (float) $h->starting_balance : 0.0;
                                                                                                                               $final_balance  = isset( $h->final_balance ) ? $h->final_balance : null;
                                                                                                                               $closed_at      = isset( $h->closed_at ) ? (string) $h->closed_at : '';
                                                                                                                               $winners        = array();

                                                                                                                               if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
                                                                                                                               $winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_limit );
                                                                                                                               if ( ! is_array( $winners ) ) {
                                                                                                                               $winners = array();
                                                                                                                               }
                                                                                                                               }

                                                                                                                               $highlight_count = $winners_count > 0 ? min( $winners_count, count( $winners ) ) : min( 3, count( $winners ) );
                                                                                                                               $highlight_count = max( 0, $highlight_count );

                                                                                                                               $closed_text = bhg_t( 'label_emdash', '—' );
                                                                                                                               if ( ! empty( $closed_at ) ) {
                                                                                                                               $ts = strtotime( $closed_at );
                                                                                                                               $closed_text = false !== $ts
                                                                                                                               ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts )
                                                                                                                               : $closed_at;
                                                                                                                               }
                                                                                                                               ?>
                                                                                                                               <article class="bhg-latest-hunt">
                                                                                                                               <header class="bhg-latest-hunt-header">
                                                                                                                               <h3 class="bhg-latest-hunt-title"><?php echo '' !== $hunt_title ? esc_html( $hunt_title ) : esc_html( bhg_t( 'label_untitled', '(untitled)' ) ); ?></h3>
                                                                                                                               <ul class="bhg-latest-hunt-meta">
                                                                                                                               <li><span class="bhg-meta-label"><?php echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ); ?></span><span class="bhg-meta-value"><?php echo esc_html( bhg_format_currency( $start_balance ) ); ?></span></li>
                                                                                                                               <li><span class="bhg-meta-label"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></span><span class="bhg-meta-value"><?php echo null !== $final_balance ? esc_html( bhg_format_currency( (float) $final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></span></li>
                                                                                                                               <li><span class="bhg-meta-label"><?php echo esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ); ?></span><span class="bhg-meta-value"><?php echo esc_html( $closed_text ); ?></span></li>
                                                                                                                               </ul>
                                                                                                                               </header>

                                                                                                                               <?php if ( ! empty( $winners ) ) : ?>
                                                                                                                               <table class="bhg-dashboard-table bhg-winners-table">
                                                                                                                               <thead>
                                                                                                                               <tr>
                                                                                                                               <th scope="col"><?php echo esc_html( bhg_t( 'label_position', 'Position' ) ); ?></th>
                                                                                                                               <th scope="col"><?php echo esc_html( bhg_t( 'label_username', 'Username' ) ); ?></th>
                                                                                                                               <th scope="col"><?php echo esc_html( bhg_t( 'label_guess', 'Guess' ) ); ?></th>
                                                                                                                               <th scope="col"><?php echo esc_html( bhg_t( 'label_difference', 'Difference' ) ); ?></th>
                                                                                                                               </tr>
                                                                                                                               </thead>
                                                                                                                               <tbody>
                                                                                                                               <?php foreach ( $winners as $index => $winner ) : ?>
                                                                                                                               <?php
                                                                                                                               $position   = $index + 1;
                                                                                                                               $user_id    = isset( $winner->user_id ) ? (int) $winner->user_id : 0;
                                                                                                                               $guess      = isset( $winner->guess ) ? (float) $winner->guess : 0.0;
                                                                                                                               $diff       = isset( $winner->diff ) ? (float) abs( $winner->diff ) : 0.0;
                                                                                                                               $user_data  = $user_id ? get_userdata( $user_id ) : false;
                                                                                                                               $user_label = $user_data ? $user_data->user_login : sprintf(
                                                                                                                               /* translators: %d: user ID. */
                                                                                                                               bhg_t( 'label_user_number', 'User #%d' ),
                                                                                                                               $user_id
                                                                                                                               );
                                                                                                                               $user_label = (string) $user_label;
                                                                                                                               $user_link  = $user_id ? get_edit_user_link( $user_id ) : '';

                                                                                                                               $row_classes = array();
                                                                                                                               if ( $highlight_count > 0 && $position <= $highlight_count ) {
                                                                                                                               $row_classes[] = 'bhg-winner-row';
                                                                                                                               }

                                                                                                                               $row_class_attr = ! empty( $row_classes ) ? ' class="' . esc_attr( implode( ' ', $row_classes ) ) . '"' : '';
                                                                                                                               ?>
                                                                                                                               <tr<?php echo $row_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                                                                                               >
                                                                                                                               <td><?php echo (int) $position; ?></td>
                                                                                                                               <td>
                                                                                                                               <?php if ( $user_link ) : ?>
                                                                                                                               <a href="<?php echo esc_url( $user_link ); ?>"><?php echo esc_html( $user_label ); ?></a>
                                                                                                                               <?php else : ?>
                                                                                                                               <?php echo esc_html( $user_label ); ?>
                                                                                                                               <?php endif; ?>
                                                                                                                               </td>
                                                                                                                               <td><?php echo esc_html( bhg_format_currency( $guess ) ); ?></td>
                                                                                                                               <td><?php echo esc_html( bhg_format_currency( $diff ) ); ?></td>
                                                                                                                               </tr>
                                                                                                                               <?php endforeach; ?>
                                                                                                                               </tbody>
                                                                                                                               </table>
                                                                                                                               <?php else : ?>
                                                                                                                               <p class="bhg-empty-state"><?php echo esc_html( bhg_t( 'no_winners_yet', 'No winners yet' ) ); ?></p>
                                                                                                                               <?php endif; ?>
                                                                                                                               </article>
                                                                                                                               <?php endforeach; ?>
                                                                                                                               </div>
                                                                                                                               <?php else : ?>
                                                                                                                               <p><?php echo esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ); ?></p>
                                                                                                                               <?php endif; ?>
                                                                                                                               <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts' ) ); ?>" class="button button-primary bhg-dashboard-button"><?php echo esc_html( bhg_t( 'view_all_hunts', 'View All Hunts' ) ); ?></a></p>
                                                                                                </div>
								</section>
				</main>
</div>
