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
				echo esc_html( bhg_t( 'this_will_delete_all_demo_data_and_pages_then_recreate_fresh_demo_content', 'This will delete all demo data and pages, then recreate fresh demo content.' ) );
				?>
</p>
		<p><input type="submit" class="button button-primary" value="
		<?php
		echo esc_attr( bhg_t( 'reset_reseed_demo_data', 'Reset & Reseed Demo Data' ) );
		?>
" /></p>
	</form>

	<?php
	global $wpdb;
	$hunts       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bhg_bonus_hunts" );
	$guesses     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bhg_guesses" );
	$users       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
	$ads         = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bhg_ads" );
	$tournaments = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bhg_tournaments" );
	?>

	<div class="card" style="max-width:900px;padding:16px;margin-top:12px;">
		<h2><?php echo esc_html( bhg_t( 'diagnostics', 'Diagnostics' ) ); ?></h2>
		<?php if ( ( $hunts + $guesses + $users + $ads + $tournaments ) > 0 ) : ?>
			<ul>
				<li><?php echo esc_html( bhg_t( 'hunts', 'Hunts:' ) ); ?> <?php echo number_format_i18n( $hunts ); ?></li>
				<li><?php echo esc_html( bhg_t( 'guesses_2', 'Guesses:' ) ); ?> <?php echo number_format_i18n( $guesses ); ?></li>
				<li><?php echo esc_html( bhg_t( 'users', 'Users:' ) ); ?> <?php echo number_format_i18n( $users ); ?></li>
				<li><?php echo esc_html( bhg_t( 'ads', 'Ads:' ) ); ?> <?php echo number_format_i18n( $ads ); ?></li>
				<li><?php echo esc_html( bhg_t( 'tournaments', 'Tournaments:' ) ); ?> <?php echo number_format_i18n( $tournaments ); ?></li>
			</ul>
		<?php else : ?>
			<p><?php echo esc_html( bhg_t( 'nothing_to_show_yet_start_by_creating_a_hunt_or_a_test_user', 'Nothing to show yet. Start by creating a hunt or a test user.' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>

