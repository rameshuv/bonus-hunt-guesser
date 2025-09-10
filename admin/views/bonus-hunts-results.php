<?php
/**
 * Bonus hunt and tournament results view.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'insufficient_permissions', 'Insufficient permissions' ) ) );
}
global $wpdb;

$view_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'hunt';
$view_type = ( 'tournament' === $view_type ) ? 'tournament' : 'hunt';
$item_id   = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

$hunts_table = $wpdb->prefix . 'bhg_bonus_hunts';
$guess_table = $wpdb->prefix . 'bhg_guesses';
$tour_table  = $wpdb->prefix . 'bhg_tournaments';
$tres_table  = $wpdb->prefix . 'bhg_tournament_results';

// Default to latest closed hunt if no ID is provided.
if ( 'hunt' === $view_type && ! $item_id ) {
	$hunt = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM %i WHERE status=%s ORDER BY closed_at DESC LIMIT 1',
			$hunts_table,
			'closed'
		)
	);
	if ( $hunt ) {
			$item_id = (int) $hunt->id;
	}
} elseif ( 'hunt' === $view_type ) {
	$hunt = $wpdb->get_row(
		$wpdb->prepare( 'SELECT * FROM %i WHERE id=%d', $hunts_table, $item_id )
	);
} elseif ( 'tournament' === $view_type && $item_id ) {
	$tournament = $wpdb->get_row(
		$wpdb->prepare( 'SELECT * FROM %i WHERE id=%d', $tour_table, $item_id )
	);
}

if ( 'tournament' === $view_type ) {
	if ( empty( $tournament ) ) {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'tournament_not_found', 'Tournament not found' ) ) . '</h1></div>';
			return;
	}
	$rows         = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT r.wins, u.display_name FROM %i r JOIN %i u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY r.wins DESC, r.id ASC',
			$tres_table,
			$wpdb->users,
			$item_id
		)
	);
	$result_title = $tournament->title;
	$wcount       = 3;
	$columns      = array(
		'sc_position' => bhg_t( 'sc_position', 'Position' ),
		'sc_user'     => bhg_t( 'sc_user', 'User' ),
		'wins'        => bhg_t( 'wins', 'Wins' ),
	);
} else {
	if ( empty( $hunt ) ) {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'hunt_not_found', 'Hunt not found' ) ) . '</h1></div>';
			return;
	}
	$rows         = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT g.guess, u.display_name, ABS(g.guess - %f) AS diff FROM %i g JOIN %i u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY diff ASC, g.id ASC',
			(float) $hunt->final_balance,
			$guess_table,
			$wpdb->users,
			$item_id
		)
	);
	$result_title = $hunt->title;
	$wcount       = (int) $hunt->winners_count;
	if ( $wcount < 1 ) {
			$wcount = 3;
	}
	$columns = array(
		'sc_position' => bhg_t( 'sc_position', 'Position' ),
		'sc_user'     => bhg_t( 'sc_user', 'User' ),
		'sc_guess'    => bhg_t( 'sc_guess', 'Guess' ),
		'difference'  => bhg_t( 'difference', 'Difference' ),
	);
}

// Gather hunts and tournaments for the selector.
$all_hunts = $wpdb->get_results(
	$wpdb->prepare( 'SELECT id, title FROM %i ORDER BY closed_at DESC, id DESC', $hunts_table )
);
$all_tours = $wpdb->get_results(
	$wpdb->prepare( 'SELECT id, title FROM %i ORDER BY id DESC', $tour_table )
);
$current   = $view_type . '-' . $item_id;
$base_url  = esc_url( admin_url( 'admin.php?page=bhg-hunt-results' ) );
?>
<div class="wrap">
<h1><?php echo esc_html( sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $result_title ) ); ?></h1>
	<div style="margin:1em 0;">
			<select id="bhg-results-select">
			<?php foreach ( (array) $all_hunts as $h ) : ?>
							<?php $val = 'hunt-' . (int) $h->id; ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $h->title ); ?></option>
			<?php endforeach; ?>
			<?php foreach ( (array) $all_tours as $t ) : ?>
							<?php $val = 'tournament-' . (int) $t->id; ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $t->title ); ?></option>
			<?php endforeach; ?>
			</select>
	</div>
	<table class="widefat striped">
			<thead>
					<tr>
							<?php foreach ( $columns as $label ) : ?>
									<th><?php echo esc_html( $label ); ?></th>
							<?php endforeach; ?>
					</tr>
			</thead>
			<tbody>
			<?php
			$pos = 1;
			foreach ( (array) $rows as $r ) :
					$is_winner = $pos <= $wcount;
				?>
					<tr<?php echo $is_winner ? ' class="bhg-winner-row"' : ''; ?>>
							<td><?php echo (int) $pos; ?></td>
							<td><?php echo esc_html( $r->display_name ); ?></td>
								<?php if ( 'tournament' === $view_type ) : ?>
										<td><?php echo (int) $r->wins; ?></td>
								<?php else : ?>
									<td><?php echo esc_html( number_format_i18n( (float) $r->guess, 2 ) ); ?></td>
									<td><?php echo esc_html( number_format_i18n( (float) $r->diff, 2 ) ); ?></td>
							<?php endif; ?>
					</tr>
					<?php
					++$pos;
			endforeach;
			?>
			</tbody>
	</table>
</div>
<script>
document.getElementById('bhg-results-select').addEventListener('change', function () {
	var val = this.value.split('-');
	if ( val.length < 2 ) {
			return;
	}
	var type = val[0];
	var id   = val[1];
		window.location = '<?php echo esc_url_raw( $base_url ); ?>' + '&type=' + type + '&id=' + id;
});
</script>
