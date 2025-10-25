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

$notifications = bhg_get_notifications_settings();
$message       = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$error_code    = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
$type_labels   = array(
        'winners'     => bhg_t( 'notification_winners', 'Winner Notifications' ),
        'tournaments' => bhg_t( 'notification_tournaments', 'Tournament Notifications' ),
        'hunts'       => bhg_t( 'notification_hunts', 'Bonus Hunt Notifications' ),
);
$token_help    = array(
        'winners'     => array( '{{username}}', '{{hunt}}', '{{final}}', '{{winner}}', '{{winners}}', '{{points}}', '{{position}}', '{{guess}}', '{{site_name}}' ),
        'tournaments' => array( '{{tournament}}', '{{description}}', '{{start}}', '{{end}}', '{{site_name}}' ),
        'hunts'       => array( '{{hunt}}', '{{starting_balance}}', '{{bonuses}}', '{{prizes}}', '{{site_name}}' ),
);
?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ); ?></h1>
        <?php if ( 'saved' === $message ) : ?>
                <div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'notifications_saved', 'Notification templates saved.' ) ); ?></p></div>
        <?php elseif ( 'nonce_failed' === $error_code ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
        <?php endif; ?>
        <p><?php echo esc_html( bhg_t( 'notifications_intro', 'Configure email templates and recipients for automatic notifications.' ) ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'bhg_notifications', 'bhg_notifications_nonce' ); ?>
                <input type="hidden" name="action" value="bhg_save_notifications" />

                <?php foreach ( $type_labels as $key => $label ) :
                        $row    = isset( $notifications[ $key ] ) ? $notifications[ $key ] : array();
                        $tokens = isset( $token_help[ $key ] ) ? $token_help[ $key ] : array();
                        ?>
                        <div class="postbox">
                                <h2 class="hndle"><span><?php echo esc_html( $label ); ?></span></h2>
                                <div class="inside">
                                        <p>
                                                <label>
                                                        <input type="hidden" name="bhg_notifications[<?php echo esc_attr( $key ); ?>][enabled]" value="0" />
                                                        <input type="checkbox" name="bhg_notifications[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( ! empty( $row['enabled'] ) ); ?> />
                                                        <?php echo esc_html( bhg_t( 'notifications_enabled', 'Enable notifications' ) ); ?>
                                                </label>
                                        </p>
                                        <p>
                                                <label for="bhg_notifications_<?php echo esc_attr( $key ); ?>_bcc"><strong><?php echo esc_html( bhg_t( 'bcc_address', 'BCC recipients' ) ); ?></strong></label><br />
                                                <input type="text" id="bhg_notifications_<?php echo esc_attr( $key ); ?>_bcc" name="bhg_notifications[<?php echo esc_attr( $key ); ?>][bcc]" value="<?php echo isset( $row['bcc'] ) ? esc_attr( $row['bcc'] ) : ''; ?>" class="regular-text" placeholder="admin@example.com, team@example.com" />
                                                <span class="description"><?php echo esc_html( bhg_t( 'bcc_description', 'Separate multiple email addresses with commas.' ) ); ?></span>
                                        </p>
                                        <p>
                                                <label for="bhg_notifications_<?php echo esc_attr( $key ); ?>_title"><strong><?php echo esc_html( bhg_t( 'email_subject', 'Email subject' ) ); ?></strong></label><br />
                                                <input type="text" id="bhg_notifications_<?php echo esc_attr( $key ); ?>_title" name="bhg_notifications[<?php echo esc_attr( $key ); ?>][title]" value="<?php echo isset( $row['title'] ) ? esc_attr( $row['title'] ) : ''; ?>" class="large-text" />
                                        </p>
                                        <p>
                                                <label for="bhg_notifications_<?php echo esc_attr( $key ); ?>_description"><strong><?php echo esc_html( bhg_t( 'email_body', 'Email body' ) ); ?></strong></label>
                                        </p>
                                        <textarea id="bhg_notifications_<?php echo esc_attr( $key ); ?>_description" name="bhg_notifications[<?php echo esc_attr( $key ); ?>][description]" class="large-text" rows="8"><?php echo isset( $row['description'] ) ? esc_textarea( $row['description'] ) : ''; ?></textarea>
                                        <?php if ( ! empty( $tokens ) ) : ?>
                                                <p class="description"><?php echo esc_html( bhg_t( 'available_tokens', 'Available tokens:' ) ); ?>
                                                        <?php foreach ( $tokens as $token ) : ?>
                                                                <code><?php echo esc_html( $token ); ?></code>
                                                        <?php endforeach; ?>
                                                </p>
                                        <?php endif; ?>
                                </div>
                        </div>
                <?php endforeach; ?>

                <?php submit_button( bhg_t( 'save_notifications', 'Save Notifications' ) ); ?>
        </form>
</div>
