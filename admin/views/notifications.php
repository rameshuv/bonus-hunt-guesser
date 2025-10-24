<?php
/**
 * Notifications configuration screen.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

if ( ! function_exists( 'bhg_get_notification_settings' ) ) {
echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ) . '</h1>';
echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'missing_helper_functions', 'Required helper functions are unavailable. Please ensure helpers.php is loaded.' ) ) . '</p></div>';
echo '</div>';
return;
}

$settings = bhg_get_notification_settings();
$notice   = '';
$error    = '';

if ( isset( $_GET['bhg_msg'] ) ) {
$message_code = sanitize_key( wp_unslash( $_GET['bhg_msg'] ) );
if ( 'notifications_saved' === $message_code ) {
$notice = bhg_t( 'notifications_saved', 'Notification settings saved.' );
} elseif ( 'nonce' === $message_code ) {
$error = bhg_t( 'notice_invalid_nonce', 'Invalid security token. Please try again.' );
} elseif ( 'missing_helpers' === $message_code ) {
$error = bhg_t( 'missing_helper_functions', 'Required helper functions are unavailable. Please ensure helpers.php is loaded.' );
}
}

$sections = array(
'winners'     => array(
'title'        => bhg_t( 'label_winner_notifications', 'Winner Notifications' ),
'description'  => bhg_t( 'winner_notifications_description', 'Configure the email that is sent to winners when a bonus hunt is closed.' ),
'placeholders' => array(
'{{username}}' => bhg_t( 'ph_username', 'Recipient username' ),
'{{hunt}}'     => bhg_t( 'ph_hunt_title', 'Bonus hunt title' ),
'{{final}}'    => bhg_t( 'ph_final_balance', 'Final balance amount' ),
'{{winner}}'   => bhg_t( 'ph_primary_winner', 'Top winner username' ),
'{{winners}}'  => bhg_t( 'ph_winner_list', 'Comma-separated list of winners' ),
),
),
'tournaments' => array(
'title'        => bhg_t( 'label_tournament_notifications', 'Tournament Notifications' ),
'description'  => bhg_t( 'tournament_notifications_description', 'Send an announcement when a new tournament is created.' ),
'placeholders' => array(
'{{username}}'    => bhg_t( 'ph_username', 'Recipient username' ),
'{{tournament}}'  => bhg_t( 'ph_tournament_title', 'Tournament title' ),
'{{type}}'        => bhg_t( 'ph_tournament_type', 'Tournament type' ),
'{{start}}'       => bhg_t( 'ph_start_date', 'Start date' ),
'{{end}}'         => bhg_t( 'ph_end_date', 'End date' ),
'{{description}}' => bhg_t( 'ph_description', 'Tournament description' ),
),
),
'hunts'       => array(
'title'        => bhg_t( 'label_hunt_notifications', 'Bonushunt Notifications' ),
'description'  => bhg_t( 'hunt_notifications_description', 'Send an email announcement when a new bonus hunt is created.' ),
'placeholders' => array(
'{{username}}'      => bhg_t( 'ph_username', 'Recipient username' ),
'{{hunt}}'          => bhg_t( 'ph_hunt_title', 'Bonus hunt title' ),
'{{start_balance}}' => bhg_t( 'ph_start_balance', 'Starting balance' ),
'{{num_bonuses}}'   => bhg_t( 'ph_number_bonuses', 'Number of bonuses' ),
'{{prizes}}'        => bhg_t( 'ph_prizes', 'Configured prizes text' ),
),
),
);
?>
<div class="wrap bhg-wrap">
<h1><?php echo esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ); ?></h1>

<?php if ( $notice ) : ?>
<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
<?php endif; ?>

<?php if ( $error ) : ?>
<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-notifications-form">
<?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
<input type="hidden" name="action" value="bhg_save_notifications" />

<?php foreach ( $sections as $key => $info ) :
$section_settings = isset( $settings[ $key ] ) ? $settings[ $key ] : array();
$enabled          = ! empty( $section_settings['enabled'] );
$subject          = isset( $section_settings['subject'] ) ? $section_settings['subject'] : '';
$body             = isset( $section_settings['body'] ) ? $section_settings['body'] : '';
$bcc              = isset( $section_settings['bcc'] ) ? $section_settings['bcc'] : '';
$editor_id        = 'notify_' . $key . '_body_editor';
?>
<div class="card bhg-notification-card">
<h2><?php echo esc_html( $info['title'] ); ?></h2>
<p class="description"><?php echo esc_html( $info['description'] ); ?></p>

<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row">
<label for="notify_<?php echo esc_attr( $key ); ?>_enabled">
<?php echo esc_html( bhg_t( 'label_enable_notifications', 'Enable notifications' ) ); ?>
</label>
</th>
<td>
<input type="checkbox" id="notify_<?php echo esc_attr( $key ); ?>_enabled" name="notify_<?php echo esc_attr( $key ); ?>_enabled" value="1" <?php checked( $enabled ); ?> />
<span class="description"><?php echo esc_html( bhg_t( 'label_notifications_default_off', 'Disabled by default.' ) ); ?></span>
</td>
</tr>
<tr>
<th scope="row"><label for="notify_<?php echo esc_attr( $key ); ?>_subject"><?php echo esc_html( bhg_t( 'label_email_subject', 'Email subject' ) ); ?></label></th>
<td><input type="text" class="regular-text" id="notify_<?php echo esc_attr( $key ); ?>_subject" name="notify_<?php echo esc_attr( $key ); ?>_subject" value="<?php echo esc_attr( $subject ); ?>" /></td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'label_email_body', 'Email body' ) ); ?></th>
<td>
<?php
wp_editor(
$body,
$editor_id,
array(
'textarea_name' => 'notify_' . $key . '_body',
'textarea_rows' => 8,
'media_buttons' => false,
)
);
?>
<p class="description">
<?php echo esc_html( bhg_t( 'label_available_placeholders', 'Available placeholders:' ) ); ?>
<?php
$placeholder_bits = array();
foreach ( $info['placeholders'] as $placeholder => $placeholder_label ) {
$placeholder_bits[] = '<code>' . esc_html( $placeholder ) . '</code> ' . esc_html( $placeholder_label );
}
echo wp_kses_post( implode( ' · ', $placeholder_bits ) );
?>
</p>
</td>
</tr>
<tr>
<th scope="row"><label for="notify_<?php echo esc_attr( $key ); ?>_bcc"><?php echo esc_html( bhg_t( 'label_bcc', 'BCC addresses' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="notify_<?php echo esc_attr( $key ); ?>_bcc" name="notify_<?php echo esc_attr( $key ); ?>_bcc" value="<?php echo esc_attr( $bcc ); ?>" placeholder="admin@example.com,manager@example.com" />
<p class="description"><?php echo esc_html( bhg_t( 'label_bcc_hint', 'Comma separated list of additional recipients.' ) ); ?></p>
</td>
</tr>
</tbody>
</table>
</div>
<?php endforeach; ?>

<?php submit_button( bhg_t( 'button_save', 'Save' ) ); ?>
</form>
</div>
