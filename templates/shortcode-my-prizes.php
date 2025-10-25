<?php
/**
 * Template for the “My Prizes” shortcode.
 *
 * @package Bonus_Hunt_Guesser
 *
 * @var array  $prizes      Prize rows for the current user.
 * @var int    $total       Total number of prize entries.
 * @var int    $per_page    Items per page.
 * @var int    $paged       Current page number.
 * @var string $pagination  Pagination HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$currency_formatter = function_exists( 'bhg_format_currency' ) ? 'bhg_format_currency' : null;
$date_format        = trim( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
$date_format        = $date_format ? $date_format : 'Y-m-d H:i';
$dash               = '—';
?>
<div class="bhg-dashboard-section bhg-dashboard-my-prizes">
        <h2><?php echo esc_html( bhg_t( 'heading_my_prizes', 'My Prizes' ) ); ?></h2>

        <?php if ( ! empty( $prizes ) ) : ?>
                <table class="bhg-table bhg-table-my-prizes">
                        <thead>
                                <tr>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_bonus_hunt', 'Bonus Hunt' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_position', 'Position' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_prize', 'Prize' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_guess', 'Guess' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_difference', 'Difference' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_final_balance', 'Final Balance' ) ); ?></th>
                                        <th scope="col"><?php echo esc_html( bhg_t( 'column_closed_at', 'Closed At' ) ); ?></th>
                                </tr>
                        </thead>
                        <tbody>
                                <?php foreach ( $prizes as $entry ) :
                                        $position      = isset( $entry['position'] ) ? (int) $entry['position'] : 0;
                                        $position_text = $position > 0 ? sprintf( bhg_t( 'label_winner_position', 'Position %d' ), $position ) : $dash;
                                        $guess_display = $currency_formatter ? call_user_func( $currency_formatter, (float) $entry['guess'] ) : number_format_i18n( (float) $entry['guess'], 2 );
                                        $difference    = null !== $entry['difference'] ? ( $currency_formatter ? call_user_func( $currency_formatter, (float) $entry['difference'] ) : number_format_i18n( (float) $entry['difference'], 2 ) ) : $dash;
                                        $final_balance = null !== $entry['final_balance'] ? ( $currency_formatter ? call_user_func( $currency_formatter, (float) $entry['final_balance'] ) : number_format_i18n( (float) $entry['final_balance'], 2 ) ) : $dash;
                                        $closed_at     = ! empty( $entry['closed_at'] ) ? mysql2date( $date_format, $entry['closed_at'] ) : $dash;
                                        $prize_title   = isset( $entry['prize']['title'] ) ? $entry['prize']['title'] : '';
                                        $prize_desc    = isset( $entry['prize']['description'] ) ? wp_strip_all_tags( $entry['prize']['description'] ) : '';
                                        ?>
                                        <tr>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_bonus_hunt', 'Bonus Hunt' ) ); ?>">
                                                        <?php echo esc_html( $entry['title'] ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_position', 'Position' ) ); ?>">
                                                        <?php echo esc_html( $position_text ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_prize', 'Prize' ) ); ?>">
                                                        <?php if ( $prize_title ) : ?>
                                                                <strong><?php echo esc_html( $prize_title ); ?></strong>
                                                                <?php if ( $prize_desc ) : ?>
                                                                        <br><small><?php echo esc_html( $prize_desc ); ?></small>
                                                                <?php endif; ?>
                                                        <?php else : ?>
                                                                <?php echo esc_html( $dash ); ?>
                                                        <?php endif; ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_guess', 'Guess' ) ); ?>">
                                                        <?php echo esc_html( $guess_display ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_difference', 'Difference' ) ); ?>">
                                                        <?php echo esc_html( $difference ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_final_balance', 'Final Balance' ) ); ?>">
                                                        <?php echo esc_html( $final_balance ); ?>
                                                </td>
                                                <td data-title="<?php echo esc_attr( bhg_t( 'column_closed_at', 'Closed At' ) ); ?>">
                                                        <?php echo esc_html( $closed_at ); ?>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        </tbody>
                </table>
        <?php else : ?>
                <p><?php echo esc_html( bhg_t( 'notice_no_prizes', 'You have not won any prizes yet.' ) ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $pagination ) ) : ?>
                <nav class="bhg-pagination" aria-label="<?php echo esc_attr( bhg_t( 'label_prizes_pagination', 'Prizes pagination' ) ); ?>">
                        <?php echo wp_kses_post( $pagination ); ?>
                </nav>
        <?php endif; ?>
</div>
