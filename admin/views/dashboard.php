<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die(
		__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' )
	);
}

if ( ! function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	wp_die(
		__(
			'Helper function bhg_get_latest_closed_hunts() missing. Please include class-bhg-bonus-hunts.php helpers.',
			'bonus-hunt-guesser'
		)
	);
}

$hunts = bhg_get_latest_closed_hunts( 3 );
?>
<div class="wrap bhg-dashboard">
  <h1><?php esc_html_e( 'Latest Hunts', 'bonus-hunt-guesser' ); ?></h1>
  <?php if ( $hunts ) : ?>
        <div id="dashboard-widgets-wrap">
          <div id="dashboard-widgets" class="metabox-holder">
                <div id="postbox-container-1" class="postbox-container">
                  <?php foreach ( $hunts as $h ) : ?>
                        <?php
                        $winners = function_exists( 'bhg_get_top_winners_for_hunt' )
                                ? bhg_get_top_winners_for_hunt( $h->id, (int) $h->winners_count )
                                : array();
                        ?>
                        <div class="postbox bhg-dashboard-card">
                          <h2 class="hndle"><span><?php echo esc_html( $h->title ); ?></span></h2>
                          <div class="inside">
                                <ul class="bhg-dashboard-meta">
                                  <li><strong><?php esc_html_e( 'Start Balance', 'bonus-hunt-guesser' ); ?>:</strong> <?php echo esc_html( number_format_i18n( (float) $h->starting_balance, 2 ) ); ?></li>
                                  <li><strong><?php esc_html_e( 'Final Balance', 'bonus-hunt-guesser' ); ?>:</strong> <?php echo ( $h->final_balance !== null ) ? esc_html( number_format_i18n( (float) $h->final_balance, 2 ) ) : esc_html__( '—', 'bonus-hunt-guesser' ); ?></li>
                                  <li><strong><?php esc_html_e( 'Closed At', 'bonus-hunt-guesser' ); ?>:</strong> <?php echo $h->closed_at ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $h->closed_at ) ) ) : esc_html__( '—', 'bonus-hunt-guesser' ); ?></li>
                                </ul>
                                <h3 class="bhg-dashboard-subtitle"><?php esc_html_e( 'Winners', 'bonus-hunt-guesser' ); ?></h3>
                                <?php if ( $winners ) : ?>
                                  <ul class="bhg-dashboard-winners">
                                        <?php foreach ( $winners as $w ) : ?>
                                          <?php
                                          $u  = get_userdata( (int) $w->user_id );
                                          $nm = $u ? $u->user_login : sprintf( __( 'User #%d', 'bonus-hunt-guesser' ), (int) $w->user_id );
                                          ?>
                                          <li><?php echo esc_html( $nm ); ?> — <?php echo esc_html( number_format_i18n( (float) $w->guess, 2 ) ); ?> (<?php esc_html_e( 'diff', 'bonus-hunt-guesser' ); ?> <?php echo esc_html( number_format_i18n( (float) $w->diff, 2 ) ); ?>)</li>
                                        <?php endforeach; ?>
                                  </ul>
                                <?php else : ?>
                                  <p><?php esc_html_e( 'No winners yet', 'bonus-hunt-guesser' ); ?></p>
                                <?php endif; ?>
                          </div>
                        </div>
                  <?php endforeach; ?>
                </div>
          </div>
        </div>
  <?php else : ?>
        <p><?php esc_html_e( 'No closed hunts yet.', 'bonus-hunt-guesser' ); ?></p>
  <?php endif; ?>
</div>
