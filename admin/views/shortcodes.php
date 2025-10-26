<?php
/**
 * Admin Shortcodes reference.
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
                'tag'         => '[bhg_active_hunt]',
                'description' => bhg_t( 'shortcode_active_hunt_desc', 'Displays the current bonus hunt with start balance and prize information.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => '[bhg_leaderboard]',
                'description' => bhg_t( 'shortcode_leaderboard_desc', 'Shows the active leaderboard with search, sorting, and filters.' ),
                'attributes'  => array(
                        'type'   => bhg_t( 'shortcode_attr_type', 'Optional leaderboard view (overall, monthly, yearly, alltime).' ),
                        'hunt'   => bhg_t( 'shortcode_attr_hunt', 'Limit the leaderboard to a specific hunt ID.' ),
                        'limit'  => bhg_t( 'shortcode_attr_limit', 'Maximum number of rows to display.' ),
                ),
        ),
        array(
                'tag'         => '[bhg_prizes]',
                'description' => bhg_t( 'shortcode_prizes_desc', 'Renders prize grids or carousels.' ),
                'attributes'  => array(
                        'category' => bhg_t( 'shortcode_attr_category', 'Filter by prize category (cash money, casino money, coupons, merchandise, various).' ),
                        'design'   => bhg_t( 'shortcode_attr_design', 'Choose between "grid" or "carousel" layouts.' ),
                        'size'     => bhg_t( 'shortcode_attr_size', 'Display size (small, medium, big).' ),
                        'active'   => bhg_t( 'shortcode_attr_active', 'Set to "yes" for active prizes or "no" for archived ones.' ),
                ),
        ),
        array(
                'tag'         => '[my_bonushunts]',
                'description' => bhg_t( 'shortcode_my_bonushunts_desc', 'Lists hunts the logged-in user has participated in with guess and result details.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => '[my_tournaments]',
                'description' => bhg_t( 'shortcode_my_tournaments_desc', 'Shows tournament standings for the current user including rank and wins.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => '[my_prizes]',
                'description' => bhg_t( 'shortcode_my_prizes_desc', 'Displays prizes linked to hunts the user has won.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => '[my_rankings]',
                'description' => bhg_t( 'shortcode_my_rankings_desc', 'Provides a combined overview of hunt and tournament rankings for the user.' ),
                'attributes'  => array(),
        ),
        array(
                'tag'         => '[bhg_tournaments]',
                'description' => bhg_t( 'shortcode_tournaments_desc', 'Outputs the tournaments directory with filters and detail views.' ),
                'attributes'  => array(
                        'status'   => bhg_t( 'shortcode_attr_status', 'Filter by status (active, closed, all).' ),
                        'timeline' => bhg_t( 'shortcode_attr_timeline', 'Filter by preset timeline or tournament type.' ),
                        'paged'    => bhg_t( 'shortcode_attr_paged', 'Initial page number for listings.' ),
                ),
        ),
);
?>
<div class="wrap">
        <h1><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>
        <p class="description"><?php echo esc_html( bhg_t( 'shortcode_overview_intro', 'Use the following shortcodes to embed Bonus Hunt Guesser features on your site.' ) ); ?></p>
        <table class="widefat striped">
                <thead>
                        <tr>
                                <th><?php echo esc_html( bhg_t( 'label_shortcode', 'Shortcode' ) ); ?></th>
                                <th><?php echo esc_html( bhg_t( 'description', 'Description' ) ); ?></th>
                                <th><?php echo esc_html( bhg_t( 'shortcode_attributes', 'Attributes' ) ); ?></th>
                        </tr>
                </thead>
                <tbody>
                        <?php foreach ( $shortcodes as $shortcode ) : ?>
                        <tr>
                                <td><code><?php echo esc_html( $shortcode['tag'] ); ?></code></td>
                                <td><?php echo esc_html( $shortcode['description'] ); ?></td>
                                <td>
                                        <?php if ( empty( $shortcode['attributes'] ) ) : ?>
                                                <em><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></em>
                                        <?php else : ?>
                                                <ul class="bhg-shortcode-attrs">
                                                        <?php foreach ( $shortcode['attributes'] as $attribute => $note ) : ?>
                                                                <li><code><?php echo esc_html( $attribute ); ?></code> – <?php echo esc_html( $note ); ?></li>
                                                        <?php endforeach; ?>
                                                </ul>
                                        <?php endif; ?>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
        </table>
</div>
