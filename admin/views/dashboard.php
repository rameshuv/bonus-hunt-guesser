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

global $wpdb;

$hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$hunts_count       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$hunts_table}" );
$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tournaments_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tournaments_table}" );

$user_counts = count_users();
$users_count = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;

$hunts_controller = null;
if ( class_exists( 'BHG_Bonus_Hunts_Controller' ) ) {
	$hunts_controller = BHG_Bonus_Hunts_Controller::get_instance();
}

$hunts = array();
if ( $hunts_controller && method_exists( $hunts_controller, 'get_latest_hunts' ) ) {
	$hunts = $hunts_controller->get_latest_hunts( 3 );
} elseif ( function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	$legacy_hunts = bhg_get_latest_closed_hunts( 3 );
	foreach ( (array) $legacy_hunts as $legacy_hunt ) {
		$hunt_id       = isset( $legacy_hunt->id ) ? (int) $legacy_hunt->id : 0;
		$winners_count = isset( $legacy_hunt->winners_count ) ? (int) $legacy_hunt->winners_count : 0;
		$winners_count = $winners_count > 0 ? $winners_count : 25;
		$winners       = array();

		if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
			$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_count );
		}

		$hunts[] = array(
			'hunt'    => $legacy_hunt,
			'winners' => $winners,
		);
	}
}

$hunts_for_display = array();
foreach ( (array) $hunts as $entry ) {
	$hunt    = null;
	$winners = array();

	if ( is_array( $entry ) && isset( $entry['hunt'] ) ) {
		$hunt    = $entry['hunt'];
		$winners = isset( $entry['winners'] ) ? $entry['winners'] : array();
	} elseif ( is_object( $entry ) ) {
		$hunt = $entry;
	}

	if ( null === $hunt ) {
		continue;
	}

	if ( empty( $winners ) && isset( $hunt->id ) && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
		$limit = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : 0;
		$limit = $limit > 0 ? $limit : 25;
		$winners = bhg_get_top_winners_for_hunt( (int) $hunt->id, $limit );
	}

	$hunts_for_display[] = array(
		'hunt'    => $hunt,
		'winners' => is_array( $winners ) ? $winners : array(),
	);
}
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
				<?php if ( ! empty( $hunts_for_display ) ) : ?>
					<table class="bhg-dashboard-table bhg-latest-hunts-table">
						<thead>
							<tr>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_bonus_hunt', 'Bonushunt' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_all_winners', 'All Winners' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_start_balance', 'Start Balance' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_final_balance', 'Final Balance' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ); ?></th>
							</tr>
						</thead>
						<tbody>
<?php foreach ( $hunts_for_display as $entry ) :
        $hunt     = $entry['hunt'];
        $winners  = $entry['winners'];
        $title    = isset( $hunt->title ) ? (string) $hunt->title : '';
	$start    = isset( $hunt->starting_balance ) ? (float) $hunt->starting_balance : 0.0;
	$final    = isset( $hunt->final_balance ) ? $hunt->final_balance : null;
	$closed   = isset( $hunt->closed_at ) ? (string) $hunt->closed_at : '';
	$final_display = null === $final ? '–' : bhg_format_currency( (float) $final );
	$closed_time   = '–';
	if ( $closed ) {
		$timestamp = strtotime( $closed );
		if ( $timestamp ) {
			$closed_time = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
		}
	}
	$row_classes = array();
	if ( ! empty( $winners ) ) {
		$row_classes[] = 'bhg-latest-hunt--has-winners';
	}
	if ( is_array( $winners ) && count( $winners ) > 1 ) {
		$row_classes[] = 'bhg-latest-hunt--multiple-winners';
	}
	$row_class_attr = ! empty( $row_classes ) ? ' class="' . esc_attr( implode( ' ', $row_classes ) ) . '"' : '';
?>
							<tr<?php echo $row_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<td>
									<strong><?php echo esc_html( $title ); ?></strong>
								</td>
								<td class="bhg-winners-cell">
									<?php if ( ! empty( $winners ) ) : ?>
										<ul class="bhg-winner-list">
<?php foreach ( $winners as $index => $winner ) :
	$position   = isset( $winner->position ) ? (int) $winner->position : ( $index + 1 );
	$user_id    = isset( $winner->user_id ) ? (int) $winner->user_id : 0;
	$name       = '';
	if ( isset( $winner->display_name ) && '' !== trim( (string) $winner->display_name ) ) {
		$name = (string) $winner->display_name;
	} elseif ( $user_id > 0 ) {
		$user = get_userdata( $user_id );
		$name = $user ? $user->display_name : sprintf( bhg_t( 'label_user_number', 'User #%d' ), $user_id );
	} else {
		$name = bhg_t( 'unknown_user', 'Unknown User' );
	}
	$guess_value      = isset( $winner->guess ) ? (float) $winner->guess : 0.0;
	$difference_value = isset( $winner->diff ) ? abs( (float) $winner->diff ) : 0.0;
	$placement_text   = sprintf( '#%d', max( 1, $position ) );
	$guess_text       = sprintf( '%s: %s', bhg_t( 'label_guess', 'Guess' ), bhg_format_currency( $guess_value ) );
	$difference_text  = sprintf( '%s: %s', bhg_t( 'label_difference', 'Difference' ), bhg_format_currency( $difference_value ) );
?>
											<li class="bhg-winner-chip">
												<span class="bhg-winner-chip__placement"><?php echo esc_html( $placement_text ); ?></span>
												<span class="bhg-winner-chip__body">
													<span class="bhg-winner-chip__name"><?php echo esc_html( $name ); ?></span>
													<span class="bhg-winner-chip__stats">
														<?php echo esc_html( $guess_text ); ?>
														<span class="bhg-winner-chip__separator" aria-hidden="true">•</span>
														<?php echo esc_html( $difference_text ); ?>
													</span>
												</span>
											</li>
<?php endforeach; ?>
										</ul>
									<?php else : ?>
										<span class="bhg-empty-state"><?php echo esc_html( bhg_t( 'no_winners_yet', 'No winners yet' ) ); ?></span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( bhg_format_currency( $start ) ); ?></td>
								<td><?php echo esc_html( $final_display ); ?></td>
								<td><?php echo esc_html( $closed_time ); ?></td>
							</tr>
<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php echo esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ); ?></p>
				<?php endif; ?>
			</div>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts' ) ); ?>" class="button button-primary bhg-dashboard-button"><?php echo esc_html( bhg_t( 'view_all_hunts', 'View All Hunts' ) ); ?></a></p>
		</section>
	</main>
</div>
