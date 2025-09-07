<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

// Get current settings
$current_settings = get_option(
	'bhg_plugin_settings',
	array(
		'default_tournament_period' => 'monthly',
		'max_guess_amount'          => 100000,
		'min_guess_amount'          => 0,
		'allow_guess_changes'       => 'yes',
	)
);

// Handle settings save via admin-post.php
$message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
if ( 'saved' === $message ) {
	echo '<div class="notice notice-success is-dismissible"><p>' .
		esc_html( bhg_t( 'settings_saved_successfully', 'Settings saved successfully.' ) ) .
		'</p></div>';
}

// Handle error messages
$error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';
if ( ! empty( $error ) ) {
	$error_message = '';
	switch ( $error ) {
		case 'nonce_failed':
				$error_message = esc_html( bhg_t( 'security_check_failed_please_try_again', 'Security check failed. Please try again.' ) );
			break;
		case 'invalid_data':
				$error_message = esc_html( bhg_t( 'invalid_data_submitted_please_check_your_inputs', 'Invalid data submitted. Please check your inputs.' ) );
			break;
		default:
				$error_message = esc_html( bhg_t( 'an_error_occurred_while_saving_settings', 'An error occurred while saving settings.' ) );
	}

	if ( ! empty( $error_message ) ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . $error_message . '</p></div>';
	}
}
?>

<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'bonus_hunt_guesser_settings', 'Bonus Hunt Guesser Settings' ) );; ?></h1>
	
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="bhg_save_settings">
                <?php wp_nonce_field( 'bhg_save_settings' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="bhg_default_tournament_period">
												<?php echo esc_html( bhg_t( 'default_tournament_period', 'Default Tournament Period' ) );; ?>
					</label>
				</th>
				<td>
					<select name="bhg_default_tournament_period" id="bhg_default_tournament_period" class="regular-text">
						<option value="weekly" <?php selected( $current_settings['default_tournament_period'], 'weekly' ); ?>>
														<?php echo esc_html( bhg_t( 'label_weekly', 'Weekly' ) );; ?>
						</option>
						<option value="monthly" <?php selected( $current_settings['default_tournament_period'], 'monthly' ); ?>>
														<?php echo esc_html( bhg_t( 'label_monthly', 'Monthly' ) );; ?>
						</option>
						<option value="quarterly" <?php selected( $current_settings['default_tournament_period'], 'quarterly' ); ?>>
														<?php echo esc_html( bhg_t( 'label_quarterly', 'Quarterly' ) );; ?>
						</option>
						<option value="yearly" <?php selected( $current_settings['default_tournament_period'], 'yearly' ); ?>>
														<?php echo esc_html( bhg_t( 'label_yearly', 'Yearly' ) );; ?>
						</option>
						<option value="alltime" <?php selected( $current_settings['default_tournament_period'], 'alltime' ); ?>>
														<?php echo esc_html( bhg_t( 'label_all_time', 'All-Time' ) );; ?>
						</option>
					</select>
					<p class="description">
												<?php echo esc_html( bhg_t( 'default_period_for_tournament_calculations', 'Default period for tournament calculations.' ) );; ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="bhg_min_guess_amount">
												<?php echo esc_html( bhg_t( 'minimum_guess_amount', 'Minimum Guess Amount' ) );; ?>
					</label>
				</th>
				<td>
					<input type="number" name="bhg_min_guess_amount" id="bhg_min_guess_amount" 
							value="<?php echo esc_attr( $current_settings['min_guess_amount'] ); ?>" 
							class="regular-text" step="0.01" min="0" required>
					<p class="description">
												<?php echo esc_html( bhg_t( 'minimum_amount_users_can_guess_for_a_bonus_hunt', 'Minimum amount users can guess for a bonus hunt.' ) );; ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="bhg_max_guess_amount">
												<?php echo esc_html( bhg_t( 'maximum_guess_amount', 'Maximum Guess Amount' ) );; ?>
					</label>
				</th>
				<td>
					<input type="number" name="bhg_max_guess_amount" id="bhg_max_guess_amount" 
							value="<?php echo esc_attr( $current_settings['max_guess_amount'] ); ?>" 
							class="regular-text" step="0.01" min="0" required>
					<p class="description">
												<?php echo esc_html( bhg_t( 'maximum_amount_users_can_guess_for_a_bonus_hunt', 'Maximum amount users can guess for a bonus hunt.' ) );; ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="bhg_allow_guess_changes">
												<?php echo esc_html( bhg_t( 'allow_guess_changes', 'Allow Guess Changes' ) );; ?>
					</label>
				</th>
				<td>
					<select name="bhg_allow_guess_changes" id="bhg_allow_guess_changes" class="regular-text">
						<option value="yes" <?php selected( $current_settings['allow_guess_changes'], 'yes' ); ?>>
														<?php echo esc_html( bhg_t( 'yes', 'Yes' ) );; ?>
						</option>
						<option value="no" <?php selected( $current_settings['allow_guess_changes'], 'no' ); ?>>
														<?php echo esc_html( bhg_t( 'no', 'No' ) );; ?>
						</option>
					</select>
					<p class="description">
												<?php echo esc_html( bhg_t( 'allow_users_to_change_their_guesses_before_a_bonus_hunt_closes', 'Allow users to change their guesses before a bonus hunt closes.' ) );; ?>
					</p>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<input type="submit" name="bhg_settings_submit" id="submit" 
								class="button button-primary" value="<?php echo esc_html( bhg_t( 'save_changes', 'Save Changes' ) );; ?>">
		</p>
	</form>
</div>