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
<div class="wrap bhg-wrap">
	<h1><?php echo esc_html( bhg_t( 'bhg_tools', 'BHG Tools' ) ); ?></h1>

        <?php
        global $wpdb;

        $hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
        $guesses_table     = esc_sql( $wpdb->prefix . 'bhg_guesses' );
        $users_table       = esc_sql( $wpdb->users );
        $ads_table         = esc_sql( $wpdb->prefix . 'bhg_ads' );
        $tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

        $hunts       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$hunts_table}" );
        $guesses     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$guesses_table}" );
        $users       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$users_table}" );
        $ads         = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$ads_table}" );
        $tournaments = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tournaments_table}" );
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
