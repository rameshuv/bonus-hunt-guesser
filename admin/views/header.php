<?php
/**
 * Admin view template for the plugin's main dashboard header.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;
$bhg_db  = new BHG_DB();
$message = '';

// Process form submissions
if ( isset( $_POST['bhg_action'] ) ) {
	$action = sanitize_text_field( $_POST['bhg_action'] );

	// Verify nonce based on action
	$nonce_action = 'bhg_' . $action;
	check_admin_referer( $nonce_action, 'bhg_nonce' );

	switch ( $action ) {
		case 'create_bonus_hunt':
			// Handle bonus hunt creation with proper sanitization
			$title             = sanitize_text_field( $_POST['title'] );
			$starting_balance  = floatval( $_POST['starting_balance'] );
			$num_bonuses       = intval( $_POST['num_bonuses'] );
			$prizes            = sanitize_textarea_field( $_POST['prizes'] );
			$status            = sanitize_text_field( $_POST['status'] );
			$affiliate_site_id = isset( $_POST['affiliate_site_id'] ) ? intval( $_POST['affiliate_site_id'] ) : 0;

			// Save to database
			$result = $bhg_db->create_bonus_hunt(
				array(
					'title'             => $title,
					'starting_balance'  => $starting_balance,
					'num_bonuses'       => $num_bonuses,
					'prizes'            => $prizes,
					'status'            => $status,
					'affiliate_site_id' => $affiliate_site_id,
					'created_by'        => get_current_user_id(),
					'created_at'        => current_time( 'mysql' ),
				)
			);

			if ( $result ) {
				$message = 'success';
			} else {
				$message = 'error';
			}
			break;

		case 'update_bonus_hunt':
			// Handle bonus hunt update
			$id                = intval( $_POST['id'] );
			$title             = sanitize_text_field( $_POST['title'] );
			$starting_balance  = floatval( $_POST['starting_balance'] );
			$num_bonuses       = intval( $_POST['num_bonuses'] );
			$prizes            = sanitize_textarea_field( $_POST['prizes'] );
			$status            = sanitize_text_field( $_POST['status'] );
			$final_balance     = isset( $_POST['final_balance'] ) ? floatval( $_POST['final_balance'] ) : null;
			$affiliate_site_id = isset( $_POST['affiliate_site_id'] ) ? intval( $_POST['affiliate_site_id'] ) : 0;

			$result = $bhg_db->update_bonus_hunt(
				$id,
				array(
					'title'             => $title,
					'starting_balance'  => $starting_balance,
					'num_bonuses'       => $num_bonuses,
					'prizes'            => $prizes,
					'status'            => $status,
					'final_balance'     => $final_balance,
					'affiliate_site_id' => $affiliate_site_id,
				)
			);

			if ( $result ) {
				$message = 'updated';
			} else {
				$message = 'error';
			}
			break;

		case 'delete_bonus_hunt':
			// Handle bonus hunt deletion
			$id     = intval( $_POST['id'] );
			$result = $bhg_db->delete_bonus_hunt( $id );

			if ( $result ) {
				$message = 'deleted';
			} else {
				$message = 'error';
			}
			break;

		default:
			// Unknown action
			break;
	}

	// Redirect to avoid form resubmission
	$redirect_url = esc_url_raw( add_query_arg( 'message', $message, $_SERVER['REQUEST_URI'] ) );
	wp_safe_redirect( $redirect_url );
	exit;
}

// Get all bonus hunts
$bonus_hunts        = $bhg_db->get_all_bonus_hunts();
$affiliate_websites = $bhg_db->get_affiliate_websites();

// Display status messages
if ( isset( $_GET['message'] ) ) {
	$message_type = sanitize_text_field( $_GET['message'] );
	switch ( $message_type ) {
		case 'success':
			echo '<div class="notice notice-success is-dismissible"><p>' .
				esc_html( bhg_t( 'notice_bonus_hunt_created', 'Bonus hunt created successfully!' ) ) .
				'</p></div>';
			break;
		case 'updated':
			echo '<div class="notice notice-success is-dismissible"><p>' .
				esc_html( bhg_t( 'notice_bonus_hunt_updated', 'Bonus hunt updated successfully!' ) ) .
				'</p></div>';
			break;
		case 'deleted':
			echo '<div class="notice notice-success is-dismissible"><p>' .
				esc_html( bhg_t( 'notice_bonus_hunt_deleted', 'Bonus hunt deleted successfully!' ) ) .
				'</p></div>';
			break;
		case 'error':
			echo '<div class="notice notice-error is-dismissible"><p>' .
				esc_html( bhg_t( 'ajax_error', 'An error occurred. Please try again.' ) ) .
				'</p></div>';
			break;
	}
}
?>

<div class="wrap bhg-admin">
	<h1>
	<?php
	echo esc_html( bhg_t( 'bonus_hunt_guesser', 'Bonus Hunt Guesser' ) );
	?>
</h1>
	<hr/>
	
	<div class="bhg-admin-content">
		<h2>
		<?php
		echo esc_html( bhg_t( 'button_create_new_bonus_hunt', 'Create New Bonus Hunt' ) );
		?>
</h2>
		<form method="post" action="">
			<?php wp_nonce_field( 'bhg_create_bonus_hunt', 'bhg_nonce' ); ?>
			<input type="hidden" name="bhg_action" value="create_bonus_hunt">
			
			<table class="form-table">
				<tr>
					<th scope="row"><label for="title">
					<?php
					echo esc_html( bhg_t( 'label_bonus_hunt_title', 'Bonus Hunt Title' ) );
					?>
</label></th>
					<td>
						<input type="text" name="title" id="title" class="regular-text" required 
								value="<?php echo isset( $_POST['title'] ) ? esc_attr( $_POST['title'] ) : ''; ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="starting_balance">
					<?php
					echo esc_html( bhg_t( 'label_start_balance_euro', 'Starting Balance (€)' ) );
					?>
</label></th>
					<td>
						<input type="number" name="starting_balance" id="starting_balance" step="0.01" min="0"
								value="<?php echo esc_attr( isset( $_POST['starting_balance'] ) ? floatval( $_POST['starting_balance'] ) : '0' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="num_bonuses">
					<?php
					echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) );
					?>
</label></th>
					<td>
						<input type="number" name="num_bonuses" id="num_bonuses" min="1"
								value="<?php echo esc_attr( isset( $_POST['num_bonuses'] ) ? intval( $_POST['num_bonuses'] ) : '10' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="prizes">
					<?php
					echo esc_html( bhg_t( 'label_prizes_description', 'Prizes Description' ) );
					?>
</label></th>
					<td>
						<textarea name="prizes" id="prizes" rows="5" class="large-text">
						<?php
							echo isset( $_POST['prizes'] ) ? esc_textarea( $_POST['prizes'] ) : '';
						?>
						</textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="status">
					<?php
					echo esc_html( bhg_t( 'sc_status', 'Status' ) );
					?>
</label></th>
					<td>
						<select name="status" id="status" required>
							<option value="active">
							<?php
							echo esc_html( bhg_t( 'label_active', 'Active' ) );
							?>
</option>
							<option value="upcoming">
							<?php
							echo esc_html( bhg_t( 'label_upcoming', 'Upcoming' ) );
							?>
</option>
							<option value="completed">
							<?php
							echo esc_html( bhg_t( 'label_completed', 'Completed' ) );
							?>
</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="affiliate_site_id">
					<?php
					echo esc_html( bhg_t( 'label_affiliate_website', 'Affiliate Website' ) );
					?>
</label></th>
					<td>
						<select name="affiliate_site_id" id="affiliate_site_id">
							<option value="0">
							<?php
							echo esc_html( bhg_t( 'none', 'None' ) );
							?>
</option>
													<?php foreach ( $affiliate_websites as $site ) : ?>
								<option value="<?php echo esc_attr( $site->id ); ?>">
														<?php echo esc_html( $site->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
			
			<?php submit_button( esc_html( bhg_t( 'create_bonus_hunt', 'Create Bonus Hunt' ) ) ); ?>
		</form>
		
		<hr>
		
		<h2>
		<?php
		echo esc_html( bhg_t( 'label_existing_bonus_hunts', 'Existing Bonus Hunts' ) );
		?>
</h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>
					<?php
					echo esc_html( bhg_t( 'id', 'ID' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'sc_title', 'Title' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'sc_status', 'Status' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'label_created', 'Created' ) );
					?>
</th>
					<th>
					<?php
					echo esc_html( bhg_t( 'label_actions', 'Actions' ) );
					?>
</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $bonus_hunts ) ) : ?>
					<?php foreach ( $bonus_hunts as $hunt ) : ?>
						<tr>
							<td><?php echo esc_html( $hunt->id ); ?></td>
							<td><?php echo esc_html( $hunt->title ); ?></td>
							<td>€<?php echo esc_html( number_format( $hunt->starting_balance, 2 ) ); ?></td>
							<td>
								<?php if ( $hunt->final_balance !== null ) : ?>
									€<?php echo esc_html( number_format( $hunt->final_balance, 2 ) ); ?>
								<?php else : ?>
									<em>
									<?php
									echo esc_html( bhg_t( 'label_not_set', 'Not set' ) );
									?>
</em>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $hunt->num_bonuses ); ?></td>
							<td>
                                                            <span class="bhg-status bhg-status-<?php echo esc_attr( $hunt->status ); ?>">
                                                                    <?php echo esc_html( bhg_t( strtolower( (string) $hunt->status ), ucfirst( $hunt->status ) ) ); ?>
                                                            </span>
							</td>
							<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $hunt->created_at ) ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg_bonus_hunts&action=edit&id=' . intval( $hunt->id ) ) ); ?>" class="button button-small">
									<?php
									echo esc_html( bhg_t( 'button_edit', 'Edit' ) );
									?>
								</a>
								<form method="post" style="display:inline;">
									<?php wp_nonce_field( 'bhg_delete_bonus_hunt', 'bhg_nonce' ); ?>
									<input type="hidden" name="bhg_action" value="delete_bonus_hunt">
									<input type="hidden" name="id" value="<?php echo esc_attr( $hunt->id ); ?>">
									<button type="submit" class="button button-small button-danger"
											onclick="return confirm('<?php echo esc_js( bhg_t( 'confirm_delete_bonus_hunt', 'Are you sure you want to delete this bonus hunt?' ) ); ?>');">
										<?php
										echo esc_html( bhg_t( 'button_delete', 'Delete' ) );
										?>
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="8">
						<?php
						echo esc_html( bhg_t( 'notice_no_bonus_hunts_found', 'No bonus hunts found.' ) );
						?>
</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<style>
.bhg-status {
	padding: 4px 8px;
	border-radius: 3px;
	font-size: 12px;
	font-weight: bold;
}

.bhg-status-active {
	background-color: #46b450;
	color: white;
}

.bhg-status-upcoming {
	background-color: #ffb900;
	color: white;
}

.bhg-status-completed {
	background-color: #0073aa;
	color: white;
}

.button-danger {
	background: #d63638;
	border-color: #d63638;
	color: white;
}

.button-danger:hover {
	background: #b32d2e;
	border-color: #b32d2e;
	color: white;
}
</style>