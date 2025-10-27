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
	wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

$settings = bhg_get_notification_settings();

$sections = array(
	'winners' => array(
		'heading' => bhg_t( 'notification_winners_heading', 'Winners notifications' ),
	),
	'hunts'   => array(
		'heading' => bhg_t( 'notification_hunts_heading', 'Bonus hunt notifications' ),
	),
	'tournaments' => array(
		'heading' => bhg_t( 'notification_tournaments_heading', 'Tournament notifications' ),
	),
);

$token_help = array(
	bhg_t( 'notification_token_hunt_title', '{{hunt_title}} — Bonus hunt title' ),
	bhg_t( 'notification_token_final_balance', '{{final_balance}} — Final balance of the bonus hunt' ),
	bhg_t( 'notification_token_user_name', '{{user_name}} — Recipient display name' ),
	bhg_t( 'notification_token_user_guess', '{{user_guess}} — Recipient guess amount' ),
	bhg_t( 'notification_token_guess_difference', '{{guess_difference}} — Difference between guess and final balance' ),
	bhg_t( 'notification_token_tournament_title', '{{tournament_title}} — Tournament title' ),
	bhg_t( 'notification_token_user_wins', '{{user_wins}} — Total wins for the user' ),
	bhg_t( 'notification_token_results_table', '{{results_table}} — HTML table with tournament standings' ),
);

$message_code = isset( $_GET['bhg_msg'] ) ? sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) : '';
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'notifications_heading', 'Notifications' ) ); ?></h1>
	<p class="description"><?php echo esc_html( bhg_t( 'notifications_description', 'Configure the emails that are sent when hunts and tournaments close.' ) ); ?></p>

	<?php if ( 'notifications_saved' === $message_code ) : ?>
	<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'notifications_saved', 'Notifications saved.' ) ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_notifications">

		<?php foreach ( $sections as $section_key => $section_meta ) : 
			$section_settings = isset( $settings[ $section_key ] ) ? $settings[ $section_key ] : array();
			$enabled          = ! empty( $section_settings['enabled'] );
			$subject          = isset( $section_settings['subject'] ) ? (string) $section_settings['subject'] : '';
			$body             = isset( $section_settings['body'] ) ? (string) $section_settings['body'] : '';
			$bcc_list         = isset( $section_settings['bcc'] ) ? (array) $section_settings['bcc'] : array();
		?>
		<h2><?php echo esc_html( $section_meta['heading'] ); ?></h2>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php echo esc_html( bhg_t( 'label_notification_enabled', 'Enable notification' ) ); ?></th>
					<td>
						<label for="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_enabled">
							<input type="checkbox" id="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_enabled" name="bhg_notifications[<?php echo esc_attr( $section_key ); ?>][enabled]" value="1" <?php checked( $enabled ); ?>>
							<?php echo esc_html( bhg_t( 'label_notification_enabled', 'Enable notification' ) ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_subject"><?php echo esc_html( bhg_t( 'label_notification_subject', 'Email subject' ) ); ?></label></th>
					<td><input type="text" class="regular-text" id="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_subject" name="bhg_notifications[<?php echo esc_attr( $section_key ); ?>][subject]" value="<?php echo esc_attr( $subject ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_body"><?php echo esc_html( bhg_t( 'label_notification_body', 'Email message (HTML allowed)' ) ); ?></label></th>
					<td>
						<textarea class="large-text code" rows="8" id="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_body" name="bhg_notifications[<?php echo esc_attr( $section_key ); ?>][body]"><?php echo esc_textarea( $body ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_bcc"><?php echo esc_html( bhg_t( 'label_notification_bcc', 'BCC recipients' ) ); ?></label></th>
					<td>
						<textarea class="large-text code" rows="3" id="bhg_notifications_<?php echo esc_attr( $section_key ); ?>_bcc" name="bhg_notifications[<?php echo esc_attr( $section_key ); ?>][bcc]"><?php echo esc_textarea( implode( '
', $bcc_list ) ); ?></textarea>
						<p class="description"><?php echo esc_html( bhg_t( 'notification_bcc_hint', 'Separate multiple email addresses with commas or new lines.' ) ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php endforeach; ?>

		<p class="submit"><button type="submit" class="button button-primary"><?php echo esc_html( bhg_t( 'save_changes', 'Save Changes' ) ); ?></button></p>
	</form>

	<h2><?php echo esc_html( bhg_t( 'notification_tokens_heading', 'Available template tags' ) ); ?></h2>
	<ul class="ul-disc">
		<?php foreach ( $token_help as $token_line ) : ?>
		<li><?php echo esc_html( $token_line ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
