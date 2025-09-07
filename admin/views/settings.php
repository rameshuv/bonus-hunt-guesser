<?php
if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}
?>
<div class="wrap">
		<h1><?php echo esc_html( bhg_t( 'bonus_hunt_guesser_settings', 'Bonus Hunt Guesser Settings' ) ); ?></h1>
		<p><?php echo esc_html( bhg_t( 'settings_currently_unavailable', 'Settings management is currently unavailable.' ) ); ?></p>
</div>
