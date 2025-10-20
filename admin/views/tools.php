<?php
/**
 * Tools page for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bhg-wrap">
	<h1><?php echo esc_html( bhg_t( 'bhg_tools', 'BHG Tools' ) ); ?></h1>

        <?php
        global $wpdb;

        $hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
        $guesses_table     = esc_sql( $wpdb->prefix . 'bhg_guesses' );
        $users_table       = esc_sql( $wpdb->users );
        $ads_table         = esc_sql( $wpdb->prefix . 'bhg_ads' );
        $tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

        $hunts       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$hunts_table}" );
        $guesses     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$guesses_table}" );
        $users       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$users_table}" );
        $ads         = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$ads_table}" );
        $tournaments = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tournaments_table}" );
	?>

	<div class="card" style="max-width:900px;padding:16px;margin-top:12px;">
		<h2><?php echo esc_html( bhg_t( 'diagnostics', 'Diagnostics' ) ); ?></h2>
		<?php if ( ( $hunts + $guesses + $users + $ads + $tournaments ) > 0 ) : ?>
			<ul>
				<li><?php echo esc_html( bhg_t( 'hunts', 'Hunts:' ) ); ?> <?php echo esc_html( number_format_i18n( $hunts ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'guesses_2', 'Guesses:' ) ); ?> <?php echo esc_html( number_format_i18n( $guesses ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'users', 'Users:' ) ); ?> <?php echo esc_html( number_format_i18n( $users ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'ads', 'Ads:' ) ); ?> <?php echo esc_html( number_format_i18n( $ads ) ); ?></li>
				<li><?php echo esc_html( bhg_t( 'tournaments', 'Tournaments:' ) ); ?> <?php echo esc_html( number_format_i18n( $tournaments ) ); ?></li>
			</ul>
		<?php else : ?>
			<p><?php echo esc_html( bhg_t( 'nothing_to_show_yet_start_by_creating_a_hunt_or_a_test_user', 'Nothing to show yet. Start by creating a hunt or a test user.' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>

<div class="card" style="max-width:900px;padding:16px;margin-top:20px;">
<h2><?php echo esc_html( bhg_t( 'info_help_shortcodes', 'Info & Help – Shortcodes' ) ); ?></h2>
<p><?php echo esc_html( bhg_t( 'info_help_shortcodes_description', 'Reference for all available shortcodes and their attributes.' ) ); ?></p>
<table class="widefat striped">
<thead>
<tr>
<th><?php echo esc_html( bhg_t( 'label_shortcode', 'Shortcode' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_attributes', 'Attributes' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_description', 'Description' ) ); ?></th>
</tr>
</thead>
<tbody>
<?php
$shortcodes = array(
array(
'name'        => '[bhg_active_hunt]',
'attributes'  => 'layout="grid|carousel", hunt="ID"',
'description' => bhg_t( 'sc_desc_active_hunt', 'Shows the current active bonus hunt with leaderboard.' ),
),
array(
'name'        => '[bhg_guess_form]',
'attributes'  => 'hunt_id="ID", redirect="URL"',
'description' => bhg_t( 'sc_desc_guess_form', 'Displays the guess submission form. Optional redirect override.' ),
),
array(
'name'        => '[bhg_user_guesses]',
'attributes'  => 'id="hunt ID", aff="yes|no", website="affiliate ID"',
'description' => bhg_t( 'sc_desc_user_guesses', 'List guesses for a hunt with affiliate filters.' ),
),
array(
'name'        => '[bhg_hunts]',
'attributes'  => 'status="active|closed", bonushunt="ID", website="affiliate ID", timeline="day|week|month|year"',
'description' => bhg_t( 'sc_desc_hunts', 'Overview of hunts with filtering and timeline options.' ),
),
array(
'name'        => '[bhg_tournaments]',
'attributes'  => 'status="active|closed", tournament="ID", website="affiliate ID", timeline="day|week|month|year"',
'description' => bhg_t( 'sc_desc_tournaments', 'Displays tournaments with filters and pagination.' ),
),
array(
'name'        => '[bhg_leaderboards]',
'attributes'  => 'tournament="ID", bonushunt="ID", aff="yes|no", website="ID", ranking="1-10", timeline="day|week|month|year"',
'description' => bhg_t( 'sc_desc_leaderboards', 'Leaderboard of winners with custom columns and filters.' ),
),
array(
'name'        => '[bhg_prizes]',
'attributes'  => 'category="", design="grid|carousel", size="small|medium|big", active="yes|no"',
'description' => bhg_t( 'sc_desc_prizes', 'Standalone prize listings for prizes manager.' ),
),
array(
'name'        => '[bhg_advertising]',
'attributes'  => 'status="active|inactive", ad="ID"',
'description' => bhg_t( 'sc_desc_advertising', 'Outputs advertising blocks by ID or status.' ),
),
array(
'name'        => '[my_bonushunts]',
'attributes'  => '',
'description' => bhg_t( 'sc_desc_my_bonushunts', 'Logged-in users see hunts they joined and their ranking.' ),
),
array(
'name'        => '[my_tournaments]',
'attributes'  => '',
'description' => bhg_t( 'sc_desc_my_tournaments', 'Lists tournament participation with wins and ranking.' ),
),
array(
'name'        => '[my_prizes]',
'attributes'  => '',
'description' => bhg_t( 'sc_desc_my_prizes', 'Displays all prizes earned by the current user.' ),
),
array(
'name'        => '[my_rankings]',
'attributes'  => '',
'description' => bhg_t( 'sc_desc_my_rankings', 'Combined view of hunt and tournament rankings for the user.' ),
),
);
foreach ( $shortcodes as $shortcode ) :
?>
<tr>
<td><code><?php echo esc_html( $shortcode['name'] ); ?></code></td>
<td><?php echo '' !== $shortcode['attributes'] ? esc_html( $shortcode['attributes'] ) : '&mdash;'; ?></td>
<td><?php echo esc_html( $shortcode['description'] ); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
