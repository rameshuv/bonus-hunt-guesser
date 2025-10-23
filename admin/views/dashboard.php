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
$hunts_count       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$hunts_table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tournaments_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tournaments_table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

$user_counts = count_users();
$users_count = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;

$hunts = bhg_get_latest_closed_hunts( 3 ); // Expect: id, title, starting_balance, final_balance, winners_count, closed_at.

$allowed_winner_tags = array(
	'strong' => array(),
	'span'   => array(
		'class' => array(),
	),
);
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
		$hunt_title    = isset( $hunt->title ) ? (string) $hunt->title : '';
		$start_balance = isset( $hunt->starting_balance ) ? (float) $hunt->starting_balance : 0.0;
		$final_balance = ( isset( $hunt->final_balance ) && null !== $hunt->final_balance ) ? (float) $hunt->final_balance : null;
		$closed_at     = isset( $hunt->closed_at ) ? (string) $hunt->closed_at : '';
		$winner_limit  = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : 0;
		$winner_limit  = max( 1, min( 25, $winner_limit ) );

		$final_display  = ( null !== $final_balance ) ? bhg_format_money( $final_balance ) : bhg_t( 'label_emdash', '—' );
		$closed_display = bhg_t( 'label_emdash', '—' );
		if ( '' !== $closed_at ) {
			$timestamp = strtotime( $closed_at );
			if ( false !== $timestamp ) {
				$closed_display = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
			} else {
				$closed_display = $closed_at;
			}
		}

				$winner_results = function_exists( 'bhg_get_top_winners_for_hunt' ) ? bhg_get_top_winners_for_hunt( $hunt_id, $winner_limit ) : array();
		$winner_rows            = array();

		if ( ! empty( $winner_results ) ) {
			$diff_label = bhg_t( 'label_difference', 'Difference' );

			foreach ( $winner_results as $index => $winner ) {
				$user_id  = isset( $winner->user_id ) ? (int) $winner->user_id : 0;
				$guess    = isset( $winner->guess ) ? (float) $winner->guess : 0.0;
				$diff     = isset( $winner->diff ) ? (float) $winner->diff : 0.0;
				$user     = $user_id ? get_userdata( $user_id ) : null;
				$username = $user && ! empty( $user->user_login ) ? $user->user_login : sprintf( /* translators: %d: user ID. */ bhg_t( 'label_user_number', 'User #%d' ), $user_id );

				$winner_rows[] = array(
					'content'   => sprintf(
						'<strong>%1$s</strong> — %2$s <span class="bhg-dashboard-diff">(%3$s %4$s)</span>',
						esc_html( $username ),
						esc_html( bhg_format_money( $guess ) ),
						esc_html( $diff_label ),
						esc_html( bhg_format_money( $diff ) )
					),
					'highlight' => ( $index < $winner_limit ),
				);
			}
		} else {
			$winner_rows[] = array(
				'content'   => esc_html( bhg_t( 'no_winners_yet', 'No winners yet' ) ),
				'highlight' => false,
			);
		}

		$rowspan = max( 1, count( $winner_rows ) );
		?>
		<?php
		foreach ( $winner_rows as $row_index => $winner_row ) :
			$row_classes = array( 'bhg-latest-hunts-row' );
			if ( ! empty( $winner_row['highlight'] ) ) {
				$row_classes[] = 'is-winner';
			}
			?>
<tr class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
			<?php if ( 0 === $row_index ) : ?>
<td class="bhg-latest-hunts-title" rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo '' !== $hunt_title ? esc_html( $hunt_title ) : esc_html( bhg_t( 'label_untitled', '(untitled)' ) ); ?></td>
<?php endif; ?>
<td class="bhg-latest-hunts-winner-cell"><?php echo wp_kses( $winner_row['content'], $allowed_winner_tags ); ?></td>
			<?php if ( 0 === $row_index ) : ?>
<td class="bhg-latest-hunts-money" rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo esc_html( bhg_format_money( $start_balance ) ); ?></td>
<td class="bhg-latest-hunts-money" rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo esc_html( $final_display ); ?></td>
<td rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo esc_html( $closed_display ); ?></td>
<?php endif; ?>
</tr>
	<?php endforeach; ?>
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
