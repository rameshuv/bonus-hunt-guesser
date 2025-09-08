<?php
/**
 * Tools page for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'bhg_tools', 'BHG Tools' ) ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="bhg_tools_action" />
		<?php wp_nonce_field( 'bhg_tools_action', 'bhg_tools_nonce' ); ?>
		<p>
		<?php
		echo esc_html(
			bhg_t(
				'this_will_delete_all_demo_data_and_pages_then_recreate_fresh_demo_content',
				'This will delete all demo data and pages, then recreate fresh demo content.'
			)
		);
		?>
		</p>
		<p>
			<input type="submit" class="button button-primary" value="<?php echo esc_attr( bhg_t( 'reset_reseed_demo_data', 'Reset & Reseed Demo Data' ) ); ?>" />
		</p>
	</form>

	<?php
	global $wpdb;

	$hunts_table       = esc_sql( "{$wpdb->prefix}bhg_bonus_hunts" );
	$guesses_table     = esc_sql( "{$wpdb->prefix}bhg_guesses" );
	$users_table       = esc_sql( $wpdb->users );
	$ads_table         = esc_sql( "{$wpdb->prefix}bhg_ads" );
	$tournaments_table = esc_sql( "{$wpdb->prefix}bhg_tournaments" );

	$hunts       = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $hunts_table ) );
	$guesses     = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $guesses_table ) );
	$users       = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $users_table ) );
	$ads         = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $ads_table ) );
	$tournaments = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $tournaments_table ) );
	?>

	<div class="card" style="max-width:900px;padding:16px;margin-top:12px;">
		<h2><?php echo esc_html( bhg_t( 'diagnostics', 'Diagnostics' ) ); ?></h2>
		<?php if ( ( $hunts + $guesses + $users + $ads + $tournaments ) > 0 ) : ?>
			<ul>
				<li><?php echo esc_html( bhg_t( 'hunts', 'Hunts:' ) ); ?> <?php echo esc_html( number_format_i18n( $hunts ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'guesses_2', 'Guesses:' ) ); ?> <?php echo esc_html( number_format_i18n( $guesses ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'users', 'Users:' ) ); ?> <?php echo esc_html( number_format_i18n( $users ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'ads', 'Ads:' ) ); ?> <?php echo esc_html( number_format_i18n( $ads ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'tournaments', 'Tournaments:' ) ); ?> <?php echo esc_html( number_format_i18n( $tournaments ) ); ?></li>
			</ul>
		<?php else : ?>
			<p><?php echo esc_html( bhg_t( 'nothing_to_show_yet_start_by_creating_a_hunt_or_a_test_user', 'Nothing to show yet. Start by creating a hunt or a test user.' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
