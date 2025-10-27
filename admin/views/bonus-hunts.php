<?php
/**
 * Admin view for managing bonus hunts.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;
			$hunts_table    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$guesses_table  = esc_sql( $wpdb->prefix . 'bhg_guesses' );
$tours_table    = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$users_table    = esc_sql( $wpdb->users );
			$aff_table      = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
$allowed_tables = array( $hunts_table, $guesses_table, $aff_table, $tours_table, $users_table );
if (
								! in_array( $hunts_table, $allowed_tables, true ) ||
								! in_array( $guesses_table, $allowed_tables, true ) ||
								! in_array( $users_table, $allowed_tables, true ) ||
								! in_array( $tours_table, $allowed_tables, true )
) {
				wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}


$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'list';
if ( ! in_array( $view, array( 'list', 'add', 'edit', 'close' ), true ) ) {
        $view = 'list';
}

/** LIST VIEW */
if ( 'list' === $view ) :
	require_once BHG_PLUGIN_DIR . 'admin/class-bhg-bonus-hunts-list-table.php';

	$hunts_table = new BHG_Bonus_Hunts_List_Table();
	$hunts_table->prepare_items();
?>
<div class="wrap bhg-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php echo esc_html( bhg_t( 'add_new', 'Add New' ) ); ?></a>

	<?php if ( isset( $_GET['bhg_msg'] ) && 'invalid_final_balance' === sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) ) : ?>
		<div class="notice notice-error notice-large is-dismissible">
			<p><strong><?php echo esc_html( bhg_t( 'hunt_not_closed_invalid_final_balance', 'Hunt not closed. Please enter a non-negative final balance.' ) ); ?></strong></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['closed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['closed'] ) ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'hunt_closed_successfully', 'Hunt closed successfully.' ) ); ?></p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['bhg_msg'] ) && 'close_failed' === sanitize_text_field( wp_unslash( $_GET['bhg_msg'] ) ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( bhg_t( 'hunt_close_failed', 'Failed to close the hunt.' ) ); ?></p></div>
	<?php endif; ?>

	<form method="get">
		<input type="hidden" name="page" value="bhg-bonus-hunts" />
		<?php $hunts_table->search_box( bhg_t( 'search_hunts', 'Search Hunts' ), 'bhg-bonus-hunts-search' ); ?>
		<?php $hunts_table->display(); ?>
	</form>
</div>
<?php endif; ?>
/** CLOSE VIEW */
if ( 'close' === $view ) :
								$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
				// db call ok; no-cache ok.
								$hunt = $wpdb->get_row(
									$wpdb->prepare(
										"SELECT * FROM {$hunts_table} WHERE id = %d",
										$id
									)
								);
	if ( ! $hunt || 'open' !== $hunt->status ) :
		echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt_2', 'Invalid hunt.' ) ) . '</p></div>';
	else :
                ?>
<div class="wrap bhg-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'close_bonus_hunt', 'Close Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-400 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_close_hunt', 'bhg_close_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_close_hunt" />
<input type="hidden" name="hunt_id" value="<?php echo esc_attr( (int) $hunt->id ); ?>" />
	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_final_balance"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
                       <td><input type="text" id="bhg_final_balance" name="final_balance" value="" required></td>
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
if ( 'add' === $view ) :
        ?>
<div class="wrap bhg-wrap">
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
												$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
						if ( ! in_array( $aff_table, $allowed_tables, true ) ) {
										wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
						}
																								// db call ok; no-cache ok.
																								$affs = $wpdb->get_results(
																									"SELECT id, name FROM {$aff_table} ORDER BY name ASC"
																								);
						$sel = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
						?>
			<select id="bhg_affiliate" name="affiliate_site_id">
				<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
                                <?php foreach ( $affs as $a ) : ?>
                                <option value="<?php echo esc_attr( (int) $a->id ); ?>" <?php selected( $sel, (int) $a->id ); ?>><?php echo esc_html( $a->name ); ?></option>
                                <?php endforeach; ?>
			</select>
			</td>
				</tr>
                                <tr>
                                                <th scope="row"><label for="bhg_tournament"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
                                                <td>
                                                                                                <?php
                                                                                                $t_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
                                                                                                if ( ! in_array( $t_table, $allowed_tables, true ) ) {
                                                                                                                               wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
                                                                                                }
                                                                                                // db call ok; no-cache ok.
                                                                                                $tours = $wpdb->get_results(
                                                                                                        "SELECT id, title FROM {$t_table} ORDER BY title ASC"
                                                                                                );
                                                                                                $selected_tournaments = array();
                                                                                                ?>
                                                <select id="bhg_tournament" name="tournament_ids[]" multiple="multiple" size="5">
                                                                <?php foreach ( $tours as $t ) : ?>
                                                                <option value="<?php echo esc_attr( (int) $t->id ); ?>" <?php selected( in_array( (int) $t->id, $selected_tournaments, true ) ); ?>><?php echo esc_html( $t->title ); ?></option>
                                                                <?php endforeach; ?>
                                                </select>
                                                <p class="description"><?php echo esc_html( bhg_t( 'select_multiple_tournaments_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple tournaments.' ) ); ?></p>
                                                </td>
                                </tr>
                                <tr>
                                                <th scope="row"><label for="bhg_prize_ids"><?php echo esc_html( bhg_t( 'label_prizes', 'Prizes' ) ); ?></label></th>
                                                <td>
                                                                                                <?php
                                                                                                $prize_rows = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_prizes() : array();
                                                                                                $selected_prizes = array();
                                                                                                ?>
                                                <select id="bhg_prize_ids" name="prize_ids[]" multiple="multiple" size="5">
                                                                <?php foreach ( $prize_rows as $prize_row ) : ?>
                                                                <option value="<?php echo esc_attr( (int) $prize_row->id ); ?>" <?php selected( in_array( (int) $prize_row->id, $selected_prizes, true ) ); ?>><?php echo esc_html( $prize_row->title ); ?></option>
                                                                <?php endforeach; ?>
                                                </select>
                                                <p class="description"><?php echo esc_html( bhg_t( 'select_multiple_prizes_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple prizes.' ) ); ?></p>
                                                </td>
                                </tr>
<tr>
<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="3"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_guessing_enabled"><?php echo esc_html( bhg_t( 'guessing_enabled', 'Guessing Enabled' ) ); ?></label></th>
<td><input type="checkbox" id="bhg_guessing_enabled" name="guessing_enabled" value="1" checked></td>
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
                        <td><input type="text" id="bhg_final" name="final_balance" value=""></td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'create_bonus_hunt', 'Create Bonus Hunt' ) ) ); ?>
	</form>
</div>
<?php endif; ?>

<?php
/** EDIT VIEW */
if ( 'edit' === $view ) :
			$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		// db call ok; no-cache ok.
								$hunt = $wpdb->get_row(
									$wpdb->prepare(
										"SELECT * FROM {$hunts_table} WHERE id = %d",
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
		$users_table_local = esc_sql( $users_table_local );
								// db call ok; no-cache ok.
								$guesses = $wpdb->get_results(
									$wpdb->prepare(
										"SELECT g.*, u.display_name FROM {$guesses_table} g LEFT JOIN {$users_table_local} u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY g.id ASC",
										$id
									)
								);
        ?>
<div class="wrap bhg-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'edit_bonus_hunt', 'Edit Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_hunt" />
<input type="hidden" name="id" value="<?php echo esc_attr( (int) $hunt->id ); ?>" />

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
												$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
						if ( ! in_array( $aff_table, $allowed_tables, true ) ) {
										wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
						}
																								// db call ok; no-cache ok.
																								$affs = $wpdb->get_results(
																									"SELECT id, name FROM {$aff_table} ORDER BY name ASC"
																								);
						$sel = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
						?>
			<select id="bhg_affiliate" name="affiliate_site_id">
				<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
                                <?php foreach ( $affs as $a ) : ?>
                                <option value="<?php echo esc_attr( (int) $a->id ); ?>" <?php selected( $sel, (int) $a->id ); ?>><?php echo esc_html( $a->name ); ?></option>
                                <?php endforeach; ?>
			</select>
						</td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_tournament"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
						<td>
												<?php
												$t_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
												if ( ! in_array( $t_table, $allowed_tables, true ) ) {
																		wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
												}
												// db call ok; no-cache ok.
												$tours = $wpdb->get_results(
													"SELECT id, title FROM {$t_table} ORDER BY title ASC"
												);
                                                                                                $selected_tournaments = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( (int) $hunt->id ) : array();
                                                                                                $prize_rows          = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_prizes() : array();
                                                                                                $selected_prizes     = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_hunt_prize_ids( (int) $hunt->id ) : array();
                                                                                                ?>
                                                <select id="bhg_tournament" name="tournament_ids[]" multiple="multiple" size="5">
                                                                <?php foreach ( $tours as $t ) : ?>
                                                                <option value="<?php echo esc_attr( (int) $t->id ); ?>" <?php selected( in_array( (int) $t->id, $selected_tournaments, true ) ); ?>><?php echo esc_html( $t->title ); ?></option>
                                                                <?php endforeach; ?>
                                                </select>
                                                <p class="description"><?php echo esc_html( bhg_t( 'select_multiple_tournaments_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple tournaments.' ) ); ?></p>
                                                </td>
                                </tr>
                                <tr>
                                                <th scope="row"><label for="bhg_prize_ids_edit"><?php echo esc_html( bhg_t( 'label_prizes', 'Prizes' ) ); ?></label></th>
                                                <td>
                                                <select id="bhg_prize_ids_edit" name="prize_ids[]" multiple="multiple" size="5">
                                                                <?php foreach ( $prize_rows as $prize_row ) : ?>
                                                                <option value="<?php echo esc_attr( (int) $prize_row->id ); ?>" <?php selected( in_array( (int) $prize_row->id, $selected_prizes, true ) ); ?>><?php echo esc_html( $prize_row->title ); ?></option>
                                                                <?php endforeach; ?>
                                                </select>
                                                <p class="description"><?php echo esc_html( bhg_t( 'select_multiple_prizes_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple prizes.' ) ); ?></p>
                                                </td>
                                </tr>
<tr>
<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="<?php echo esc_attr( $hunt->winners_count ? $hunt->winners_count : 3 ); ?>"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_guessing_enabled"><?php echo esc_html( bhg_t( 'guessing_enabled', 'Guessing Enabled' ) ); ?></label></th>
<td><input type="checkbox" id="bhg_guessing_enabled" name="guessing_enabled" value="1" <?php checked( $hunt->guessing_enabled, 1 ); ?>></td>
</tr>
<tr>
<th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
<td><input type="text" id="bhg_final" name="final_balance" value="<?php echo esc_attr( $hunt->final_balance ); ?>" placeholder="<?php echo esc_attr( esc_html( bhg_t( 'label_emdash', '—' ) ) ); ?>"></td>
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

	<?php
        $participant_total = is_array( $guesses ) ? count( $guesses ) : 0;
        $participant_label = sprintf( _n( '%s participant', '%s participants', $participant_total, 'bonus-hunt-guesser' ), number_format_i18n( $participant_total ) );
        $date_format       = (string) get_option( 'date_format', 'Y-m-d' );
        $time_format       = (string) get_option( 'time_format', 'H:i' );
        $submitted_format  = trim( $date_format . ' ' . $time_format );
        if ( '' === $submitted_format ) {
                $submitted_format = 'Y-m-d H:i';
        }
        ?>

        <table class="form-table" role="presentation">
                <tbody>
                        <tr>
                                <th scope="row"><?php echo esc_html( bhg_t( 'participants', 'Participants' ) ); ?></th>
                                <td>
                                        <p class="description"><?php echo esc_html( $participant_label ); ?></p>
                                        <table class="wp-list-table widefat striped table-view-list bhg-participants-table">
                                                <thead>
                                                        <tr>
                                                                <th scope="col"><?php echo esc_html( bhg_t( 'sc_user', 'User' ) ); ?></th>
                                                                <th scope="col"><?php echo esc_html( bhg_t( 'sc_guess', 'Guess' ) ); ?></th>
                                                                <th scope="col"><?php echo esc_html( bhg_t( 'submitted_at', 'Submitted' ) ); ?></th>
                                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        <?php if ( empty( $guesses ) ) : ?>
                                                                <tr>
                                                                        <td colspan="4"><?php echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) ); ?></td>
                                                                </tr>
                                                        <?php else : ?>
                                                                <?php foreach ( $guesses as $g ) : ?>
                                                                        <?php
                                                                        /* translators: %d: user ID. */
                                                                        $name         = $g->display_name ? $g->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $g->user_id );
                                                                        $url          = admin_url( 'user-edit.php?user_id=' . (int) $g->user_id );
                                                                        $submitted_at = $g->created_at ? mysql2date( $submitted_format, $g->created_at, true ) : bhg_t( 'label_emdash', '—' );
                                                                        ?>
                                                                        <tr>
                                                                                <td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a></td>
                                                                                <td><?php echo esc_html( bhg_format_currency( (float) ( $g->guess ?? 0 ) ) ); ?></td>
                                                                                <td><?php echo esc_html( $submitted_at ); ?></td>
                                                                                <td>
                                                                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_guess', 'Delete this guess?' ) ); ?>');" class="bhg-inline-form">
                                                                                                <?php wp_nonce_field( 'bhg_delete_guess', 'bhg_delete_guess_nonce' ); ?>
                                                                                                <input type="hidden" name="action" value="bhg_delete_guess">
                                                                                                <input type="hidden" name="guess_id" value="<?php echo esc_attr( (int) $g->id ); ?>">
                                                                                                <button type="submit" class="button-link-delete"><?php echo esc_html( bhg_t( 'remove', 'Remove' ) ); ?></button>
                                                                                        </form>
                                                                                </td>
                                                                        </tr>
                                                                <?php endforeach; ?>
                                                        <?php endif; ?>
                                                </tbody>
                                        </table>
                                </td>
                        </tr>
                </tbody>
        </table>
</div>
<?php endif; ?>
