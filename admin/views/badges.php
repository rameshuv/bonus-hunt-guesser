<?php
/**
 * Badges management screen.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

if ( ! class_exists( 'BHG_Badges' ) ) {
        require_once BHG_PLUGIN_DIR . 'includes/class-bhg-badges.php';
}

global $wpdb;

$edit_id  = isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$badges   = BHG_Badges::all();
$editing  = null;

$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
$aff_rows  = array();
if ( $aff_table ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $aff_rows = (array) $wpdb->get_results( "SELECT id, name FROM {$aff_table} ORDER BY name ASC" );
}

$user_data_options = array(
        'none'             => bhg_t( 'badge_data_none', 'None (use affiliate activation date when set)' ),
        'bonushunt_wins'   => bhg_t( 'badge_data_hunt_wins', 'Total bonus hunt wins' ),
        'tournament_wins'  => bhg_t( 'badge_data_tournament_wins', 'Total tournament wins' ),
        'guesses'          => bhg_t( 'badge_data_guesses', 'Total guesses' ),
        'registration_days'=> bhg_t( 'badge_data_registration_days', 'Days since registration' ),
        'affiliate_days'   => bhg_t( 'badge_data_affiliate_days', 'Days of affiliate active' ),
);

$threshold_options = array( 5, 10, 25, 50, 100, 250, 500, 1000 );

if ( $edit_id ) {
        foreach ( $badges as $row ) {
                if ( (int) $row->id === $edit_id ) {
                        $editing = $row;
                        break;
                }
        }
}

$default_badge = (object) array(
        'id'                => 0,
        'title'             => '',
        'image_id'          => 0,
        'affiliate_site_id' => 0,
        'user_data'         => 'none',
        'threshold'         => 0,
        'active'            => 1,
);

$editing = $editing ? $editing : $default_badge;
$image_url = $editing->image_id ? wp_get_attachment_image_url( $editing->image_id, 'thumbnail' ) : '';
?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'menu_badges', 'Badges' ) ); ?></h1>

        <div class="bhg-admin-grid">
                <div class="bhg-admin-column">
                        <h2><?php echo esc_html( bhg_t( 'existing_badges', 'Existing Badges' ) ); ?></h2>
                        <?php if ( empty( $badges ) ) : ?>
                                <p><?php echo esc_html( bhg_t( 'no_badges_found', 'No badges created yet.' ) ); ?></p>
                        <?php else : ?>
                                <table class="widefat">
                                        <thead>
                                                <tr>
                                                        <th><?php echo esc_html( bhg_t( 'title', 'Title' ) ); ?></th>
                                                        <th><?php echo esc_html( bhg_t( 'status', 'Status' ) ); ?></th>
                                                        <th><?php echo esc_html( bhg_t( 'actions', 'Actions' ) ); ?></th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ( $badges as $badge ) : ?>
                                                        <tr>
                                                                <td><?php echo esc_html( $badge->title ); ?></td>
                                                                <td><?php echo esc_html( ! empty( $badge->active ) ? bhg_t( 'active', 'Active' ) : bhg_t( 'inactive', 'Inactive' ) ); ?></td>
                                                                <td>
                                                                        <a class="button" href="<?php echo esc_url( add_query_arg( 'edit', (int) $badge->id ) ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
                                                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                                                                                <?php wp_nonce_field( 'bhg_delete_badge', 'bhg_delete_badge_nonce' ); ?>
                                                                                <input type="hidden" name="action" value="bhg_delete_badge" />
                                                                                <input type="hidden" name="id" value="<?php echo esc_attr( (int) $badge->id ); ?>" />
                                                                                <button type="submit" class="button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'confirm_delete', 'Delete this item?' ) ); ?>');"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
                                                                        </form>
                                                                </td>
                                                        </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>
                        <?php endif; ?>
                </div>

                <div class="bhg-admin-column">
                        <h2><?php echo esc_html( $editing->id ? bhg_t( 'edit_badge', 'Edit Badge' ) : bhg_t( 'add_badge', 'Add Badge' ) ); ?></h2>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-form">
                                <?php wp_nonce_field( 'bhg_save_badge', 'bhg_save_badge_nonce' ); ?>
                                <input type="hidden" name="action" value="bhg_save_badge" />
                                <input type="hidden" name="id" value="<?php echo esc_attr( (int) $editing->id ); ?>" />

                                <table class="form-table">
                                        <tr>
                                                <th scope="row"><label for="bhg_badge_title"><?php echo esc_html( bhg_t( 'title', 'Title' ) ); ?></label></th>
                                                <td><input type="text" id="bhg_badge_title" name="title" value="<?php echo esc_attr( $editing->title ); ?>" class="regular-text" required /></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'badge_image', 'Badge Image/Icon' ) ); ?></th>
                                                <td>
                                                        <div class="bhg-media-field">
                                                                <input type="hidden" name="image_id" id="bhg_badge_image" value="<?php echo esc_attr( (int) $editing->image_id ); ?>" />
                                                                <div class="bhg-media-preview" id="bhg_badge_preview">
                                                                        <?php if ( $image_url ) : ?>
                                                                                <img src="<?php echo esc_url( $image_url ); ?>" alt="" />
                                                                        <?php endif; ?>
                                                                </div>
                                                                <button type="button" class="button" id="bhg_select_badge_image"><?php echo esc_html( bhg_t( 'select_image', 'Select Image' ) ); ?></button>
                                                                <button type="button" class="button" id="bhg_clear_badge_image"><?php echo esc_html( bhg_t( 'clear', 'Clear' ) ); ?></button>
                                                        </div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_badge_affiliate"><?php echo esc_html( bhg_t( 'affiliate_site', 'Affiliate Site' ) ); ?></label></th>
                                                <td>
                                                        <select id="bhg_badge_affiliate" name="affiliate_site_id">
                                                                <option value="0" <?php selected( 0, (int) $editing->affiliate_site_id ); ?>><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
                                                                <?php foreach ( $aff_rows as $site ) : ?>
                                                                        <option value="<?php echo esc_attr( (int) $site->id ); ?>" <?php selected( (int) $site->id, (int) $editing->affiliate_site_id ); ?>><?php echo esc_html( $site->name ); ?></option>
                                                                <?php endforeach; ?>
                                                        </select>
                                                        <p class="description"><?php echo esc_html( bhg_t( 'badge_affiliate_help', 'When set, qualification uses the affiliate activation date for that site.' ) ); ?></p>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_badge_user_data"><?php echo esc_html( bhg_t( 'badge_user_data', 'User Data' ) ); ?></label></th>
                                                <td>
                                                        <select id="bhg_badge_user_data" name="user_data">
                                                                <?php foreach ( $user_data_options as $key => $label ) : ?>
                                                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $editing->user_data ); ?>><?php echo esc_html( $label ); ?></option>
                                                                <?php endforeach; ?>
                                                        </select>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_badge_threshold"><?php echo esc_html( bhg_t( 'badge_threshold', 'Set Data' ) ); ?></label></th>
                                                <td>
                                                        <select id="bhg_badge_threshold" name="threshold">
                                                                <?php foreach ( $threshold_options as $value ) : ?>
                                                                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $editing->threshold, (int) $value ); ?>><?php echo esc_html( $value ); ?></option>
                                                                <?php endforeach; ?>
                                                        </select>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_badge_active"><?php echo esc_html( bhg_t( 'status', 'Status' ) ); ?></label></th>
                                                <td>
                                                        <label><input type="checkbox" id="bhg_badge_active" name="active" value="1" <?php checked( ! empty( $editing->active ) ); ?> /> <?php echo esc_html( bhg_t( 'active', 'Active' ) ); ?></label>
                                                </td>
                                        </tr>
                                </table>

                                <p><button type="submit" class="button button-primary"><?php echo esc_html( $editing->id ? bhg_t( 'update_badge', 'Update Badge' ) : bhg_t( 'add_badge', 'Add Badge' ) ); ?></button></p>
                        </form>
                </div>
        </div>
</div>

