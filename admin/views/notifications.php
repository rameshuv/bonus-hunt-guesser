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
$message  = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$error    = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';

$sections = array(
        'winners'     => array(
                'title'  => bhg_t( 'notifications_section_winners', 'Winner Notifications' ),
                'tokens' => bhg_t( 'notifications_tokens_winner', '{{hunt}}, {{final}}, {{guess}}, {{diff}}, {{position}}, {{position_label}}' ),
        ),
        'hunts'       => array(
                'title'  => bhg_t( 'notifications_section_hunts', 'Bonus Hunt Notifications' ),
                'tokens' => bhg_t( 'notifications_tokens_hunt', '{{hunt}}, {{final}}, {{winner}}, {{winners}}' ),
        ),
        'tournaments' => array(
                'title'  => bhg_t( 'notifications_section_tournaments', 'Tournament Notifications' ),
                'tokens' => bhg_t( 'notifications_tokens_tournament', '{{tournament}}, {{wins}}, {{position}}, {{position_label}}' ),
        ),
);
?>
<div class="wrap">
        <h1><?php echo esc_html( bhg_t( 'label_notifications', 'Notifications' ) ); ?></h1>
        <p class="description"><?php echo esc_html( bhg_t( 'notifications_page_description', 'Configure automated emails for hunts and tournaments.' ) ); ?></p>

        <?php if ( 'saved' === $message ) : ?>
        <div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'notifications_saved', 'Notifications saved.' ) ); ?></p></div>
        <?php elseif ( 'nonce_failed' === $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
        <?php elseif ( 'save_failed' === $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'notifications_error', 'Unable to save notifications settings.' ) ); ?></p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
                <input type="hidden" name="action" value="bhg_save_notifications">

                <table class="form-table" role="presentation">
                        <tbody>
                        <?php foreach ( $sections as $context => $section ) :
                                $context_settings = isset( $settings[ $context ] ) ? $settings[ $context ] : array();
                                $enabled          = isset( $context_settings['enabled'] ) ? (int) $context_settings['enabled'] : 0;
                                $subject          = isset( $context_settings['subject'] ) ? $context_settings['subject'] : '';
                                $body             = isset( $context_settings['body'] ) ? $context_settings['body'] : '';
                                $bcc              = isset( $context_settings['bcc'] ) ? $context_settings['bcc'] : '';
                                $field_prefix     = 'notifications_' . $context;
                                ?>
                                <tr class="bhg-settings-section">
                                        <th scope="colgroup" colspan="2"><h2><?php echo esc_html( $section['title'] ); ?></h2></th>
                                </tr>
                                <tr>
                                        <th scope="row"><?php echo esc_html( bhg_t( 'notifications_enable', 'Enable notifications' ) ); ?></th>
                                        <td>
                                                <label>
                                                        <input type="checkbox" name="notifications[<?php echo esc_attr( $context ); ?>][enabled]" value="1" <?php checked( $enabled, 1 ); ?>>
                                                        <?php echo esc_html( bhg_t( 'notifications_enable', 'Enable notifications' ) ); ?>
                                                </label>
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row"><label for="<?php echo esc_attr( $field_prefix . '_subject' ); ?>"><?php echo esc_html( bhg_t( 'notifications_subject', 'Email Subject' ) ); ?></label></th>
                                        <td>
                                                <input type="text" class="regular-text" id="<?php echo esc_attr( $field_prefix . '_subject' ); ?>" name="notifications[<?php echo esc_attr( $context ); ?>][subject]" value="<?php echo esc_attr( $subject ); ?>">
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row"><label for="<?php echo esc_attr( $field_prefix . '_body' ); ?>"><?php echo esc_html( bhg_t( 'notifications_body', 'Email Content' ) ); ?></label></th>
                                        <td>
                                                <?php
                                                wp_editor(
                                                        $body,
                                                        $field_prefix . '_body',
                                                        array(
                                                                'textarea_name' => 'notifications[' . $context . '][body]',
                                                                'media_buttons' => false,
                                                                'teeny'         => true,
                                                                'textarea_rows' => 6,
                                                        )
                                                );
                                                ?>
                                                <p class="description">
                                                        <?php echo esc_html( bhg_t( 'notifications_tokens_label', 'Available placeholders' ) ); ?>:
                                                        <code><?php echo esc_html( bhg_t( 'notifications_tokens_common', '{{username}}, {{site_name}}' ) ); ?></code>
                                                        <code><?php echo esc_html( $section['tokens'] ); ?></code>
                                                </p>
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row"><label for="<?php echo esc_attr( $field_prefix . '_bcc' ); ?>"><?php echo esc_html( bhg_t( 'notifications_bcc', 'BCC Addresses' ) ); ?></label></th>
                                        <td>
                                                <input type="text" class="regular-text" id="<?php echo esc_attr( $field_prefix . '_bcc' ); ?>" name="notifications[<?php echo esc_attr( $context ); ?>][bcc]" value="<?php echo esc_attr( $bcc ); ?>">
                                                <p class="description"><?php echo esc_html( bhg_t( 'notifications_bcc_hint', 'Separate multiple addresses with commas.' ) ); ?></p>
                                        </td>
                                </tr>
                        <?php endforeach; ?>
                        </tbody>
                </table>

                <?php submit_button( bhg_t( 'save_changes', 'Save Changes' ) ); ?>
        </form>
</div>
