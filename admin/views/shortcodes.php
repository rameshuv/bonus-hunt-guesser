<?php
/**
 * Shortcodes reference screen.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) )
		);
}

$shortcodes = array(
	array(
		'tag'         => '[bhg_active_hunt]',
		'description' => __( 'Display currently open bonus hunts with the live guess table and associated prizes.', 'bonus-hunt-guesser' ),
		'aliases'     => array( '[bhg_active]' ),
		'attributes'  => array(
			array(
				'name'        => 'prize_layout',
				'values'      => 'grid | carousel',
				'default'     => 'grid',
				'description' => __( 'Controls how the selected hunt\'s prizes are rendered.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'prize_size',
				'values'      => 'small | medium | big',
				'default'     => 'medium',
				'description' => __( 'Selects the registered image size used for prize artwork.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_active_hunt prize_layout="carousel" prize_size="big"]',
	),
	array(
		'tag'         => '[bhg_guess_form]',
		'description' => __( 'Interactive guess submission form that respects guessing toggles and allows users to edit prior guesses.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'hunt_id',
				'values'      => __( 'Numeric hunt ID', 'bonus-hunt-guesser' ),
				'default'     => __( '0 (auto-detect latest open hunt)', 'bonus-hunt-guesser' ),
				'description' => __( 'Lock the form to a specific hunt when multiple hunts are open.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_guess_form hunt_id="42"]',
	),
	array(
		'tag'         => '[bhg_user_guesses]',
		'description' => __( 'Paginated leaderboard of guesses for a hunt with affiliate and timeline filters.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'id',
				'values'      => __( 'Numeric hunt ID', 'bonus-hunt-guesser' ),
				'default'     => __( '0 (latest active or most recent hunt)', 'bonus-hunt-guesser' ),
				'description' => __( 'Restrict results to a specific hunt.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'aff',
				'values'      => 'yes | no',
				'default'     => __( 'blank (show all users)', 'bonus-hunt-guesser' ),
				'description' => __( 'Filter results to affiliate or non-affiliate users.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'website',
				'values'      => __( 'Affiliate site ID', 'bonus-hunt-guesser' ),
				'default'     => __( '0 (all sites)', 'bonus-hunt-guesser' ),
				'description' => __( 'Limit guesses to a specific affiliate website.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'status',
				'values'      => 'open | closed',
				'default'     => __( 'blank (any status)', 'bonus-hunt-guesser' ),
				'description' => __( 'Match hunts by their open/closed state.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'this_week | this_month | this_year | last_year | all_time',
				'default'     => __( 'blank (show all dates)', 'bonus-hunt-guesser' ),
				'description' => __( 'Apply timeframe filters to guesses. Accepts aliases such as week/month/year.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'fields',
				'values'      => 'hunt, user, guess, final, site',
				'default'     => 'hunt,user,guess,final',
				'description' => __( 'Choose which columns appear in the table.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'hunt | guess | final | time | difference',
				'default'     => 'hunt',
				'description' => __( 'Set the initial sort column. Open hunts default to submission time.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => __( 'Initial sort direction. Open hunts default to earliest first.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'paged',
				'values'      => __( 'Positive integer', 'bonus-hunt-guesser' ),
				'default'     => '1',
				'description' => __( 'Override the starting page when embedding multiple lists.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'search',
				'values'      => __( 'Free text', 'bonus-hunt-guesser' ),
				'default'     => __( 'blank', 'bonus-hunt-guesser' ),
				'description' => __( 'Seed the search box with a value.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_user_guesses id="12" fields="user,guess,difference" timeline="this_month"]',
	),
	array(
		'tag'         => '[bhg_hunts]',
		'description' => __( 'Front-end list of hunts with sortable columns, search, and affiliate filters.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'id',
				'values'      => __( 'Numeric hunt ID', 'bonus-hunt-guesser' ),
				'default'     => __( '0 (all hunts)', 'bonus-hunt-guesser' ),
				'description' => __( 'Show a single hunt when an ID is supplied.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'aff',
				'values'      => 'yes | no',
				'default'     => 'no',
				'description' => __( 'Highlight affiliate status lights in the results.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'website',
				'values'      => __( 'Affiliate site ID', 'bonus-hunt-guesser' ),
				'default'     => __( '0 (all sites)', 'bonus-hunt-guesser' ),
				'description' => __( 'Limit hunts to a specific affiliate website.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'status',
				'values'      => 'active | closed',
				'default'     => __( 'blank (any status)', 'bonus-hunt-guesser' ),
				'description' => __( 'Filter hunts by open or closed status.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'this_week | this_month | this_year | last_year | all_time',
				'default'     => __( 'blank (show all dates)', 'bonus-hunt-guesser' ),
				'description' => __( 'Restrict hunts by creation date using timeline keywords.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'fields',
				'values'      => 'title, start, final, winners, status, user, site',
				'default'     => 'title,start,final,status',
				'description' => __( 'Control which columns render in the table.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'title | start | final | winners | status | created',
				'default'     => 'created',
				'description' => __( 'Initial sort column.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => __( 'Initial sort direction.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'paged',
				'values'      => __( 'Positive integer', 'bonus-hunt-guesser' ),
				'default'     => '1',
				'description' => __( 'Set the starting page number.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'search',
				'values'      => __( 'Free text', 'bonus-hunt-guesser' ),
				'default'     => __( 'blank', 'bonus-hunt-guesser' ),
				'description' => __( 'Seed the hunts search box.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_hunts status="active" timeline="this_month" fields="title,start,final,winners,site"]',
	),
	array(
		'tag'         => '[bhg_tournaments]',
		'description' => __( 'Tournament directory with detail view, sortable columns, and timeline filters.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'status',
				'values'      => 'active | closed',
				'default'     => 'active',
				'description' => __( 'List only active or closed tournaments.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'tournament',
				'values'      => __( 'Numeric tournament ID', 'bonus-hunt-guesser' ),
				'default'     => '0',
				'description' => __( 'Preselect a specific tournament in the filter dropdown.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'website',
				'values'      => __( 'Affiliate site ID', 'bonus-hunt-guesser' ),
				'default'     => '0',
				'description' => __( 'Filter tournaments by affiliate site.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'weekly | monthly | yearly | quarterly | alltime | this_week | this_month | this_year | last_year',
				'default'     => __( 'blank (show all types)', 'bonus-hunt-guesser' ),
				'description' => __( 'Match tournaments by time-based type or explicit timeline alias.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'title | start_date | end_date | status | type',
				'default'     => 'start_date',
				'description' => __( 'Initial sort column for the table view.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => __( 'Initial sort direction.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'paged',
				'values'      => __( 'Positive integer', 'bonus-hunt-guesser' ),
				'default'     => '1',
				'description' => __( 'Set the starting page number.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'search',
				'values'      => __( 'Free text', 'bonus-hunt-guesser' ),
				'default'     => __( 'blank', 'bonus-hunt-guesser' ),
				'description' => __( 'Seed the tournaments search box.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_tournaments status="active" timeline="yearly"]',
	),
	array(
		'tag'         => '[bhg_leaderboards]',
		'description' => __( 'Comprehensive leaderboard with wins, averages, and affiliate filters.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'fields',
				'values'      => 'pos, user, wins, avg_hunt, avg_tournament, aff, site, hunt, tournament',
				'default'     => 'pos,user,wins,avg_hunt,avg_tournament',
				'description' => __( 'Choose which leaderboard columns render.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'ranking',
				'values'      => '1 – 10',
				'default'     => '1',
				'description' => __( 'Limit the number of rows displayed.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'timeline',
				'values'      => 'day | week | month | year | quarter | all_time (aliases supported)',
				'default'     => 'all_time',
				'description' => __( 'Set the scoring window for results.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'wins | avg_hunt | avg_tournament | user',
				'default'     => 'wins',
				'description' => __( 'Initial sort column.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'DESC',
				'description' => __( 'Initial sort direction.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'search',
				'values'      => __( 'Free text', 'bonus-hunt-guesser' ),
				'default'     => __( 'blank', 'bonus-hunt-guesser' ),
				'description' => __( 'Seed the leaderboard search box.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'tournament',
				'values'      => __( 'Tournament ID', 'bonus-hunt-guesser' ),
				'default'     => '0',
				'description' => __( 'Filter scores by tournament.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'bonushunt',
				'values'      => __( 'Hunt ID', 'bonus-hunt-guesser' ),
				'default'     => '0',
				'description' => __( 'Filter scores by a single hunt.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'website',
				'values'      => __( 'Affiliate site ID', 'bonus-hunt-guesser' ),
				'default'     => '0',
				'description' => __( 'Filter scores by affiliate website.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'aff',
				'values'      => 'yes | no',
				'default'     => __( 'blank (any user)', 'bonus-hunt-guesser' ),
				'description' => __( 'Filter leaderboard rows by affiliate status.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_leaderboards ranking="10" timeline="month" fields="pos,user,wins,avg_hunt,aff,site"]',
	),
	array(
		'tag'         => '[bhg_leaderboard]',
		'description' => __( 'Legacy single-hunt leaderboard retained for backward compatibility.', 'bonus-hunt-guesser' ),
		'aliases'     => array( '[bonus_hunt_leaderboard]' ),
		'attributes'  => array(
			array(
				'name'        => 'hunt_id',
				'values'      => __( 'Numeric hunt ID', 'bonus-hunt-guesser' ),
				'default'     => __( '0 (latest hunt)', 'bonus-hunt-guesser' ),
				'description' => __( 'Choose which hunt to display.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'fields',
				'values'      => 'position, user, guess',
				'default'     => 'position,user,guess',
				'description' => __( 'Columns rendered in the legacy table.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'orderby',
				'values'      => 'guess | user | position',
				'default'     => 'guess',
				'description' => __( 'Initial sort column.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'order',
				'values'      => 'ASC | DESC',
				'default'     => 'ASC',
				'description' => __( 'Initial sort direction.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'paged',
				'values'      => __( 'Positive integer', 'bonus-hunt-guesser' ),
				'default'     => '1',
				'description' => __( 'Set the starting page number.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'per_page',
				'values'      => __( 'Positive integer', 'bonus-hunt-guesser' ),
				'default'     => __( 'bhg_get_per_page( "shortcode_leaderboard" )', 'bonus-hunt-guesser' ),
				'description' => __( 'Override rows per page.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'search',
				'values'      => __( 'Free text', 'bonus-hunt-guesser' ),
				'default'     => __( 'blank', 'bonus-hunt-guesser' ),
				'description' => __( 'Seed the legacy search box.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_leaderboard hunt_id="24" order="ASC"]',
	),
	array(
		'tag'         => '[bhg_best_guessers]',
		'description' => __( 'Tabbed widget that highlights the best guessers overall, monthly, yearly, and all-time.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bhg_best_guessers]',
	),
	array(
		'tag'         => '[bhg_winner_notifications]',
		'description' => __( 'Compact feed of recently closed hunts and their winners.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'limit',
				'values'      => __( 'Positive integer', 'bonus-hunt-guesser' ),
				'default'     => '5',
				'description' => __( 'Number of closed hunts to show.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_winner_notifications limit="3"]',
	),
	array(
		'tag'         => '[bhg_prizes]',
		'description' => __( 'Displays prize cards as a grid or carousel with optional category filters.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'category',
				'values'      => 'cash | casino | coupon | merch | various',
				'default'     => __( 'blank (all categories)', 'bonus-hunt-guesser' ),
				'description' => __( 'Limit results to a single prize category.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'design',
				'values'      => 'grid | carousel',
				'default'     => 'grid',
				'description' => __( 'Choose the display layout.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'size',
				'values'      => 'small | medium | big',
				'default'     => 'medium',
				'description' => __( 'Select the registered prize image size.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'active',
				'values'      => 'yes | no',
				'default'     => 'yes',
				'description' => __( 'Show only active prizes by default.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_prizes category="cash" design="carousel" size="big"]',
	),
	array(
		'tag'         => '[bhg_user_profile]',
		'description' => __( 'Profile summary for the logged-in user with affiliate indicators and edit link.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bhg_user_profile]',
	),
	array(
		'tag'         => '[bhg_advertising]',
		'description' => __( 'Render a specific ad block when ads are enabled.', 'bonus-hunt-guesser' ),
		'aliases'     => array( '[bhg_ad]' ),
		'attributes'  => array(
			array(
				'name'        => 'id',
				'values'      => __( 'Ad ID (also accepts ad="")', 'bonus-hunt-guesser' ),
				'default'     => '0',
				'description' => __( 'Select which ad to render.', 'bonus-hunt-guesser' ),
			),
			array(
				'name'        => 'status',
				'values'      => 'active | inactive | all',
				'default'     => 'active',
				'description' => __( 'Choose whether to render only active ads or include inactive ones.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_advertising ad="7" status="all"]',
	),
	array(
		'tag'         => '[bhg_nav]',
		'description' => __( 'Outputs the appropriate navigation menu for the requested audience.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(
			array(
				'name'        => 'area',
				'values'      => 'admin | user | guest',
				'default'     => 'guest',
				'description' => __( 'Force a specific menu location instead of auto-detecting by role.', 'bonus-hunt-guesser' ),
			),
		),
		'example'     => '[bhg_nav area="admin"]',
	),
	array(
		'tag'         => '[bhg_menu]',
		'description' => __( 'Automatically renders the correct BHG menu based on the current visitor.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bhg_menu]',
	),
	array(
		'tag'         => '[bonus_hunt_login]',
		'description' => __( 'Login prompt that preserves the current page and links to wp-login.php.', 'bonus-hunt-guesser' ),
		'aliases'     => array(),
		'attributes'  => array(),
		'example'     => '[bonus_hunt_login]',
	),
);

?>
<div class="wrap bhg-admin bhg-shortcodes">
		<h1><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>
		<p class="description">
				<?php
				esc_html_e( 'All shortcode tables honour the bhg_orderby, bhg_order, bhg_paged, bhg_search, and bhg_timeline query arguments in addition to the attributes listed below.', 'bonus-hunt-guesser' );
				?>
		</p>
		<p class="description">
				<?php
				esc_html_e( 'Per-page limits use the bhg_get_per_page() helper, so filters such as bhg_hunts_per_page or bhg_user_guesses_per_page can adjust row counts globally.', 'bonus-hunt-guesser' );
				?>
		</p>
		<?php foreach ( $shortcodes as $shortcode ) : ?>
				<section class="bhg-shortcode-card">
						<h2><code><?php echo esc_html( $shortcode['tag'] ); ?></code></h2>
						<p><?php echo esc_html( $shortcode['description'] ); ?></p>
						<?php if ( ! empty( $shortcode['aliases'] ) ) : ?>
								<p><strong><?php esc_html_e( 'Aliases', 'bonus-hunt-guesser' ); ?>:</strong> <?php echo esc_html( implode( ', ', $shortcode['aliases'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $shortcode['attributes'] ) ) : ?>
								<table class="widefat striped">
										<thead>
												<tr>
														<th><?php esc_html_e( 'Attribute', 'bonus-hunt-guesser' ); ?></th>
														<th><?php esc_html_e( 'Values / Default', 'bonus-hunt-guesser' ); ?></th>
														<th><?php esc_html_e( 'Notes', 'bonus-hunt-guesser' ); ?></th>
												</tr>
										</thead>
										<tbody>
												<?php foreach ( $shortcode['attributes'] as $attribute ) : ?>
														<tr>
																<td><code><?php echo esc_html( $attribute['name'] ); ?></code></td>
																<td>
																		<?php
																		$values = isset( $attribute['values'] ) ? $attribute['values'] : '';
																		if ( isset( $attribute['default'] ) && '' !== $attribute['default'] ) {
																				/* translators: %s Default shortcode value. */
																				$values .= ' ' . sprintf( esc_html__( '(default: %s)', 'bonus-hunt-guesser' ), $attribute['default'] );
																		}
																		echo esc_html( $values );
																		?>
																</td>
																<td><?php echo esc_html( $attribute['description'] ); ?></td>
														</tr>
												<?php endforeach; ?>
										</tbody>
								</table>
						<?php endif; ?>
						<?php if ( ! empty( $shortcode['example'] ) ) : ?>
								<p><strong><?php esc_html_e( 'Example', 'bonus-hunt-guesser' ); ?>:</strong></p>
								<pre><code><?php echo esc_html( $shortcode['example'] ); ?></code></pre>
						<?php endif; ?>
				</section>
		<?php endforeach; ?>
</div>
