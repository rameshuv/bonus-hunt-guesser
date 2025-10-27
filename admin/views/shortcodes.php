<?php
/**
 * Shortcodes reference view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

$shortcodes = array(
	array(
		'tag'         => '[bhg_prizes]',
		'description' => bhg_t( 'shortcode_desc_bhg_prizes', 'Displays a list of prizes with optional design and sizing controls.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_category', '`category` – Filter prizes by category slug.' ),
			bhg_t( 'shortcode_attr_design', '`design` – Choose between grid or carousel layouts.' ),
			bhg_t( 'shortcode_attr_size', '`size` – Render prizes using small, medium, or big imagery.' ),
			bhg_t( 'shortcode_attr_active', '`active` – Limit results to active items (yes/no).' ),
		),
		'example'     => '[bhg_prizes category="cash-money" design="carousel" size="medium" active="yes"]',
	),
	array(
		'tag'         => '[bhg_active_hunt]',
		'description' => bhg_t( 'shortcode_desc_bhg_active_hunt', 'Shows the currently active bonus hunt with summary details.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_none', 'No attributes required.' ),
		),
		'example'     => '[bhg_active_hunt]',
	),
	array(
		'tag'         => '[bhg_leaderboard]',
		'description' => bhg_t( 'shortcode_desc_bhg_leaderboard', 'Outputs the primary leaderboard for recent bonus hunt results.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_layout', '`layout` – Choose the leaderboard layout (overall, monthly, yearly).' ),
		),
		'example'     => '[bhg_leaderboard layout="monthly"]',
	),
	array(
		'tag'         => '[my_bonushunts]',
		'description' => bhg_t( 'shortcode_desc_my_bonushunts', 'Lists hunts the current user has participated in with ranking details.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_user', '`user` – Optional user ID to render another profile.' ),
		),
		'example'     => '[my_bonushunts]',
	),
	array(
		'tag'         => '[my_tournaments]',
		'description' => bhg_t( 'shortcode_desc_my_tournaments', 'Lists tournaments the current user has participated in.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_user', '`user` – Optional user ID to render another profile.' ),
		),
		'example'     => '[my_tournaments user="42"]',
	),
	array(
		'tag'         => '[my_prizes]',
		'description' => bhg_t( 'shortcode_desc_my_prizes', 'Shows prizes won by the current user.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_user', '`user` – Optional user ID to render another profile.' ),
		),
		'example'     => '[my_prizes]',
	),
	array(
		'tag'         => '[my_rankings]',
		'description' => bhg_t( 'shortcode_desc_my_rankings', 'Displays combined rankings for the current user across hunts and tournaments.' ),
		'attributes'  => array(
			bhg_t( 'shortcode_attr_user', '`user` – Optional user ID to render another profile.' ),
		),
		'example'     => '[my_rankings]',
	),
);
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'shortcodes_heading', 'Shortcodes Reference' ) ); ?></h1>
	<p class="description"><?php echo esc_html( bhg_t( 'shortcodes_description', 'Use these shortcodes to display Bonus Hunt data on the frontend.' ) ); ?></p>

	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html( bhg_t( 'shortcodes_table_shortcode', 'Shortcode' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'shortcodes_table_description', 'Description' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'shortcodes_table_attributes', 'Attributes' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'shortcodes_table_example', 'Example' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $shortcodes as $shortcode ) : ?>
			<tr>
				<td><code><?php echo esc_html( $shortcode['tag'] ); ?></code></td>
				<td><?php echo esc_html( $shortcode['description'] ); ?></td>
				<td>
					<ul>
					<?php foreach ( $shortcode['attributes'] as $attribute ) : ?>
						<li><?php echo esc_html( $attribute ); ?></li>
					<?php endforeach; ?>
					</ul>
				</td>
				<td><code><?php echo esc_html( $shortcode['example'] ); ?></code></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
