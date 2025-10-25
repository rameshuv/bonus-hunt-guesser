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
        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$shortcodes = array(
        array(
                'tag'         => 'bhg_active_hunt',
                'description' => bhg_t( 'shortcode_desc_active_hunt', 'Displays details for the active bonus hunt.' ),
                'attributes'  => array(
                        'id' => bhg_t( 'shortcode_attr_active_hunt_id', 'Optional hunt ID override.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_guess_form',
                'description' => bhg_t( 'shortcode_desc_guess_form', 'Shows the public guess submission form for the active hunt.' ),
                'attributes'  => array(
                        'hunt' => bhg_t( 'shortcode_attr_guess_form_hunt', 'Force a specific hunt ID.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_leaderboard',
                'description' => bhg_t( 'shortcode_desc_leaderboard', 'Renders a sortable leaderboard of hunt guesses.' ),
                'attributes'  => array(
                        'hunt'     => bhg_t( 'shortcode_attr_leaderboard_hunt', 'Filter by hunt ID; defaults to active hunt.' ),
                        'status'   => bhg_t( 'shortcode_attr_leaderboard_status', 'Limit hunts by status (open/closed).' ),
                        'timeline' => bhg_t( 'shortcode_attr_leaderboard_timeline', 'Time filter (weekly, monthly, yearly, etc.).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_tournaments',
                'description' => bhg_t( 'shortcode_desc_tournaments', 'Lists tournaments or shows a specific tournament detail view.' ),
                'attributes'  => array(
                        'status'   => bhg_t( 'shortcode_attr_tournaments_status', 'Filter by status (active/archived).' ),
                        'timeline' => bhg_t( 'shortcode_attr_tournaments_timeline', 'Filter by time range (weekly, monthly, quarterly, yearly, alltime).' ),
                        'website'  => bhg_t( 'shortcode_attr_tournaments_website', 'Filter by affiliate website ID.' ),
                        'paged'    => bhg_t( 'shortcode_attr_tournaments_paged', 'Initial page number (defaults to 1).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_prizes',
                'description' => bhg_t( 'shortcode_desc_prizes', 'Displays configured prizes in a grid or carousel layout.' ),
                'attributes'  => array(
                        'category' => bhg_t( 'shortcode_attr_prizes_category', 'Category slug to filter (cash, coupons, etc.).' ),
                        'design'   => bhg_t( 'shortcode_attr_prizes_design', 'Layout style: grid or carousel.' ),
                        'size'     => bhg_t( 'shortcode_attr_prizes_size', 'Image size: small, medium, or big.' ),
                        'active'   => bhg_t( 'shortcode_attr_prizes_active', 'Show only active prizes (yes/no).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_best_guessers',
                'description' => bhg_t( 'shortcode_desc_best_guessers', 'Outputs leaderboard tabs for overall, monthly, and yearly best guessers.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => 'bhg_user_guesses',
                'description' => bhg_t( 'shortcode_desc_user_guesses', 'Shows a paginated table of guesses submitted across hunts.' ),
                'attributes'  => array(
                        'user'   => bhg_t( 'shortcode_attr_user_guesses_user', 'User ID to inspect; defaults to current user.' ),
                        'status' => bhg_t( 'shortcode_attr_user_guesses_status', 'Filter hunts by status (open/closed).' ),
                ),
        ),
        array(
                'tag'         => 'bhg_hunts',
                'description' => bhg_t( 'shortcode_desc_hunts', 'Displays a searchable list of bonus hunts.' ),
                'attributes'  => array(
                        'status' => bhg_t( 'shortcode_attr_hunts_status', 'Filter by hunt status (open/closed).' ),
                        'paged'  => bhg_t( 'shortcode_attr_hunts_paged', 'Initial page number for listings.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_leaderboards',
                'description' => bhg_t( 'shortcode_desc_leaderboards', 'Aggregated leaderboards for hunts and tournaments with filters.' ),
                'attributes'  => array(
                        'type'   => bhg_t( 'shortcode_attr_leaderboards_type', 'Limit to hunts, tournaments, or both.' ),
                        'period' => bhg_t( 'shortcode_attr_leaderboards_period', 'Timeframe key such as weekly, monthly, yearly, or alltime.' ),
                ),
        ),
        array(
                'tag'         => 'my_bonushunts',
                'description' => bhg_t( 'shortcode_desc_my_bonushunts', 'Lists hunts the logged-in user has participated in, including ranking details.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => 'my_tournaments',
                'description' => bhg_t( 'shortcode_desc_my_tournaments', 'Shows tournaments where the logged-in user has earned results.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => 'my_prizes',
                'description' => bhg_t( 'shortcode_desc_my_prizes', 'Displays prizes won by the logged-in user across hunts.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => 'my_rankings',
                'description' => bhg_t( 'shortcode_desc_my_rankings', 'Combined summary of the user\'s hunt and tournament rankings.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => 'bhg_user_profile',
                'description' => bhg_t( 'shortcode_desc_user_profile', 'Outputs the logged-in user\'s profile card with affiliate badges.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => 'bhg_nav',
                'description' => bhg_t( 'shortcode_desc_bhg_nav', 'Renders the configured navigation menu (guest, logged-in, or admin).' ),
                'attributes'  => array(
                        'area' => bhg_t( 'shortcode_attr_bhg_nav_area', 'Menu area key: guest, user, or admin.' ),
                ),
        ),
        array(
                'tag'         => 'bhg_ad',
                'description' => bhg_t( 'shortcode_desc_bhg_ad', 'Displays a specific advertising block by ID.' ),
                'attributes'  => array(
                        'id' => bhg_t( 'shortcode_attr_bhg_ad_id', 'Advertising entry ID.' ),
                ),
        ),
);
?>
<div class="wrap">
        <h1><?php echo esc_html( bhg_t( 'shortcodes_reference', 'Shortcodes Reference' ) ); ?></h1>
        <p class="description"><?php echo esc_html( bhg_t( 'shortcodes_reference_intro', 'Use the shortcodes below to embed hunts, leaderboards, and user dashboards throughout your site.' ) ); ?></p>
        <p class="description"><?php echo esc_html( bhg_t( 'shortcodes_login_note', 'Profile shortcodes require a logged-in user and can be toggled under Settings → Profile Shortcodes.' ) ); ?></p>

        <table class="widefat striped">
                <thead>
                        <tr>
                                <th scope="col"><?php echo esc_html( bhg_t( 'column_shortcode', 'Shortcode' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'column_description', 'Description' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'column_attributes', 'Attributes' ) ); ?></th>
                        </tr>
                </thead>
                <tbody>
                        <?php foreach ( $shortcodes as $item ) : ?>
                                <tr>
                                        <td><code>[<?php echo esc_html( $item['tag'] ); ?>]</code></td>
                                        <td><?php echo esc_html( $item['description'] ); ?></td>
                                        <td>
                                                <?php if ( empty( $item['attributes'] ) ) : ?>
                                                        <em><?php echo esc_html( bhg_t( 'shortcode_no_attributes', 'None' ) ); ?></em>
                                                <?php else : ?>
                                                        <ul style="margin:0;padding-left:18px;">
                                                                <?php foreach ( $item['attributes'] as $attr => $attr_desc ) : ?>
                                                                        <li><code><?php echo esc_html( $attr ); ?></code> – <?php echo esc_html( $attr_desc ); ?></li>
                                                                <?php endforeach; ?>
                                                        </ul>
                                                <?php endif; ?>
                                        </td>
                                </tr>
                        <?php endforeach; ?>
                </tbody>
        </table>
</div>
