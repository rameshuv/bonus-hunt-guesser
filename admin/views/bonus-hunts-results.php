<?php
/**
 * Bonus hunt results page.
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

$view_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'hunt';
$view_type = ( 'tournament' === $view_type ) ? 'tournament' : 'hunt';

if ( 'tournament' === $view_type ) {
        $tournament_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        $tours_table   = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
        $tres_table    = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
        $users_table   = esc_sql( $wpdb->users );

        if ( ! $tournament_id ) {
                echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'tournament', 'Tournament' ) ) . '</h1>';
                echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'tournament_not_found', 'Tournament not found' ) ) . '</p></div>';
                echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=bhg-tournaments' ) ) . '">' . esc_html( bhg_t( 'back_to_tournaments', 'Back to Tournaments' ) ) . '</a></p>';
                echo '</div>';
                return;
        }

        $tournament = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT * FROM {$tours_table} WHERE id=%d",
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

        $rows = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT r.user_id, r.wins, u.display_name FROM {$tres_table} r JOIN {$users_table} u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY r.wins DESC, r.id ASC",
                        $tournament_id
                )
        );

        $total_participants = count( $rows );
        $winners_limit      = 3;
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
                                <th scope="col"><?php echo esc_html( bhg_t( 'wins', 'Wins' ) ); ?></th>
                        </tr>
                </thead>
                <tbody>
                        <?php if ( empty( $rows ) ) : ?>
                                <tr>
                                        <td colspan="3" class="bhg-text-center"><?php echo esc_html( bhg_t( 'no_results_yet', 'No results yet.' ) ); ?></td>
                                </tr>
                        <?php else : ?>
                                <?php foreach ( $rows as $index => $row ) :
                                        $position  = $index + 1;
                                        $is_winner = $position <= $winners_limit;
                                        $row_class = $is_winner ? 'bhg-results-row bhg-results-row--winner' : 'bhg-results-row';
                                        $name      = $row->display_name ? $row->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $row->user_id );
                                ?>
                                        <tr class="<?php echo esc_attr( $row_class ); ?>">
                                                <td><span class="bhg-badge <?php echo esc_attr( $is_winner ? 'bhg-badge-primary' : 'bhg-badge-muted' ); ?>"><?php echo esc_html( $position ); ?></span></td>
                                                <td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $row->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
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

$hunt = $hunt_id ? BHG_Bonus_Hunts::get_hunt( $hunt_id ) : null;

if ( ! $hunt ) {
        echo '<div class="wrap bhg-wrap"><h1>' . esc_html( bhg_t( 'bonus_hunt', 'Bonus Hunt' ) ) . '</h1>';
        echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt', 'Invalid hunt' ) ) . '</p></div>';
        echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts' ) ) . '">' . esc_html( bhg_t( 'back_to_bonus_hunts', 'Back to Bonus Hunts' ) ) . '</a></p>';
        echo '</div>';
        return;
}

$guesses            = BHG_Bonus_Hunts::get_hunt_guesses_ranked( (int) $hunt->id );
$winners_limit      = max( 1, (int) $hunt->winners_count );
$final_balance_raw  = isset( $hunt->final_balance ) ? $hunt->final_balance : null;
$has_final_balance  = null !== $final_balance_raw;
$final_balance      = $has_final_balance ? (float) $final_balance_raw : null;
$total_participants = count( $guesses );

$final_balance_display = $has_final_balance ? bhg_format_currency( $final_balance ) : bhg_t( 'label_en_dash', '–' );
$winners_display       = number_format_i18n( $winners_limit );
$participants_display  = number_format_i18n( $total_participants );
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
                        </tr>
                </thead>
                <tbody>
                        <?php if ( empty( $guesses ) ) : ?>
                                <tr>
                                        <td colspan="5" class="bhg-text-center"><?php echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) ); ?></td>
                                </tr>
                        <?php else : ?>
                                <?php foreach ( $guesses as $index => $guess ) :
                                        $position   = $index + 1;
                                        $is_winner  = $has_final_balance && $position <= $winners_limit;
                                        $row_class  = $is_winner ? 'bhg-results-row bhg-results-row--winner' : 'bhg-results-row';
                                        $user_name  = $guess->display_name ? $guess->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $guess->user_id );
                                        $guess_link = admin_url( 'user-edit.php?user_id=' . (int) $guess->user_id );
                                        $difference = $has_final_balance && isset( $guess->diff ) ? bhg_format_currency( abs( (float) $guess->diff ) ) : bhg_t( 'label_en_dash', '–' );
                                        $date_value = isset( $guess->created_at ) ? strtotime( $guess->created_at ) : false;
                                        $date_label = $date_value ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $date_value ) : bhg_t( 'label_en_dash', '–' );
                                ?>
                                        <tr class="<?php echo esc_attr( $row_class ); ?>">
                                                <td>
                                                        <span class="bhg-badge <?php echo esc_attr( $is_winner ? 'bhg-badge-primary' : 'bhg-badge-muted' ); ?>"><?php echo esc_html( $position ); ?></span>
                                                </td>
                                                <td>
                                                        <span class="bhg-result-name"><a href="<?php echo esc_url( $guess_link ); ?>"><?php echo esc_html( $user_name ); ?></a></span>
                                                </td>
                                                <td><?php echo esc_html( bhg_format_currency( (float) $guess->guess ) ); ?></td>
                                                <td><?php echo esc_html( $difference ); ?></td>
                                                <td><?php echo esc_html( $date_label ); ?></td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                </tbody>
        </table>
</div>
