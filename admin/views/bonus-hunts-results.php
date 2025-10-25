<?php
/**
 * Bonus hunt and tournament results view.
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

$view_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'hunt';
$view_type = ( 'tournament' === $view_type ) ? 'tournament' : 'hunt';
$item_id   = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$timeframe = isset( $_GET['timeframe'] ) ? sanitize_key( wp_unslash( $_GET['timeframe'] ) ) : 'month';
if ( 'all' === $timeframe ) {
        $timeframe = 'all_time';
}
if ( ! in_array( $timeframe, array( 'month', 'year', 'last_year', 'all_time' ), true ) ) {
        $timeframe = 'month';
}

$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$guess_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
$tour_table  = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tres_table  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
$users_table = esc_sql( $wpdb->users );

// Default to latest closed hunt if no ID is provided.
if ( 'hunt' === $view_type && ! $item_id ) {
        if ( 'year' === $timeframe ) {
                $start = gmdate( 'Y-01-01 00:00:00' );
                $hunt  = $wpdb->get_row(
                        $wpdb->prepare(
                                "SELECT * FROM {$hunts_table} WHERE status=%s AND closed_at >= %s ORDER BY closed_at DESC LIMIT 1",
                                'closed',
                                $start
                        )
                );
        } elseif ( 'last_year' === $timeframe ) {
                $start = gmdate( 'Y-01-01 00:00:00', strtotime( '-1 year' ) );
                $end   = gmdate( 'Y-01-01 00:00:00' );
                $hunt  = $wpdb->get_row(
                        $wpdb->prepare(
                                "SELECT * FROM {$hunts_table} WHERE status=%s AND closed_at >= %s AND closed_at < %s ORDER BY closed_at DESC LIMIT 1",
                                'closed',
                                $start,
                                $end
                        )
                );
        } elseif ( 'month' === $timeframe ) {
                $start = gmdate( 'Y-m-01 00:00:00' );
                $hunt  = $wpdb->get_row(
                        $wpdb->prepare(
                                "SELECT * FROM {$hunts_table} WHERE status=%s AND closed_at >= %s ORDER BY closed_at DESC LIMIT 1",
                                'closed',
                                $start
                        )
                );
        } else {
                $hunt = $wpdb->get_row(
                        $wpdb->prepare(
                                "SELECT * FROM {$hunts_table} WHERE status=%s ORDER BY closed_at DESC LIMIT 1",
                                'closed'
                        )
                );
        }
        if ( $hunt ) {
                $item_id = (int) $hunt->id;
        }
} elseif ( 'hunt' === $view_type ) {
        $hunt = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$hunts_table} WHERE id=%d", $item_id )
        );
} elseif ( 'tournament' === $view_type && $item_id ) {
        $tournament = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$tour_table} WHERE id=%d", $item_id )
        );
}

if ( 'tournament' === $view_type ) {
	if ( empty( $tournament ) ) {
                    echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'tournament_not_found', 'Tournament not found' ) ) . '</h1></div>';
			return;
	}
        $rows         = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT r.wins, u.display_name FROM {$tres_table} r JOIN {$users_table} u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY r.wins DESC, r.id ASC",
                        $item_id
                )
        );
	$result_title = $tournament->title;
	$wcount       = 3;
	$columns      = array(
		'sc_position' => bhg_t( 'sc_position', 'Position' ),
		'sc_user'     => bhg_t( 'sc_user', 'User' ),
		'wins'        => bhg_t( 'wins', 'Wins' ),
	);
} else {
	if ( empty( $hunt ) ) {
                    echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'hunt_not_found', 'Hunt not found' ) ) . '</h1></div>';
			return;
	}
        $has_final_balance = isset( $hunt->final_balance ) && '' !== $hunt->final_balance && null !== $hunt->final_balance;
        if ( $has_final_balance ) {
                $rows = $wpdb->get_results(
                        $wpdb->prepare(
                                "SELECT g.guess, u.display_name, (%f - g.guess) AS diff FROM {$guess_table} g JOIN {$users_table} u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY ABS(%f - g.guess) ASC, g.id ASC",
                                (float) $hunt->final_balance,
                                $item_id,
                                (float) $hunt->final_balance
                        )
                );
        } else {
                $rows = array();
        }
        $result_title = $hunt->title;
        $wcount       = (int) $hunt->winners_count;
	if ( $wcount < 1 ) {
			$wcount = 3;
	}
	$prize_lookup = array();
	if ( class_exists( 'BHG_Prizes' ) ) {
		$prize_rows = BHG_Prizes::get_prizes_for_hunt( $item_id );
		if ( ! empty( $prize_rows ) ) {
			$position = 1;
			foreach ( $prize_rows as $prize_row ) {
				$title = isset( $prize_row->title ) ? trim( (string) $prize_row->title ) : '';
				if ( '' !== $title ) {
					$prize_lookup[ $position ] = $title;
				}
				++$position;
			}
		}
	}
	$columns = array(
		'sc_position' => bhg_t( 'sc_position', 'Position' ),
		'sc_user'     => bhg_t( 'sc_user', 'User' ),
		'sc_guess'    => bhg_t( 'sc_guess', 'Guess' ),
		'difference'  => bhg_t( 'difference', 'Difference' ),
		'price'       => bhg_t( 'label_price', 'Price' ),
	);
}

// Gather hunts and tournaments for the selector.
if ( 'year' === $timeframe ) {
        $start     = gmdate( 'Y-01-01 00:00:00' );
        $all_hunts = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT id, title FROM {$hunts_table} WHERE closed_at >= %s ORDER BY closed_at DESC, id DESC",
                        $start
                )
        );
} elseif ( 'last_year' === $timeframe ) {
        $start     = gmdate( 'Y-01-01 00:00:00', strtotime( '-1 year' ) );
        $end       = gmdate( 'Y-01-01 00:00:00' );
        $all_hunts = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT id, title FROM {$hunts_table} WHERE closed_at >= %s AND closed_at < %s ORDER BY closed_at DESC, id DESC",
                        $start,
                        $end
                )
        );
} elseif ( 'month' === $timeframe ) {
        $start     = gmdate( 'Y-m-01 00:00:00' );
        $all_hunts = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT id, title FROM {$hunts_table} WHERE closed_at >= %s ORDER BY closed_at DESC, id DESC",
                        $start
                )
        );
} else {
        $all_hunts = $wpdb->get_results(
                "SELECT id, title FROM {$hunts_table} ORDER BY closed_at DESC, id DESC"
        );
}
$all_tours = $wpdb->get_results(
        "SELECT id, title FROM {$tour_table} ORDER BY id DESC"
);
$current   = $view_type . '-' . $item_id;
?>
<div class="wrap bhg-wrap">
<h1><?php echo esc_html( sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $result_title ) ); ?></h1>
        <div style="margin:1em 0;">
                        <select id="bhg-results-select">
                        <?php foreach ( (array) $all_hunts as $h ) : ?>
                                                        <?php $val = 'hunt-' . (int) $h->id; ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $h->title ); ?></option>
                        <?php endforeach; ?>
                        <?php foreach ( (array) $all_tours as $t ) : ?>
                                                        <?php $val = 'tournament-' . (int) $t->id; ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $t->title ); ?></option>
                        <?php endforeach; ?>
                        </select>
                        <select id="bhg-results-timeframe">
                                <option value="month" <?php selected( $timeframe, 'month' ); ?>><?php echo esc_html( bhg_t( 'this_month', 'This Month' ) ); ?></option>
                                <option value="year" <?php selected( $timeframe, 'year' ); ?>><?php echo esc_html( bhg_t( 'this_year', 'This Year' ) ); ?></option>
                                <option value="last_year" <?php selected( $timeframe, 'last_year' ); ?>><?php echo esc_html( bhg_t( 'label_last_year', 'Last Year' ) ); ?></option>
                                <option value="all_time" <?php selected( $timeframe, 'all_time' ); ?>><?php echo esc_html( bhg_t( 'all_time', 'All Time' ) ); ?></option>
                        </select>
        </div>
        <?php if ( empty( $rows ) ) : ?>
                <div class="notice notice-info">
                        <p><?php echo esc_html( bhg_t( 'no_winners_yet', 'There are no winners yet' ) ); ?></p>
                </div>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<?php foreach ( $columns as $label ) : ?>
						<th><?php echo esc_html( $label ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
			<?php
			$pos = 1;
			foreach ( (array) $rows as $r ) :
				$is_winner = $pos <= $wcount;
				?>
				<tr<?php echo $is_winner ? ' class="bhg-winner-row"' : ''; ?>>
					<td><?php echo (int) $pos; ?></td>
					<td><?php echo esc_html( $r->display_name ); ?></td>
					<?php if ( 'tournament' === $view_type ) : ?>
						<td><?php echo (int) $r->wins; ?></td>
					<?php else : ?>
						<td><?php echo esc_html( bhg_format_currency( (float) $r->guess ) ); ?></td>
                                                <td><?php echo esc_html( bhg_format_currency( abs( (float) $r->diff ) ) ); ?></td>
					<?php if ( 'hunt' === $view_type ) : ?>
						<td><?php echo isset( $prize_lookup[ $pos ] ) ? esc_html( $prize_lookup[ $pos ] ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
					<?php endif; ?>
					<?php endif; ?>
				</tr>
				<?php
				++$pos;
			endforeach;
				?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

