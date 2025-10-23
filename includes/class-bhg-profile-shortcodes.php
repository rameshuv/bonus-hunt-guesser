<?php
/**
 * Profile shortcode handlers for Bonus Hunt Guesser.
 *
 * Provides front-end shortcodes for displaying the current user's
 * participation history, tournament standings, and won prizes.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

if ( ! class_exists( 'BHG_Profile_Shortcodes' ) ) {

/**
 * Handles user-centric shortcode rendering.
 */
class BHG_Profile_Shortcodes {

/**
 * Cache for hunt participation rows keyed by user ID.
 *
 * @var array<int,array>
 */
private $hunt_rows_cache = array();

/**
 * Cache for tournament result rows keyed by user ID.
 *
 * @var array<int,array>
 */
private $tournament_rows_cache = array();

/**
 * Constructor. Registers shortcodes.
 */
public function __construct() {
add_shortcode( 'my_bonushunts', array( $this, 'my_bonushunts_shortcode' ) );
add_shortcode( 'my_tournaments', array( $this, 'my_tournaments_shortcode' ) );
add_shortcode( 'my_prizes', array( $this, 'my_prizes_shortcode' ) );
add_shortcode( 'my_rankings', array( $this, 'my_rankings_shortcode' ) );
}

/**
 * Render the My Bonus Hunts table.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_bonushunts_shortcode( $atts ) {
unset( $atts );

if ( ! $this->is_section_enabled( 'my_bonushunts' ) ) {
return '';
}

if ( ! is_user_logged_in() ) {
return $this->login_message();
}

$this->enqueue_assets();

$user_id = get_current_user_id();
$rows    = $this->fetch_hunt_rows( $user_id );

if ( empty( $rows ) ) {
return '<div class="bhg-profile-section bhg-my-bonushunts"><p>' . esc_html( bhg_t( 'notice_no_personal_hunts', 'You have not participated in any bonus hunts yet.' ) ) . '</p></div>';
}

$dash = bhg_t( 'label_emdash', '—' );

ob_start();
echo '<div class="bhg-profile-section bhg-my-bonushunts">';
echo '<h3>' . esc_html( bhg_t( 'label_my_bonushunts', 'My Bonus Hunts' ) ) . '</h3>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonushunt', 'Bonushunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_status', 'Status' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_guess_date', 'Guess Date' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $rows as $row ) {
$row_classes = array();

if ( isset( $row->winner_position ) && (int) $row->winner_position > 0 ) {
$row_classes[] = 'bhg-profile-winner';
}

$class_attr = $row_classes ? ' class="' . esc_attr( implode( ' ', $row_classes ) ) . '"' : '';

echo '<tr' . $class_attr . '>';
echo '<td>' . esc_html( $row->title ) . '</td>';
echo '<td>' . esc_html( bhg_format_currency( (float) $row->guess ) ) . '</td>';

$diff_display = ( isset( $row->diff ) && null !== $row->diff ) ? bhg_format_currency( (float) $row->diff ) : $dash;
echo '<td>' . esc_html( $diff_display ) . '</td>';

$rank_display = ( isset( $row->user_rank ) && null !== $row->user_rank ) ? (int) $row->user_rank : $dash;
echo '<td>' . esc_html( $rank_display ) . '</td>';

$position_display = ( isset( $row->winner_position ) && $row->winner_position ) ? (int) $row->winner_position : $dash;
echo '<td>' . esc_html( $position_display ) . '</td>';

$points_display = ( isset( $row->winner_points ) && $row->winner_points ) ? (int) $row->winner_points : 0;
echo '<td>' . esc_html( $points_display ) . '</td>';

$status_label = isset( $row->status ) ? bhg_t( $row->status, ucfirst( (string) $row->status ) ) : '';
echo '<td>' . esc_html( $status_label ) . '</td>';

$final_display = ( isset( $row->final_balance ) && null !== $row->final_balance ) ? bhg_format_currency( (float) $row->final_balance ) : $dash;
echo '<td>' . esc_html( $final_display ) . '</td>';

$guess_date = $this->format_datetime( isset( $row->guess_created_at ) ? $row->guess_created_at : '' );
echo '<td>' . esc_html( $guess_date ? $guess_date : $dash ) . '</td>';
echo '</tr>';
}

echo '</tbody></table></div>';

return ob_get_clean();
}

/**
 * Render the My Tournaments table.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_tournaments_shortcode( $atts ) {
unset( $atts );

if ( ! $this->is_section_enabled( 'my_tournaments' ) ) {
return '';
}

if ( ! is_user_logged_in() ) {
return $this->login_message();
}

$this->enqueue_assets();

$user_id = get_current_user_id();
$rows    = $this->fetch_tournament_rows( $user_id );

if ( empty( $rows ) ) {
return '<div class="bhg-profile-section bhg-my-tournaments"><p>' . esc_html( bhg_t( 'notice_no_personal_tournaments', 'You do not have any tournament results yet.' ) ) . '</p></div>';
}

$dash = bhg_t( 'label_emdash', '—' );

ob_start();
echo '<div class="bhg-profile-section bhg-my-tournaments">';
echo '<h3>' . esc_html( bhg_t( 'label_my_tournaments', 'My Tournaments' ) ) . '</h3>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'wins', 'Wins' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_type', 'Type' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_status', 'Status' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_last_result', 'Last Result' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $rows as $row ) {
$rank_display = ( isset( $row->user_rank ) && null !== $row->user_rank ) ? (int) $row->user_rank : $dash;
$type_label   = isset( $row->type ) ? bhg_t( $row->type, ucfirst( (string) $row->type ) ) : '';
$status_label = isset( $row->status ) ? bhg_t( $row->status, ucfirst( (string) $row->status ) ) : '';
$last_result  = $row->last_win_date ? $row->last_win_date : ( $row->end_date ? $row->end_date : ( $row->start_date ? $row->start_date : $row->sort_date ) );
$last_display = $this->format_datetime( $last_result );

echo '<tr>';
echo '<td>' . esc_html( $row->title ) . '</td>';
echo '<td>' . esc_html( $rank_display ) . '</td>';
echo '<td>' . esc_html( isset( $row->wins ) ? (int) $row->wins : 0 ) . '</td>';
echo '<td>' . esc_html( isset( $row->points ) ? (int) $row->points : 0 ) . '</td>';
echo '<td>' . esc_html( $type_label ) . '</td>';
echo '<td>' . esc_html( $status_label ) . '</td>';
echo '<td>' . esc_html( $last_display ? $last_display : $dash ) . '</td>';
echo '</tr>';
}

echo '</tbody></table></div>';

return ob_get_clean();
}

/**
 * Render the My Prizes table.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_prizes_shortcode( $atts ) {
unset( $atts );

if ( ! $this->is_section_enabled( 'my_prizes' ) ) {
return '';
}

if ( ! is_user_logged_in() ) {
return $this->login_message();
}

$this->enqueue_assets();

$user_id = get_current_user_id();
$rows    = $this->fetch_hunt_rows( $user_id );

$winners = array();
foreach ( $rows as $row ) {
if ( isset( $row->winner_position ) && (int) $row->winner_position > 0 ) {
$winners[] = $row;
}
}

if ( empty( $winners ) ) {
return '<div class="bhg-profile-section bhg-my-prizes"><p>' . esc_html( bhg_t( 'notice_no_personal_prizes', 'You have not won any prizes yet.' ) ) . '</p></div>';
}

$dash        = bhg_t( 'label_emdash', '—' );
$prize_cache = array();

ob_start();
echo '<div class="bhg-profile-section bhg-my-prizes">';
echo '<h3>' . esc_html( bhg_t( 'label_my_prizes', 'My Prizes' ) ) . '</h3>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonushunt', 'Bonushunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_prize', 'Prize' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_prize_category', 'Prize Category' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_image', 'Image' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $winners as $winner ) {
$hunt_id = isset( $winner->hunt_id ) ? (int) $winner->hunt_id : ( isset( $winner->id ) ? (int) $winner->id : 0 );

if ( ! isset( $prize_cache[ $hunt_id ] ) ) {
$prize_cache[ $hunt_id ] = array();
if ( class_exists( 'BHG_Prizes' ) && $hunt_id > 0 ) {
$prize_cache[ $hunt_id ] = BHG_Prizes::get_prizes_for_hunt( $hunt_id );
}
}

$prize     = null;
$prize_set = $prize_cache[ $hunt_id ];
$index     = ( isset( $winner->winner_position ) ? (int) $winner->winner_position : 1 ) - 1;

if ( isset( $prize_set[ $index ] ) ) {
$prize = $prize_set[ $index ];
}

$prize_title = $prize && isset( $prize->title ) ? $prize->title : $dash;
$category    = $prize && isset( $prize->category ) ? ucwords( str_replace( '_', ' ', $prize->category ) ) : $dash;
$diff_display = ( isset( $winner->winner_diff ) && null !== $winner->winner_diff ) ? bhg_format_currency( (float) $winner->winner_diff ) : ( ( isset( $winner->diff ) && null !== $winner->diff ) ? bhg_format_currency( (float) $winner->diff ) : $dash );
$closed_at    = $this->format_datetime( isset( $winner->closed_at ) ? $winner->closed_at : $winner->sort_date );

echo '<tr>';
echo '<td>' . esc_html( $winner->title ) . '</td>';
echo '<td>' . esc_html( isset( $winner->winner_position ) ? (int) $winner->winner_position : $dash ) . '</td>';
echo '<td>' . esc_html( $prize_title ) . '</td>';
echo '<td>' . esc_html( $category ) . '</td>';
echo '<td>' . esc_html( isset( $winner->winner_points ) ? (int) $winner->winner_points : 0 ) . '</td>';
echo '<td>' . esc_html( $diff_display ) . '</td>';
echo '<td>' . esc_html( $closed_at ? $closed_at : $dash ) . '</td>';
echo '<td class="bhg-profile-prize-thumb">';
if ( $prize && class_exists( 'BHG_Prizes' ) ) {
$image_url = BHG_Prizes::get_image_url( $prize, 'small', false );
if ( $image_url ) {
echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $prize->title ) . '" />';
} else {
echo esc_html( $dash );
}
} else {
echo esc_html( $dash );
}
echo '</td>';
echo '</tr>';
}

echo '</tbody></table></div>';

return ob_get_clean();
}

/**
 * Render the My Rankings tables.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_rankings_shortcode( $atts ) {
unset( $atts );

if ( ! $this->is_section_enabled( 'my_rankings' ) ) {
return '';
}

if ( ! is_user_logged_in() ) {
return $this->login_message();
}

$this->enqueue_assets();

$user_id = get_current_user_id();
$hunts   = $this->fetch_hunt_rows( $user_id );
$tours   = $this->fetch_tournament_rows( $user_id );

$dash               = bhg_t( 'label_emdash', '—' );
$ranked_hunts       = array();
$ranked_tournaments = array();

foreach ( $hunts as $row ) {
if ( isset( $row->user_rank ) && null !== $row->user_rank ) {
$ranked_hunts[] = $row;
}
}

foreach ( $tours as $row ) {
if ( isset( $row->user_rank ) && null !== $row->user_rank ) {
$ranked_tournaments[] = $row;
}
}

if ( empty( $ranked_hunts ) && empty( $ranked_tournaments ) ) {
return '<div class="bhg-profile-section bhg-my-rankings"><p>' . esc_html( bhg_t( 'notice_no_personal_rankings', 'No ranking data available yet.' ) ) . '</p></div>';
}

ob_start();
echo '<div class="bhg-profile-section bhg-my-rankings">';
echo '<h3>' . esc_html( bhg_t( 'label_my_rankings', 'My Rankings' ) ) . '</h3>';

if ( ! empty( $ranked_hunts ) ) {
echo '<h4>' . esc_html( bhg_t( 'label_bonus_hunt_rankings', 'Bonus Hunt Rankings' ) ) . '</h4>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonushunt', 'Bonushunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $ranked_hunts as $row ) {
$guess_display = bhg_format_currency( (float) $row->guess );
$diff_display  = ( isset( $row->diff ) && null !== $row->diff ) ? bhg_format_currency( (float) $row->diff ) : $dash;
$closed_at     = $this->format_datetime( isset( $row->closed_at ) ? $row->closed_at : $row->sort_date );

echo '<tr>';
echo '<td>' . esc_html( $row->title ) . '</td>';
echo '<td>' . esc_html( (int) $row->user_rank ) . '</td>';
echo '<td>' . esc_html( isset( $row->winner_position ) && $row->winner_position ? (int) $row->winner_position : $dash ) . '</td>';
echo '<td>' . esc_html( isset( $row->winner_points ) ? (int) $row->winner_points : 0 ) . '</td>';
echo '<td>' . esc_html( $guess_display ) . '</td>';
echo '<td>' . esc_html( $diff_display ) . '</td>';
echo '<td>' . esc_html( $closed_at ? $closed_at : $dash ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}

if ( ! empty( $ranked_tournaments ) ) {
echo '<h4>' . esc_html( bhg_t( 'label_tournament_rankings', 'Tournament Rankings' ) ) . '</h4>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'wins', 'Wins' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_last_result', 'Last Result' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $ranked_tournaments as $row ) {
$last_result  = $row->last_win_date ? $row->last_win_date : ( $row->end_date ? $row->end_date : ( $row->start_date ? $row->start_date : $row->sort_date ) );
$last_display = $this->format_datetime( $last_result );

echo '<tr>';
echo '<td>' . esc_html( $row->title ) . '</td>';
echo '<td>' . esc_html( (int) $row->user_rank ) . '</td>';
echo '<td>' . esc_html( isset( $row->wins ) ? (int) $row->wins : 0 ) . '</td>';
echo '<td>' . esc_html( isset( $row->points ) ? (int) $row->points : 0 ) . '</td>';
echo '<td>' . esc_html( $last_display ? $last_display : $dash ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Check if a profile section is enabled via settings.
 *
 * @param string $section Section key.
 * @return bool
 */
private function is_section_enabled( $section ) {
$map = array(
'my_bonushunts' => 'show_my_bonushunts',
'my_tournaments' => 'show_my_tournaments',
'my_prizes'      => 'show_my_prizes',
'my_rankings'    => 'show_my_rankings',
);

$settings = get_option( 'bhg_plugin_settings', array() );

if ( ! isset( $map[ $section ] ) ) {
return true;
}

$option_key = $map[ $section ];
$value      = isset( $settings[ $option_key ] ) ? (int) $settings[ $option_key ] : 1;
$enabled    = 1 === $value;

return (bool) apply_filters( 'bhg_profile_section_enabled', $enabled, $section, $settings );
}

/**
 * Enqueue shared shortcode assets.
 */
private function enqueue_assets() {
wp_enqueue_style(
'bhg-shortcodes',
( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
array(),
defined( 'BHG_VERSION' ) ? BHG_VERSION : null
);
}

/**
 * Sanitize a table name to ensure it belongs to this plugin.
 *
 * @param string $table Table name.
 * @return string
 */
private function sanitize_table( $table ) {
global $wpdb;

$allowed = array(
$wpdb->prefix . 'bhg_bonus_hunts',
$wpdb->prefix . 'bhg_guesses',
$wpdb->prefix . 'bhg_hunt_winners',
$wpdb->prefix . 'bhg_tournaments',
$wpdb->prefix . 'bhg_tournament_results',
$wpdb->users,
$wpdb->usermeta,
);

return in_array( $table, $allowed, true ) ? $table : '';
}

/**
 * Fetch hunt participation rows for the user.
 *
 * @param int $user_id User ID.
 * @return array
 */
private function fetch_hunt_rows( $user_id ) {
$user_id = (int) $user_id;
if ( $user_id <= 0 ) {
return array();
}

if ( isset( $this->hunt_rows_cache[ $user_id ] ) ) {
return $this->hunt_rows_cache[ $user_id ];
}

global $wpdb;
$hunts_table   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
$guesses_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
$winners_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );

if ( ! $hunts_table || ! $guesses_table ) {
$this->hunt_rows_cache[ $user_id ] = array();
return array();
}

$join_winners   = '';
$select_winners = 'NULL AS winner_position, NULL AS winner_points, NULL AS winner_diff, ';
if ( $winners_table ) {
$join_winners   = sprintf( ' LEFT JOIN %s w ON w.hunt_id = h.id AND w.user_id = %%d', $winners_table );
$select_winners = 'w.position AS winner_position, w.points AS winner_points, w.diff AS winner_diff, ';
}

$sql = sprintf(
'SELECT h.id AS hunt_id, h.title, h.status, h.final_balance, h.closed_at, h.updated_at, h.created_at, h.winners_count, h.affiliate_site_id, g.id AS guess_id, g.guess, g.created_at AS guess_created_at, CASE WHEN h.final_balance IS NOT NULL THEN ABS(h.final_balance - g.guess) ELSE NULL END AS diff, CASE WHEN h.final_balance IS NOT NULL THEN (
SELECT COUNT(*) + 1
FROM %1$s g2
WHERE g2.hunt_id = h.id
AND (
ABS(h.final_balance - g2.guess) < ABS(h.final_balance - g.guess)
OR ( ABS(h.final_balance - g2.guess) = ABS(h.final_balance - g.guess) AND g2.id < g.id )
)
) ELSE NULL END AS user_rank, %4$sCOALESCE(h.closed_at, h.updated_at, h.created_at) AS sort_date
FROM %2$s h
INNER JOIN %1$s g ON g.hunt_id = h.id AND g.user_id = %%d%3$s
ORDER BY sort_date DESC, h.id DESC',
$guesses_table,
$hunts_table,
$join_winners,
$select_winners
);

$params = array( $user_id );
if ( $winners_table ) {
$params[] = $user_id;
}

$rows = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );
$this->hunt_rows_cache[ $user_id ] = $rows ? $rows : array();

return $this->hunt_rows_cache[ $user_id ];
}

/**
 * Fetch tournament result rows for the user.
 *
 * @param int $user_id User ID.
 * @return array
 */
private function fetch_tournament_rows( $user_id ) {
$user_id = (int) $user_id;
if ( $user_id <= 0 ) {
return array();
}

if ( isset( $this->tournament_rows_cache[ $user_id ] ) ) {
return $this->tournament_rows_cache[ $user_id ];
}

global $wpdb;
$results_table     = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
$tournaments_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );

if ( ! $results_table || ! $tournaments_table ) {
$this->tournament_rows_cache[ $user_id ] = array();
return array();
}

$sql = sprintf(
'SELECT t.id AS tournament_id, t.title, t.type, t.status, t.start_date, t.end_date, t.created_at, tr.id AS result_id, tr.wins, tr.points, tr.last_win_date, (
SELECT COUNT(*) + 1
FROM %1$s tr2
WHERE tr2.tournament_id = tr.tournament_id
AND (
tr2.points > tr.points
OR ( tr2.points = tr.points AND tr2.wins > tr.wins )
OR ( tr2.points = tr.points AND tr2.wins = tr.wins AND tr2.id < tr.id )
)
) AS user_rank,
COALESCE(tr.last_win_date, t.end_date, t.start_date, t.created_at) AS sort_date
FROM %1$s tr
INNER JOIN %2$s t ON t.id = tr.tournament_id
WHERE tr.user_id = %%d
ORDER BY sort_date DESC, tr.id DESC',
$results_table,
$tournaments_table
);

$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );
$this->tournament_rows_cache[ $user_id ] = $rows ? $rows : array();

return $this->tournament_rows_cache[ $user_id ];
}

/**
 * Format a MySQL datetime value.
 *
 * @param string $datetime Datetime string.
 * @return string
 */
private function format_datetime( $datetime ) {
$datetime = (string) $datetime;

if ( '' === $datetime ) {
return '';
}

$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$formatted = mysql2date( $format, $datetime, true );

if ( ! $formatted ) {
return $datetime;
}

return $formatted;
}

/**
 * Render a login prompt for anonymous users.
 *
 * @return string
 */
private function login_message() {
return '<p>' . esc_html( bhg_t( 'notice_login_view_content', 'Please log in to view this content.' ) ) . '</p>';
}
}
}
