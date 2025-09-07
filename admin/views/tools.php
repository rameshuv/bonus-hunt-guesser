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
	<h1><?php echo esc_html__( 'BHG Tools', 'bonus-hunt-guesser' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="bhg_demo_reseed" />
		<?php wp_nonce_field( 'bhg_demo_reseed' ); ?>
		<p><?php esc_html_e( 'This will delete all demo data and pages, then recreate fresh demo content.', 'bonus-hunt-guesser' ); ?></p>
		<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Reset & Reseed Demo Data', 'bonus-hunt-guesser' ); ?>" /></p>
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
		<h2><?php echo esc_html__( 'Diagnostics', 'bonus-hunt-guesser' ); ?></h2>
		<?php if ( ( $hunts + $guesses + $users + $ads + $tournaments ) > 0 ) : ?>
			<ul>
				<li><?php echo esc_html__( 'Hunts:', 'bonus-hunt-guesser' ); ?> <?php echo number_format_i18n( $hunts ); ?></li>
				<li><?php echo esc_html__( 'Guesses:', 'bonus-hunt-guesser' ); ?> <?php echo number_format_i18n( $guesses ); ?></li>
				<li><?php echo esc_html__( 'Users:', 'bonus-hunt-guesser' ); ?> <?php echo number_format_i18n( $users ); ?></li>
				<li><?php echo esc_html__( 'Ads:', 'bonus-hunt-guesser' ); ?> <?php echo number_format_i18n( $ads ); ?></li>
				<li><?php echo esc_html__( 'Tournaments:', 'bonus-hunt-guesser' ); ?> <?php echo number_format_i18n( $tournaments ); ?></li>
			</ul>
		<?php else : ?>
			<p><?php echo esc_html__( 'Nothing to show yet. Start by creating a hunt or a test user.', 'bonus-hunt-guesser' ); ?></p>
		<?php endif; ?>
	</div>
</div>

