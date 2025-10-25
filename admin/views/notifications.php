<?php
/**
 * Notifications settings view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$settings    = class_exists( 'BHG_Notifications' ) ? BHG_Notifications::get_settings() : array();
$definitions = class_exists( 'BHG_Notifications' ) ? BHG_Notifications::get_notification_types() : array();
$message     = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$error_code  = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ); ?></h1>

	<?php if ( 'saved' === $message ) : ?>
		<div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'settings_saved', 'Settings saved.' ) ); ?></p></div>
	<?php elseif ( 'nonce_failed' === $error_code ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'bhg_notifications', 'bhg_notifications_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_notifications" />

		<?php foreach ( $definitions as $type => $definition ) :
			$config          = isset( $settings[ $type ] ) ? $settings[ $type ] : array();
			$enabled         = isset( $config['enabled'] ) ? (int) $config['enabled'] : 0;
			$title           = isset( $config['title'] ) ? $config['title'] : '';
			$description     = isset( $config['description'] ) ? $config['description'] : '';
			$bcc             = isset( $config['bcc'] ) ? $config['bcc'] : '';
			$label           = isset( $definition['label'] ) ? $definition['label'] : ucfirst( $type );
			$description_text = isset( $definition['description'] ) ? $definition['description'] : '';
			$placeholders    = isset( $definition['placeholders'] ) && is_array( $definition['placeholders'] ) ? $definition['placeholders'] : array();
			?>
			<h2><?php echo esc_html( $label ); ?></h2>
			<?php if ( $description_text ) : ?>
				<p class="description"><?php echo esc_html( $description_text ); ?></p>
			<?php endif; ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php echo esc_html( bhg_t( 'notification_enable', 'Enable notification' ) ); ?></th>
						<td>
							<input type="hidden" name="notifications[<?php echo esc_attr( $type ); ?>][enabled]" value="0" />
							<label>
								<input type="checkbox" name="notifications[<?php echo esc_attr( $type ); ?>][enabled]" value="1" <?php checked( 1, $enabled ); ?> />
								<?php echo esc_html( bhg_t( 'yes', 'Yes' ) ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bhg-notification-<?php echo esc_attr( $type ); ?>-title"><?php echo esc_html( bhg_t( 'notification_title', 'Title / Subject' ) ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id="bhg-notification-<?php echo esc_attr( $type ); ?>-title" name="notifications[<?php echo esc_attr( $type ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bhg-notification-<?php echo esc_attr( $type ); ?>-description"><?php echo esc_html( bhg_t( 'notification_description', 'HTML description / body' ) ); ?></label></th>
						<td>
							<textarea class="large-text code" rows="8" id="bhg-notification-<?php echo esc_attr( $type ); ?>-description" name="notifications[<?php echo esc_attr( $type ); ?>][description]"><?php echo esc_textarea( $description ); ?></textarea>
							<?php if ( $placeholders ) : ?>
								<p class="description">
									<?php echo esc_html( bhg_t( 'notification_available_placeholders', 'Available placeholders:' ) ); ?>
									<?php
									$placeholder_parts = array();
									foreach ( $placeholders as $placeholder => $help ) {
										$placeholder_parts[] = sprintf( '%s (%s)', esc_html( $placeholder ), esc_html( $help ) );
									}
									echo ' ' . esc_html( implode( '; ', $placeholder_parts ) );
									?>
								</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bhg-notification-<?php echo esc_attr( $type ); ?>-bcc"><?php echo esc_html( bhg_t( 'notification_bcc', 'BCC recipients' ) ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id="bhg-notification-<?php echo esc_attr( $type ); ?>-bcc" name="notifications[<?php echo esc_attr( $type ); ?>][bcc]" value="<?php echo esc_attr( $bcc ); ?>" placeholder="admin@example.com, team@example.com" />
							<p class="description"><?php echo esc_html( bhg_t( 'notification_bcc_help', 'Comma separated email addresses to receive a blind carbon copy.' ) ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		<?php endforeach; ?>

		<?php submit_button( bhg_t( 'save_settings', 'Save Settings' ) ); ?>
	</form>
</div>
