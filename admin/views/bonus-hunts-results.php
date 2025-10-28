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
$timeframe = isset( $_GET['timeframe'] ) ? sanitize_key( wp_unslash( $_GET['timeframe'] ) ) : 'all_time';
$aliases   = array(
        'all'        => 'all_time',
        'this_month' => 'month',
        'monthly'    => 'month',
        'this_year'  => 'year',
        'yearly'     => 'year',
        'alltime'    => 'all_time',
);

if ( isset( $aliases[ $timeframe ] ) ) {
        $timeframe = $aliases[ $timeframe ];
}

$allowed_timeframes = array( 'month', 'year', 'all_time' );
if ( ! in_array( $timeframe, $allowed_timeframes, true ) ) {
        $timeframe = 'all_time';
}

$hunts_table        = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$guess_table        = esc_sql( $wpdb->prefix . 'bhg_guesses' );
$tour_table         = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tres_table         = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
$users_table        = esc_sql( $wpdb->users );
$timeframe_labels   = array(
        'month'    => bhg_t( 'this_month', 'This Month' ),
        'year'     => bhg_t( 'this_year', 'This Year' ),
        'all_time' => bhg_t( 'all_time', 'All Time' ),
);

// Default to the latest closed hunt when no explicit selection is provided.
$hunt       = null;
$tournament = null;

if ( 'hunt' === $view_type ) {
        if ( $item_id ) {
                $hunt = $wpdb->get_row(
                        $wpdb->prepare( "SELECT * FROM {$hunts_table} WHERE id=%d", $item_id )
                );
        }

        if ( ! $hunt ) {
                $hunt = $wpdb->get_row(
                        $wpdb->prepare(
                                "SELECT * FROM {$hunts_table} WHERE status = %s AND closed_at IS NOT NULL ORDER BY closed_at DESC, id DESC LIMIT 1",
                                'closed'
                        )
                );

                if ( $hunt ) {
                        $item_id = (int) $hunt->id;
                }
        }
} elseif ( 'tournament' === $view_type && $item_id ) {
        $tournament = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$tour_table} WHERE id=%d", $item_id )
        );
}

$rows              = array();
$result_title      = '';
$wcount            = 0;
$columns           = array();
$no_hunt_selected  = false;
$has_final_balance = false;
$prize_titles      = array();

if ( 'tournament' === $view_type ) {
	if ( empty( $tournament ) ) {
                    echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'tournament_not_found', 'Tournament not found' ) ) . '</h1></div>';
			return;
	}
        $rows         = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT r.points, r.wins, u.display_name FROM {$tres_table} r JOIN {$users_table} u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY r.points DESC, r.wins DESC, r.last_win_date ASC, r.id ASC",
                        $item_id
                )
        );
        $result_title = $tournament->title;
        $wcount       = 3;
        $columns      = array(
                'sc_position' => bhg_t( 'sc_position', 'Position' ),
                'sc_user'     => bhg_t( 'sc_user', 'User' ),
                'label_points' => bhg_t( 'label_points', 'Points' ),
                'wins'        => bhg_t( 'wins', 'Wins' ),
        );
} else {
        $columns = array(
                'sc_position' => bhg_t( 'sc_position', 'Position' ),
                'sc_user'     => bhg_t( 'sc_user', 'User' ),
                'sc_guess'    => bhg_t( 'sc_guess', 'Guess' ),
                'difference'  => bhg_t( 'difference', 'Difference' ),
                'price'       => bhg_t( 'label_price', 'Price' ),
        );

        if ( empty( $hunt ) ) {
                $no_hunt_selected = true;
                $result_title     = bhg_t( 'bonus_hunt', 'Bonus Hunt' );
                $rows             = array();
        } else {
                $result_title     = $hunt->title;
                $wcount           = (int) $hunt->winners_count;
                if ( $wcount < 1 ) {
                        $wcount = 3;
                }

                $has_final_balance = isset( $hunt->final_balance ) && '' !== $hunt->final_balance && null !== $hunt->final_balance;
                if ( $has_final_balance ) {
                        $rows = $wpdb->get_results(
                                $wpdb->prepare(
                                        "SELECT g.guess, u.display_name, ABS(%f - g.guess) AS diff FROM {$guess_table} g JOIN {$users_table} u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY diff ASC, g.id ASC",
                                        (float) $hunt->final_balance,
                                        $item_id,
                                        (float) $hunt->final_balance
                                )
                        );

                        if ( class_exists( 'BHG_Prizes' ) ) {
                                $hunt_prizes = BHG_Prizes::get_prizes_for_hunt( $item_id );
                                foreach ( (array) $hunt_prizes as $prize_row ) {
                                        if ( isset( $prize_row->title ) && '' !== $prize_row->title ) {
                                                $prize_titles[] = (string) $prize_row->title;
                                        }
                                }
                        }
                }
        }
}

// Gather hunts and tournaments for the selector.
$hunts_sql    = "SELECT id, title FROM {$hunts_table} WHERE status = %s AND closed_at IS NOT NULL";
$hunts_params = array( 'closed' );

switch ( $timeframe ) {
        case 'month':
                $hunts_sql    .= ' AND closed_at >= %s';
                $hunts_params[] = gmdate( 'Y-m-01 00:00:00' );
                break;
        case 'year':
                $hunts_sql    .= ' AND closed_at >= %s';
                $hunts_params[] = gmdate( 'Y-01-01 00:00:00' );
                break;
}

$hunts_sql .= ' ORDER BY closed_at DESC, id DESC';

$all_hunts = $wpdb->get_results(
        $wpdb->prepare(
                $hunts_sql,
                ...$hunts_params
        )
);

$all_tours = $wpdb->get_results(
        "SELECT id, title FROM {$tour_table} ORDER BY id DESC"
);

if ( 'hunt' === $view_type && $hunt ) {
        $selected_in_list = false;
        foreach ( (array) $all_hunts as $candidate ) {
                if ( (int) $candidate->id === (int) $item_id ) {
                        $selected_in_list = true;
                        break;
                }
        }

        if ( ! $selected_in_list ) {
                array_unshift(
                        $all_hunts,
                        (object) array(
                                'id'    => (int) $hunt->id,
                                'title' => $hunt->title,
                        )
                );
        }
}

$current            = $view_type . '-' . (int) $item_id;
$no_hunts_in_scope  = ( 'hunt' === $view_type && empty( $all_hunts ) );
$has_rows           = ! empty( $rows );
?>
<div class="wrap bhg-wrap bhg-results-page">
        <h1><?php echo esc_html( sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $result_title ) ); ?></h1>

        <div class="bhg-results-toolbar">
                <div class="bhg-results-field">
                        <label for="bhg-results-select"><?php echo esc_html( bhg_t( 'label_select_hunt', 'Select Hunt' ) ); ?></label>
                        <select id="bhg-results-select">
                                <?php if ( ! empty( $all_hunts ) ) : ?>
                                        <optgroup label="<?php echo esc_attr( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ); ?>">
                                                <?php foreach ( (array) $all_hunts as $h ) : ?>
                                                        <?php $val = 'hunt-' . (int) $h->id; ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $h->title ); ?></option>
                                                <?php endforeach; ?>
                                        </optgroup>
                                <?php else : ?>
                                        <option value="" disabled><?php echo esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ); ?></option>
                                <?php endif; ?>
                                <?php if ( ! empty( $all_tours ) ) : ?>
                                        <optgroup label="<?php echo esc_attr( bhg_t( 'menu_tournaments', 'Tournaments' ) ); ?>">
                                                <?php foreach ( (array) $all_tours as $t ) : ?>
                                                        <?php $val = 'tournament-' . (int) $t->id; ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $t->title ); ?></option>
                                                <?php endforeach; ?>
                                        </optgroup>
                                <?php endif; ?>
                        </select>
                </div>
                <div class="bhg-results-field">
                        <label for="bhg-results-timeframe"><?php echo esc_html( bhg_t( 'label_results_scope', 'Hunt Scope' ) ); ?></label>
                        <select id="bhg-results-timeframe">
                                <?php foreach ( $timeframe_labels as $key => $label ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $timeframe, $key ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                        </select>
                </div>
        </div>

        <?php if ( 'hunt' === $view_type && $no_hunts_in_scope ) : ?>
                <div class="notice notice-info"><p><?php echo esc_html( bhg_t( 'notice_no_closed_hunts_timeframe', 'No closed hunts found for this timeframe.' ) ); ?></p></div>
        <?php endif; ?>

        <?php if ( 'hunt' === $view_type && $hunt ) : ?>
                <div class="bhg-results-summary">
                        <div class="bhg-summary-card">
                                <span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ); ?></span>
                                <span class="bhg-summary-value"><?php echo esc_html( bhg_format_currency( (float) $hunt->starting_balance ) ); ?></span>
                        </div>
                        <div class="bhg-summary-card">
                                <span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></span>
                                <span class="bhg-summary-value">
                                        <?php
                                        if ( $has_final_balance ) {
                                                echo esc_html( bhg_format_currency( (float) $hunt->final_balance ) );
                                        } else {
                                                echo esc_html( bhg_t( 'notice_results_pending', 'Results pending.' ) );
                                        }
                                        ?>
                                </span>
                        </div>
                        <div class="bhg-summary-card">
                                <span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'label_winners', 'Winners' ) ); ?></span>
                                <span class="bhg-summary-value"><?php echo esc_html( number_format_i18n( max( 1, (int) $hunt->winners_count ) ) ); ?></span>
                        </div>
                        <?php if ( ! empty( $hunt->closed_at ) ) : ?>
                                <div class="bhg-summary-card">
                                        <span class="bhg-summary-label"><?php echo esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ); ?></span>
                                        <span class="bhg-summary-value"><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $hunt->closed_at, true ) ); ?></span>
                                </div>
                        <?php endif; ?>
                </div>
        <?php endif; ?>

        <?php if ( ! $has_rows ) : ?>
                <div class="notice notice-info">
                        <p>
                        <?php
                        if ( $no_hunt_selected ) {
                                echo esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) );
                        } elseif ( $has_final_balance ) {
                                echo esc_html( bhg_t( 'notice_no_results_yet', 'No results yet.' ) );
                        } else {
                                echo esc_html( bhg_t( 'notice_results_pending', 'Results pending.' ) );
                        }
                        ?>
                        </p>
                </div>
        <?php else : ?>
                <table class="widefat fixed striped bhg-results-table">
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
                                        $is_winner   = $pos <= $wcount;
                                        $row_classes = $is_winner ? 'bhg-results-row bhg-results-row--winner' : 'bhg-results-row';
                                        $prize_index = $pos - 1;
                                        $prize_title = isset( $prize_titles[ $prize_index ] ) ? (string) $prize_titles[ $prize_index ] : '';
                                        ?>
                                        <tr class="<?php echo esc_attr( $row_classes ); ?>">
                                                <td>
                                                        <span class="bhg-badge <?php echo $is_winner ? 'bhg-badge-primary' : 'bhg-badge-muted'; ?>"><?php echo (int) $pos; ?></span>
                                                        <?php if ( 'hunt' === $view_type && $is_winner ) : ?>
                                                                <span class="bhg-badge-label"><?php echo esc_html( sprintf( bhg_t( 'winner_position', 'Winner #%d' ), $pos ) ); ?></span>
                                                        <?php endif; ?>
                                                </td>
                                                <td><span class="bhg-result-name"><?php echo esc_html( $r->display_name ); ?></span></td>
                                                <?php if ( 'tournament' === $view_type ) : ?>
                                                        <td><?php echo isset( $r->points ) ? (int) $r->points : 0; ?></td>
                                                        <td><?php echo (int) $r->wins; ?></td>
                                                <?php else : ?>
                                                        <td><?php echo esc_html( bhg_format_currency( (float) $r->guess ) ); ?></td>
                                                        <td><?php echo esc_html( bhg_format_currency( (float) $r->diff ) ); ?></td>
                                                        <td><?php echo '' !== $prize_title ? esc_html( $prize_title ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
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

