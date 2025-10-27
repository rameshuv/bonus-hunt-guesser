<?php
/**
 * Admin view that documents available shortcodes.
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
                'aliases'     => array( '[bhg_active]' ),
                'description' => bhg_t( 'sc_desc_active_hunt', 'Displays the currently active bonus hunt with participant list and prizes.' ),
                'example'     => '[bhg_active_hunt prize_layout="carousel" prize_size="big"]',
                'attributes'  => array(
                        array(
                                'name'        => 'prize_layout',
                                'description' => bhg_t( 'sc_attr_prize_layout', 'Choose how prizes are displayed beneath the hunt details.' ),
                                'default'     => 'grid',
                                'options'     => 'grid, carousel',
                        ),
                        array(
                                'name'        => 'prize_size',
                                'description' => bhg_t( 'sc_attr_prize_size', 'Controls the card size used for prize tiles.' ),
                                'default'     => 'medium',
                                'options'     => 'small, medium, big',
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_guess_form]',
                'description' => bhg_t( 'sc_desc_guess_form', 'Outputs the guess submission form for logged-in users.' ),
                'example'     => '[bhg_guess_form hunt_id="12"]',
                'attributes'  => array(
                        array(
                                'name'        => 'hunt_id',
                                'description' => bhg_t( 'sc_attr_hunt_id', 'Force the form to target a specific hunt (defaults to the only open hunt).' ),
                                'default'     => bhg_t( 'sc_default_detect', 'auto-detect' ),
                                'options'     => bhg_t( 'sc_options_hunt_id', 'Numeric hunt ID' ),
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_leaderboard]',
                'aliases'     => array( '[bonus_hunt_leaderboard]' ),
                'description' => bhg_t( 'sc_desc_leaderboard', 'Shows a sortable leaderboard for a bonus hunt.' ),
                'example'     => '[bhg_leaderboard hunt_id="42" orderby="user" order="DESC"]',
                'attributes'  => array(
                        array(
                                'name'        => 'hunt_id',
                                'description' => bhg_t( 'sc_attr_leaderboard_hunt_id', 'Select which hunt to show (latest hunt is used when omitted).' ),
                                'default'     => bhg_t( 'sc_default_latest', 'latest closed or active hunt' ),
                                'options'     => bhg_t( 'sc_options_hunt_id', 'Numeric hunt ID' ),
                        ),
                        array(
                                'name'        => 'orderby',
                                'description' => bhg_t( 'sc_attr_orderby', 'Column used to sort the leaderboard.' ),
                                'default'     => 'guess',
                                'options'     => 'guess, user, position',
                        ),
                        array(
                                'name'        => 'order',
                                'description' => bhg_t( 'sc_attr_order_direction', 'Ascending or descending order.' ),
                                'default'     => 'ASC',
                                'options'     => 'ASC, DESC',
                        ),
                        array(
                                'name'        => 'fields',
                                'description' => bhg_t( 'sc_attr_fields', 'Comma separated list of columns to display.' ),
                                'default'     => 'position,user,guess',
                                'options'     => 'position, user, guess',
                        ),
                        array(
                                'name'        => 'paged',
                                'description' => bhg_t( 'sc_attr_paged', 'Starting page number when pagination is enabled.' ),
                                'default'     => '1',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'per_page',
                                'description' => bhg_t( 'sc_attr_per_page', 'Number of rows per page.' ),
                                'default'     => '30',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'search',
                                'description' => bhg_t( 'sc_attr_search', 'Prefill the search box with a username filter.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_text', 'Text value' ),
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_user_guesses]',
                'description' => bhg_t( 'sc_desc_user_guesses', 'Lists guesses for a hunt with optional affiliate and site filters.' ),
                'example'     => '[bhg_user_guesses fields="hunt,user,guess,final" orderby="difference"]',
                'attributes'  => array(
                        array(
                                'name'        => 'id',
                                'description' => bhg_t( 'sc_attr_user_hunt_id', 'Limit results to a specific hunt ID (defaults to the latest).' ),
                                'default'     => bhg_t( 'sc_default_latest', 'latest closed or active hunt' ),
                                'options'     => bhg_t( 'sc_options_hunt_id', 'Numeric hunt ID' ),
                        ),
                        array(
                                'name'        => 'aff',
                                'description' => bhg_t( 'sc_attr_aff_filter', 'Filter to affiliate (yes) or non-affiliate (no) users.' ),
                                'default'     => '',
                                'options'     => 'yes, no',
                        ),
                        array(
                                'name'        => 'website',
                                'description' => bhg_t( 'sc_attr_aff_site', 'Show guesses linked to a specific affiliate website ID.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'status',
                                'description' => bhg_t( 'sc_attr_hunt_status', 'Filter hunts by status before listing guesses.' ),
                                'default'     => '',
                                'options'     => 'open, closed',
                        ),
                        array(
                                'name'        => 'timeline',
                                'description' => bhg_t( 'sc_attr_timeline', 'Restrict guesses to a time window.' ),
                                'default'     => '',
                                'options'     => 'day, week, month, quarter, year, last_year, all_time',
                        ),
                        array(
                                'name'        => 'fields',
                                'description' => bhg_t( 'sc_attr_fields', 'Comma separated list of columns to display.' ),
                                'default'     => 'hunt,user,guess,final',
                                'options'     => 'hunt, user, guess, final, site',
                        ),
                        array(
                                'name'        => 'orderby',
                                'description' => bhg_t( 'sc_attr_orderby', 'Column used to sort the results.' ),
                                'default'     => 'guess',
                                'options'     => 'guess, hunt, final, time, difference',
                        ),
                        array(
                                'name'        => 'order',
                                'description' => bhg_t( 'sc_attr_order_direction', 'Ascending or descending order.' ),
                                'default'     => 'DESC',
                                'options'     => 'ASC, DESC',
                        ),
                        array(
                                'name'        => 'paged',
                                'description' => bhg_t( 'sc_attr_paged', 'Starting page number when pagination is enabled.' ),
                                'default'     => '1',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'search',
                                'description' => bhg_t( 'sc_attr_search', 'Prefill the hunt title search box.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_text', 'Text value' ),
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_hunts]',
                'description' => bhg_t( 'sc_desc_hunts', 'Outputs a paginated table of hunts.' ),
                'example'     => '[bhg_hunts status="closed" fields="title,start,final" timeline="year"]',
                'attributes'  => array(
                        array(
                                'name'        => 'id',
                                'description' => bhg_t( 'sc_attr_specific_hunt', 'Only display a single hunt by ID.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_hunt_id', 'Numeric hunt ID' ),
                        ),
                        array(
                                'name'        => 'aff',
                                'description' => bhg_t( 'sc_attr_aff_filter', 'Filter hunts by affiliate participation.' ),
                                'default'     => 'no',
                                'options'     => 'yes, no',
                        ),
                        array(
                                'name'        => 'website',
                                'description' => bhg_t( 'sc_attr_aff_site', 'Restrict hunts to a specific affiliate website ID.' ),
                                'default'     => '0',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'status',
                                'description' => bhg_t( 'sc_attr_hunt_status', 'Filter hunts by status.' ),
                                'default'     => '',
                                'options'     => 'open, closed',
                        ),
                        array(
                                'name'        => 'timeline',
                                'description' => bhg_t( 'sc_attr_timeline', 'Restrict hunts to a time window.' ),
                                'default'     => '',
                                'options'     => 'day, week, month, quarter, year, last_year, all_time',
                        ),
                        array(
                                'name'        => 'fields',
                                'description' => bhg_t( 'sc_attr_fields', 'Columns to display in the table.' ),
                                'default'     => 'title,start,final,status',
                                'options'     => 'title, start, final, winners, status, user, site',
                        ),
                        array(
                                'name'        => 'orderby',
                                'description' => bhg_t( 'sc_attr_orderby', 'Column used to sort the table.' ),
                                'default'     => 'created',
                                'options'     => 'created, title, final, status',
                        ),
                        array(
                                'name'        => 'order',
                                'description' => bhg_t( 'sc_attr_order_direction', 'Ascending or descending order.' ),
                                'default'     => 'DESC',
                                'options'     => 'ASC, DESC',
                        ),
                        array(
                                'name'        => 'paged',
                                'description' => bhg_t( 'sc_attr_paged', 'Starting page number for pagination.' ),
                                'default'     => '1',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'search',
                                'description' => bhg_t( 'sc_attr_search', 'Prefill the search box with a keyword.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_text', 'Text value' ),
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_leaderboards]',
                'description' => bhg_t( 'sc_desc_leaderboards', 'Builds aggregate leaderboards for winners with optional filters.' ),
                'example'     => '[bhg_leaderboards timeline="monthly" fields="pos,user,wins,avg"]',
                'attributes'  => array(
                        array(
                                'name'        => 'fields',
                                'description' => bhg_t( 'sc_attr_fields', 'Columns to display in the table.' ),
                                'default'     => 'pos,user,wins,avg_hunt,avg_tournament',
                                'options'     => 'pos, user, wins, avg_hunt, avg_tournament, aff, site, hunt, tournament',
                        ),
                        array(
                                'name'        => 'ranking',
                                'description' => bhg_t( 'sc_attr_ranking', 'Maximum number of rows to display.' ),
                                'default'     => '1',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer (max 10)' ),
                        ),
                        array(
                                'name'        => 'timeline',
                                'description' => bhg_t( 'sc_attr_timeline', 'Restrict results to a time window.' ),
                                'default'     => 'all_time',
                                'options'     => 'day, week, month, quarter, year, last_year, all_time',
                        ),
                        array(
                                'name'        => 'orderby',
                                'description' => bhg_t( 'sc_attr_orderby', 'Column used to sort the leaderboard.' ),
                                'default'     => 'wins',
                                'options'     => 'wins, user, avg_hunt, avg_tournament',
                        ),
                        array(
                                'name'        => 'order',
                                'description' => bhg_t( 'sc_attr_order_direction', 'Ascending or descending order.' ),
                                'default'     => 'DESC',
                                'options'     => 'ASC, DESC',
                        ),
                        array(
                                'name'        => 'search',
                                'description' => bhg_t( 'sc_attr_search', 'Prefill the user search box.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_text', 'Text value' ),
                        ),
                        array(
                                'name'        => 'tournament',
                                'description' => bhg_t( 'sc_attr_tournament_filter', 'Limit results to a specific tournament ID.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'bonushunt',
                                'description' => bhg_t( 'sc_attr_hunt_filter', 'Limit results to a specific hunt ID.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_hunt_id', 'Numeric hunt ID' ),
                        ),
                        array(
                                'name'        => 'website',
                                'description' => bhg_t( 'sc_attr_aff_site', 'Limit results to an affiliate website ID.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'aff',
                                'description' => bhg_t( 'sc_attr_aff_filter', 'Filter results to affiliate or non-affiliate users.' ),
                                'default'     => '',
                                'options'     => 'yes, no',
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_tournaments]',
                'description' => bhg_t( 'sc_desc_tournaments', 'Lists tournaments with filters and detail view links.' ),
                'example'     => '[bhg_tournaments status="active" timeline="month"]',
                'attributes'  => array(
                        array(
                                'name'        => 'status',
                                'description' => bhg_t( 'sc_attr_tournament_status', 'Filter tournaments by status.' ),
                                'default'     => 'active',
                                'options'     => 'active, closed, draft',
                        ),
                        array(
                                'name'        => 'tournament',
                                'description' => bhg_t( 'sc_attr_tournament_filter', 'Focus on a specific tournament ID.' ),
                                'default'     => '0',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'website',
                                'description' => bhg_t( 'sc_attr_aff_site', 'Limit results to an affiliate website ID.' ),
                                'default'     => '0',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'timeline',
                                'description' => bhg_t( 'sc_attr_timeline', 'Restrict tournaments to a time window.' ),
                                'default'     => '',
                                'options'     => 'day, week, month, quarter, year, last_year, all_time',
                        ),
                        array(
                                'name'        => 'paged',
                                'description' => bhg_t( 'sc_attr_paged', 'Starting page number when pagination is enabled.' ),
                                'default'     => '1',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                        array(
                                'name'        => 'orderby',
                                'description' => bhg_t( 'sc_attr_orderby', 'Column used to sort the table.' ),
                                'default'     => 'start_date',
                                'options'     => 'title, start_date, end_date, status, type',
                        ),
                        array(
                                'name'        => 'order',
                                'description' => bhg_t( 'sc_attr_order_direction', 'Ascending or descending order.' ),
                                'default'     => 'desc',
                                'options'     => 'asc, desc',
                        ),
                        array(
                                'name'        => 'search',
                                'description' => bhg_t( 'sc_attr_search', 'Prefill the search box with a keyword.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_text', 'Text value' ),
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_prizes]',
                'description' => bhg_t( 'sc_desc_prizes', 'Displays prizes in a grid or carousel layout.' ),
                'example'     => '[bhg_prizes category="cash" design="carousel" size="big" active="yes"]',
                'attributes'  => array(
                        array(
                                'name'        => 'category',
                                'description' => bhg_t( 'sc_attr_prize_category', 'Filter prizes by category slug.' ),
                                'default'     => '',
                                'options'     => bhg_t( 'sc_options_text', 'Category slug (cash_money, casino_money, coupons, merchandise, various)' ),
                        ),
                        array(
                                'name'        => 'design',
                                'description' => bhg_t( 'sc_attr_prize_layout', 'Choose the presentation layout.' ),
                                'default'     => bhg_t( 'sc_default_inherit', 'inherit from settings' ),
                                'options'     => 'grid, carousel',
                        ),
                        array(
                                'name'        => 'size',
                                'description' => bhg_t( 'sc_attr_prize_size', 'Control the prize card size.' ),
                                'default'     => bhg_t( 'sc_default_inherit', 'inherit from settings' ),
                                'options'     => 'small, medium, big',
                        ),
                        array(
                                'name'        => 'active',
                                'description' => bhg_t( 'sc_attr_prize_active', 'Limit the list to active prizes only.' ),
                                'default'     => 'yes',
                                'options'     => 'yes, no',
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_winner_notifications]',
                'description' => bhg_t( 'sc_desc_winner_notifications', 'Shows the latest closed hunts and their winners.' ),
                'example'     => '[bhg_winner_notifications limit="10"]',
                'attributes'  => array(
                        array(
                                'name'        => 'limit',
                                'description' => bhg_t( 'sc_attr_limit', 'Maximum number of hunts to display.' ),
                                'default'     => '5',
                                'options'     => bhg_t( 'sc_options_number', 'Positive integer' ),
                        ),
                ),
        ),
        array(
                'tag'         => '[bhg_user_profile]',
                'description' => bhg_t( 'sc_desc_user_profile', 'Shows the logged-in user profile summary with affiliate status.' ),
                'example'     => '[bhg_user_profile]',
        ),
        array(
                'tag'         => '[my_bonushunts]',
                'description' => bhg_t( 'sc_desc_my_bonushunts', 'Lists hunts the current user has joined.' ),
                'example'     => '[my_bonushunts]',
        ),
        array(
                'tag'         => '[my_tournaments]',
                'description' => bhg_t( 'sc_desc_my_tournaments', 'Lists tournaments the current user has participated in.' ),
                'example'     => '[my_tournaments]',
        ),
        array(
                'tag'         => '[my_prizes]',
                'description' => bhg_t( 'sc_desc_my_prizes', 'Displays prizes won by the current user.' ),
                'example'     => '[my_prizes]',
        ),
        array(
                'tag'         => '[my_rankings]',
                'description' => bhg_t( 'sc_desc_my_rankings', 'Shows personal rankings across hunts and tournaments.' ),
                'example'     => '[my_rankings]',
        ),
        array(
                'tag'         => '[bhg_best_guessers]',
                'description' => bhg_t( 'sc_desc_best_guessers', 'Tabbed leaderboard highlighting top performers (overall, monthly, yearly, all-time).' ),
                'example'     => '[bhg_best_guessers]',
        ),
        array(
                'tag'         => '[bonus_hunt_login]',
                'description' => bhg_t( 'sc_desc_login_hint', 'Prompts visitors to log in before interacting with bonus hunts.' ),
                'example'     => '[bonus_hunt_login]',
        ),
);

$shortcodes = apply_filters( 'bhg_admin_shortcode_reference', $shortcodes );
if ( ! is_array( $shortcodes ) ) {
        $shortcodes = array();
}
?>
<div class="wrap">
        <h1><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>

        <div class="notice notice-info inline">
                <p><strong><?php echo esc_html( bhg_t( 'sc_info_title', 'Info & Help' ) ); ?>:</strong> <?php echo esc_html( bhg_t( 'sc_info_blurb', 'Copy any shortcode into the block editor or widgets. Attributes are optional—omit them to use the defaults shown below.' ) ); ?></p>
        </div>

        <?php if ( empty( $shortcodes ) ) : ?>
                <p><?php echo esc_html( bhg_t( 'sc_no_shortcodes_found', 'No shortcodes are registered.' ) ); ?></p>
        <?php else : ?>
                <?php foreach ( $shortcodes as $shortcode ) :
                        $tag         = isset( $shortcode['tag'] ) ? (string) $shortcode['tag'] : '';
                        $description = isset( $shortcode['description'] ) ? (string) $shortcode['description'] : '';
                        $example     = isset( $shortcode['example'] ) ? (string) $shortcode['example'] : '';
                        $aliases     = isset( $shortcode['aliases'] ) && is_array( $shortcode['aliases'] ) ? array_filter( array_map( 'strval', $shortcode['aliases'] ) ) : array();
                        $attributes  = isset( $shortcode['attributes'] ) && is_array( $shortcode['attributes'] ) ? $shortcode['attributes'] : array();
                        ?>
                        <div class="bhg-shortcode-card">
                                <h2><code><?php echo esc_html( $tag ); ?></code></h2>

                                <?php if ( $aliases ) : ?>
                                        <p class="description">
                                                <?php echo esc_html( bhg_t( 'sc_aliases_label', 'Aliases' ) ); ?>:
                                                <?php
                                                $alias_markup = array();
                                                foreach ( $aliases as $alias ) {
                                                        $alias_markup[] = '<code>' . esc_html( $alias ) . '</code>';
                                                }
                                                echo wp_kses_post( implode( ', ', $alias_markup ) );
                                                ?>
                                        </p>
                                <?php endif; ?>

                                <?php if ( $description ) : ?>
                                        <p><?php echo esc_html( $description ); ?></p>
                                <?php endif; ?>

                                <?php if ( $example ) : ?>
                                        <p><strong><?php echo esc_html( bhg_t( 'sc_example', 'Example' ) ); ?>:</strong> <code><?php echo esc_html( $example ); ?></code></p>
                                <?php endif; ?>

                                <?php if ( $attributes ) : ?>
                                        <table class="widefat striped">
                                                <thead>
                                                        <tr>
                                                                <th><?php echo esc_html( bhg_t( 'sc_attribute', 'Attribute' ) ); ?></th>
                                                                <th><?php echo esc_html( bhg_t( 'sc_description', 'Description' ) ); ?></th>
                                                                <th><?php echo esc_html( bhg_t( 'sc_default', 'Default' ) ); ?></th>
                                                                <th><?php echo esc_html( bhg_t( 'sc_options', 'Options' ) ); ?></th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        <?php foreach ( $attributes as $attribute ) :
                                                                $name        = isset( $attribute['name'] ) ? (string) $attribute['name'] : '';
                                                                $attr_desc   = isset( $attribute['description'] ) ? (string) $attribute['description'] : '';
                                                                $default     = isset( $attribute['default'] ) ? (string) $attribute['default'] : '';
                                                                $options     = isset( $attribute['options'] ) ? (string) $attribute['options'] : '';
                                                                ?>
                                                                <tr>
                                                                        <td><code><?php echo esc_html( $name ); ?></code></td>
                                                                        <td><?php echo esc_html( $attr_desc ); ?></td>
                                                                        <td><?php echo esc_html( $default ); ?></td>
                                                                        <td><?php echo esc_html( $options ); ?></td>
                                                                </tr>
                                                        <?php endforeach; ?>
                                                </tbody>
                                        </table>
                                <?php endif; ?>
                        </div>
                <?php endforeach; ?>
        <?php endif; ?>
</div>
