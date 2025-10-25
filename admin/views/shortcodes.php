<?php
/**
 * Shortcode reference view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$shortcodes = array(
        array(
                'tag'         => 'bhg_active_hunt',
                'description' => bhg_t( 'shortcode_active_hunt_desc', 'Displays details and leaderboards for the currently active bonus hunt.' ),
                'attributes'  => array(
                        'id'        => bhg_t( 'shortcode_attr_hunt_id', 'Optional hunt ID to force a specific hunt.' ),
                        'layout'    => bhg_t( 'shortcode_attr_layout', 'Set to "grid" or "carousel" for prize display; defaults to grid.' ),
                        'page_size' => bhg_t( 'shortcode_attr_page_size', 'Number of leaderboard entries per page (default 25).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_guess_form',
                'description' => bhg_t( 'shortcode_guess_form_desc', 'Displays the guess submission form for logged-in users.' ),
                'attributes'  => array(
                        'redirect' => bhg_t( 'shortcode_attr_redirect', 'Override the post-submit redirect URL.' ),
                        'hunt'     => bhg_t( 'shortcode_attr_hunt_override', 'Display the form for a specific hunt ID.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_leaderboard',
                'description' => bhg_t( 'shortcode_leaderboard_desc', 'Shows a paginated leaderboard with filters for hunts and tournaments.' ),
                'attributes'  => array(
                        'hunt'       => bhg_t( 'shortcode_attr_leaderboard_hunt', 'Filter to a specific hunt ID.' ),
                        'tournament' => bhg_t( 'shortcode_attr_leaderboard_tournament', 'Filter to a specific tournament ID.' ),
                        'aff'        => bhg_t( 'shortcode_attr_affiliate_filter', 'Filter by affiliate status (yes|no).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_tournaments',
                'description' => bhg_t( 'shortcode_tournaments_desc', 'Lists tournaments with standings and filters.' ),
                'attributes'  => array(
                        'status' => bhg_t( 'shortcode_attr_tournament_status', 'Filter by status (active|archived).' ),
                        'type'   => bhg_t( 'shortcode_attr_tournament_type', 'Filter by type (monthly, yearly, etc.).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_winner_notifications',
                'description' => bhg_t( 'shortcode_winner_notifications_desc', 'Outputs a compact list of recent hunt winners.' ),
                'attributes'  => array(
                        'limit' => bhg_t( 'shortcode_attr_limit', 'Number of hunts to show (default 5).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_user_profile',
                'description' => bhg_t( 'shortcode_user_profile_desc', 'Displays the logged-in user profile summary with affiliate badges.' ),
                'attributes'  => array(
                        'show_prizes' => bhg_t( 'shortcode_attr_show_prizes', 'Toggle prize history (yes|no).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_best_guessers',
                'description' => bhg_t( 'shortcode_best_guessers_desc', 'Tabbed leaderboard of overall, monthly, yearly, and all-time performers.' ),
                'attributes'  => array(
                        'limit' => bhg_t( 'shortcode_attr_limit', 'Number of rows per tab (default 25).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_user_guesses',
                'description' => bhg_t( 'shortcode_user_guesses_desc', 'Lists guesses placed by the current user.' ),
                'attributes'  => array(
                        'hunt' => bhg_t( 'shortcode_attr_hunt_filter', 'Filter guesses to a specific hunt ID.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_hunts',
                'description' => bhg_t( 'shortcode_hunts_desc', 'Outputs a table of hunts with filters for status and affiliate site.' ),
                'attributes'  => array(
                        'status' => bhg_t( 'shortcode_attr_status', 'Filter hunts by status (open|closed).' ),
                        'site'   => bhg_t( 'shortcode_attr_site', 'Filter by affiliate site slug.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_leaderboards',
                'description' => bhg_t( 'shortcode_leaderboards_desc', 'Multi-leaderboard view combining hunt, tournament, and affiliate filters.' ),
                'attributes'  => array(
                        'timeline' => bhg_t( 'shortcode_attr_timeline', 'Timeline keyword (month|year|quarter|all_time).' ),
                        'site'     => bhg_t( 'shortcode_attr_site', 'Filter by affiliate site slug.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_prizes',
                'description' => bhg_t( 'shortcode_prizes_desc', 'Displays prizes in a grid or carousel layout.' ),
                'attributes'  => array(
                        'category' => bhg_t( 'shortcode_attr_prize_category', 'Filter by prize category slug.' ),
                        'layout'   => bhg_t( 'shortcode_attr_layout', 'Layout mode (grid|carousel).' ),
                        'size'     => bhg_t( 'shortcode_attr_prize_size', 'Image size (small|medium|big).' ),
                        'active'   => bhg_t( 'shortcode_attr_active_filter', 'Filter by active status (yes|no).' ),
                ),
        ),
);
?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'shortcode_reference', 'Shortcode Reference' ) ); ?></h1>
        <p><?php echo esc_html( bhg_t( 'shortcode_reference_intro', 'Use the following shortcodes to embed plugin features into pages or posts.' ) ); ?></p>
        <table class="widefat striped">
                <thead>
                        <tr>
                                <th><?php echo esc_html( bhg_t( 'shortcode', 'Shortcode' ) ); ?></th>
                                <th><?php echo esc_html( bhg_t( 'description', 'Description' ) ); ?></th>
                                <th><?php echo esc_html( bhg_t( 'attributes', 'Attributes' ) ); ?></th>
                        </tr>
                </thead>
                <tbody>
                <?php foreach ( $shortcodes as $row ) : ?>
                        <tr>
                                <td><code>[<?php echo esc_html( $row['tag'] ); ?>]</code></td>
                                <td><?php echo esc_html( $row['description'] ); ?></td>
                                <td>
                                        <?php if ( ! empty( $row['attributes'] ) ) : ?>
                                                <ul>
                                                        <?php foreach ( $row['attributes'] as $key => $label ) : ?>
                                                                <li><code><?php echo esc_html( $key ); ?></code> — <?php echo esc_html( $label ); ?></li>
                                                        <?php endforeach; ?>
                                                </ul>
                                        <?php else : ?>
                                                <em><?php echo esc_html( bhg_t( 'no_attributes', 'No attributes.' ) ); ?></em>
                                        <?php endif; ?>
                                </td>
                        </tr>
                <?php endforeach; ?>
                </tbody>
        </table>
</div>
