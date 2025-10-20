<?php
/**
 * Tools page for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

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

$environment = array(
        'wp_version'       => get_bloginfo( 'version' ),
        'php_version'      => PHP_VERSION,
        'mysql_version'    => $wpdb->db_version(),
        'locale'           => get_locale(),
        'timezone'         => wp_timezone_string(),
        'environment_type' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
);

$docs_url          = 'https://yourdomain.com/bonus-hunt-guesser/docs/';
$translations_url  = admin_url( 'admin.php?page=bhg-translations' );
$support_url       = 'https://yourdomain.com/support/';
?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'bhg_tools', 'BHG Tools' ) ); ?></h1>

        <div class="bhg-tools-grid">
                <section class="card bhg-tool-card">
                        <h2><?php echo esc_html( bhg_t( 'tools_data_overview_title', 'Data Overview' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'tools_data_overview_help', 'Quick glance at the records stored by the plugin.' ) ); ?></p>
                        <table class="widefat striped">
                                <tbody>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_counts_hunts_label', 'Bonus hunts' ) ); ?></th>
                                                <td><?php echo esc_html( number_format_i18n( $hunts ) ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_counts_guesses_label', 'Guesses submitted' ) ); ?></th>
                                                <td><?php echo esc_html( number_format_i18n( $guesses ) ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_counts_users_label', 'Registered users' ) ); ?></th>
                                                <td><?php echo esc_html( number_format_i18n( $users ) ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_counts_ads_label', 'Advertisements' ) ); ?></th>
                                                <td><?php echo esc_html( number_format_i18n( $ads ) ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_counts_tournaments_label', 'Tournaments' ) ); ?></th>
                                                <td><?php echo esc_html( number_format_i18n( $tournaments ) ); ?></td>
                                        </tr>
                                </tbody>
                        </table>
                </section>

                <section class="card bhg-tool-card">
                        <h2><?php echo esc_html( bhg_t( 'tools_environment_title', 'Environment Status' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'tools_environment_help', 'Reference details that are useful for debugging and support.' ) ); ?></p>
                        <table class="widefat striped">
                                <tbody>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_environment_wp_version', 'WordPress version' ) ); ?></th>
                                                <td><?php echo esc_html( $environment['wp_version'] ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_environment_php_version', 'PHP version' ) ); ?></th>
                                                <td><?php echo esc_html( $environment['php_version'] ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_environment_mysql_version', 'Database version' ) ); ?></th>
                                                <td><?php echo esc_html( $environment['mysql_version'] ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_environment_locale', 'Site locale' ) ); ?></th>
                                                <td><?php echo esc_html( $environment['locale'] ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_environment_timezone', 'Timezone' ) ); ?></th>
                                                <td><?php echo esc_html( $environment['timezone'] ); ?></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'tools_environment_environment_type', 'Environment type' ) ); ?></th>
                                                <td><?php echo esc_html( $environment['environment_type'] ); ?></td>
                                        </tr>
                                </tbody>
                        </table>
                </section>

                <section class="card bhg-tool-card">
                        <h2><?php echo esc_html( bhg_t( 'tools_support_resources_title', 'Help & Resources' ) ); ?></h2>
                        <p class="description"><?php echo esc_html( bhg_t( 'tools_support_resources_help', 'Use these quick links to manage and troubleshoot the plugin.' ) ); ?></p>
                        <ul class="bhg-tool-links">
                                <li>
                                        <a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( bhg_t( 'tools_documentation_label', 'View documentation' ) ); ?></a>
                                        <p class="description"><?php echo esc_html( bhg_t( 'tools_resources_docs_description', 'Review setup instructions, shortcode usage, and feature guides.' ) ); ?></p>
                                </li>
                                <li>
                                        <a href="<?php echo esc_url( $translations_url ); ?>"><?php echo esc_html( bhg_t( 'tools_translations_label', 'Manage translations' ) ); ?></a>
                                        <p class="description"><?php echo esc_html( bhg_t( 'tools_resources_translations_description', 'Quickly adjust wording across the plugin interface.' ) ); ?></p>
                                </li>
                                <li>
                                        <a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( bhg_t( 'tools_contact_support_label', 'Contact support' ) ); ?></a>
                                        <p class="description"><?php echo esc_html( bhg_t( 'tools_resources_support_description', 'Need a hand? Share diagnostics with your support team.' ) ); ?></p>
                                </li>
                        </ul>
                </section>
        </div>
</div>

<style>
.bhg-tools-grid {
                display: grid;
                gap: 20px;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                margin-top: 24px;
}
.bhg-tool-card {
                padding: 20px;
}
.bhg-tool-card .description {
                margin: 0 0 16px;
                max-width: 520px;
}
.bhg-tool-card table.widefat {
                margin-top: 8px;
}
.bhg-tool-card table.widefat th {
                width: 55%;
}
.bhg-tool-links {
                list-style: none;
                margin: 0;
                padding: 0;
}
.bhg-tool-links li {
                border-top: 1px solid #dcdcde;
                padding: 12px 0;
}
.bhg-tool-links li:first-child {
                border-top: none;
}
.bhg-tool-links a {
                color: #1d2327;
                font-weight: 600;
                text-decoration: none;
}
.bhg-tool-links a:hover,
.bhg-tool-links a:focus {
                color: #2271b1;
}
.bhg-tool-links .description {
                margin: 4px 0 0;
}
</style>
