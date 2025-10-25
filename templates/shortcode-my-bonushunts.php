<?php
/**
 * Template for the “My Bonus Hunts” shortcode.
 *
 * @package Bonus_Hunt_Guesser
 *
 * @var array  $hunts      Hunt rows for the current user.
 * @var int    $total      Total number of hunts.
 * @var int    $per_page   Items per page.
 * @var int    $paged      Current page number.
 * @var string $pagination Pagination HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$currency_formatter = function_exists( 'bhg_format_currency' ) ? 'bhg_format_currency' : null;
$date_format        = trim( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
$date_format        = $date_format ? $date_format : 'Y-m-d H:i';
$dash               = '—';
?>
<div class="bhg-dashboard-section bhg-dashboard-my-bonushunts">
        <h2><?php echo esc_html( bhg_t( 'heading_my_bonus_hunts', 'My Bonus Hunts' ) ); ?></h2>

        <?php if ( ! empty( $hunts ) ) : ?>
                <table class="bhg-table bhg-table-my-bonushunts">
                        <thead>
                                <tr>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_bonus_hunt', 'Bonus Hunt' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_guess', 'Guess' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_difference', 'Difference' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_rank', 'Rank' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_final_balance', 'Final Balance' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_status', 'Status' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_submitted', 'Submitted' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_winner_position', 'Winner' ) ); ?></th>
                                </tr>
                        </thead>
                        <tbody>
                                <?php foreach ( $hunts as $hunt ) :
                                        $is_winner     = ! empty( $hunt['winner_position'] );
                                        $row_class     = $is_winner ? 'is-winner' : 'is-participant';
                                        $status        = isset( $hunt['status'] ) ? strtolower( (string) $hunt['status'] ) : '';
                                        $status_label  = $status ? bhg_t( 'status_' . $status, ucfirst( $status ) ) : '';
                                        $guess_display = $currency_formatter ? call_user_func( $currency_formatter, (float) $hunt['guess'] ) : number_format_i18n( (float) $hunt['guess'], 2 );
                                        $difference    = null !== $hunt['difference'] ? ( $currency_formatter ? call_user_func( $currency_formatter, (float) $hunt['difference'] ) : number_format_i18n( (float) $hunt['difference'], 2 ) ) : $dash;
                                        $final_balance = null !== $hunt['final_balance'] ? ( $currency_formatter ? call_user_func( $currency_formatter, (float) $hunt['final_balance'] ) : number_format_i18n( (float) $hunt['final_balance'], 2 ) ) : $dash;
                                        $submitted     = ! empty( $hunt['created_at'] ) ? mysql2date( $date_format, $hunt['created_at'] ) : '';
                                        $winner_label  = $is_winner ? sprintf( bhg_t( 'label_winner_position', 'Position %d' ), (int) $hunt['winner_position'] ) : $dash;
                                        $winner_diff   = ( $is_winner && null !== $hunt['winner_diff'] ) ? ( $currency_formatter ? call_user_func( $currency_formatter, (float) $hunt['winner_diff'] ) : number_format_i18n( (float) $hunt['winner_diff'], 2 ) ) : '';
                                        ?>
                                        <tr class="<?php echo esc_attr( $row_class ); ?>">
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_bonus_hunt', 'Bonus Hunt' ) ); ?>">
                                                        <?php echo esc_html( $hunt['title'] ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_guess', 'Guess' ) ); ?>">
                                                        <?php echo esc_html( $guess_display ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_difference', 'Difference' ) ); ?>">
                                                        <?php echo esc_html( $difference ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_rank', 'Rank' ) ); ?>">
                                                        <?php echo isset( $hunt['rank'] ) && $hunt['rank'] ? esc_html( (int) $hunt['rank'] ) : esc_html( $dash ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_final_balance', 'Final Balance' ) ); ?>">
                                                        <?php echo esc_html( $final_balance ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_status', 'Status' ) ); ?>">
                                                        <?php echo esc_html( $status_label ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_submitted', 'Submitted' ) ); ?>">
                                                        <?php echo esc_html( $submitted ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_winner_position', 'Winner' ) ); ?>">
                                                        <?php
                                                        if ( $is_winner ) {
                                                                echo esc_html( $winner_label );
                                                                if ( $winner_diff ) {
                                                                        echo '<br><small>' . esc_html( sprintf( bhg_t( 'label_difference_value', 'Diff: %s' ), $winner_diff ) ) . '</small>';
                                                                }
                                                        } else {
                                                                echo esc_html( $dash );
                                                        }
                                                        ?>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        </tbody>
                </table>
        <?php else : ?>
                <p><?php echo esc_html( bhg_t( 'notice_no_bonus_hunts', 'You have not participated in any bonus hunts yet.' ) ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $pagination ) ) : ?>
                <nav class="bhg-pagination" aria-label="<?php echo esc_attr( bhg_t( 'label_bonus_hunts_pagination', 'Bonus hunts pagination' ) ); ?>">
                        <?php echo wp_kses_post( $pagination ); ?>
                </nav>
        <?php endif; ?>
</div>
