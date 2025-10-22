<?php
/**
 * Notifications configuration view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

if ( ! class_exists( 'BHG_Notifications' ) ) {
        echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ) . '</h1><div class="notice notice-error"><p>' . esc_html( bhg_t( 'notifications_module_missing', 'Notifications module not available.' ) ) . '</p></div></div>';
        return;
}

$settings = BHG_Notifications::get_settings();

$sections = array(
        'hunts'       => array(
                'label'       => bhg_t( 'bonushunt_notifications', 'Bonushunt Notifications' ),
                'description' => bhg_t( 'bonushunt_notifications_help', 'Sent to all hunt participants when a hunt closes.' ),
                'tokens'      => array( 'site_name', 'hunt_title', 'final_balance', 'winner_names', 'username', 'display_name' ),
        ),
        'winners'     => array(
                'label'       => bhg_t( 'winner_notifications', 'Winner Notifications' ),
                'description' => bhg_t( 'winner_notifications_help', 'Sent to each winner when a hunt closes.' ),
                'tokens'      => array( 'site_name', 'hunt_title', 'final_balance', 'username', 'display_name', 'position', 'guess', 'difference' ),
        ),
        'tournaments' => array(
                'label'       => bhg_t( 'tournament_notifications', 'Tournament Notifications' ),
                'description' => bhg_t( 'tournament_notifications_help', 'Sent to tournament leaderboard winners when a tournament closes.' ),
                'tokens'      => array( 'site_name', 'tournament_title', 'username', 'display_name', 'wins', 'position' ),
        ),
);

$messages = array(
        'notifications_saved' => array(
                'class' => 'notice-success',
                'text'  => bhg_t( 'notifications_saved', 'Notifications updated.' ),
        ),
        'nonce'               => array(
                'class' => 'notice-error',
                'text'  => bhg_t( 'nonce_failed', 'Security check failed. Please try again.' ),
        ),
);

$current_msg = isset( $_GET['bhg_msg'] ) ? sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) : '';
?>
<div class="wrap bhg-admin">
        <h1><?php echo esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ); ?></h1>

        <?php if ( $current_msg && isset( $messages[ $current_msg ] ) ) : ?>
                <div class="notice <?php echo esc_attr( $messages[ $current_msg ]['class'] ); ?>"><p><?php echo esc_html( $messages[ $current_msg ]['text'] ); ?></p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-notifications-form">
                <input type="hidden" name="action" value="bhg_save_notifications" />
                <?php wp_nonce_field( 'bhg_save_notifications', 'bhg_notifications_nonce' ); ?>

                <?php foreach ( $sections as $key => $section ) :
                        $data = isset( $settings[ $key ] ) ? $settings[ $key ] : array();
                        $enabled     = ! empty( $data['enabled'] );
                        $title       = isset( $data['title'] ) ? $data['title'] : '';
                        $description = isset( $data['description'] ) ? $data['description'] : '';
                        $bcc         = isset( $data['bcc'] ) ? $data['bcc'] : '';

                        $token_list = array();
                        foreach ( $section['tokens'] as $token ) {
                                $token_list[] = '<code>{{' . esc_html( $token ) . '}}</code>';
                        }
                        ?>
                        <div class="card bhg-notification-card">
                                <h2><?php echo esc_html( $section['label'] ); ?></h2>
                                <p class="description"><?php echo esc_html( $section['description'] ); ?></p>

                                <table class="form-table" role="presentation">
                                        <tbody>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'enabled', 'Enabled' ) ); ?></th>
                                                        <td>
                                                                <label>
                                                                        <input type="checkbox" name="notifications[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $enabled ); ?> />
                                                                        <?php echo esc_html( bhg_t( 'enable_notifications', 'Enable notifications' ) ); ?>
                                                                </label>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'email_subject', 'Email subject' ) ); ?></th>
                                                        <td>
                                                                <input type="text" name="notifications[<?php echo esc_attr( $key ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="regular-text" />
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'email_body', 'Email body' ) ); ?></th>
                                                        <td>
                                                                <?php
                                                                $editor_id = 'bhg_notifications_' . sanitize_key( $key ) . '_description';
                                                                wp_editor(
                                                                        $description,
                                                                        $editor_id,
                                                                        array(
                                                                                'textarea_name' => 'notifications[' . esc_attr( $key ) . '][description]',
                                                                                'media_buttons' => false,
                                                                                'textarea_rows' => 8,
                                                                        )
                                                                );
                                                                ?>
                                                                <p class="description"><?php echo esc_html( bhg_t( 'html_allowed_sanitized', 'HTML allowed and sanitized.' ) ); ?></p>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'bcc', 'BCC' ) ); ?></th>
                                                        <td>
                                                                <input type="text" name="notifications[<?php echo esc_attr( $key ); ?>][bcc]" value="<?php echo esc_attr( $bcc ); ?>" class="regular-text" />
                                                                <p class="description"><?php echo esc_html( bhg_t( 'bcc_help', 'Comma separated email addresses.' ) ); ?></p>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'available_tokens', 'Available tokens' ) ); ?></th>
                                                        <td>
                                                                <p class="description"><?php echo wp_kses_post( implode( ' ', $token_list ) ); ?></p>
                                                        </td>
                                                </tr>
                                        </tbody>
                                </table>
                        </div>
                <?php endforeach; ?>

                <?php submit_button( bhg_t( 'button_save_changes', 'Save Changes' ) ); ?>
        </form>
</div>
