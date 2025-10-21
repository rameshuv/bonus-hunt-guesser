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
$nextend_enabled = function_exists( 'bhg_nextend_is_available' ) ? bhg_nextend_is_available() : false;
$provider_map    = isset( $settings['nextend_providers'] ) && is_array( $settings['nextend_providers'] ) ? $settings['nextend_providers'] : array();
$provider_values = array(
        'google' => isset( $provider_map['google'] ) ? $provider_map['google'] : '',
        'twitch' => isset( $provider_map['twitch'] ) ? $provider_map['twitch'] : '',
        'kick'   => isset( $provider_map['kick'] ) ? $provider_map['kick'] : '',
);
?>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'label_social_login_integration', 'Social Login Integration' ) ); ?></th>
<td>
<?php if ( ! $nextend_enabled ) : ?>
<p class="description"><?php echo esc_html( bhg_t( 'notice_nextend_inactive', 'Nextend Social Login plugin is not active. Install and activate it to enable these settings.' ) ); ?></p>
<?php endif; ?>
<p class="description"><?php echo esc_html( bhg_t( 'description_nextend_provider_mapping', 'Map the Bonus Hunt providers to Nextend Social Login provider slugs. Leave blank to hide the button.' ) ); ?></p>
<fieldset>
<legend class="screen-reader-text"><?php echo esc_html( bhg_t( 'label_social_login_integration', 'Social Login Integration' ) ); ?></legend>
<p>
<label for="bhg_nextend_provider_google"><?php echo esc_html( bhg_t( 'label_nextend_google_provider', 'Google provider slug' ) ); ?></label><br>
<input type="text" class="regular-text" id="bhg_nextend_provider_google" name="bhg_nextend_provider_google" value="<?php echo esc_attr( $provider_values['google'] ); ?>" autocomplete="off">
</p>
<p>
<label for="bhg_nextend_provider_twitch"><?php echo esc_html( bhg_t( 'label_nextend_twitch_provider', 'Twitch provider slug' ) ); ?></label><br>
<input type="text" class="regular-text" id="bhg_nextend_provider_twitch" name="bhg_nextend_provider_twitch" value="<?php echo esc_attr( $provider_values['twitch'] ); ?>" autocomplete="off">
</p>
<p>
<label for="bhg_nextend_provider_kick"><?php echo esc_html( bhg_t( 'label_nextend_kick_provider', 'Kick provider slug' ) ); ?></label><br>
<input type="text" class="regular-text" id="bhg_nextend_provider_kick" name="bhg_nextend_provider_kick" value="<?php echo esc_attr( $provider_values['kick'] ); ?>" autocomplete="off">
</p>
</fieldset>
</td>
</tr>
</tbody>
</table>

<?php submit_button( bhg_t( 'save_settings', 'Save Settings' ) ); ?>
</form>
</div>
