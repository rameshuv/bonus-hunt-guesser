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
                        "SELECT r.user_id, r.wins, u.display_name FROM {$tres_table} r JOIN {$users_table} u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY r.wins DESC, r.id ASC",
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
                                "SELECT g.user_id, g.guess, u.display_name, (%f - g.guess) AS diff FROM {$guess_table} g JOIN {$users_table} u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY ABS(%f - g.guess) ASC, g.id ASC",
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
        if ( $wcount > 25 ) {
                        $wcount = 25;
        }
        $columns = array(
                'sc_position' => bhg_t( 'sc_position', 'Position' ),
                'sc_user'     => bhg_t( 'sc_user', 'User' ),
                'sc_guess'    => bhg_t( 'sc_guess', 'Guess' ),
                'difference'  => bhg_t( 'difference', 'Difference' ),
        );
}

$winner_limit      = min( 25, max( 1, (int) $wcount ) );
$total_rows        = count( (array) $rows );
$highlighted_total = $total_rows > 0 ? min( $winner_limit, $total_rows ) : 0;

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
        <div class="bhg-results-controls">
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
        <?php if ( 'tournament' === $view_type ) : ?>
                <div class="bhg-results-summary">
                        <span class="bhg-summary-item"><strong><?php echo esc_html( bhg_t( 'label_winners', 'Winners' ) ); ?>:</strong> <?php echo esc_html( number_format_i18n( $highlighted_total ) ); ?></span>
                        <span class="bhg-summary-item"><strong><?php echo esc_html( bhg_t( 'label_total_participants', 'Participants' ) ); ?>:</strong> <?php echo esc_html( number_format_i18n( $total_rows ) ); ?></span>
                </div>
        <?php else : ?>
                <div class="bhg-results-summary">
                        <span class="bhg-summary-item"><strong><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?>:</strong> <?php echo $has_final_balance ? esc_html( bhg_format_currency( (float) $hunt->final_balance ) ) : esc_html( bhg_t( 'label_endash', '–' ) ); ?></span>
                        <span class="bhg-summary-item"><strong><?php echo esc_html( bhg_t( 'label_winners', 'Winners' ) ); ?>:</strong> <?php echo esc_html( number_format_i18n( $winner_limit ) ); ?></span>
                        <span class="bhg-summary-item"><strong><?php echo esc_html( bhg_t( 'label_total_guesses', 'Total Guesses' ) ); ?>:</strong> <?php echo esc_html( number_format_i18n( $total_rows ) ); ?></span>
                </div>
        <?php endif; ?>

        <?php if ( 0 === $total_rows ) : ?>
                <div class="notice notice-info">
                        <p>
                                <?php
                                if ( 'hunt' === $view_type && ( ! isset( $has_final_balance ) || ! $has_final_balance ) ) {
                                        echo esc_html( bhg_t( 'notice_results_pending', 'Results pending.' ) );
                                } else {
                                        echo esc_html( bhg_t( 'notice_no_guesses_yet', 'No guesses yet.' ) );
                                }
                                ?>
                        </p>
                </div>
        <?php else : ?>
                <p class="bhg-results-note"><?php echo esc_html( sprintf( bhg_t( 'winners_highlighted_message', 'Top %d entries are highlighted as winners.' ), $highlighted_total ) ); ?></p>
                <table class="widefat fixed striped bhg-results-table">
                        <thead>
                                <tr>
                                        <?php foreach ( $columns as $key => $label ) : ?>
                                                <th class="column-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></th>
                                        <?php endforeach; ?>
                                </tr>
                        </thead>
                        <tbody>
                        <?php
                        $pos = 1;
                        foreach ( (array) $rows as $r ) :
                                $is_winner = $pos <= $winner_limit;
                                ?>
                                <tr class="<?php echo $is_winner ? 'bhg-winner-row' : 'bhg-runner-row'; ?>">
                                        <td class="column-position">
                                                <span class="bhg-rank-number"><?php echo (int) $pos; ?></span>
                                                <?php if ( $is_winner ) : ?>
                                                        <span class="bhg-winner-badge"><?php echo esc_html( sprintf( bhg_t( 'label_winner_number', 'Winner #%d' ), (int) $pos ) ); ?></span>
                                                <?php endif; ?>
                                        </td>
                                        <td class="column-user">
                                                <?php if ( isset( $r->user_id ) && $r->user_id ) : ?>
                                                        <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $r->user_id ) ); ?>"><?php echo esc_html( $r->display_name ); ?></a>
                                                <?php else : ?>
                                                        <?php echo esc_html( $r->display_name ); ?>
                                                <?php endif; ?>
                                        </td>
                                        <?php if ( 'tournament' === $view_type ) : ?>
                                                <td class="column-wins column-numeric"><?php echo esc_html( number_format_i18n( (int) $r->wins ) ); ?></td>
                                        <?php else : ?>
                                                <td class="column-guess column-numeric"><?php echo esc_html( bhg_format_currency( (float) $r->guess ) ); ?></td>
                                                <td class="column-difference column-numeric"><?php echo esc_html( bhg_format_currency( abs( (float) $r->diff ) ) ); ?></td>
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

