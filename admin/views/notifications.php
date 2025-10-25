<?php
/**
 * Notifications settings page.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$notifications = function_exists( 'bhg_get_notification_settings' ) ? bhg_get_notification_settings() : array();
$message       = isset( $_GET['bhg_msg'] ) ? sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) : '';
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ); ?></h1>

	<?php if ( 'notifications_saved' === $message ) : ?>
		<div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'settings_saved', 'Settings saved.' ) ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_notifications" />
		<table class="form-table" role="presentation">
			<tbody>
				<?php
				$blocks = array(
					'winner'     => array(
						'title'       => bhg_t( 'label_winner_notifications', 'Winner Notifications' ),
						'description' => bhg_t( 'winner_notification_tokens', 'Available tags: {{username}}, {{hunt}}, {{final}}, {{winner}}, {{winners}}' ),
					),
					'tournament' => array(
						'title'       => bhg_t( 'label_tournament_notifications', 'Tournament Notifications' ),
						'description' => bhg_t( 'tournament_notification_tokens', 'Available tags: {{tournament}}, {{start}}, {{end}}' ),
					),
					'hunt'       => array(
						'title'       => bhg_t( 'label_hunt_notifications', 'Bonus Hunt Notifications' ),
						'description' => bhg_t( 'hunt_notification_tokens', 'Available tags: {{hunt}}, {{start_balance}}, {{num_bonuses}}' ),
					),
				);

				foreach ( $blocks as $type => $meta ) :
					$data    = isset( $notifications[ $type ] ) ? $notifications[ $type ] : array();
					$enabled = ! empty( $data['enabled'] );
					$subject = isset( $data['subject'] ) ? $data['subject'] : '';
					$body    = isset( $data['description'] ) ? $data['description'] : '';
					$bcc     = isset( $data['bcc'] ) ? $data['bcc'] : '';
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $meta['title'] ); ?></th>
						<td>
							<label>
								<input type="hidden" name="notifications[<?php echo esc_attr( $type ); ?>][enabled]" value="0" />
								<input type="checkbox" name="notifications[<?php echo esc_attr( $type ); ?>][enabled]" value="1" <?php checked( $enabled ); ?> />
								<?php echo esc_html( bhg_t( 'enable_notifications', 'Enable email notifications' ) ); ?>
							</label>
							<p class="description"><?php echo esc_html( $meta['description'] ); ?></p>

							<label for="bhg_notification_subject_<?php echo esc_attr( $type ); ?>"><?php echo esc_html( bhg_t( 'label_email_subject', 'Email Subject' ) ); ?></label>
							<input type="text" class="regular-text" id="bhg_notification_subject_<?php echo esc_attr( $type ); ?>" name="notifications[<?php echo esc_attr( $type ); ?>][subject]" value="<?php echo esc_attr( $subject ); ?>" />

							<label for="bhg_notification_body_<?php echo esc_attr( $type ); ?>" style="display:block;margin-top:12px;">
								<?php echo esc_html( bhg_t( 'label_email_body', 'Email Body' ) ); ?>
							</label>
							<textarea class="large-text" rows="6" id="bhg_notification_body_<?php echo esc_attr( $type ); ?>" name="notifications[<?php echo esc_attr( $type ); ?>][description]"><?php echo esc_textarea( $body ); ?></textarea>

							<label for="bhg_notification_bcc_<?php echo esc_attr( $type ); ?>" style="display:block;margin-top:12px;">
								<?php echo esc_html( bhg_t( 'label_bcc', 'BCC Recipients' ) ); ?>
							</label>
							<input type="text" class="regular-text" id="bhg_notification_bcc_<?php echo esc_attr( $type ); ?>" name="notifications[<?php echo esc_attr( $type ); ?>][bcc]" value="<?php echo esc_attr( $bcc ); ?>" placeholder="admin@example.com" />
							<p class="description"><?php echo esc_html( bhg_t( 'bcc_hint', 'Separate multiple addresses with commas.' ) ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php submit_button( bhg_t( 'save_settings', 'Save Settings' ) ); ?>
	</form>
</div>
