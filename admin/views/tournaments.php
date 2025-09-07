<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}
global $wpdb;
$table          = $wpdb->prefix . 'bhg_tournaments';
$allowed_tables = array( $wpdb->prefix . 'bhg_tournaments' );
if ( ! in_array( $table, $allowed_tables, true ) ) {
	wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}
$table = esc_sql( $table );

$edit_id = isset( $_GET['edit'] ) ? (int) wp_unslash( $_GET['edit'] ) : 0;
$row     = $edit_id
	? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ) )
	: null;

$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );

$labels = array(
	'weekly'    => bhg_t( 'label_weekly', 'Weekly' ),
	'monthly'   => bhg_t( 'label_monthly', 'Monthly' ),
	'quarterly' => bhg_t( 'label_quarterly', 'Quarterly' ),
	'yearly'    => bhg_t( 'label_yearly', 'Yearly' ),
	'alltime'   => bhg_t( 'label_alltime', 'Alltime' ),
);
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
	<table class="widefat striped">
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
		echo esc_html( bhg_t( 'label_type', 'Type' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_start', 'Start' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_end', 'End' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_status', 'Status' ) );
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
			<td><?php echo (int) $r->id; ?></td>
			<td><?php echo esc_html( $r->title ); ?></td>
			<td><?php echo esc_html( $labels[ $r->type ] ?? $r->type ); ?></td>
			<td><?php echo esc_html( $r->start_date ); ?></td>
			<td><?php echo esc_html( $r->end_date ); ?></td>
                       <td><?php echo esc_html( bhg_t( $r->status, ucfirst( $r->status ) ) ); ?></td>
			<td>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'edit' => (int) $r->id ) ) ); ?>">
				<?php
				echo esc_html( bhg_t( 'button_edit', 'Edit' ) );
				?>
</a>
			</td>
		</tr>
					<?php
		endforeach;
endif;
		?>
	</tbody>
	</table>

	<h2 class="bhg-margin-top-large"><?php echo $row ? esc_html( bhg_t( 'edit_tournament', 'Edit Tournament' ) ) : esc_html( bhg_t( 'add_tournament', 'Add Tournament' ) ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900">
				<?php wp_nonce_field( 'bhg_tournament_save_action', 'bhg_tournament_save_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_tournament_save" />
	<?php
	if ( $row ) :
		?>
		<input type="hidden" name="id" value="<?php echo (int) $row->id; ?>" /><?php endif; ?>
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
		<th><label for="bhg_t_type">
		<?php
		echo esc_html( bhg_t( 'label_type', 'Type' ) );
		?>
</label></th>
		<td>
			<?php
			$types = array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' );
			$cur   = $row->type ?? 'weekly';
			?>
			<select id="bhg_t_type" name="type">
			<?php foreach ( $types as $t ) : ?>
				<option value="<?php echo esc_attr( $t ); ?>" <?php selected( $cur, $t ); ?>><?php echo esc_html( $labels[ $t ] ); ?></option>
			<?php endforeach; ?>
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
