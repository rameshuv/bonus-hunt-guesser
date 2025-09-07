<?php
/**
 * Admin view for managing bonus hunts.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;
$hunts_table    = $wpdb->prefix . 'bhg_bonus_hunts';
$guesses_table  = $wpdb->prefix . 'bhg_guesses';
$users_table    = $wpdb->users;
$allowed_tables = array(
	$wpdb->prefix . 'bhg_bonus_hunts',
	$wpdb->prefix . 'bhg_guesses',
	$wpdb->prefix . 'bhg_affiliate_websites',
	$wpdb->users,
);
if (
		! in_array( $hunts_table, $allowed_tables, true ) ||
		! in_array( $guesses_table, $allowed_tables, true ) ||
		! in_array( $users_table, $allowed_tables, true )
) {
		wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}

$hunts_table   = esc_sql( $hunts_table );
$guesses_table = esc_sql( $guesses_table );
$users_table   = esc_sql( $users_table );

$view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'list';

/** LIST VIEW */
if ( 'list' === $view ) :
		$current_page = max( 1, isset( $_GET['paged'] ) ? (int) wp_unslash( $_GET['paged'] ) : 1 );
		$per_page     = 30;
		$offset       = ( $current_page - 1 ) * $per_page;

				// db call ok; no-cache ok.
				$hunts = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM %i ORDER BY id DESC LIMIT %d OFFSET %d',
						$hunts_table,
						$per_page,
						$offset
					)
				);

				// db call ok; no-cache ok.
				$total = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $hunts_table ) );
		$base_url      = remove_query_arg( array( 'paged' ) );
	?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php echo esc_html( bhg_t( 'add_new', 'Add New' ) ); ?></a>

	<?php if ( isset( $_GET['closed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['closed'] ) ) ) : ?>
	<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'hunt_closed_successfully', 'Hunt closed successfully.' ) ); ?></p></div>
	<?php endif; ?>

	<table class="widefat striped bhg-margin-top-small">
	<thead>
		<tr>
		<th><?php echo esc_html( bhg_t( 'id', 'ID' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'winners', 'Winners' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $hunts ) ) : ?>
		<tr><td colspan="7"><?php echo esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ); ?></td></tr>
			<?php
		else :
			foreach ( $hunts as $h ) :
				?>
		<tr>
			<td><?php echo (int) $h->id; ?></td>
			<td><a href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'view' => 'edit',
							'id'   => (int) $h->id,
						)
					)
				);
				?>
							"><?php echo esc_html( $h->title ); ?></a></td>
			<td><?php echo esc_html( number_format_i18n( (float) $h->starting_balance, 2 ) ); ?></td>
					<td><?php echo null !== $h->final_balance ? esc_html( number_format_i18n( (float) $h->final_balance, 2 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
			<td><?php echo (int) ( $h->winners_count ?? 3 ); ?></td>
                       <td><?php echo esc_html( bhg_t( $h->status, ucfirst( $h->status ) ) ); ?></td>
			<td>
			<a class="button" href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'view' => 'edit',
							'id'   => (int) $h->id,
						)
					)
				);
				?>
									"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
				<?php if ( 'open' === $h->status ) : ?>
				<a class="button" href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'view' => 'close',
								'id'   => (int) $h->id,
							)
						)
					);
					?>
										"><?php echo esc_html( bhg_t( 'close_hunt', 'Close Hunt' ) ); ?></a>
			<?php elseif ( $h->final_balance !== null ) : ?>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts-results&id=' . (int) $h->id ) ); ?>"><?php echo esc_html( bhg_t( 'button_results', 'Results' ) ); ?></a>
			<?php endif; ?>
			</td>
		</tr>
				<?php
			endforeach;
endif;
		?>
		</tbody>
	</table>

	<?php
		$total_pages = (int) ceil( $total / $per_page );
	if ( $total_pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo paginate_links(
				array(
					'base'      => add_query_arg( 'paged', '%#%', $base_url ),
					'format'    => '',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'total'     => $total_pages,
					'current'   => $current_page,
				)
			);
			echo '</div></div>';
	}
	?>
</div>
<?php endif; ?>

<?php
/** CLOSE VIEW */
if ( 'close' === $view ) :
		$id = isset( $_GET['id'] ) ? (int) wp_unslash( $_GET['id'] ) : 0;
		// db call ok; no-cache ok.
		$hunt = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $hunts_table, $id )
		);
	if ( ! $hunt || 'open' !== $hunt->status ) :
		echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt_2', 'Invalid hunt.' ) ) . '</p></div>';
	else :
		?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'close_bonus_hunt', 'Close Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-400 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_close_hunt', 'bhg_close_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_close_hunt" />
	<input type="hidden" name="hunt_id" value="<?php echo (int) $hunt->id; ?>" />
	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_final_balance"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="bhg_final_balance" name="final_balance" required></td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'close_hunt', 'Close Hunt' ) ) ); ?>
	</form>
</div>
		<?php
	endif;
endif;
?>

<?php
/** ADD VIEW */
if ( $view === 'add' ) :
	?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'add_new_bonus_hunt', 'Add New Bonus Hunt' ) ); ?></h1>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_hunt" />

	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
			<td><input required class="regular-text" id="bhg_title" name="title" value=""></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_starting"><?php echo esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="bhg_starting" name="starting_balance" value=""></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_num"><?php echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ); ?></label></th>
			<td><input type="number" min="0" id="bhg_num" name="num_bonuses" value=""></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_prizes"><?php echo esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ); ?></label></th>
			<td><textarea class="large-text" rows="3" id="bhg_prizes" name="prizes"></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_affiliate"><?php echo esc_html( bhg_t( 'affiliate_site', 'Affiliate Site' ) ); ?></label></th>
			<td>
						<?php
						$aff_table = $wpdb->prefix . 'bhg_affiliate_websites';
						if ( ! in_array( $aff_table, $allowed_tables, true ) ) {
								wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
						}
						$aff_table = esc_sql( $aff_table );
												// db call ok; no-cache ok.
												$affs = $wpdb->get_results(
													$wpdb->prepare( 'SELECT id, name FROM %i ORDER BY name ASC', $aff_table )
												);
						$sel                          = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
						?>
			<select id="bhg_affiliate" name="affiliate_site_id">
				<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
				<?php foreach ( $affs as $a ) : ?>
				<option value="<?php echo (int) $a->id; ?>" 
					<?php
					if ( $sel === (int) $a->id ) {
						echo 'selected';}
					?>
				><?php echo esc_html( $a->name ); ?></option>
				<?php endforeach; ?>
			</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
			<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="3"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_status"><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></label></th>
			<td>
			<select id="bhg_status" name="status">
				<option value="open"><?php echo esc_html( bhg_t( 'open', 'Open' ) ); ?></option>
				<option value="closed"><?php echo esc_html( bhg_t( 'label_closed', 'Closed' ) ); ?></option>
			</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'final_balance_optional', 'Final Balance (optional)' ) ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="bhg_final" name="final_balance" value=""></td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'create_bonus_hunt', 'Create Bonus Hunt' ) ) ); ?>
	</form>
</div>
<?php endif; ?>

<?php
/** EDIT VIEW */
if ( $view === 'edit' ) :
		$id = isset( $_GET['id'] ) ? (int) wp_unslash( $_GET['id'] ) : 0;
		// db call ok; no-cache ok.
                $hunt = $wpdb->get_row(
                        $wpdb->prepare(
                                'SELECT * FROM %i WHERE id = %d',
                                $hunts_table,
                                $id
                        )
                );
	if ( ! $hunt ) {
		echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt', 'Invalid hunt' ) ) . '</p></div>';
		return;
	}
		$users_table_local = $users_table;
	if ( ! in_array( $users_table_local, $allowed_tables, true ) ) {
			wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
	}
				// db call ok; no-cache ok.
								$guesses = $wpdb->get_results(
									$wpdb->prepare(
										'SELECT g.*, u.display_name FROM %i g LEFT JOIN %i u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY g.id ASC',
										$guesses_table,
										$users_table_local,
										$id
									)
								);
	?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'edit_bonus_hunt', 'Edit Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_hunt" />
	<input type="hidden" name="id" value="<?php echo (int) $hunt->id; ?>" />

	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
			<td><input required class="regular-text" id="bhg_title" name="title" value="<?php echo esc_attr( $hunt->title ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_starting"><?php echo esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="bhg_starting" name="starting_balance" value="<?php echo esc_attr( $hunt->starting_balance ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_num"><?php echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ); ?></label></th>
			<td><input type="number" min="0" id="bhg_num" name="num_bonuses" value="<?php echo esc_attr( $hunt->num_bonuses ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_prizes"><?php echo esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ); ?></label></th>
			<td><textarea class="large-text" rows="3" id="bhg_prizes" name="prizes"><?php echo esc_textarea( $hunt->prizes ); ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_affiliate"><?php echo esc_html( bhg_t( 'affiliate_site', 'Affiliate Site' ) ); ?></label></th>
			<td>
						<?php
						$aff_table = $wpdb->prefix . 'bhg_affiliate_websites';
						if ( ! in_array( $aff_table, $allowed_tables, true ) ) {
								wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
						}
						$aff_table = esc_sql( $aff_table );
												// db call ok; no-cache ok.
												$affs = $wpdb->get_results(
													$wpdb->prepare( 'SELECT id, name FROM %i ORDER BY name ASC', $aff_table )
												);
						$sel                          = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
						?>
			<select id="bhg_affiliate" name="affiliate_site_id">
				<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
				<?php foreach ( $affs as $a ) : ?>
				<option value="<?php echo (int) $a->id; ?>" 
					<?php
					if ( $sel === (int) $a->id ) {
						echo 'selected';}
					?>
				><?php echo esc_html( $a->name ); ?></option>
				<?php endforeach; ?>
			</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
			<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="<?php echo esc_attr( $hunt->winners_count ?: 3 ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
					<td><input type="number" step="0.01" min="0" id="bhg_final" name="final_balance" value="<?php echo esc_attr( $hunt->final_balance ); ?>" placeholder="<?php echo esc_attr( esc_html( bhg_t( 'label_emdash', '—' ) ) ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_status"><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></label></th>
			<td>
			<select id="bhg_status" name="status">
				<option value="open" <?php selected( $hunt->status, 'open' ); ?>><?php echo esc_html( bhg_t( 'open', 'Open' ) ); ?></option>
				<option value="closed" <?php selected( $hunt->status, 'closed' ); ?>><?php echo esc_html( bhg_t( 'label_closed', 'Closed' ) ); ?></option>
			</select>
			</td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'save_hunt', 'Save Hunt' ) ) ); ?>
	</form>

	<h2 class="bhg-margin-top-large"><?php echo esc_html( bhg_t( 'participants', 'Participants' ) ); ?></h2>
	<table class="widefat striped">
	<thead>
		<tr>
		<th><?php echo esc_html( bhg_t( 'sc_user', 'User' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'sc_guess', 'Guess' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $guesses ) ) : ?>
		<tr><td colspan="3"><?php echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) ); ?></td></tr>
			<?php
		else :
			foreach ( $guesses as $g ) :
				?>
		<tr>
			<td>
							<?php
										/* translators: %d: user ID. */
										$name = $g->display_name ? $g->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $g->user_id );
							$url              = admin_url( 'user-edit.php?user_id=' . (int) $g->user_id );
							echo '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
							?>
			</td>
			<td><?php echo esc_html( number_format_i18n( (float) ( $g->guess ?? 0 ), 2 ) ); ?></td>
			<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_guess', 'Delete this guess?' ) ); ?>');" class="bhg-inline-form">
																<?php wp_nonce_field( 'bhg_delete_guess', 'bhg_delete_guess_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_delete_guess">
				<input type="hidden" name="guess_id" value="<?php echo (int) $g->id; ?>">
				<button type="submit" class="button-link-delete"><?php echo esc_html( bhg_t( 'remove', 'Remove' ) ); ?></button>
			</form>
			</td>
		</tr>
					<?php
		endforeach;
endif;
		?>
	</tbody>
	</table>
</div>
<?php endif; ?>
