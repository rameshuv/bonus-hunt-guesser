<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

// Handle form submissions
if ( isset( $_POST['bhg_action'] ) ) {
	if ( 'db_cleanup' === $_POST['bhg_action'] && isset( $_POST['bhg_db_cleanup'] ) ) {
		check_admin_referer( 'bhg_db_cleanup_action', 'bhg_nonce' );

		// Perform database cleanup
		bhg_database_cleanup();
		$cleanup_completed = true;
	} elseif ( 'db_optimize' === $_POST['bhg_action'] && isset( $_POST['bhg_db_optimize'] ) ) {
		check_admin_referer( 'bhg_db_optimize_action', 'bhg_nonce' );

		// Perform database optimization
		bhg_database_optimize();
		$optimize_completed = true;
	}
}

// Database cleanup function
function bhg_database_cleanup() {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'bhg_bonus_hunts',
		$wpdb->prefix . 'bhg_guesses',
		$wpdb->prefix . 'bhg_tournaments',
		$wpdb->prefix . 'bhg_tournament_results',
		$wpdb->prefix . 'bhg_translations',
		$wpdb->prefix . 'bhg_affiliate_websites',
		$wpdb->prefix . 'bhg_hunt_winners',
		$wpdb->prefix . 'bhg_ads',
	);

	foreach ( $tables as $table ) {
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$wpdb->query( "TRUNCATE TABLE {$table}" );
		}
	}

	// Reinsert default data if needed
	bhg_insert_demo_data();
}

// Database optimization function
function bhg_database_optimize() {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'bhg_bonus_hunts',
		$wpdb->prefix . 'bhg_guesses',
		$wpdb->prefix . 'bhg_tournaments',
		$wpdb->prefix . 'bhg_tournament_results',
		$wpdb->prefix . 'bhg_translations',
		$wpdb->prefix . 'bhg_affiliate_websites',
		$wpdb->prefix . 'bhg_hunt_winners',
		$wpdb->prefix . 'bhg_ads',
	);

	foreach ( $tables as $table ) {
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$wpdb->query( "OPTIMIZE TABLE {$table}" );
		}
	}
}

// Demo data insertion function (simplified version)
function bhg_insert_demo_data() {
	// This would typically be in a separate file like includes/demo.php
	global $wpdb;

	// Insert default bonus hunt
	$wpdb->insert(
		$wpdb->prefix . 'bhg_bonus_hunts',
		array(
			'title'             => 'Demo Bonus Hunt',
			'starting_balance'  => 2000,
			'number_of_bonuses' => 10,
			'status'            => 'active',
			'created_at'        => current_time( 'mysql' ),
		),
		array( '%s', '%d', '%d', '%s', '%s' )
	);
}
?>
<div class="wrap bhg-wrap">
	<h1><?php echo esc_html( bhg_t( 'database_tools', 'Database Tools' ) );; ?></h1>
	<p><?php echo esc_html( bhg_t( 'tables_are_automatically_created_on_activation_if_you_need_to_reinstall_them_deactivate_and_activate_the_plugin_again', 'Tables are automatically created on activation. If you need to reinstall them, deactivate and activate the plugin again.' ) );; ?></p>
	
	<?php if ( isset( $cleanup_completed ) && $cleanup_completed ) : ?>
		<div class="notice notice-success">
			<p><?php echo esc_html( bhg_t( 'database_cleanup_completed_successfully', 'Database cleanup completed successfully.' ) );; ?></p>
		</div>
	<?php endif; ?>
	
	<?php if ( isset( $optimize_completed ) && $optimize_completed ) : ?>
		<div class="notice notice-success">
			<p><?php echo esc_html( bhg_t( 'database_optimization_completed_successfully', 'Database optimization completed successfully.' ) );; ?></p>
		</div>
	<?php endif; ?>
	
	<form method="post" action="">
		<?php wp_nonce_field( 'bhg_db_cleanup_action', 'bhg_nonce' ); ?>
		<input type="hidden" name="bhg_action" value="db_cleanup">
		<p>
			<input type="submit" name="bhg_db_cleanup" class="button button-secondary" value="<?php echo esc_attr( bhg_t( 'run_database_cleanup', 'Run Database Cleanup' ) );; ?>"
					onclick="return confirm('<?php echo esc_js( bhg_t( 'are_you_sure_you_want_to_run_database_cleanup_this_action_cannot_be_undone', 'Are you sure you want to run database cleanup? This action cannot be undone.' ) ); ?>')">
		</p>
		<p class="description">
			<?php echo esc_html( bhg_t( 'note_this_will_remove_any_demo_data_and_reset_tables_to_their_initial_state', 'Note: This will remove any demo data and reset tables to their initial state.' ) );; ?>
		</p>
	</form>
	
	<h2><?php echo esc_html( bhg_t( 'current_database_status', 'Current Database Status' ) );; ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php echo esc_html( bhg_t( 'table_name', 'Table Name' ) );; ?></th>
				<th><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) );; ?></th>
				<th><?php echo esc_html( bhg_t( 'rows', 'Rows' ) );; ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			global $wpdb;
			$tables = array(
				'bhg_bonus_hunts',
				'bhg_guesses',
				'bhg_tournaments',
				'bhg_tournament_results',
				'bhg_translations',
				'bhg_affiliate_websites',
				'bhg_hunt_winners',
				'bhg_ads',
			);

			foreach ( $tables as $table ) {
				$table_name = $wpdb->prefix . $table;
				$exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;
				$row_count  = $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" ) : 0;

				echo '<tr>';
				echo '<td>' . esc_html( $table_name ) . '</td>';
				echo '<td><span class="' . ( $exists ? 'dashicons dashicons-yes-alt" style="color: #46b450"' : 'dashicons dashicons-no" style="color: #dc3232"' ) . '"></span> ' . ( $exists ? esc_html( bhg_t( 'exists', 'Exists' ) ) : esc_html( bhg_t( 'missing', 'Missing' ) ) ) . '</td>';
				echo '<td>' . esc_html( number_format_i18n( $row_count ) ) . '</td>';
				echo '</tr>';
			}
			?>
		</tbody>
	</table>
	
	<h2><?php echo esc_html( bhg_t( 'database_maintenance', 'Database Maintenance' ) );; ?></h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'bhg_db_optimize_action', 'bhg_nonce' ); ?>
		<input type="hidden" name="bhg_action" value="db_optimize">
		<p>
			<input type="submit" name="bhg_db_optimize" class="button button-primary" value="<?php echo esc_attr( bhg_t( 'optimize_database_tables', 'Optimize Database Tables' ) );; ?>">
		</p>
	</form>
</div>