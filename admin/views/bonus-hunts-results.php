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

$view_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'hunt';
$view_type = ( 'tournament' === $view_type ) ? 'tournament' : 'hunt';

if ( 'tournament' === $view_type ) {
        $tournament_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        if ( ! $tournament_id ) {
                echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'tournament', 'Tournament' ) ) . '</h1>';
                echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'tournament_not_found', 'Tournament not found' ) ) . '</p></div>';
                echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=bhg-tournaments' ) ) . '">' . esc_html( bhg_t( 'back_to_tournaments', 'Back to Tournaments' ) ) . '</a></p>';
                echo '</div>';
                return;
        }

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
                echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'tournament', 'Tournament' ) ) . '</h1>';
                echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'tournament_not_found', 'Tournament not found' ) ) . '</p></div>';
                echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=bhg-tournaments' ) ) . '">' . esc_html( bhg_t( 'back_to_tournaments', 'Back to Tournaments' ) ) . '</a></p>';
                echo '</div>';
                return;
        }

        $results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->prepare(
                        "SELECT r.*, u.display_name FROM {$results_table} r
"
                        . "LEFT JOIN {$users_table} u ON u.ID = r.user_id
"
                        . "WHERE r.tournament_id = %d
"
                        . "ORDER BY r.points DESC, r.wins DESC, r.last_win_date ASC, r.id ASC",
                        $tournament_id
                )
        );

        $total_participants = count( $results );
        $winners_limit      = apply_filters( 'bhg_tournament_winners_limit', 3, $tournament );
        $winners_limit      = max( 1, (int) $winners_limit );
        ?>
        <div class="wrap bhg-wrap bhg-results-page">
                <h1><?php echo esc_html( sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $tournament->title ) ); ?></h1>

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
                                <?php foreach ( $results as $index => $row ) :
                                        $position   = $index + 1;
                                        $is_winner  = ( $position <= $winners_limit );
                                        $row_classes = array( 'bhg-results-row' );
                                        if ( $is_winner ) {
                                                $row_classes[] = 'bhg-results-row--winner';
                                        }
                                        $name = $row->display_name ? $row->display_name : sprintf( /* translators: %d: user ID. */ bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
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
        </div>
        <?php
        return;
}

$hunt_id = isset( $_GET['hunt_id'] ) ? absint( wp_unslash( $_GET['hunt_id'] ) ) : 0;
if ( ! $hunt_id && isset( $_GET['id'] ) ) {
        $hunt_id = absint( wp_unslash( $_GET['id'] ) );
}

if ( ! $hunt_id && method_exists( 'BHG_Bonus_Hunts', 'get_default_results_hunt_id' ) ) {
        $hunt_id = (int) BHG_Bonus_Hunts::get_default_results_hunt_id();
}

$hunt = $hunt_id ? BHG_Bonus_Hunts::get_hunt( $hunt_id ) : null;

if ( ! $hunt ) {
        echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'bonus_hunt', 'Bonus Hunt' ) ) . '</h1>';

        if ( $hunt_id ) {
                echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt', 'Invalid hunt' ) ) . '</p></div>';
        } else {
                echo '<div class="notice notice-warning"><p>' . esc_html( bhg_t( 'notice_no_hunts_available', 'No bonus hunts available yet. Create a hunt to view results.' ) ) . '</p></div>';
        }

        echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts' ) ) . '">' . esc_html( bhg_t( 'back_to_bonus_hunts', 'Back to Bonus Hunts' ) ) . '</a></p>';
        echo '</div>';
        return;
}

$guesses                = BHG_Bonus_Hunts::get_hunt_guesses_ranked( (int) $hunt->id );
$official_winner_ids    = method_exists( 'BHG_Bonus_Hunts', 'get_hunt_winner_ids' ) ? BHG_Bonus_Hunts::get_hunt_winner_ids( (int) $hunt->id ) : array();
$winner_lookup          = array();
foreach ( $official_winner_ids as $index => $winner_user_id ) {
        $winner_lookup[ (int) $winner_user_id ] = (int) $index;
}
$winners_limit          = max( 1, (int) $hunt->winners_count );
$final_balance_raw      = isset( $hunt->final_balance ) ? $hunt->final_balance : null;
$has_final_balance      = null !== $final_balance_raw;
$final_balance          = $has_final_balance ? (float) $final_balance_raw : null;
$total_participants     = count( $guesses );
$actual_winner_count    = count( $official_winner_ids );

$final_balance_display = $has_final_balance ? bhg_format_currency( $final_balance ) : bhg_t( 'label_emdash', '—' );
$winners_display       = number_format_i18n( $actual_winner_count ? $actual_winner_count : $winners_limit );
$participants_display  = number_format_i18n( $total_participants );

$prize_titles = array();
if ( class_exists( 'BHG_Prizes' ) ) {
        $hunt_prizes = BHG_Prizes::get_prizes_for_hunt( (int) $hunt->id );
        foreach ( (array) $hunt_prizes as $prize_row ) {
                if ( isset( $prize_row->title ) && '' !== $prize_row->title ) {
                        $prize_titles[] = (string) $prize_row->title;
                }
        }
}
?>
<div class="wrap bhg-wrap bhg-results-page">
        <h1><?php echo esc_html( sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $hunt->title ) ); ?></h1>

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
                                <td colspan="6" class="bhg-text-center"><?php echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) ); ?></td>
                        </tr>
                        <?php else : ?>
                        <?php foreach ( $guesses as $index => $row ) :
                                $position   = $index + 1;
                                $user_id    = isset( $row->user_id ) ? (int) $row->user_id : 0;
                                $is_winner  = $user_id > 0 && isset( $winner_lookup[ $user_id ] );
                                $row_classes = array( 'bhg-results-row' );
                                if ( $is_winner ) {
                                        $row_classes[] = 'bhg-results-row--winner';
                                }

                                $name          = $row->display_name ? $row->display_name : sprintf( /* translators: %d: user ID. */ bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
                                $guess_display = isset( $row->guess ) ? bhg_format_currency( (float) $row->guess ) : bhg_t( 'label_emdash', '—' );
                                $diff_value    = ( isset( $row->diff ) && null !== $row->diff ) ? bhg_format_currency( abs( (float) $row->diff ) ) : bhg_t( 'label_emdash', '—' );
                                $created_at    = isset( $row->created_at ) ? $row->created_at : null;
                                $submitted_on  = $created_at ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $created_at ) : bhg_t( 'label_emdash', '—' );
                                $prize_title   = '';
                                if ( $is_winner ) {
                                        $prize_index = isset( $winner_lookup[ $user_id ] ) ? (int) $winner_lookup[ $user_id ] : 0;
                                        if ( isset( $prize_titles[ $prize_index ] ) ) {
                                                $prize_title = $prize_titles[ $prize_index ];
                                        }
                                }
                                ?>
                        <tr class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
                                <td><span class="bhg-badge <?php echo esc_attr( $is_winner ? 'bhg-badge-primary' : 'bhg-badge-muted' ); ?>"><?php echo esc_html( $position ); ?></span></td>
                                <td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $row->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
                                <td><?php echo esc_html( $guess_display ); ?></td>
                                <td><?php echo esc_html( $diff_value ); ?></td>
                                <td><?php echo esc_html( $submitted_on ); ?></td>
                                <td><?php echo '' !== $prize_title ? esc_html( $prize_title ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                </tbody>
        </table>
</div>
