<?php
/**
 * Bonus hunt edit screen.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

/*
 * Verify nonce before processing request parameters.
 */
check_admin_referer( 'bhg_edit_hunt' );

global $wpdb;

$hunt_id = absint( wp_unslash( $_GET['id'] ?? '' ) );
$hunt    = bhg_get_hunt( $hunt_id );
if ( ! $hunt ) {
	echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt', 'Invalid hunt' ) ) . '</p></div>';
	return;
}

$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
if ( isset( $allowed_tables ) && ! in_array( $aff_table, $allowed_tables, true ) ) {
	wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}
$affiliates = $wpdb->get_results(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized via esc_sql above.
	"SELECT id, name FROM {$aff_table} ORDER BY name ASC"
);
$selected_affiliate = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;

$selected_tournaments = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( (int) $hunt->id ) : array();
$selected_tournaments = array_values( array_filter( array_map( 'absint', (array) $selected_tournaments ) ) );
$t_table              = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$sql_params           = array( 'active' );
$query                = "SELECT id, title FROM {$t_table} WHERE status = %s";

if ( ! empty( $selected_tournaments ) ) {
	$placeholders = implode( ',', array_fill( 0, count( $selected_tournaments ), '%d' ) );
	$query       .= " OR id IN ({$placeholders})";
	$sql_params   = array_merge( $sql_params, $selected_tournaments );
}

$query       .= ' ORDER BY title ASC';
$sql_args     = array_merge( array( $query ), $sql_params );
$prepared_sql = call_user_func_array( array( $wpdb, 'prepare' ), $sql_args );
$tournaments  = $wpdb->get_results( $prepared_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared via call_user_func_array above.

$participants_page     = max( 1, absint( wp_unslash( $_GET['ppaged'] ?? '' ) ) );
$participants_per_page = 30;
$data                  = bhg_get_hunt_participants( $hunt_id, $participants_page, $participants_per_page );
$participants          = $data['rows'];
$total_participants    = (int) $data['total'];
$total_pages           = max( 1, (int) ceil( $total_participants / $participants_per_page ) );
$participants_base     = remove_query_arg( 'ppaged' );

$prize_rows      = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_prizes() : array();
$selected_prizes = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_hunt_prize_ids( $hunt->id ) : array();
?>
<div class="wrap">
<?php
$message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
if ( 'guess_deleted' === $message ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( bhg_t( 'notice_guess_removed_successfully', 'Guess removed successfully.' ) ) . '</p></div>';
} elseif ( 'error' === $message ) {
	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( bhg_t( 'ajax_error', 'An error occurred. Please try again.' ) ) . '</p></div>';
}
?>
<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'edit_bonus_hunt', 'Edit Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
<input type="hidden" name="action" value="bhg_save_hunt" />
<input type="hidden" name="id" value="<?php echo (int) $hunt->id; ?>" />

<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><label for="bhg_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
<td><input required class="regular-text" id="bhg_title" name="title" value="<?php echo esc_attr( $hunt->title ); ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_starting"><?php echo esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ); ?></label></th>
<td><input type="number" step="0.01" min="0" id="bhg_starting" name="starting_balance" value="<?php echo esc_attr( $hunt->starting_balance ); ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_num"><?php echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ); ?></label></th>
<td><input type="number" min="0" id="bhg_num" name="num_bonuses" value="<?php echo esc_attr( $hunt->num_bonuses ); ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_prizes"><?php echo esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ); ?></label></th>
<td><textarea class="large-text" rows="3" id="bhg_prizes" name="prizes"><?php echo esc_textarea( $hunt->prizes ); ?></textarea></td>
</tr>
<tr>
<th scope="row"><label for="bhg_affiliate"><?php echo esc_html( bhg_t( 'affiliate_site', 'Affiliate Site' ) ); ?></label></th>
<td>
<select id="bhg_affiliate" name="affiliate_site_id">
<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
<?php foreach ( $affiliates as $affiliate ) : ?>
<option value="<?php echo (int) $affiliate->id; ?>" <?php selected( $selected_affiliate, (int) $affiliate->id ); ?>><?php echo esc_html( $affiliate->name ); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_tournament_select"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
<td>
<select id="bhg_tournament_select" name="tournament_ids[]" multiple="multiple" size="5">
<?php foreach ( $tournaments as $tournament ) : ?>
<option value="<?php echo (int) $tournament->id; ?>" <?php selected( in_array( (int) $tournament->id, $selected_tournaments, true ) ); ?>><?php echo esc_html( $tournament->title ); ?></option>
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
<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="<?php echo esc_attr( $hunt->winners_count ? $hunt->winners_count : 3 ); ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
<td><input type="number" step="0.01" min="0" id="bhg_final" name="final_balance" value="<?php echo esc_attr( $hunt->final_balance ); ?>" placeholder="<?php echo esc_attr( bhg_t( 'label_dash', '-' ) ); ?>" /></td>
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

<h2 class="bhg-margin-top-large">
<?php echo esc_html( bhg_t( 'participants', 'Participants' ) ); ?>
</h2>
<p>
<?php
/* translators: %s: number of participants */
echo esc_html( sprintf( _n( '%s participant', '%s participants', $total_participants, 'bonus-hunt-guesser' ), number_format_i18n( $total_participants ) ) );
?>
</p>

<table class="widefat striped">
<thead>
<tr>
<th><?php echo esc_html( bhg_t( 'sc_user', 'User' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'sc_guess', 'Guess' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'date', 'Date' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
</tr>
</thead>
<tbody>
<?php if ( empty( $participants ) ) : ?>
<tr>
<td colspan="4"><?php echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) ); ?></td>
</tr>
<?php else : ?>
	<?php foreach ( $participants as $participant ) : ?>
		<?php
		$user_data = get_userdata( (int) $participant->user_id );
		$name      = $user_data ? $user_data->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $participant->user_id );
		?>
<tr>
<td>
<a href="<?php echo esc_url( get_edit_user_link( (int) $participant->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a>
</td>
<td><?php echo esc_html( bhg_format_currency( $participant->guess ) ); ?></td>
<td><?php echo esc_html( bhg_format_date_i18n( $participant->created_at ) ); ?></td>
<td>
<a class="button-link-delete" href="
		<?php
		echo esc_url(
			wp_nonce_url(
				add_query_arg(
					array(
						'action'   => 'bhg_delete_guess',
						'guess_id' => (int) $participant->id,
					),
					admin_url( 'admin-post.php' )
				),
				'bhg_delete_guess_' . (int) $participant->id
			)
		);
		?>
									">
		<?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?>
</a>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
<?php
if ( $total_pages > 1 ) {
	$pagination = paginate_links(
		array(
			'base'      => esc_url_raw( add_query_arg( 'ppaged', '%#%', $participants_base ) ),
			'format'    => '',
			'prev_text' => esc_html__( '&laquo;', 'bonus-hunt-guesser' ),
			'next_text' => esc_html__( '&raquo;', 'bonus-hunt-guesser' ),
			'total'     => $total_pages,
			'current'   => $participants_page,
		)
	);
	if ( $pagination ) {
		echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $pagination ) . '</div></div>';
	}
}
?>
</div>
