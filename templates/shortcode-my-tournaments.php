<?php
/**
 * Template for the “My Tournaments” shortcode.
 *
 * @package Bonus_Hunt_Guesser
 *
 * @var array  $tournaments Tournament rows for the current user.
 * @var int    $total       Total number of tournaments.
 * @var int    $per_page    Items per page.
 * @var int    $paged       Current page number.
 * @var string $pagination  Pagination HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$date_format = get_option( 'date_format' );
$date_format = $date_format ? $date_format : 'Y-m-d';
$dash        = '—';
?>
<div class="bhg-dashboard-section bhg-dashboard-my-tournaments">
        <h2><?php echo esc_html( bhg_t( 'heading_my_tournaments', 'My Tournaments' ) ); ?></h2>

        <?php if ( ! empty( $tournaments ) ) : ?>
                <table class="bhg-table bhg-table-my-tournaments">
                        <thead>
                                <tr>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_tournament', 'Tournament' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_type', 'Type' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_wins', 'Wins' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_last_win', 'Last Win' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_status', 'Status' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_start_date', 'Start Date' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_end_date', 'End Date' ) ); ?></th>
                                </tr>
                        </thead>
                        <tbody>
                                <?php foreach ( $tournaments as $tournament ) :
                                        $tournament_type = isset( $tournament['type'] ) ? sanitize_key( $tournament['type'] ) : '';
                                        $type_label = $tournament_type ? bhg_t( 'tournament_type_' . $tournament_type, ucwords( str_replace( '_', ' ', $tournament_type ) ) ) : $dash;
                                        $status_key = isset( $tournament['status'] ) ? strtolower( (string) $tournament['status'] ) : '';
                                        $status_label = $status_key ? bhg_t( 'status_' . $status_key, ucfirst( $status_key ) ) : $dash;
                                        $wins       = isset( $tournament['wins'] ) ? (int) $tournament['wins'] : 0;
                                        $last_win   = ! empty( $tournament['last_win_date'] ) ? mysql2date( $date_format, $tournament['last_win_date'] ) : $dash;
                                        $start_date = ! empty( $tournament['start_date'] ) ? mysql2date( $date_format, $tournament['start_date'] ) : $dash;
                                        $end_date   = ! empty( $tournament['end_date'] ) ? mysql2date( $date_format, $tournament['end_date'] ) : $dash;
                                        ?>
                                        <tr>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_tournament', 'Tournament' ) ); ?>">
                                                        <?php echo esc_html( $tournament['title'] ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_type', 'Type' ) ); ?>">
                                                        <?php echo esc_html( $type_label ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_wins', 'Wins' ) ); ?>">
                                                        <?php echo esc_html( $wins ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_last_win', 'Last Win' ) ); ?>">
                                                        <?php echo esc_html( $last_win ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_status', 'Status' ) ); ?>">
                                                        <?php echo esc_html( $status_label ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_start_date', 'Start Date' ) ); ?>">
                                                        <?php echo esc_html( $start_date ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_end_date', 'End Date' ) ); ?>">
                                                        <?php echo esc_html( $end_date ); ?>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        </tbody>
                </table>
        <?php else : ?>
                <p><?php echo esc_html( bhg_t( 'notice_no_tournaments', 'You are not ranked in any tournaments yet.' ) ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $pagination ) ) : ?>
                <nav class="bhg-pagination" aria-label="<?php echo esc_attr( bhg_t( 'label_tournaments_pagination', 'Tournaments pagination' ) ); ?>">
                        <?php echo wp_kses_post( $pagination ); ?>
                </nav>
        <?php endif; ?>
</div>
