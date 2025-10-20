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

        <p class="description"><?php echo esc_html( bhg_t( 'tools_intro', 'Use these diagnostics to verify your installation and find helpful shortcuts.' ) ); ?></p>

        <?php
        global $wpdb;

        $table_status_cache = array();

        $fetch_table_status = static function ( $table_name ) use ( $wpdb, &$table_status_cache ) {
                $table_name = (string) $table_name;

                if ( isset( $table_status_cache[ $table_name ] ) ) {
                        return $table_status_cache[ $table_name ];
                }

                $exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name );
                $count  = 0;

                if ( $exists ) {
                        $table_sql = esc_sql( $table_name );
                        $count     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_sql}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                }

                $table_status_cache[ $table_name ] = array(
                        'exists' => $exists,
                        'count'  => $count,
                );

                return $table_status_cache[ $table_name ];
        };

        $count_sources = array(
                array(
                        'label' => bhg_t( 'bonus_hunts', 'Bonus Hunts' ),
                        'table' => $wpdb->prefix . 'bhg_bonus_hunts',
                ),
                array(
                        'label' => bhg_t( 'guesses', 'Guesses' ),
                        'table' => $wpdb->prefix . 'bhg_guesses',
                ),
                array(
                        'label' => bhg_t( 'label_users', 'Users' ),
                        'table' => $wpdb->users,
                ),
                array(
                        'label' => bhg_t( 'menu_ads', 'Ads' ),
                        'table' => $wpdb->prefix . 'bhg_ads',
                ),
                array(
                        'label' => bhg_t( 'menu_tournaments', 'Tournaments' ),
                        'table' => $wpdb->prefix . 'bhg_tournaments',
                ),
        );

        $count_metrics = array();
        foreach ( $count_sources as $source ) {
                $status          = $fetch_table_status( $source['table'] );
                $count_metrics[] = array(
                        'label'  => $source['label'],
                        'count'  => $status['count'],
                        'exists' => $status['exists'],
                );
        }

        $database_status_tables = array(
                array(
                        'label' => bhg_t( 'bonus_hunts', 'Bonus Hunts' ),
                        'table' => $wpdb->prefix . 'bhg_bonus_hunts',
                ),
                array(
                        'label' => bhg_t( 'guesses', 'Guesses' ),
                        'table' => $wpdb->prefix . 'bhg_guesses',
                ),
                array(
                        'label' => bhg_t( 'menu_tournaments', 'Tournaments' ),
                        'table' => $wpdb->prefix . 'bhg_tournaments',
                ),
                array(
                        'label' => bhg_t( 'label_winners', 'Winners' ),
                        'table' => $wpdb->prefix . 'bhg_hunt_winners',
                ),
                array(
                        'label' => bhg_t( 'label_affiliate_websites', 'Affiliate Websites' ),
                        'table' => $wpdb->prefix . 'bhg_affiliate_websites',
                ),
                array(
                        'label' => bhg_t( 'label_prizes', 'Prizes' ),
                        'table' => $wpdb->prefix . 'bhg_prizes',
                ),
                array(
                        'label' => bhg_t( 'translations', 'Translations' ),
                        'table' => $wpdb->prefix . 'bhg_translations',
                ),
                array(
                        'label' => bhg_t( 'label_tournament', 'Tournament' ) . ' ' . bhg_t( 'results', 'Results' ),
                        'table' => $wpdb->prefix . 'bhg_tournament_results',
                ),
        );

        $database_status = array();
        foreach ( $database_status_tables as $table_data ) {
                $status             = $fetch_table_status( $table_data['table'] );
                $table_data['exists'] = $status['exists'];
                $table_data['count']  = $status['count'];
                $database_status[]    = $table_data;
        }

        $shortcode_items = array(
                array(
                        'tag'         => 'bhg_active_hunt',
                        'description' => bhg_t( 'shortcode_active_hunt_help', 'Displays the currently active bonus hunt summary.' ),
                        'aliases'     => array( 'bhg_active' ),
                ),
                array(
                        'tag'         => 'bhg_guess_form',
                        'description' => bhg_t( 'shortcode_guess_form_help', 'Shows the frontend guess submission form for logged-in users.' ),
                ),
                array(
                        'tag'         => 'bhg_leaderboard',
                        'description' => bhg_t( 'shortcode_leaderboard_help', 'Outputs the main leaderboard table with all recorded guesses.' ),
                        'aliases'     => array( 'bonus_hunt_leaderboard' ),
                ),
                array(
                        'tag'         => 'bhg_tournaments',
                        'description' => bhg_t( 'shortcode_tournaments_help', 'Lists active tournaments with sortable leaderboards.' ),
                ),
                array(
                        'tag'         => 'bhg_winner_notifications',
                        'description' => bhg_t( 'shortcode_winner_notifications_help', 'Displays the latest winner notifications for hunts and tournaments.' ),
                ),
                array(
                        'tag'         => 'bhg_user_profile',
                        'description' => bhg_t( 'shortcode_user_profile_help', 'Renders the logged-in user profile with affiliate indicators.' ),
                ),
                array(
                        'tag'         => 'bhg_best_guessers',
                        'description' => bhg_t( 'shortcode_best_guessers_help', 'Highlights top-performing guessers across timeframes.' ),
                ),
                array(
                        'tag'         => 'bhg_user_guesses',
                        'description' => bhg_t( 'shortcode_user_guesses_help', 'Shows a table of guesses submitted by the current user.' ),
                ),
                array(
                        'tag'         => 'bhg_hunts',
                        'description' => bhg_t( 'shortcode_hunts_help', 'Outputs a paginated list of available bonus hunts.' ),
                ),
                array(
                        'tag'         => 'bhg_leaderboards',
                        'description' => bhg_t( 'shortcode_leaderboards_help', 'Displays leaderboard tabs for weekly, monthly, and yearly rankings.' ),
                ),
                array(
                        'tag'         => 'bhg_prizes',
                        'description' => bhg_t( 'shortcode_prizes_help', 'Displays the prize showcase component.' ),
                ),
                array(
                        'tag'         => 'bonus_hunt_login',
                        'description' => bhg_t( 'shortcode_bonus_hunt_login_help', 'Provides a login prompt for visitors with supported providers.' ),
                ),
        );

        $memory_limit = ini_get( 'memory_limit' );
        $memory_limit = false !== $memory_limit ? $memory_limit : '—';

        $environment_data = array(
                array(
                        'label' => bhg_t( 'label_version', 'Version' ),
                        'value' => defined( 'BHG_VERSION' ) ? BHG_VERSION : '—',
                        'type'  => 'text',
                ),
                array(
                        'label' => bhg_t( 'label_wordpress', 'WordPress' ),
                        'value' => get_bloginfo( 'version' ),
                        'type'  => 'text',
                ),
                array(
                        'label' => bhg_t( 'label_php', 'PHP' ),
                        'value' => PHP_VERSION,
                        'type'  => 'text',
                ),
                array(
                        'label' => bhg_t( 'label_mysql', 'MySQL' ),
                        'value' => $wpdb->db_version(),
                        'type'  => 'text',
                ),
                array(
                        'label' => bhg_t( 'label_environment', 'Environment' ),
                        'value' => wp_get_environment_type(),
                        'type'  => 'text',
                ),
                array(
                        'label' => bhg_t( 'label_memory_limit', 'Memory Limit' ),
                        'value' => $memory_limit,
                        'type'  => 'text',
                ),
                array(
                        'label' => bhg_t( 'label_site_url', 'Site URL' ),
                        'value' => home_url(),
                        'type'  => 'url',
                ),
                array(
                        'label' => bhg_t( 'label_admin_email', 'Admin Email' ),
                        'value' => sanitize_email( get_option( 'admin_email' ) ),
                        'type'  => 'email',
                ),
        );

        $support_links = array(
                array(
                        'label' => bhg_t( 'label_documentation', 'Documentation' ),
                        'url'   => 'https://yourdomain.com/docs/bonus-hunt-guesser',
                ),
                array(
                        'label' => bhg_t( 'label_support', 'Support' ),
                        'url'   => 'https://yourdomain.com/support',
                ),
        );
        ?>

        <div class="bhg-tool-grid">
                <div class="card">
                        <h2><?php echo esc_html( bhg_t( 'diagnostics_overview', 'Content Snapshot' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'diagnostics_overview_help', 'High-level counts of the data your site is tracking.' ) ); ?></p>
                        <?php if ( ! empty( $count_metrics ) ) : ?>
                                <table class="widefat striped">
                                        <thead>
                                                <tr>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_metric', 'Metric' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_count', 'Count' ) ); ?></th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ( $count_metrics as $metric ) : ?>
                                                        <tr>
                                                                <td><?php echo esc_html( $metric['label'] ); ?></td>
                                                                <td><?php echo esc_html( number_format_i18n( $metric['count'] ) ); ?></td>
                                                        </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>
                        <?php else : ?>
                                <p><?php echo esc_html( bhg_t( 'nothing_to_show_yet_start_by_creating_a_hunt_or_a_test_user', 'Nothing to show yet. Start by creating a hunt or a test user.' ) ); ?></p>
                        <?php endif; ?>
                </div>

                <div class="card">
                        <h2><?php echo esc_html( bhg_t( 'diagnostics_database_status', 'Database Status' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'diagnostics_database_status_help', 'Quick view of required tables and their row counts.' ) ); ?></p>
                        <table class="widefat striped bhg-database-status-table">
                                <thead>
                                        <tr>
                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_table', 'Table' ) ); ?></th>
                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_status', 'Status' ) ); ?></th>
                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_rows', 'Rows' ) ); ?></th>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?php foreach ( $database_status as $status ) :
                                                $status_text  = $status['exists'] ? bhg_t( 'status_ok', 'OK' ) : bhg_t( 'status_missing', 'Missing' );
                                                $status_class = $status['exists'] ? 'status-ok' : 'status-missing';
                                                ?>
                                                <tr>
                                                        <td>
                                                                <strong><?php echo esc_html( $status['label'] ); ?></strong><br />
                                                                <code><?php echo esc_html( $status['table'] ); ?></code>
                                                        </td>
                                                        <td class="<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_text ); ?></td>
                                                        <td><?php echo esc_html( number_format_i18n( $status['count'] ) ); ?></td>
                                                </tr>
                                        <?php endforeach; ?>
                                </tbody>
                        </table>
                </div>

                <div class="card">
                        <h2><?php echo esc_html( bhg_t( 'tools_shortcodes', 'Shortcode Reference' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'tools_shortcodes_help', 'Copy these shortcodes into pages or blocks to display plugin features.' ) ); ?></p>
                        <ul class="bhg-shortcode-list">
                                <?php foreach ( $shortcode_items as $shortcode ) :
                                        $aliases = isset( $shortcode['aliases'] ) ? array_filter( array_map( 'sanitize_text_field', (array) $shortcode['aliases'] ) ) : array();
                                        ?>
                                        <li>
                                                <code>[<?php echo esc_html( $shortcode['tag'] ); ?>]</code>
                                                <span class="description"><?php echo esc_html( $shortcode['description'] ); ?></span>
                                                <?php if ( ! empty( $aliases ) ) : ?>
                                                        <span class="description"><?php echo esc_html( bhg_t( 'label_aliases', 'Aliases' ) ); ?>: <?php echo esc_html( implode( ', ', $aliases ) ); ?></span>
                                                <?php endif; ?>
                                        </li>
                                <?php endforeach; ?>
                        </ul>
                </div>

                <div class="card">
                        <h2><?php echo esc_html( bhg_t( 'tools_support', 'Support & Environment' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'tools_support_help', 'Share these environment details with support when requesting assistance.' ) ); ?></p>
                        <table class="widefat striped">
                                <tbody>
                                        <?php foreach ( $environment_data as $item ) :
                                                $value = $item['value'];
                                                ?>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( $item['label'] ); ?></th>
                                                        <td>
                                                                <?php
                                                                if ( 'url' === $item['type'] && $value ) {
                                                                        printf(
                                                                                '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
                                                                                esc_url( $value ),
                                                                                esc_html( $value )
                                                                        );
                                                                } elseif ( 'email' === $item['type'] && $value ) {
                                                                        $email = sanitize_email( $value );
                                                                        if ( $email ) {
                                                                                printf(
                                                                                        '<a href="mailto:%1$s">%2$s</a>',
                                                                                        esc_attr( $email ),
                                                                                        esc_html( $email )
                                                                                );
                                                                        } else {
                                                                                echo esc_html( $value );
                                                                        }
                                                                } else {
                                                                        echo esc_html( $value );
                                                                }
                                                                ?>
                                                        </td>
                                                </tr>
                                        <?php endforeach; ?>
                                </tbody>
                        </table>
                        <p class="description"><?php echo esc_html( bhg_t( 'tools_support_contact_description', 'Need help? Visit our knowledge base or contact support using the information below.' ) ); ?></p>
                        <?php if ( ! empty( $support_links ) ) : ?>
                                <h3><?php echo esc_html( bhg_t( 'tools_support_resources', 'Resources' ) ); ?></h3>
                                <ul class="bhg-support-links">
                                        <?php foreach ( $support_links as $link ) :
                                                $url = isset( $link['url'] ) ? esc_url( $link['url'] ) : '';
                                                if ( ! $url ) {
                                                        continue;
                                                }
                                                ?>
                                                <li><a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link['label'] ); ?></a></li>
                                        <?php endforeach; ?>
                                </ul>
                        <?php endif; ?>
                </div>
        </div>
</div>
