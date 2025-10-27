<?php
/**
 * Prize section view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout        = isset( $layout ) ? $layout : 'grid';
$size          = isset( $size ) ? $size : 'medium';
$prizes        = isset( $prizes ) && is_array( $prizes ) ? $prizes : array();
$title_text    = isset( $title_text ) ? $title_text : '';
$count         = isset( $count ) ? (int) $count : count( $prizes );
$card_renderer = ( isset( $card_renderer ) && is_callable( $card_renderer ) ) ? $card_renderer : null;
?>
<div class="bhg-prizes-block bhg-prizes-layout-<?php echo esc_attr( $layout ); ?> size-<?php echo esc_attr( $size ); ?>">
	<?php if ( '' !== $title_text ) : ?>
		<h4 class="bhg-prizes-title"><?php echo esc_html( $title_text ); ?></h4>
	<?php endif; ?>

	<?php if ( 'carousel' === $layout && $card_renderer ) : ?>
		<?php $show_nav = $count > 1; ?>
		<div class="bhg-prize-carousel" data-count="<?php echo esc_attr( $count ); ?>">
			<?php if ( $show_nav ) : ?>
				<button type="button" class="bhg-prize-nav bhg-prize-prev" aria-label="<?php echo esc_attr( bhg_t( 'previous', 'Previous' ) ); ?>">
					<span aria-hidden="true">&lsaquo;</span>
				</button>
			<?php endif; ?>

			<div class="bhg-prize-track-wrapper">
				<div class="bhg-prize-track">
					<?php foreach ( $prizes as $index => $prize ) : // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>
						<?php echo $card_renderer ? call_user_func( $card_renderer, $prize, $size ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $show_nav ) : ?>
				<button type="button" class="bhg-prize-nav bhg-prize-next" aria-label="<?php echo esc_attr( bhg_t( 'next', 'Next' ) ); ?>">
					<span aria-hidden="true">&rsaquo;</span>
				</button>

				<div class="bhg-prize-dots" role="tablist">
					<?php for ( $i = 0; $i < $count; $i++ ) : // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>
						<button type="button" class="bhg-prize-dot<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( bhg_t( 'prize_slide_label', 'Go to prize %d' ), $i + 1 ) ); ?>"></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php elseif ( $card_renderer ) : ?>
		<div class="bhg-prizes-grid">
			<?php foreach ( $prizes as $prize ) : // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>
				<?php echo call_user_func( $card_renderer, $prize, $size ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
