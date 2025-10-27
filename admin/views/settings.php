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
<th scope="row"><label for="bhg_prize_layout"><?php echo esc_html( bhg_t( 'label_prize_layout', 'Prize Layout' ) ); ?></label></th>
<td>
<select name="bhg_prize_layout" id="bhg_prize_layout">
<?php
$current_layout = isset( $settings['prize_layout'] ) ? $settings['prize_layout'] : 'grid';
$layouts        = array(
        'grid'      => bhg_t( 'layout_grid', 'Grid' ),
        'carousel'  => bhg_t( 'layout_carousel', 'Carousel' ),
);
foreach ( $layouts as $key => $label ) :
        ?>
<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_layout, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
<p class="description"><?php echo esc_html( bhg_t( 'prize_layout_description', 'Choose how prizes display on active hunts.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_prize_size"><?php echo esc_html( bhg_t( 'label_prize_size', 'Prize Card Size' ) ); ?></label></th>
<td>
<select name="bhg_prize_size" id="bhg_prize_size">
<?php
$current_size = isset( $settings['prize_size'] ) ? $settings['prize_size'] : 'medium';
$sizes        = array(
        'small'  => bhg_t( 'size_small', 'Small' ),
        'medium' => bhg_t( 'size_medium', 'Medium' ),
        'big'    => bhg_t( 'size_big', 'Big' ),
);
foreach ( $sizes as $key => $label ) :
        ?>
<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_size, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
<p class="description"><?php echo esc_html( bhg_t( 'prize_size_description', 'Control the image size used for prize cards.' ) ); ?></p>
</td>
</tr>
<tr class="bhg-settings-section">
<th colspan="2" scope="colgroup">
<h2><?php echo esc_html( bhg_t( 'global_style_panel', 'Global Style Panel' ) ); ?></h2>
<p class="description"><?php echo esc_html( bhg_t( 'global_style_panel_description', 'Control the typography and spacing for plugin output.' ) ); ?></p>
</th>
</tr>
<tr class="bhg-settings-subheading">
<th colspan="2" scope="colgroup"><h3><?php echo esc_html( bhg_t( 'title_block_styles', 'Title Block Styles' ) ); ?></h3></th>
</tr>
<tr>
<th scope="row"><label for="bhg_title_block_background"><?php echo esc_html( bhg_t( 'title_block_background_color', 'Title block background color' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_title_block_background" name="bhg_title_block_background" value="<?php echo isset( $settings['title_block_background'] ) ? esc_attr( $settings['title_block_background'] ) : ''; ?>" placeholder="#ffffff">
<p class="description"><?php echo esc_html( bhg_t( 'css_color_hint', 'Accepts hex or named colors (e.g. #1e293b or navy).' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_title_block_border_radius"><?php echo esc_html( bhg_t( 'title_block_border_radius', 'Title block border radius' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_title_block_border_radius" name="bhg_title_block_border_radius" value="<?php echo isset( $settings['title_block_border_radius'] ) ? esc_attr( $settings['title_block_border_radius'] ) : ''; ?>" placeholder="8px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_title_block_padding"><?php echo esc_html( bhg_t( 'title_block_padding', 'Title block padding' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_title_block_padding" name="bhg_title_block_padding" value="<?php echo isset( $settings['title_block_padding'] ) ? esc_attr( $settings['title_block_padding'] ) : ''; ?>" placeholder="12px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_title_block_margin"><?php echo esc_html( bhg_t( 'title_block_margin', 'Title block margin' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_title_block_margin" name="bhg_title_block_margin" value="<?php echo isset( $settings['title_block_margin'] ) ? esc_attr( $settings['title_block_margin'] ) : ''; ?>" placeholder="12px 0">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr class="bhg-settings-subheading">
<th colspan="2" scope="colgroup"><h3><?php echo esc_html( bhg_t( 'heading_2_styles', 'H2 Styles' ) ); ?></h3></th>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_2_font_size"><?php echo esc_html( bhg_t( 'heading_2_font_size', 'H2 font size' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_2_font_size" name="bhg_heading_2_font_size" value="<?php echo isset( $settings['heading_2_font_size'] ) ? esc_attr( $settings['heading_2_font_size'] ) : ''; ?>" placeholder="1.5rem">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_2_font_weight"><?php echo esc_html( bhg_t( 'heading_2_font_weight', 'H2 font weight' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_2_font_weight" name="bhg_heading_2_font_weight" value="<?php echo isset( $settings['heading_2_font_weight'] ) ? esc_attr( $settings['heading_2_font_weight'] ) : ''; ?>" placeholder="700">
<p class="description"><?php echo esc_html( bhg_t( 'css_font_weight_hint', 'Use values like 400, 600, normal, or bold.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_2_color"><?php echo esc_html( bhg_t( 'heading_2_color', 'H2 color' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_2_color" name="bhg_heading_2_color" value="<?php echo isset( $settings['heading_2_color'] ) ? esc_attr( $settings['heading_2_color'] ) : ''; ?>" placeholder="#1e293b">
<p class="description"><?php echo esc_html( bhg_t( 'css_color_hint', 'Accepts hex or named colors (e.g. #1e293b or navy).' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_2_padding"><?php echo esc_html( bhg_t( 'heading_2_padding', 'H2 padding' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_2_padding" name="bhg_heading_2_padding" value="<?php echo isset( $settings['heading_2_padding'] ) ? esc_attr( $settings['heading_2_padding'] ) : ''; ?>" placeholder="0 0 12px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_2_margin"><?php echo esc_html( bhg_t( 'heading_2_margin', 'H2 margin' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_2_margin" name="bhg_heading_2_margin" value="<?php echo isset( $settings['heading_2_margin'] ) ? esc_attr( $settings['heading_2_margin'] ) : ''; ?>" placeholder="0 0 12px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr class="bhg-settings-subheading">
<th colspan="2" scope="colgroup"><h3><?php echo esc_html( bhg_t( 'heading_3_styles', 'H3 Styles' ) ); ?></h3></th>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_3_font_size"><?php echo esc_html( bhg_t( 'heading_3_font_size', 'H3 font size' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_3_font_size" name="bhg_heading_3_font_size" value="<?php echo isset( $settings['heading_3_font_size'] ) ? esc_attr( $settings['heading_3_font_size'] ) : ''; ?>" placeholder="1.25rem">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_3_font_weight"><?php echo esc_html( bhg_t( 'heading_3_font_weight', 'H3 font weight' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_3_font_weight" name="bhg_heading_3_font_weight" value="<?php echo isset( $settings['heading_3_font_weight'] ) ? esc_attr( $settings['heading_3_font_weight'] ) : ''; ?>" placeholder="700">
<p class="description"><?php echo esc_html( bhg_t( 'css_font_weight_hint', 'Use values like 400, 600, normal, or bold.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_3_color"><?php echo esc_html( bhg_t( 'heading_3_color', 'H3 color' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_3_color" name="bhg_heading_3_color" value="<?php echo isset( $settings['heading_3_color'] ) ? esc_attr( $settings['heading_3_color'] ) : ''; ?>" placeholder="#1e293b">
<p class="description"><?php echo esc_html( bhg_t( 'css_color_hint', 'Accepts hex or named colors (e.g. #1e293b or navy).' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_3_padding"><?php echo esc_html( bhg_t( 'heading_3_padding', 'H3 padding' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_3_padding" name="bhg_heading_3_padding" value="<?php echo isset( $settings['heading_3_padding'] ) ? esc_attr( $settings['heading_3_padding'] ) : ''; ?>" placeholder="0 0 8px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_heading_3_margin"><?php echo esc_html( bhg_t( 'heading_3_margin', 'H3 margin' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_heading_3_margin" name="bhg_heading_3_margin" value="<?php echo isset( $settings['heading_3_margin'] ) ? esc_attr( $settings['heading_3_margin'] ) : ''; ?>" placeholder="0 0 12px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr class="bhg-settings-subheading">
<th colspan="2" scope="colgroup"><h3><?php echo esc_html( bhg_t( 'description_styles', 'Description Styles' ) ); ?></h3></th>
</tr>
<tr>
<th scope="row"><label for="bhg_description_font_size"><?php echo esc_html( bhg_t( 'description_font_size', 'Description font size' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_description_font_size" name="bhg_description_font_size" value="<?php echo isset( $settings['description_font_size'] ) ? esc_attr( $settings['description_font_size'] ) : ''; ?>" placeholder="1rem">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_description_font_weight"><?php echo esc_html( bhg_t( 'description_font_weight', 'Description font weight' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_description_font_weight" name="bhg_description_font_weight" value="<?php echo isset( $settings['description_font_weight'] ) ? esc_attr( $settings['description_font_weight'] ) : ''; ?>" placeholder="400">
<p class="description"><?php echo esc_html( bhg_t( 'css_font_weight_hint', 'Use values like 400, 600, normal, or bold.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_description_color"><?php echo esc_html( bhg_t( 'description_color', 'Description color' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_description_color" name="bhg_description_color" value="<?php echo isset( $settings['description_color'] ) ? esc_attr( $settings['description_color'] ) : ''; ?>" placeholder="#475569">
<p class="description"><?php echo esc_html( bhg_t( 'css_color_hint', 'Accepts hex or named colors (e.g. #1e293b or navy).' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_description_padding"><?php echo esc_html( bhg_t( 'description_padding', 'Description padding' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_description_padding" name="bhg_description_padding" value="<?php echo isset( $settings['description_padding'] ) ? esc_attr( $settings['description_padding'] ) : ''; ?>" placeholder="0">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_description_margin"><?php echo esc_html( bhg_t( 'description_margin', 'Description margin' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_description_margin" name="bhg_description_margin" value="<?php echo isset( $settings['description_margin'] ) ? esc_attr( $settings['description_margin'] ) : ''; ?>" placeholder="0 0 12px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr class="bhg-settings-subheading">
<th colspan="2" scope="colgroup"><h3><?php echo esc_html( bhg_t( 'body_text_styles', 'Paragraph & Span Styles' ) ); ?></h3></th>
</tr>
<tr>
<th scope="row"><label for="bhg_body_text_font_size"><?php echo esc_html( bhg_t( 'body_text_font_size', 'Paragraph font size' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_body_text_font_size" name="bhg_body_text_font_size" value="<?php echo isset( $settings['body_text_font_size'] ) ? esc_attr( $settings['body_text_font_size'] ) : ''; ?>" placeholder="1rem">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_body_text_padding"><?php echo esc_html( bhg_t( 'body_text_padding', 'Paragraph padding' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_body_text_padding" name="bhg_body_text_padding" value="<?php echo isset( $settings['body_text_padding'] ) ? esc_attr( $settings['body_text_padding'] ) : ''; ?>" placeholder="0">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_body_text_margin"><?php echo esc_html( bhg_t( 'body_text_margin', 'Paragraph margin' ) ); ?></label></th>
<td>
<input type="text" class="regular-text" id="bhg_body_text_margin" name="bhg_body_text_margin" value="<?php echo isset( $settings['body_text_margin'] ) ? esc_attr( $settings['body_text_margin'] ) : ''; ?>" placeholder="0 0 12px">
<p class="description"><?php echo esc_html( bhg_t( 'css_dimension_hint', 'Accepts CSS values such as px, em, rem, %, or calc() expressions.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'profile_sections_visibility', 'Profile Sections Visibility' ) ); ?></th>
<td>
<fieldset>
<legend class="screen-reader-text"><span><?php echo esc_html( bhg_t( 'profile_sections_visibility', 'Profile Sections Visibility' ) ); ?></span></legend>
<?php
$profile_blocks = array(
        'profile_show_my_bonushunts'  => bhg_t( 'label_my_bonus_hunts', 'My Bonus Hunts' ),
        'profile_show_my_tournaments' => bhg_t( 'label_my_tournaments', 'My Tournaments' ),
        'profile_show_my_prizes'      => bhg_t( 'label_my_prizes', 'My Prizes' ),
        'profile_show_my_rankings'    => bhg_t( 'label_my_rankings', 'My Rankings' ),
);
foreach ( $profile_blocks as $key => $label ) :
        $enabled = isset( $settings[ $key ] ) ? (int) $settings[ $key ] : 1;
        $message = sprintf( bhg_t( 'label_show_block', 'Show %s section' ), $label );
        ?>
<label>
        <input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $enabled ); ?>>
        <?php echo esc_html( $message ); ?>
</label><br>
<?php endforeach; ?>
<p class="description"><?php echo esc_html( bhg_t( 'profile_visibility_hint', 'Uncheck to hide a section from the user profile shortcodes.' ) ); ?></p>
</fieldset>
</td>
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
</tbody>
</table>

<?php submit_button( bhg_t( 'save_settings', 'Save Settings' ) ); ?>
</form>
</div>
