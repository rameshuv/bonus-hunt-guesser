<?php
/**
 * Notifications settings view.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$settings = class_exists( 'BHG_Notifications' ) ? BHG_Notifications::get_settings() : array();

$sections = array(
        'bonushunt'  => array(
                'title'       => bhg_t( 'notification_bonushunt_title', 'Bonus Hunt Notifications' ),
                'description' => bhg_t( 'notification_bonushunt_desc', 'Email all participants when a bonus hunt closes.' ),
                'tokens'      => array( 'user_name', 'hunt_title', 'start_balance', 'final_balance', 'user_guess', 'winner_list', 'site_name' ),
        ),
        'winner'     => array(
                'title'       => bhg_t( 'notification_winner_title', 'Winner Notifications' ),
                'description' => bhg_t( 'notification_winner_desc', 'Email winners with their placement once a hunt is closed.' ),
                'tokens'      => array( 'user_name', 'hunt_title', 'position', 'guess', 'difference', 'final_balance', 'site_name' ),
        ),
        'tournament' => array(
                'title'       => bhg_t( 'notification_tournament_title', 'Tournament Notifications' ),
                'description' => bhg_t( 'notification_tournament_desc', 'Email tournament participants when a tournament is closed.' ),
                'tokens'      => array( 'user_name', 'tournament_title', 'tournament_type', 'wins', 'site_name' ),
        ),
);

?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'notifications', 'Notifications' ) ); ?></h1>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900">
                <?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
                <input type="hidden" name="action" value="bhg_save_notifications" />

                <?php foreach ( $sections as $key => $section ) :
                        $current = isset( $settings[ $key ] ) ? $settings[ $key ] : array();
                        $enabled = ! empty( $current['enabled'] );
                        $subject = isset( $current['subject'] ) ? $current['subject'] : '';
                        $body    = isset( $current['body'] ) ? $current['body'] : '';
                        $bcc     = isset( $current['bcc'] ) ? $current['bcc'] : '';
                        ?>
                        <div class="bhg-notification-section">
                                <h2><?php echo esc_html( $section['title'] ); ?></h2>
                                <p><?php echo esc_html( $section['description'] ); ?></p>

                                <table class="form-table">
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'label_enabled', 'Enabled' ) ); ?></th>
                                                <td><label><input type="checkbox" name="notifications[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $enabled ); ?> /> <?php echo esc_html( bhg_t( 'label_enable_email', 'Enable email notification' ) ); ?></label></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_<?php echo esc_attr( $key ); ?>_subject"><?php echo esc_html( bhg_t( 'label_subject', 'Subject' ) ); ?></label></th>
                                                <td><input type="text" class="regular-text" id="bhg_<?php echo esc_attr( $key ); ?>_subject" name="notifications[<?php echo esc_attr( $key ); ?>][subject]" value="<?php echo esc_attr( $subject ); ?>" /></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_<?php echo esc_attr( $key ); ?>_body"><?php echo esc_html( bhg_t( 'label_message_body', 'Message Body' ) ); ?></label></th>
                                                <td>
                                                        <textarea id="bhg_<?php echo esc_attr( $key ); ?>_body" name="notifications[<?php echo esc_attr( $key ); ?>][body]" rows="8" class="large-text"><?php echo esc_textarea( $body ); ?></textarea>
                                                        <p class="description"><?php echo esc_html( bhg_t( 'notification_tokens_hint', 'Available tokens:' ) ); ?>
                                                                <?php
                                                                $token_labels = array();
                                                                foreach ( $section['tokens'] as $token ) {
                                                                        $token_labels[] = '{{' . $token . '}}';
                                                                }
                                                                echo esc_html( implode( ', ', $token_labels ) );
                                                                ?>
                                                        </p>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_<?php echo esc_attr( $key ); ?>_bcc"><?php echo esc_html( bhg_t( 'label_bcc', 'BCC Recipients' ) ); ?></label></th>
                                                <td><input type="text" class="regular-text" id="bhg_<?php echo esc_attr( $key ); ?>_bcc" name="notifications[<?php echo esc_attr( $key ); ?>][bcc]" value="<?php echo esc_attr( $bcc ); ?>" />
                                                        <p class="description"><?php echo esc_html( bhg_t( 'notification_bcc_hint', 'Separate multiple email addresses with commas.' ) ); ?></p></td>
                                        </tr>
                                </table>
                        </div>
                <?php endforeach; ?>

                <?php submit_button( bhg_t( 'save_changes', 'Save Changes' ) ); ?>
        </form>
</div>
