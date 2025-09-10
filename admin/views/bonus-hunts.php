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
$tours_table    = $wpdb->prefix . 'bhg_tournaments';
$users_table    = $wpdb->users;
$aff_table      = $wpdb->prefix . 'bhg_affiliate_websites';
$allowed_tables = array(
	$wpdb->prefix . 'bhg_bonus_hunts',
	$wpdb->prefix . 'bhg_guesses',
	$wpdb->prefix . 'bhg_affiliate_websites',
	$wpdb->prefix . 'bhg_tournaments',
	$wpdb->users,
);
if (
				! in_array( $hunts_table, $allowed_tables, true ) ||
				! in_array( $guesses_table, $allowed_tables, true ) ||
				! in_array( $users_table, $allowed_tables, true ) ||
				! in_array( $tours_table, $allowed_tables, true )
) {
				wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}

$hunts_table   = esc_sql( $hunts_table );
$guesses_table = esc_sql( $guesses_table );
$users_table   = esc_sql( $users_table );
$tours_table   = esc_sql( $tours_table );
$aff_table     = esc_sql( $aff_table );

$view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'list';

/** LIST VIEW */
if ( 'list' === $view ) :
	$current_page = max( 1, isset( $_GET['paged'] ) ? (int) wp_unslash( $_GET['paged'] ) : 1 );
	$per_page     = 30;
	$offset       = ( $current_page - 1 ) * $per_page;
	$search       = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$orderby      = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'id';
	$order        = ( isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC';

	$allowed_orderby = array(
		'id'               => 'h.id',
		'title'            => 'h.title',
		'starting_balance' => 'h.starting_balance',
		'final_balance'    => 'h.final_balance',
		'affiliate'        => 'a.name',
		'winners'          => 'h.winners_count',
		'status'           => 'h.status',
	);
	$order_by_sql    = isset( $allowed_orderby[ $orderby ] ) ? $allowed_orderby[ $orderby ] : 'h.id';

	$sql  = "SELECT h.*, a.name AS affiliate_name FROM {$hunts_table} h LEFT JOIN {$aff_table} a ON a.id = h.affiliate_site_id";
	$like = '';
	if ( $search ) {
		$like = '%' . $wpdb->esc_like( $search ) . '%';
		$sql .= $wpdb->prepare( ' WHERE h.title LIKE %s', $like );
	}
	$sql  .= " ORDER BY {$order_by_sql} {$order}";
	$hunts = $wpdb->get_results( $wpdb->prepare( $sql . ' LIMIT %d OFFSET %d', $per_page, $offset ) );

	$count_sql = "SELECT COUNT(*) FROM {$hunts_table} h";
	if ( $search ) {
		$count_sql .= $wpdb->prepare( ' WHERE h.title LIKE %s', $like );
	}
	$total     = (int) $wpdb->get_var( $count_sql );
	$base_url  = remove_query_arg( array( 'paged' ) );
	$sort_base = remove_query_arg( array( 'paged', 'orderby', 'order' ) );
	?>
<div class="wrap">
<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ); ?></h1>
<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php echo esc_html( bhg_t( 'add_new', 'Add New' ) ); ?></a>

<form method="get" class="search-form">
<input type="hidden" name="page" value="bhg-bonus-hunts" />
<p class="search-box">
<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" />
	<?php if ( $orderby ) : ?>
<input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>" />
	<?php endif; ?>
	<?php if ( $order ) : ?>
<input type="hidden" name="order" value="<?php echo esc_attr( strtolower( $order ) ); ?>" />
	<?php endif; ?>
<input type="submit" class="button" value="<?php echo esc_attr( bhg_t( 'search_hunts', 'Search Hunts' ) ); ?>" />
</p>
</form>

	<?php if ( isset( $_GET['closed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['closed'] ) ) ) : ?>
	<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'hunt_closed_successfully', 'Hunt closed successfully.' ) ); ?></p></div>
	<?php endif; ?>

<table class="widefat striped bhg-margin-top-small">
<thead>
<tr>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'id',
				'order'   => ( 'id' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'id', 'ID' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'title',
				'order'   => ( 'title' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'starting_balance',
				'order'   => ( 'starting_balance' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'final_balance',
				'order'   => ( 'final_balance' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'affiliate',
				'order'   => ( 'affiliate' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'affiliate', 'Affiliate' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'winners',
				'order'   => ( 'winners' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'winners', 'Winners' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'status',
				'order'   => ( 'status' === $orderby && 'ASC' === $order ? 'desc' : 'asc' ),
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></a></th>
<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
</tr>
</thead>
	<tbody>
		<?php if ( empty( $hunts ) ) : ?>
<tr><td colspan="8"><?php echo esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ); ?></td></tr>
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
<td><?php echo $h->affiliate_name ? esc_html( $h->affiliate_name ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
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
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_hunt', 'Delete this hunt?' ) ); ?>');" class="bhg-inline-form">
				<?php wp_nonce_field( 'bhg_delete_hunt', 'bhg_delete_hunt_nonce' ); ?>
<input type="hidden" name="action" value="bhg_delete_hunt" />
<input type="hidden" name="hunt_id" value="<?php echo (int) $h->id; ?>" />
<button type="submit" class="button-link-delete"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
</form>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-inline-form">
				<?php wp_nonce_field( 'bhg_toggle_guessing', 'bhg_toggle_guessing_nonce' ); ?>
<input type="hidden" name="action" value="bhg_toggle_guessing" />
<input type="hidden" name="hunt_id" value="<?php echo (int) $h->id; ?>" />
<input type="hidden" name="guessing_enabled" value="<?php echo $h->guessing_enabled ? 0 : 1; ?>" />
<button type="submit" class="button"><?php echo esc_html( $h->guessing_enabled ? bhg_t( 'disable_guessing', 'Disable Guessing' ) : bhg_t( 'enable_guessing', 'Enable Guessing' ) ); ?></button>
</form>
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
					$wpdb->prepare(
						'SELECT * FROM %i WHERE id = %d',
						$hunts_table,
						$id
					)
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
						<th scope="row"><label for="bhg_tournament"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
						<td>
												<?php
												$t_table = $wpdb->prefix . 'bhg_tournaments';
												if ( ! in_array( $t_table, $allowed_tables, true ) ) {
																wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
												}
												$t_table = esc_sql( $t_table );
												// db call ok; no-cache ok.
												$tours = $wpdb->get_results(
													$wpdb->prepare( 'SELECT id, title FROM %i ORDER BY title ASC', $t_table )
												);
												$tsel  = isset( $hunt->tournament_id ) ? (int) $hunt->tournament_id : 0;
												?>
						<select id="bhg_tournament" name="tournament_id">
								<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
								<?php foreach ( $tours as $t ) : ?>
								<option value="<?php echo (int) $t->id; ?>"
										<?php
										if ( $tsel === (int) $t->id ) {
												echo 'selected';
										}
										?>
								><?php echo esc_html( $t->title ); ?></option>
								<?php endforeach; ?>
						</select>
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
		$users_table_local = esc_sql( $users_table_local );
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
						<th scope="row"><label for="bhg_tournament"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
						<td>
												<?php
												$t_table = $wpdb->prefix . 'bhg_tournaments';
												if ( ! in_array( $t_table, $allowed_tables, true ) ) {
																wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
												}
												$t_table = esc_sql( $t_table );
												// db call ok; no-cache ok.
												$tours = $wpdb->get_results(
													$wpdb->prepare( 'SELECT id, title FROM %i ORDER BY title ASC', $t_table )
												);
												$tsel  = isset( $hunt->tournament_id ) ? (int) $hunt->tournament_id : 0;
												?>
						<select id="bhg_tournament" name="tournament_id">
								<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
								<?php foreach ( $tours as $t ) : ?>
								<option value="<?php echo (int) $t->id; ?>"
										<?php
										if ( $tsel === (int) $t->id ) {
												echo 'selected';
										}
										?>
								><?php echo esc_html( $t->title ); ?></option>
								<?php endforeach; ?>
						</select>
						</td>
				</tr>
<tr>
<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="<?php echo esc_attr( $hunt->winners_count ?: 3 ); ?>"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_guessing_enabled"><?php echo esc_html( bhg_t( 'guessing_enabled', 'Guessing Enabled' ) ); ?></label></th>
<td><input type="checkbox" id="bhg_guessing_enabled" name="guessing_enabled" value="1" <?php checked( $hunt->guessing_enabled, 1 ); ?>></td>
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
