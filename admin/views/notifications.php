<?php
/**
 * Notifications management view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

$settings      = function_exists( 'bhg_get_notification_settings' ) ? bhg_get_notification_settings() : array();
$message       = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$error_code    = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
$placeholders  = array(
        'winner'     => array( '{{username}}', '{{hunt}}', '{{position}}', '{{guess}}', '{{difference}}', '{{final_balance}}' ),
        'tournament' => array( '{{tournament}}', '{{start_date}}', '{{end_date}}', '{{link}}' ),
        'hunt'       => array( '{{hunt}}', '{{starting_balance}}', '{{num_bonuses}}', '{{link}}' ),
);
?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'notifications_page_title', 'Notifications' ) ); ?></h1>

        <?php if ( 'saved' === $message ) : ?>
                <div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'notifications_saved', 'Notifications updated.' ) ); ?></p></div>
        <?php elseif ( 'nonce_failed' === $error_code ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
        <?php endif; ?>

        <p class="description"><?php echo esc_html( bhg_t( 'notifications_description', 'Configure email notifications that are sent when hunts close or tournaments are created.' ) ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
                <input type="hidden" name="action" value="bhg_save_notifications" />

                <?php
                $sections = array(
                        'winner'     => bhg_t( 'winner_notification_heading', 'Winner notifications' ),
                        'tournament' => bhg_t( 'tournament_notification_heading', 'Tournament notifications' ),
                        'hunt'       => bhg_t( 'hunt_notification_heading', 'Bonus hunt notifications' ),
                );

                foreach ( $sections as $slug => $heading ) :
                        $config          = isset( $settings[ $slug ] ) ? $settings[ $slug ] : array();
                        $enabled         = isset( $config['enabled'] ) ? (int) $config['enabled'] : 0;
                        $title           = isset( $config['title'] ) ? $config['title'] : '';
                        $description     = isset( $config['description'] ) ? $config['description'] : '';
                        $bcc             = isset( $config['bcc'] ) ? $config['bcc'] : '';
                        $placeholder_msg = isset( $placeholders[ $slug ] ) ? implode( ', ', $placeholders[ $slug ] ) : '';
                        ?>
                        <div class="card" style="max-width:900px;padding:16px;margin-top:20px;">
                                <h2><?php echo esc_html( $heading ); ?></h2>
                                <p class="description"><?php echo esc_html( bhg_t( 'notification_available_placeholders', 'Available placeholders:' ) ); ?> <?php echo esc_html( $placeholder_msg ); ?></p>

                                <table class="form-table" role="presentation">
                                        <tbody>
                                                <tr>
                                                        <th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $slug ); ?>_enabled"><?php echo esc_html( bhg_t( 'notification_enable_label', 'Enable notification' ) ); ?></label></th>
                                                        <td>
                                                                <label>
                                                                        <input type="checkbox" id="bhg_notifications_<?php echo esc_attr( $slug ); ?>_enabled" name="notifications[<?php echo esc_attr( $slug ); ?>][enabled]" value="1" <?php checked( 1, $enabled ); ?> />
                                                                        <?php echo esc_html( bhg_t( 'notification_enable_help', 'Send this email when the related event occurs.' ) ); ?>
                                                                </label>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $slug ); ?>_title"><?php echo esc_html( bhg_t( 'notification_subject_label', 'Email subject' ) ); ?></label></th>
                                                        <td>
                                                                <input type="text" class="regular-text" id="bhg_notifications_<?php echo esc_attr( $slug ); ?>_title" name="notifications[<?php echo esc_attr( $slug ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" />
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $slug ); ?>_description"><?php echo esc_html( bhg_t( 'notification_body_label', 'Email content' ) ); ?></label></th>
                                                        <td>
                                                                <?php
                                                                wp_editor(
                                                                        $description,
                                                                        'bhg_notifications_' . $slug . '_description',
                                                                        array(
                                                                                'textarea_name' => 'notifications[' . esc_attr( $slug ) . '][description]',
                                                                                'textarea_rows' => 8,
                                                                                'media_buttons' => false,
                                                                        )
                                                                );
                                                                ?>
                                                                <p class="description"><?php echo esc_html( bhg_t( 'notification_body_help', 'HTML is allowed. Placeholders will be replaced automatically.' ) ); ?></p>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><label for="bhg_notifications_<?php echo esc_attr( $slug ); ?>_bcc"><?php echo esc_html( bhg_t( 'notification_bcc_label', 'BCC recipients' ) ); ?></label></th>
                                                        <td>
                                                                <input type="text" class="regular-text" id="bhg_notifications_<?php echo esc_attr( $slug ); ?>_bcc" name="notifications[<?php echo esc_attr( $slug ); ?>][bcc]" value="<?php echo esc_attr( $bcc ); ?>" />
                                                                <p class="description"><?php echo esc_html( bhg_t( 'notification_bcc_help', 'Comma-separated email addresses that should receive a copy.' ) ); ?></p>
                                                        </td>
                                                </tr>
                                        </tbody>
                                </table>
                        </div>
                <?php endforeach; ?>

                <?php submit_button( bhg_t( 'save_notifications', 'Save notifications' ) ); ?>
        </form>
</div>
