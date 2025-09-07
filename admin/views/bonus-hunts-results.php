<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'insufficient_permissions', 'Insufficient permissions' ) ) );
}
global $wpdb;
$hunt_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
$hunts   = $wpdb->prefix . 'bhg_bonus_hunts';
$guesses = $wpdb->prefix . 'bhg_guesses';
$hunt    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$hunts` WHERE id=%d", $hunt_id ) );
if ( ! $hunt ) {
	echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'hunt_not_found', 'Hunt not found' ) ) . '</h1></div>';
	return; }
$rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT g.*, u.display_name, ABS(g.guess - %f) as diff FROM `$guesses` g JOIN `$wpdb->users` u ON u.ID=g.user_id WHERE g.hunt_id=%d ORDER BY diff ASC, g.id ASC",
		(float) $hunt->final_balance,
		$hunt_id
	)
);
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'results_for', 'Results for ' ) ) . esc_html( $hunt->title ); ?></h1>
	<table class="widefat striped">
	<thead><tr>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_position', 'Position' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_user', 'User' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_guess', 'Guess' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'difference', 'Difference' ) );
		?>
</th>
	</tr></thead>
	<tbody>
	<?php
	$pos = 1;
	foreach ( $rows as $r ) :
		$wcount = (int) $hunt->winners_count;
		if ( $wcount < 1 ) {
			$wcount = 3;
		} $is_winner = $pos <= $wcount;
		?>
		<tr 
		<?php
		if ( $is_winner ) {
			echo 'class="bhg-winner-row"';}
		?>
		>
		<td><?php echo (int) $pos; ?></td>
		<td><?php echo esc_html( $r->display_name ); ?></td>
		<td><?php echo esc_html( number_format_i18n( (float) $r->guess, 2 ) ); ?></td>
		<td><?php echo esc_html( number_format_i18n( (float) $r->diff, 2 ) ); ?></td>
		</tr>
		<?php
		++$pos;
endforeach;
	?>
	</tbody>
	</table>
</div>
