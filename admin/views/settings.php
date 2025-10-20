<?php
/**
 * Settings view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

// Fetch existing settings.
$settings = get_option( 'bhg_plugin_settings', array() );

$message    = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$error_code = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
?>
<div class="wrap">
<h1><?php echo esc_html( bhg_t( 'bonus_hunt_guesser_settings', 'Bonus Hunt Guesser Settings' ) ); ?></h1>

<?php if ( 'saved' === $message ) : ?>
<div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'settings_saved', 'Settings saved.' ) ); ?></p></div>
<?php elseif ( 'invalid_data' === $error_code ) : ?>
<div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'invalid_settings', 'Invalid data submitted.' ) ); ?></p></div>
<?php elseif ( 'nonce_failed' === $error_code ) : ?>
<div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<?php wp_nonce_field( 'bhg_settings', 'bhg_settings_nonce' ); ?>
<input type="hidden" name="action" value="bhg_save_settings">

<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><label for="bhg_default_tournament_period"><?php echo esc_html( bhg_t( 'default_tournament_period', 'Default Tournament Period' ) ); ?></label></th>
<td>
<select name="bhg_default_tournament_period" id="bhg_default_tournament_period">
<?php
$periods        = array(
	'weekly'    => bhg_t( 'weekly', 'Weekly' ),
	'monthly'   => bhg_t( 'monthly', 'Monthly' ),
	'quarterly' => bhg_t( 'quarterly', 'Quarterly' ),
	'yearly'    => bhg_t( 'yearly', 'Yearly' ),
	'alltime'   => bhg_t( 'alltime', 'All-time' ),
);
$current_period = isset( $settings['default_tournament_period'] ) ? $settings['default_tournament_period'] : '';
foreach ( $periods as $key => $label ) :
	?>
<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_period, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_currency"><?php echo esc_html( bhg_t( 'currency', 'Currency' ) ); ?></label></th>
<td>
<select name="bhg_currency" id="bhg_currency">
<?php
$currencies       = array(
	'eur' => bhg_t( 'eur', 'EUR' ),
	'usd' => bhg_t( 'usd', 'USD' ),
);
$current_currency = isset( $settings['currency'] ) ? $settings['currency'] : 'eur';
foreach ( $currencies as $key => $label ) :
	?>
<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_currency, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_min_guess_amount"><?php echo esc_html( bhg_t( 'min_guess_amount', 'Minimum Guess Amount' ) ); ?></label></th>
<td><input type="number" class="small-text" id="bhg_min_guess_amount" name="bhg_min_guess_amount" value="<?php echo isset( $settings['min_guess_amount'] ) ? esc_attr( $settings['min_guess_amount'] ) : '0'; ?>" min="0"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_max_guess_amount"><?php echo esc_html( bhg_t( 'max_guess_amount', 'Maximum Guess Amount' ) ); ?></label></th>
<td><input type="number" class="small-text" id="bhg_max_guess_amount" name="bhg_max_guess_amount" value="<?php echo isset( $settings['max_guess_amount'] ) ? esc_attr( $settings['max_guess_amount'] ) : '100000'; ?>" min="0"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_allow_guess_changes"><?php echo esc_html( bhg_t( 'allow_guess_changes', 'Allow Guess Changes' ) ); ?></label></th>
<td>
<select name="bhg_allow_guess_changes" id="bhg_allow_guess_changes">
<option value="yes" <?php selected( isset( $settings['allow_guess_changes'] ) ? $settings['allow_guess_changes'] : '', 'yes' ); ?>><?php echo esc_html( bhg_t( 'yes', 'Yes' ) ); ?></option>
<option value="no" <?php selected( isset( $settings['allow_guess_changes'] ) ? $settings['allow_guess_changes'] : '', 'no' ); ?>><?php echo esc_html( bhg_t( 'no', 'No' ) ); ?></option>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_ads_enabled"><?php echo esc_html( bhg_t( 'ads_enabled', 'Enable Ads' ) ); ?></label></th>
<td>
<input type="hidden" name="bhg_ads_enabled" value="0">
<input type="checkbox" id="bhg_ads_enabled" name="bhg_ads_enabled" value="1" <?php checked( ! empty( $settings['ads_enabled'] ) ); ?>>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_email_from"><?php echo esc_html( bhg_t( 'email_from', 'Email From Address' ) ); ?></label></th>
<td><input type="email" class="regular-text" id="bhg_email_from" name="bhg_email_from" value="<?php echo isset( $settings['email_from'] ) ? esc_attr( $settings['email_from'] ) : esc_attr( get_bloginfo( 'admin_email' ) ); ?>"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_post_submit_redirect"><?php echo esc_html( bhg_t( 'post_submit_redirect_url', 'Post-submit redirect URL' ) ); ?></label></th>
<td>
<input type="url" class="regular-text" id="bhg_post_submit_redirect" name="bhg_post_submit_redirect" value="<?php echo isset( $settings['post_submit_redirect'] ) ? esc_attr( $settings['post_submit_redirect'] ) : ''; ?>" placeholder="<?php echo esc_attr( bhg_t( 'post_submit_redirect_placeholder', 'https://example.com/thank-you' ) ); ?>">
<p class="description"><?php echo esc_html( bhg_t( 'post_submit_redirect_description', 'Send users to this URL after submitting or editing a guess. Leave blank to stay on the same page.' ) ); ?></p>
</td>
</tr>
<?php
$user_shortcode_defaults = array(
        'my_bonushunts'  => 1,
        'my_tournaments' => 1,
        'my_prizes'      => 1,
        'my_rankings'    => 1,
);
$user_visibility       = isset( $settings['user_shortcodes'] ) && is_array( $settings['user_shortcodes'] ) ? $settings['user_shortcodes'] : array();
$design_settings       = isset( $settings['design'] ) && is_array( $settings['design'] ) ? $settings['design'] : array();
?>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'user_dashboard_shortcodes', 'User dashboard shortcodes' ) ); ?></th>
<td>
        <fieldset>
        <legend class="screen-reader-text"><?php echo esc_html( bhg_t( 'user_dashboard_shortcodes', 'User dashboard shortcodes' ) ); ?></legend>
        <?php foreach ( $user_shortcode_defaults as $slug => $default_state ) :
                $checked = isset( $user_visibility[ $slug ] ) ? (int) $user_visibility[ $slug ] : (int) $default_state;
                ?>
                <label style="display:block;margin-bottom:6px;">
                        <input type="hidden" name="bhg_user_shortcode_visibility[<?php echo esc_attr( $slug ); ?>]" value="0">
                        <input type="checkbox" name="bhg_user_shortcode_visibility[<?php echo esc_attr( $slug ); ?>]" value="1" <?php checked( 1, $checked ); ?>>
                        <?php echo esc_html( sprintf( bhg_t( 'toggle_shortcode_visibility', 'Show [%s] on frontend profiles' ), $slug ) ); ?>
                </label>
        <?php endforeach; ?>
        <p class="description"><?php echo esc_html( bhg_t( 'user_shortcode_visibility_help', 'Uncheck a box to hide the matching shortcode output for logged-in users.' ) ); ?></p>
        </fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'design_settings_heading', 'Design & typography' ) ); ?></th>
<td>
        <fieldset class="bhg-design-settings">
        <legend class="screen-reader-text"><?php echo esc_html( bhg_t( 'design_settings_heading', 'Design & typography' ) ); ?></legend>
        <p class="description"><?php echo esc_html( bhg_t( 'design_settings_description', 'Customize colours, spacing, and typography for shortcode blocks. Leave blank to use defaults.' ) ); ?></p>
        <table class="form-table">
                <tbody>
                <?php
                $design_fields = array(
                        'title_block_background' => bhg_t( 'design_title_block_background', 'Title block background' ),
                        'title_block_border'     => bhg_t( 'design_title_block_border', 'Title block border' ),
                        'title_block_radius'     => bhg_t( 'design_title_block_radius', 'Title block border radius' ),
                        'title_block_padding'    => bhg_t( 'design_title_block_padding', 'Title block padding' ),
                        'title_block_margin'     => bhg_t( 'design_title_block_margin', 'Title block margin' ),
                        'title_block_shadow'     => bhg_t( 'design_title_block_shadow', 'Title block shadow' ),
                        'h2_font_size'           => bhg_t( 'design_h2_font_size', 'H2 font size' ),
                        'h2_font_weight'         => bhg_t( 'design_h2_font_weight', 'H2 font weight' ),
                        'h2_color'               => bhg_t( 'design_h2_color', 'H2 colour' ),
                        'h2_padding'             => bhg_t( 'design_h2_padding', 'H2 padding' ),
                        'h2_margin'              => bhg_t( 'design_h2_margin', 'H2 margin' ),
                        'h3_font_size'           => bhg_t( 'design_h3_font_size', 'H3 font size' ),
                        'h3_font_weight'         => bhg_t( 'design_h3_font_weight', 'H3 font weight' ),
                        'h3_color'               => bhg_t( 'design_h3_color', 'H3 colour' ),
                        'h3_padding'             => bhg_t( 'design_h3_padding', 'H3 padding' ),
                        'h3_margin'              => bhg_t( 'design_h3_margin', 'H3 margin' ),
                        'description_font_size'  => bhg_t( 'design_description_font_size', 'Description font size' ),
                        'description_font_weight'=> bhg_t( 'design_description_font_weight', 'Description font weight' ),
                        'description_color'      => bhg_t( 'design_description_color', 'Description colour' ),
                        'description_padding'    => bhg_t( 'design_description_padding', 'Description padding' ),
                        'description_margin'     => bhg_t( 'design_description_margin', 'Description margin' ),
                        'field_font_size'        => bhg_t( 'design_field_font_size', 'Paragraph/span font size' ),
                        'field_padding'          => bhg_t( 'design_field_padding', 'Paragraph/span padding' ),
                        'field_margin'           => bhg_t( 'design_field_margin', 'Paragraph/span margin' ),
                );
                foreach ( $design_fields as $field_key => $label ) :
                        $value = isset( $design_settings[ $field_key ] ) ? $design_settings[ $field_key ] : '';
                        ?>
                        <tr>
                                <th scope="row"><label for="bhg_design_<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $label ); ?></label></th>
                                <td><input type="text" class="regular-text" id="bhg_design_<?php echo esc_attr( $field_key ); ?>" name="bhg_design[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $value ); ?>"></td>
                        </tr>
                <?php endforeach; ?>
                </tbody>
        </table>
        </fieldset>
</td>
</tr>
</tbody>
</table>

<?php submit_button( bhg_t( 'save_settings', 'Save Settings' ) ); ?>
</form>
</div>
