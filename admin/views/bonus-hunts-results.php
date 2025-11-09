<?php
/**
 * Bonus hunt and tournament results page.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'insufficient_permissions', 'Insufficient permissions' ) ) );
}

global $wpdb;

if ( ! class_exists( 'BHG_Bonus_Hunts' ) ) {
	require_once BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts.php';
}

if ( ! class_exists( 'BHG_Prizes' ) && file_exists( BHG_PLUGIN_DIR . 'includes/class-bhg-prizes.php' ) ) {
	require_once BHG_PLUGIN_DIR . 'includes/class-bhg-prizes.php';
}

// Controls: timeframe & selection.
$timeframe        = isset( $_GET['timeframe'] ) ? sanitize_key( wp_unslash( $_GET['timeframe'] ) ) : 'month';
$valid_timeframes = array( 'month', 'year', 'all' );
if ( ! in_array( $timeframe, $valid_timeframes, true ) ) {
	$timeframe = 'month';
}

$selector_limit       = (int) apply_filters( 'bhg_results_selector_limit', 50 );
$hunts_selector       = BHG_Bonus_Hunts::get_closed_hunts_for_selector( $timeframe, $selector_limit );
$tournaments_selector = BHG_Bonus_Hunts::get_tournaments_for_selector( $timeframe, $selector_limit );

// Build quick lookup maps for select controls.
$hunts_map = array();
foreach ( (array) $hunts_selector as $hunt_row ) {
	$hunts_map[ (int) $hunt_row->id ] = $hunt_row;
}

$tournaments_map = array();
foreach ( (array) $tournaments_selector as $tournament_row ) {
	$tournaments_map[ (int) $tournament_row->id ] = $tournament_row;
}

// View selection.
$requested_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'hunt';
$requested_id   = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$view_type      = in_array( $requested_type, array( 'hunt', 'tournament' ), true ) ? $requested_type : 'hunt';

$hunt_id       = ( 'tournament' === $view_type ) ? 0 : $requested_id;
$tournament_id = ( 'tournament' === $view_type ) ? $requested_id : 0;

// Fallback gracefully if the requested item isn't available in the current timeframe.
if ( 'tournament' === $view_type ) {
	if ( ! $tournament_id || ! isset( $tournaments_map[ $tournament_id ] ) ) {
		$tournament_id = $tournaments_map ? (int) array_key_first( $tournaments_map ) : 0;
		if ( ! $tournament_id && $hunts_map ) {
			$view_type = 'hunt';
			$hunt_id   = (int) array_key_first( $hunts_map );
		}
	}
} elseif ( ! $hunt_id || ! isset( $hunts_map[ $hunt_id ] ) ) {
		$hunt_id = $hunts_map ? (int) array_key_first( $hunts_map ) : 0;
	if ( ! $hunt_id && $tournaments_map ) {
		$view_type     = 'tournament';
		$tournament_id = (int) array_key_first( $tournaments_map );
	}
}

// Derive the currently selected value for the control.
$selected_control_value = '';
if ( 'tournament' === $view_type && $tournament_id ) {
	$selected_control_value = 'tournament-' . (int) $tournament_id;
} elseif ( 'hunt' === $view_type && $hunt_id ) {
	$selected_control_value = 'hunt-' . (int) $hunt_id;
}

// Labels & defaults.
$timeframe_labels = array(
	'month' => bhg_t( 'option_timeframe_this_month', 'This Month' ),
	'year'  => bhg_t( 'option_timeframe_this_year', 'This Year' ),
	'all'   => bhg_t( 'option_timeframe_all_time', 'All Time' ),
);

$notices               = array();
$page_title            = bhg_t( 'title_results', 'Results' );
$tournament            = null;
$results               = array();
$total_participants    = 0;
$hunt                  = null;
$guesses               = array();
$winner_lookup         = array();
$winners_limit         = 0;
$final_balance_display = '';
$winners_display       = '';
$participants_display  = '';
$has_final_balance     = false;

// Prize titles will be collected by set, used when mapping per affiliate status.
$prize_titles = array(
	'regular' => array(),
	'premium' => array(),
);

// No data at all for current timeframe?
if ( empty( $hunts_map ) && empty( $tournaments_map ) ) {
	$notices[] = array(
		'type'    => 'info',
		'message' => bhg_t( 'notice_no_closed_hunts_timeframe', 'No closed hunts found for this timeframe.' ),
	);
}

// Load data for selected view.
if ( 'tournament' === $view_type && $tournament_id ) {
	$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
	$results_table     = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
	$users_table       = esc_sql( $wpdb->users );

	$tournament = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT * FROM {$tournaments_table} WHERE id = %d",
			$tournament_id
		)
	);

	if ( ! $tournament ) {
		$notices[] = array(
			'type'    => 'error',
			'message' => bhg_t( 'tournament_not_found', 'Tournament not found' ),
		);
	} else {
		$page_title = sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $tournament->title );
		$results    = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT r.*, u.display_name
				 FROM {$results_table} r
				 LEFT JOIN {$users_table} u ON u.ID = r.user_id
				 WHERE r.tournament_id = %d
				 ORDER BY r.points DESC, r.wins DESC, r.last_win_date ASC, r.id ASC",
				$tournament_id
			)
		);

		$total_participants = count( $results );
		$winners_limit      = apply_filters( 'bhg_tournament_winners_limit', 3, $tournament );
		$winners_limit      = max( 1, (int) $winners_limit );
	}
} elseif ( 'hunt' === $view_type && $hunt_id ) {
	$hunt = BHG_Bonus_Hunts::get_hunt( $hunt_id );

	if ( ! $hunt ) {
		$notices[] = array(
			'type'    => 'error',
			'message' => bhg_t( 'invalid_hunt', 'Invalid hunt' ),
		);
	} else {
		$hunt_site_id          = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
		$page_title            = sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $hunt->title );
		$guesses               = BHG_Bonus_Hunts::get_hunt_guesses_ranked( (int) $hunt->id );
		$official_winner_ids   = method_exists( 'BHG_Bonus_Hunts', 'get_hunt_winner_ids' ) ? BHG_Bonus_Hunts::get_hunt_winner_ids( (int) $hunt->id ) : array();
		$ineligible_winner_ids = method_exists( 'BHG_Bonus_Hunts', 'get_ineligible_winner_ids' ) ? BHG_Bonus_Hunts::get_ineligible_winner_ids( (int) $hunt->id ) : array();

		foreach ( $official_winner_ids as $index => $winner_user_id ) {
			$winner_lookup[ (int) $winner_user_id ] = (int) $index;
		}

		$winners_limit         = max( 1, (int) $hunt->winners_count );
		$final_balance_raw     = isset( $hunt->final_balance ) ? $hunt->final_balance : null;
		$has_final_balance     = null !== $final_balance_raw;
		$final_balance_value   = $has_final_balance ? (float) $final_balance_raw : null;
		$total_participants    = count( $guesses );
		$actual_winner_count   = count( $official_winner_ids );
		$final_balance_display = $has_final_balance ? bhg_format_money( $final_balance_value ) : bhg_t( 'label_emdash', '—' );
		$winners_display       = number_format_i18n( $actual_winner_count ? $actual_winner_count : $winners_limit );
		$participants_display  = number_format_i18n( $total_participants );

		// If win-limit rules exclude some winners, show a friendly notice.
		if ( ! empty( $ineligible_winner_ids ) && function_exists( 'bhg_get_win_limit_config' ) && function_exists( 'bhg_build_win_limit_notice' ) ) {
			$limit_config = bhg_get_win_limit_config( 'hunt' );
			$limit_count  = isset( $limit_config['count'] ) ? (int) $limit_config['count'] : 0;
			$limit_period = isset( $limit_config['period'] ) ? $limit_config['period'] : 'none';
			$notice_text  = bhg_build_win_limit_notice( 'hunt', $limit_count, $limit_period, count( $ineligible_winner_ids ) );

			if ( '' !== $notice_text ) {
				$notices[] = array(
					'type'    => 'info',
					'message' => $notice_text,
				);
			}
		}

		// Load prize titles, grouped by set (regular/premium) for mapping based on affiliate status.
		if ( class_exists( 'BHG_Prizes' ) ) {
			$prize_sets = BHG_Prizes::get_prizes_for_hunt( (int) $hunt->id, array( 'grouped' => true ) );
			foreach ( array( 'regular', 'premium' ) as $set_key ) {
				if ( empty( $prize_sets[ $set_key ] ) || ! is_array( $prize_sets[ $set_key ] ) ) {
					continue;
				}
				foreach ( $prize_sets[ $set_key ] as $prize_row ) {
					if ( isset( $prize_row->title ) && '' !== $prize_row->title ) {
						$prize_titles[ $set_key ][] = (string) $prize_row->title;
					}
				}
			}
		}
	}
}

$timeframe_label = isset( $timeframe_labels[ $timeframe ] ) ? $timeframe_labels[ $timeframe ] : $timeframe_labels['month'];
?>
<div class="wrap bhg-wrap bhg-results-page">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<div class="bhg-results-controls">
		<label for="bhg-results-select" class="bhg-results-control">
			<span class="screen-reader-text"><?php echo esc_html( bhg_t( 'label_show_results_for', 'Show results for' ) ); ?></span>
			<select id="bhg-results-select" class="bhg-results-select">
<?php if ( $hunts_map ) : ?>
				<optgroup label="<?php echo esc_attr( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ); ?>">
	<?php
	foreach ( $hunts_map as $hunt_row ) :
		$value    = 'hunt-' . (int) $hunt_row->id;
		$selected = selected( $selected_control_value, $value, false );
		$label    = isset( $hunt_row->title ) ? (string) $hunt_row->title : '';
		$closed   = '';
		if ( ! empty( $hunt_row->closed_at ) ) {
			$timestamp = strtotime( $hunt_row->closed_at );
			if ( $timestamp ) {
				$closed = wp_date( get_option( 'date_format' ), $timestamp );
			}
		}
		if ( $closed ) {
			$label .= sprintf( ' (%s)', $closed );
		}
		?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php echo $selected; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> ><?php echo esc_html( $label ); ?></option>
	<?php endforeach; ?>
				</optgroup>
<?php endif; ?>
<?php if ( $tournaments_map ) : ?>
				<optgroup label="<?php echo esc_attr( bhg_t( 'tournaments', 'Tournaments' ) ); ?>">
	<?php
	foreach ( $tournaments_map as $tournament_row ) :
		$value     = 'tournament-' . (int) $tournament_row->id;
		$selected  = selected( $selected_control_value, $value, false );
		$label     = isset( $tournament_row->title ) ? (string) $tournament_row->title : '';
		$end_date  = '';
		$reference = ! empty( $tournament_row->end_date ) ? $tournament_row->end_date : ( ! empty( $tournament_row->start_date ) ? $tournament_row->start_date : '' );
		if ( $reference ) {
			$timestamp = strtotime( $reference );
			if ( $timestamp ) {
				$end_date = wp_date( get_option( 'date_format' ), $timestamp );
			}
		}
		if ( $end_date ) {
			$label .= sprintf( ' (%s)', $end_date );
		}
		?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php echo $selected; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> ><?php echo esc_html( $label ); ?></option>
	<?php endforeach; ?>
				</optgroup>
<?php endif; ?>
<?php if ( '' === $selected_control_value ) : ?>
				<option value="" selected="selected"><?php echo esc_html( bhg_t( 'label_select_result', 'Select an item' ) ); ?></option>
<?php endif; ?>
			</select>
		</label>

		<label for="bhg-results-timeframe" class="bhg-results-control">
			<span class="screen-reader-text"><?php echo esc_html( bhg_t( 'label_timeframe', 'Timeframe' ) ); ?></span>
			<select id="bhg-results-timeframe" class="bhg-results-select">
<?php foreach ( $timeframe_labels as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $timeframe, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
			</select>
		</label>
		<div class="bhg-results-timeframe-label"><?php echo esc_html( $timeframe_label ); ?></div>
	</div>

<?php
foreach ( $notices as $notice ) :
	$type    = isset( $notice['type'] ) ? $notice['type'] : 'info';
	$message = isset( $notice['message'] ) ? $notice['message'] : '';
	if ( '' === $message ) {
		continue;
	}
	?>
	<div class="notice notice-<?php echo esc_attr( $type ); ?>">
		<p><?php echo esc_html( $message ); ?></p>
	</div>
<?php endforeach; ?>

<?php if ( 'tournament' === $view_type && $tournament ) : ?>
	<div class="bhg-results-summary">
		<div class="bhg-summary-card">
			<span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'participants', 'Participants' ) ); ?></span>
			<span class="bhg-summary-value"><?php echo esc_html( number_format_i18n( $total_participants ) ); ?></span>
		</div>
		<div class="bhg-summary-card">
			<span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'top_winners', 'Top Winners' ) ); ?></span>
			<span class="bhg-summary-value"><?php echo esc_html( number_format_i18n( $winners_limit ) ); ?></span>
		</div>
	</div>

	<table class="widefat striped bhg-results-table">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html( bhg_t( 'sc_position', 'Position' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'sc_user', 'User' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'label_points', 'Points' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'wins', 'Wins' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php if ( empty( $results ) ) : ?>
			<tr>
				<td colspan="4" class="bhg-text-center"><?php echo esc_html( bhg_t( 'no_results_yet', 'No results yet.' ) ); ?></td>
			</tr>
	<?php else : ?>
		<?php foreach ( $results as $index => $row ) : ?>
			<?php
			$position    = (int) $index + 1;
			$is_winner   = ( $position <= $winners_limit );
			$row_classes = array( 'bhg-results-row' );
			if ( $is_winner ) {
				$row_classes[] = 'bhg-results-row--winner';
			}
			$name = $row->display_name ? $row->display_name : sprintf(
				/* translators: %d: user ID. */
				bhg_t( 'label_user_hash', 'user#%d' ),
				(int) $row->user_id
			);
			?>
			<tr class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
				<td><span class="bhg-badge <?php echo esc_attr( $is_winner ? 'bhg-badge-primary' : 'bhg-badge-muted' ); ?>"><?php echo esc_html( $position ); ?></span></td>
				<td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $row->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
				<td><?php echo esc_html( number_format_i18n( (int) $row->points ) ); ?></td>
				<td><?php echo esc_html( number_format_i18n( (int) $row->wins ) ); ?></td>
			</tr>
	<?php endforeach; ?>
	<?php endif; ?>
		</tbody>
	</table>
<?php elseif ( 'hunt' === $view_type && $hunt ) : ?>
	<div class="bhg-results-summary">
		<div class="bhg-summary-card">
			<span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></span>
			<span class="bhg-summary-value"><?php echo esc_html( $final_balance_display ); ?></span>
		</div>
		<div class="bhg-summary-card">
			<span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></span>
			<span class="bhg-summary-value"><?php echo esc_html( $winners_display ); ?></span>
		</div>
		<div class="bhg-summary-card">
			<span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'participants', 'Participants' ) ); ?></span>
			<span class="bhg-summary-value"><?php echo esc_html( $participants_display ); ?></span>
		</div>
	</div>

	<?php if ( ! $has_final_balance ) : ?>
	<div class="notice notice-warning is-dismissible">
		<p><?php echo esc_html( bhg_t( 'final_balance_not_set', 'Final balance has not been recorded yet. Rankings are shown by submission time until the hunt is closed.' ) ); ?></p>
	</div>
	<?php endif; ?>

	<table class="widefat striped bhg-results-table">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html( bhg_t( 'sc_position', 'Position' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'sc_user', 'User' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'sc_guess', 'Guess' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'difference', 'Difference' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'date', 'Date' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'label_price', 'Price' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php if ( empty( $guesses ) ) : ?>
			<tr>
				<td colspan="6" class="bhg-text-center"><?php echo esc_html( bhg_t( 'notice_no_winners_yet', 'There are no winners yet' ) ); ?></td>
			</tr>
	<?php else : ?>
		<?php foreach ( $guesses as $index => $row ) : ?>
			<?php
			$position    = (int) $index + 1;
			$user_id     = isset( $row->user_id ) ? (int) $row->user_id : 0;
			$is_winner   = ( $user_id > 0 && isset( $winner_lookup[ $user_id ] ) );
			$row_classes = array( 'bhg-results-row' );
			if ( $is_winner ) {
				$row_classes[] = 'bhg-results-row--winner';
			}

			$name          = $row->display_name ? $row->display_name : sprintf( /* translators: %d: user ID. */ bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
			$guess_display = isset( $row->guess ) ? bhg_format_money( (float) $row->guess ) : bhg_t( 'label_emdash', '—' );
			$diff_value    = ( isset( $row->diff ) && null !== $row->diff ) ? bhg_format_money( abs( (float) $row->diff ) ) : bhg_t( 'label_emdash', '—' );
			$created_at    = isset( $row->created_at ) ? $row->created_at : null;
			$submitted_on  = $created_at ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $created_at ) : bhg_t( 'label_emdash', '—' );
			$prize_title   = '';

			if ( $is_winner ) {
				$prize_index         = isset( $winner_lookup[ $user_id ] ) ? (int) $winner_lookup[ $user_id ] : 0;
				$is_affiliate_winner = false;

				if ( $hunt_site_id > 0 && function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
					$is_affiliate_winner = bhg_is_user_affiliate_for_site( $user_id, $hunt_site_id );
				} elseif ( function_exists( 'bhg_is_user_affiliate' ) ) {
					$is_affiliate_winner = bhg_is_user_affiliate( $user_id );
				} else {
					$is_affiliate_winner = (bool) get_user_meta( $user_id, 'bhg_is_affiliate', true );
				}

				if ( $is_affiliate_winner && isset( $prize_titles['premium'][ $prize_index ] ) ) {
					$prize_title = $prize_titles['premium'][ $prize_index ];
				} elseif ( isset( $prize_titles['regular'][ $prize_index ] ) ) {
					$prize_title = $prize_titles['regular'][ $prize_index ];
				} elseif ( $is_affiliate_winner && isset( $prize_titles['premium'][0] ) ) {
					$prize_title = $prize_titles['premium'][0];
				}
			}
			?>
			<tr class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
				<td><span class="bhg-badge <?php echo esc_attr( $is_winner ? 'bhg-badge-primary' : 'bhg-badge-muted' ); ?>"><?php echo esc_html( $position ); ?></span></td>
				<td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $row->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
				<td><?php echo esc_html( $guess_display ); ?></td>
				<td><?php echo esc_html( $diff_value ); ?></td>
				<td><?php echo esc_html( $submitted_on ); ?></td>
				<td><?php echo ( '' !== $prize_title ) ? esc_html( $prize_title ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
			</tr>
	<?php endforeach; ?>
	<?php endif; ?>
		</tbody>
	</table>
<?php endif; ?>
</div>
