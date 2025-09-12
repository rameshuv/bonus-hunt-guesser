<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}
global $wpdb;
$table          = $wpdb->prefix . 'bhg_tournaments';
$allowed_tables = array( $wpdb->prefix . 'bhg_tournaments' );
if ( ! in_array( $table, $allowed_tables, true ) ) {
		wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}

$edit_id = isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0;
$row     = $edit_id
? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ) ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
: null;

$search_term = '';
if ( isset( $_GET['s'] ) ) {
    check_admin_referer( 'bhg_tournaments_search', 'bhg_tournaments_search_nonce' );
    $search_term = sanitize_text_field( wp_unslash( $_GET['s'] ) );
}
$orderby_param   = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'id';
$order_param     = isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'DESC';
$allowed_orderby = array(
'id'         => 'id',
'title'      => 'title',
'start_date' => 'start_date',
'end_date'   => 'end_date',
'status'     => 'status',
);
$orderby_column  = isset( $allowed_orderby[ $orderby_param ] ) ? $allowed_orderby[ $orderby_param ] : 'id';
$order_param     = in_array( strtolower( $order_param ), array( 'asc', 'desc' ), true ) ? strtoupper( $order_param ) : 'DESC';
$order_by_clause = sprintf( '%s %s', $orderby_column, $order_param );

$paged    = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
$per_page = 30;
$offset   = ( $paged - 1 ) * $per_page;

$sql       = "SELECT * FROM {$table}";
$count_sql = "SELECT COUNT(*) FROM {$table}";
$params    = array();

if ( $search_term ) {
$sql       .= ' WHERE title LIKE %s';
$count_sql .= ' WHERE title LIKE %s';
$params[]   = '%' . $wpdb->esc_like( $search_term ) . '%';
}

$sql       .= " ORDER BY {$order_by_clause} LIMIT %d OFFSET %d";
$params_sql = array_merge( $params, array( $per_page, $offset ) );
$sql        = $wpdb->prepare( $sql, ...$params_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$rows       = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$count_sql = $params ? $wpdb->prepare( $count_sql, ...$params ) : $count_sql; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$total     = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$base_url  = remove_query_arg( array( 'paged' ) );
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
	<?php
	echo esc_html( bhg_t( 'menu_tournaments', 'Tournaments' ) );
	?>
</h1>

	<h2 class="bhg-margin-top-small">
	<?php
	echo esc_html( bhg_t( 'all_tournaments', 'All Tournaments' ) );
	?>
</h2>
               <form method="get" class="search-form">
                               <?php wp_nonce_field( 'bhg_tournaments_search', 'bhg_tournaments_search_nonce' ); ?>
                               <input type="hidden" name="page" value="bhg-tournaments" />
                               <p class="search-box">
                                               <label class="screen-reader-text" for="bhg-search-input"><?php echo esc_html( bhg_t( 'search_tournaments', 'Search Tournaments' ) ); ?></label>
<input type="search" id="bhg-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
                                               <?php submit_button( bhg_t( 'search', 'Search' ), 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
                               </p>
               </form>
		<table class="widefat striped">
		<thead>
		<tr>
				<th>
				<?php
				$n = ( 'id' === $orderby_param && 'ASC' === $order_param ) ? 'desc' : 'asc';
				echo '<a href="' . esc_url(
					add_query_arg(
						array(
							'orderby' => 'id',
							'order'   => $n,
						)
					)
				) . '">' . esc_html( bhg_t( 'id', 'ID' ) ) . '</a>';
				?>
				</th>
				<th>
				<?php
				$n = ( 'title' === $orderby_param && 'ASC' === $order_param ) ? 'desc' : 'asc';
				echo '<a href="' . esc_url(
					add_query_arg(
						array(
							'orderby' => 'title',
							'order'   => $n,
						)
					)
				) . '">' . esc_html( bhg_t( 'sc_title', 'Title' ) ) . '</a>';
				?>
				</th>
				<th>
				<?php
				$n = ( 'start_date' === $orderby_param && 'ASC' === $order_param ) ? 'desc' : 'asc';
				echo '<a href="' . esc_url(
					add_query_arg(
						array(
							'orderby' => 'start_date',
							'order'   => $n,
						)
					)
				) . '">' . esc_html( bhg_t( 'sc_start', 'Start' ) ) . '</a>';
				?>
				</th>
				<th>
				<?php
				$n = ( 'end_date' === $orderby_param && 'ASC' === $order_param ) ? 'desc' : 'asc';
				echo '<a href="' . esc_url(
					add_query_arg(
						array(
							'orderby' => 'end_date',
							'order'   => $n,
						)
					)
				) . '">' . esc_html( bhg_t( 'sc_end', 'End' ) ) . '</a>';
				?>
				</th>
				<th>
				<?php
				$n = ( 'status' === $orderby_param && 'ASC' === $order_param ) ? 'desc' : 'asc';
				echo '<a href="' . esc_url(
					add_query_arg(
						array(
							'orderby' => 'status',
							'order'   => $n,
						)
					)
				) . '">' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . '</a>';
				?>
				</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'label_actions', 'Actions' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'admin_action', 'Admin Action' ) );
				?>
</th>
				</tr>
		</thead>
		<tbody>
				<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="7"><em>
						<?php
						echo esc_html( bhg_t( 'no_tournaments_yet', 'No tournaments yet.' ) );
						?>
</em></td></tr>
					<?php
		else :
			foreach ( $rows as $r ) :
				?>
		<tr>
<td><?php echo esc_html( (int) $r->id ); ?></td>
			<td><?php echo esc_html( $r->title ); ?></td>
			<td><?php echo esc_html( $r->start_date ); ?></td>
						<td><?php echo esc_html( $r->end_date ); ?></td>
<td><?php echo esc_html( bhg_t( $r->status, ucfirst( $r->status ) ) ); ?></td>
<td>
<a class="button" href="<?php echo esc_url( add_query_arg( array( 'edit' => (int) $r->id ) ) ); ?>">
                                <?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?>
</a>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
                                <?php wp_nonce_field( 'bhg_tournament_close', 'bhg_tournament_close_nonce' ); ?>
                                <input type="hidden" name="action" value="bhg_tournament_close" />
                                <input type="hidden" name="tournament_id" value="<?php echo esc_attr( (int) $r->id ); ?>" />
                                <button type="submit" class="button"><?php echo esc_html( bhg_t( 'close', 'Close' ) ); ?></button>
</form>
<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts-results&type=tournament&id=' . (int) $r->id ) ); ?>">
                                <?php echo esc_html( bhg_t( 'button_results', 'Results' ) ); ?>
</a>
</td>
<td>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<?php wp_nonce_field( 'bhg_tournament_delete_action', 'bhg_tournament_delete_nonce' ); ?>
<input type="hidden" name="action" value="bhg_tournament_delete" />
<input type="hidden" name="id" value="<?php echo esc_attr( (int) $r->id ); ?>" />
<button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'are_you_sure', 'Are you sure?' ) ); ?>');"><?php echo esc_html( bhg_t( 'button_delete', 'Delete' ) ); ?></button>
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
                                'current'   => $paged,
                        )
                );
                echo '</div></div>';
        }
        ?>


	<h2 class="bhg-margin-top-large"><?php echo $row ? esc_html( bhg_t( 'edit_tournament', 'Edit Tournament' ) ) : esc_html( bhg_t( 'add_tournament', 'Add Tournament' ) ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900">
				<?php wp_nonce_field( 'bhg_tournament_save_action', 'bhg_tournament_save_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_tournament_save" />
	<?php
	if ( $row ) :
		?>
<input type="hidden" name="id" value="<?php echo esc_attr( (int) $row->id ); ?>" /><?php endif; ?>
	<table class="form-table">
		<tr>
		<th><label for="bhg_t_title">
		<?php
		echo esc_html( bhg_t( 'sc_title', 'Title' ) );
		?>
</label></th>
		<td><input id="bhg_t_title" class="regular-text" name="title" value="<?php echo esc_attr( $row->title ?? '' ); ?>" required /></td>
		</tr>
		<tr>
		<th><label for="bhg_t_desc">
		<?php
		echo esc_html( bhg_t( 'description', 'Description' ) );
		?>
</label></th>
		<td><textarea id="bhg_t_desc" class="large-text" rows="4" name="description"><?php echo esc_textarea( $row->description ?? '' ); ?></textarea></td>
		</tr>
		<tr>
				<th><label for="bhg_t_pmode">
				<?php
				echo esc_html( bhg_t( 'participants_mode', 'Participants Mode' ) );
				?>
				</label></th>
				<td>
						<?php $pmode = $row->participants_mode ?? 'winners'; ?>
						<select id="bhg_t_pmode" name="participants_mode">
								<option value="winners" <?php selected( $pmode, 'winners' ); ?>><?php echo esc_html( bhg_t( 'winners', 'Winners' ) ); ?></option>
								<option value="all" <?php selected( $pmode, 'all' ); ?>><?php echo esc_html( bhg_t( 'all', 'All' ) ); ?></option>
						</select>
				</td>
				</tr>
				<tr>
				<th><label for="bhg_t_start">
		<?php
		echo esc_html( bhg_t( 'label_start_date', 'Start Date' ) );
		?>
</label></th>
		<td><input id="bhg_t_start" type="date" name="start_date" value="<?php echo esc_attr( $row->start_date ?? '' ); ?>" /></td>
		</tr>
		<tr>
		<th><label for="bhg_t_end">
		<?php
		echo esc_html( bhg_t( 'label_end_date', 'End Date' ) );
		?>
</label></th>
		<td><input id="bhg_t_end" type="date" name="end_date" value="<?php echo esc_attr( $row->end_date ?? '' ); ?>" /></td>
		</tr>
		<tr>
		<th><label for="bhg_t_status">
		<?php
		echo esc_html( bhg_t( 'sc_status', 'Status' ) );
		?>
</label></th>
		<td>
			<?php
			$st  = array( 'active', 'archived' );
			$cur = $row->status ?? 'active';
			?>
			<select id="bhg_t_status" name="status">
			<?php foreach ( $st as $v ) : ?>
				<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $cur, $v ); ?>><?php echo esc_html( ucfirst( $v ) ); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
		</tr>
	</table>
	<?php submit_button( $row ? bhg_t( 'update_tournament', 'Update Tournament' ) : bhg_t( 'create_tournament', 'Create Tournament' ) ); ?>
	</form>
</div>
