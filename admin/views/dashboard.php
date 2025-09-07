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

$hunts = bhg_get_latest_closed_hunts( 3 ); // Expect array of objects with: id, title, starting_balance, final_balance, winners_count, closed_at.
?>
<div class="wrap bhg-dashboard">
	<h1><?php esc_html_e( 'Latest Hunts', 'bonus-hunt-guesser' ); ?></h1>

	<table class="widefat striped">
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
				?>
				<tr>
					<td><?php echo isset( $h->title ) ? esc_html( (string) $h->title ) : esc_html__( '(untitled)', 'bonus-hunt-guesser' ); ?></td>
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

								// Build a plain-text, safely escaped chunk: "name — 1,234.00 (diff 12.34)".
								$out[] = sprintf(
									'%1$s %2$s %3$s (%4$s %5$s)',
									esc_html( $nm ),
									esc_html_x( '—', 'name/guess separator', 'bonus-hunt-guesser' ),
									esc_html( number_format_i18n( $guess, 2 ) ),
									esc_html__( 'diff', 'bonus-hunt-guesser' ),
									esc_html( number_format_i18n( $diff, 2 ) )
								);
							}
							echo esc_html( implode( ' • ', $out ) );
						} else {
							esc_html_e( 'No winners yet', 'bonus-hunt-guesser' );
						}
						?>
					</td>
					<td>
						<?php
						$start = isset( $h->starting_balance ) ? (float) $h->starting_balance : 0.0;
						echo esc_html( number_format_i18n( $start, 2 ) );
						?>
					</td>
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
