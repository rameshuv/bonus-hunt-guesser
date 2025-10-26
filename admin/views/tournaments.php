<?php
/**
 * Tournaments admin view.
 *
 * @package Bonus_Hunt_Guesser
 */

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
		? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'bhg_tournaments WHERE id = %d', $edit_id ) )
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
        'type'       => 'type',
        'start_date' => 'start_date',
        'end_date'   => 'end_date',
        'status'     => 'status',
);
$orderby_column  = isset( $allowed_orderby[ $orderby_param ] ) ? $allowed_orderby[ $orderby_param ] : 'id';
$order_param     = in_array( strtolower( $order_param ), array( 'asc', 'desc' ), true ) ? strtoupper( $order_param ) : 'DESC';
$order_by_clause = sprintf( '%s %s', $orderby_column, $order_param );

$current_page   = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
$items_per_page = 30;
$offset         = ( $current_page - 1 ) * $items_per_page;

$like_clause = '';

if ( $search_term ) {
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'bhg_tournaments WHERE title LIKE %s',
				'%' . $wpdb->esc_like( $search_term ) . '%'
			)
		);
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'bhg_tournaments WHERE title LIKE %s ORDER BY ' . $order_by_clause . ' LIMIT %d OFFSET %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Order by clause sanitized via whitelist
				'%' . $wpdb->esc_like( $search_term ) . '%',
				$items_per_page,
				$offset
			)
		);
} else {
		$total = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'bhg_tournaments' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'bhg_tournaments ORDER BY ' . $order_by_clause . ' LIMIT %d OFFSET %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Order by clause sanitized via whitelist
				$items_per_page,
				$offset
			)
		);
}
$base_url = remove_query_arg( array( 'paged' ) );
$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$linked_hunts = $row && function_exists( 'bhg_get_tournament_hunt_ids' ) ? bhg_get_tournament_hunt_ids( (int) $row->id ) : array();
if ( ! empty( $linked_hunts ) ) {
        $linked_hunts = array_values( array_filter( array_map( 'absint', (array) $linked_hunts ) ) );
}

$current_year     = (int) wp_date( 'Y' );
$date_expression  = 'COALESCE(closed_at, updated_at, created_at)';
$hunt_where_parts = array( "YEAR({$date_expression}) = %d" );
$hunt_params      = array( $current_year );

if ( ! empty( $linked_hunts ) ) {
        $placeholders       = implode( ', ', array_fill( 0, count( $linked_hunts ), '%d' ) );
        $hunt_where_parts[] = "id IN ( {$placeholders} )";
        $hunt_params        = array_merge( $hunt_params, $linked_hunts );
}

$hunts_sql = "SELECT id, title FROM {$hunts_table} WHERE " . implode( ' OR ', $hunt_where_parts ) . ' ORDER BY title ASC';
$all_hunts = $wpdb->get_results( $wpdb->prepare( $hunts_sql, $hunt_params ) );
$hunt_link_mode = isset( $row->hunt_link_mode ) ? sanitize_key( $row->hunt_link_mode ) : 'manual';
if ( ! in_array( $hunt_link_mode, array( 'manual', 'auto' ), true ) ) {
        $hunt_link_mode = 'manual';
}
$hunts_row_style = ( 'auto' === $hunt_link_mode ) ? 'display:none;' : '';
$hunts_row_attr  = $hunts_row_style ? sprintf( ' style="%s"', esc_attr( $hunts_row_style ) ) : '';
?>
<div class="wrap bhg-wrap">
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
                                $n = ( 'type' === $orderby_param && 'ASC' === $order_param ) ? 'desc' : 'asc';
                                echo '<a href="' . esc_url(
                                        add_query_arg(
                                                array(
                                                        'orderby' => 'type',
                                                        'order'   => $n,
                                                )
                                        )
                                ) . '">' . esc_html( bhg_t( 'label_type', 'Type' ) ) . '</a>';
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
                                <tr><td colspan="8"><em>
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
                        <td>
                                <?php
                                $type_slug  = sanitize_key( (string) $r->type );
                                $type_label = bhg_t( 'label_' . $type_slug, bhg_t( $type_slug, ucfirst( $type_slug ) ) );
                                echo esc_html( $type_label );
                                ?>
                        </td>
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
		$total_pages = (int) ceil( $total / $items_per_page );
		if ( $total_pages > 1 ) {
								echo '<div class="tablenav"><div class="tablenav-pages">';
								echo wp_kses_post(
									paginate_links(
										array(
											'base'      => add_query_arg( 'paged', '%#%', $base_url ),
											'format'    => '',
											'prev_text' => '&laquo;',
											'next_text' => '&raquo;',
											'total'     => $total_pages,
											'current'   => $current_page,
										)
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
                <th><label for="bhg_t_type">
                <?php
                echo esc_html( bhg_t( 'label_type', 'Type' ) );
                ?>
                </label></th>
                <td>
                        <?php
                        $type_value   = isset( $row->type ) ? sanitize_key( (string) $row->type ) : 'monthly';
                        $type_options = array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' );
                        if ( ! in_array( $type_value, $type_options, true ) ) {
                                $type_value = 'monthly';
                        }
                        ?>
                        <select id="bhg_t_type" name="type">
                        <?php foreach ( $type_options as $type_option ) : ?>
                                <option value="<?php echo esc_attr( $type_option ); ?>" <?php selected( $type_value, $type_option ); ?>><?php echo esc_html( bhg_t( $type_option, ucfirst( $type_option ) ) ); ?></option>
                        <?php endforeach; ?>
                        </select>
                </td>
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
                <tr>
                                <th><label for="bhg_t_hunt_mode">
                                <?php
                                echo esc_html( bhg_t( 'hunt_connection_mode', 'Hunt Connection Mode' ) );
                                ?>
                                </label></th>
                                <td>
                                                <fieldset>
                                                                <label>
                                                                                <input type="radio" id="bhg_t_hunt_mode_manual" name="hunt_link_mode" value="manual" <?php checked( $hunt_link_mode, 'manual' ); ?> />
                                                                                <?php echo esc_html( bhg_t( 'hunt_mode_manual', 'From Hunt admin (manual selection)' ) ); ?>
                                                                </label><br />
                                                                <label>
                                                                                <input type="radio" id="bhg_t_hunt_mode_auto" name="hunt_link_mode" value="auto" <?php checked( $hunt_link_mode, 'auto' ); ?> />
                                                                                <?php echo esc_html( bhg_t( 'hunt_mode_auto', 'All hunts within start/end period' ) ); ?>
                                                                </label>
                                                </fieldset>
                                                <p class="description"><?php echo esc_html( bhg_t( 'hunt_mode_description', 'Choose how hunts are linked to this tournament.' ) ); ?></p>
                                </td>
                </tr>
                <tr id="bhg_t_hunts_row"<?php echo $hunts_row_attr; ?>>
                <th><label for="bhg_t_hunts">
                <?php
                echo esc_html( bhg_t( 'connected_bonus_hunts', 'Connected Bonus Hunts' ) );
                ?>
                </label></th>
                <td>
                        <select id="bhg_t_hunts" name="hunt_ids[]" multiple="multiple" size="5">
                        <?php foreach ( $all_hunts as $hunt_option ) : ?>
                                <option value="<?php echo esc_attr( (int) $hunt_option->id ); ?>" <?php selected( in_array( (int) $hunt_option->id, $linked_hunts, true ) ); ?>><?php echo esc_html( $hunt_option->title ); ?></option>
                        <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html( bhg_t( 'select_multiple_tournaments_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple tournaments.' ) ); ?></p>
                </td>
                </tr>
        </table>
        <?php submit_button( $row ? bhg_t( 'update_tournament', 'Update Tournament' ) : bhg_t( 'create_tournament', 'Create Tournament' ) ); ?>
        </form>
        <script>
        document.addEventListener( 'DOMContentLoaded', function() {
                var modeInputs = document.querySelectorAll( 'input[name="hunt_link_mode"]' );
                var huntsRow = document.getElementById( 'bhg_t_hunts_row' );

                if ( ! modeInputs.length || ! huntsRow ) {
                        return;
                }

                var toggleRow = function() {
                        var selectedMode = 'manual';
                        for ( var i = 0; i < modeInputs.length; i++ ) {
                                if ( modeInputs[ i ].checked ) {
                                        selectedMode = modeInputs[ i ].value;
                                        break;
                                }
                        }

                        huntsRow.style.display = ( 'auto' === selectedMode ) ? 'none' : '';
                };

                for ( var j = 0; j < modeInputs.length; j++ ) {
                        modeInputs[ j ].addEventListener( 'change', toggleRow );
                }

                toggleRow();
        } );
        </script>
</div>
