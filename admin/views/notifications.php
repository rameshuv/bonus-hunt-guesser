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

$settings = BHG_Utils::get_notification_settings();
$defaults = BHG_Utils::get_notification_defaults();
$sections = array(
        'winners'    => array(
                'title'        => bhg_t( 'notification_section_winners', 'Winner notifications' ),
                'placeholders' => bhg_t( 'notification_placeholders_hint', 'Available placeholders: {{username}}, {{hunt}}, {{final_balance}}, {{winners}}, {{position}}, {{guess}}, {{difference}}.' ),
        ),
        'bonushunt' => array(
                'title'        => bhg_t( 'notification_section_bonushunt', 'Bonus hunt notifications' ),
                'placeholders' => bhg_t( 'notification_placeholders_hint', 'Available placeholders: {{username}}, {{hunt}}, {{final_balance}}, {{winners}}, {{position}}, {{guess}}, {{difference}}.' ),
        ),
        'tournament' => array(
                'title'        => bhg_t( 'notification_section_tournament', 'Tournament notifications' ),
                'placeholders' => bhg_t( 'notification_placeholders_tournament', 'Available placeholders: {{username}}, {{tournament}}, {{rank}}, {{wins}}.' ),
        ),
);
?>
<div class="wrap">
        <h1><?php echo esc_html( bhg_t( 'notifications_heading', 'Email Notifications' ) ); ?></h1>
        <p class="description"><?php echo esc_html( bhg_t( 'notifications_intro', 'Configure automated messages that are sent when hunts and tournaments close.' ) ); ?></p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900">
                <?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
                <input type="hidden" name="action" value="bhg_save_notifications" />
                <?php foreach ( $sections as $key => $section ) :
                        $current = isset( $settings[ $key ] ) ? $settings[ $key ] : $defaults[ $key ];
                        ?>
                        <div class="card" style="margin-bottom:20px;">
                                <h2><?php echo esc_html( $section['title'] ); ?></h2>
                                <p class="description"><?php echo esc_html( $section['placeholders'] ); ?></p>
                                <p>
                                        <label>
                                                <input type="checkbox" name="notifications_<?php echo esc_attr( $key ); ?>_enabled" value="1" <?php checked( ! empty( $current['enabled'] ) ); ?> />
                                                <?php echo esc_html( bhg_t( 'notification_enable_label', 'Enable this notification' ) ); ?>
                                        </label>
                                </p>
                                <p>
                                        <label for="notifications_<?php echo esc_attr( $key ); ?>_subject"><strong><?php echo esc_html( bhg_t( 'notification_subject_label', 'Subject' ) ); ?></strong></label><br />
                                        <input type="text" class="widefat" id="notifications_<?php echo esc_attr( $key ); ?>_subject" name="notifications_<?php echo esc_attr( $key ); ?>_subject" value="<?php echo esc_attr( $current['subject'] ); ?>" />
                                </p>
                                <p>
                                        <label for="notifications_<?php echo esc_attr( $key ); ?>_body"><strong><?php echo esc_html( bhg_t( 'notification_body_label', 'Message (HTML allowed)' ) ); ?></strong></label>
                                        <textarea class="widefat" rows="8" id="notifications_<?php echo esc_attr( $key ); ?>_body" name="notifications_<?php echo esc_attr( $key ); ?>_body"><?php echo esc_textarea( $current['body'] ); ?></textarea>
                                </p>
                                <p>
                                        <label for="notifications_<?php echo esc_attr( $key ); ?>_bcc"><strong><?php echo esc_html( bhg_t( 'notification_bcc_label', 'BCC recipients' ) ); ?></strong></label>
                                        <input type="text" class="widefat" id="notifications_<?php echo esc_attr( $key ); ?>_bcc" name="notifications_<?php echo esc_attr( $key ); ?>_bcc" value="<?php echo esc_attr( $current['bcc'] ); ?>" />
                                        <span class="description"><?php echo esc_html( bhg_t( 'notification_bcc_hint', 'Separate multiple email addresses with commas.' ) ); ?></span>
                                </p>
                        </div>
                <?php endforeach; ?>
                <?php submit_button( bhg_t( 'notification_save_button', 'Save Notifications' ) ); ?>
        </form>
</div>
