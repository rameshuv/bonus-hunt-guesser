<?php
/**
 * Buttons management screen.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

if ( ! class_exists( 'BHG_Buttons' ) ) {
    require_once BHG_PLUGIN_DIR . 'includes/class-bhg-buttons.php';
}

$edit_id    = isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$editing    = null;
$buttons    = BHG_Buttons::get_buttons();
$placement  = isset( $_GET['placement'] ) ? sanitize_key( wp_unslash( $_GET['placement'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$placements = array(
    'none'                    => bhg_t( 'placement_none', 'None' ),
    'active_bonushunt'        => bhg_t( 'placement_active_hunt', 'Active bonushunt details' ),
    'active_tournament'       => bhg_t( 'placement_active_tournament', 'Active tournament details' ),
);

$visibility_options = array(
    'all'            => bhg_t( 'visible_all', 'All' ),
    'guests'         => bhg_t( 'visible_guests', 'Guests' ),
    'logged_in'      => bhg_t( 'visible_logged_in', 'Logged in' ),
    'affiliates'     => bhg_t( 'visible_affiliates', 'Affiliates' ),
    'non_affiliates' => bhg_t( 'visible_non_affiliates', 'Non affiliates' ),
);

$timing_options = array(
    'always'           => bhg_t( 'visible_always', 'Always' ),
    'active_bonushunt' => bhg_t( 'visible_when_active_hunt', 'Active bonushunt' ),
    'active_tournament'=> bhg_t( 'visible_when_active_tournament', 'Active tournament' ),
);

if ( $edit_id ) {
    foreach ( $buttons as $row ) {
        if ( (int) $row->id === $edit_id ) {
            $editing = $row;
            break;
        }
    }
}

$default_button = (object) array(
    'id'               => 0,
    'title'            => '',
    'text'             => bhg_t( 'cta_guess_now', 'Guess Now' ),
    'placement'        => 'none',
    'visible_to'       => 'all',
    'visible_when'     => 'always',
    'link_url'         => '',
    'link_target'      => '_self',
    'background'       => '',
    'background_hover' => '',
    'text_color'       => '',
    'text_hover'       => '',
    'border_color'     => '',
    'text_size'        => '',
    'size'             => 'medium',
    'active'           => 1,
);

$editing = $editing ? $editing : $default_button;
?>
<div class="wrap bhg-wrap">
    <h1><?php echo esc_html( bhg_t( 'menu_buttons', 'Buttons' ) ); ?></h1>

    <div class="bhg-admin-grid">
        <div class="bhg-admin-column">
            <h2><?php echo esc_html( bhg_t( 'existing_buttons', 'Existing Buttons' ) ); ?></h2>
            <?php if ( empty( $buttons ) ) : ?>
                <p><?php echo esc_html( bhg_t( 'no_buttons_found', 'No buttons have been created yet.' ) ); ?></p>
            <?php else : ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php echo esc_html( bhg_t( 'title', 'Title' ) ); ?></th>
                            <th><?php echo esc_html( bhg_t( 'placement', 'Placement' ) ); ?></th>
                            <th><?php echo esc_html( bhg_t( 'status', 'Status' ) ); ?></th>
                            <th><?php echo esc_html( bhg_t( 'actions', 'Actions' ) ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $buttons as $row ) :
                            $placement_label = isset( $placements[ $row->placement ] ) ? $placements[ $row->placement ] : $row->placement;
                            ?>
                            <tr>
                                <td><?php echo esc_html( $row->title ); ?></td>
                                <td><?php echo esc_html( $placement_label ); ?></td>
                                <td><?php echo esc_html( $row->active ? bhg_t( 'active', 'Active' ) : bhg_t( 'inactive', 'Inactive' ) ); ?></td>
                                <td>
                                    <a class="button" href="<?php echo esc_url( add_query_arg( 'edit', (int) $row->id ) ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                                        <?php wp_nonce_field( 'bhg_delete_button', 'bhg_delete_button_nonce' ); ?>
                                        <input type="hidden" name="action" value="bhg_delete_button" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr( (int) $row->id ); ?>" />
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
            <h2><?php echo esc_html( $editing->id ? bhg_t( 'edit_button', 'Edit Button' ) : bhg_t( 'add_button', 'Add Button' ) ); ?></h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'bhg_save_button', 'bhg_save_button_nonce' ); ?>
                <input type="hidden" name="action" value="bhg_save_button" />
                <input type="hidden" name="id" value="<?php echo esc_attr( (int) $editing->id ); ?>" />

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="bhg-button-title"><?php echo esc_html( bhg_t( 'title', 'Title' ) ); ?></label></th>
                            <td><input type="text" name="title" id="bhg-button-title" class="regular-text" value="<?php echo esc_attr( $editing->title ); ?>" required /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-text"><?php echo esc_html( bhg_t( 'button_text', 'Button text' ) ); ?></label></th>
                            <td><input type="text" name="text" id="bhg-button-text" class="regular-text" value="<?php echo esc_attr( $editing->text ); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-placement"><?php echo esc_html( bhg_t( 'placement', 'Placement' ) ); ?></label></th>
                            <td>
                                <select name="placement" id="bhg-button-placement">
                                    <?php foreach ( $placements as $key => $label ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $editing->placement, $key ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-visible-to"><?php echo esc_html( bhg_t( 'visible_to', 'Visible to' ) ); ?></label></th>
                            <td>
                                <select name="visible_to" id="bhg-button-visible-to">
                                    <?php foreach ( $visibility_options as $key => $label ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $editing->visible_to, $key ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-visible-when"><?php echo esc_html( bhg_t( 'visible_when', 'Visible when' ) ); ?></label></th>
                            <td>
                                <select name="visible_when" id="bhg-button-visible-when">
                                    <?php foreach ( $timing_options as $key => $label ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $editing->visible_when, $key ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-link"><?php echo esc_html( bhg_t( 'link_url', 'Link URL' ) ); ?></label></th>
                            <td>
                                <input type="url" name="link_url" id="bhg-button-link" class="regular-text" value="<?php echo esc_attr( $editing->link_url ); ?>" />
                                <p class="description"><?php echo esc_html( bhg_t( 'link_url_help', 'Destination for the button. Leave blank for #.' ) ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-target"><?php echo esc_html( bhg_t( 'link_target', 'Link target' ) ); ?></label></th>
                            <td>
                                <select name="link_target" id="bhg-button-target">
                                    <option value="_self" <?php selected( $editing->link_target, '_self' ); ?>><?php echo esc_html( bhg_t( 'target_self', 'Same window' ) ); ?></option>
                                    <option value="_blank" <?php selected( $editing->link_target, '_blank' ); ?>><?php echo esc_html( bhg_t( 'target_blank', 'New window' ) ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html( bhg_t( 'appearance', 'Appearance' ) ); ?></th>
                            <td>
                                <fieldset>
                                    <label><?php echo esc_html( bhg_t( 'background', 'Background' ) ); ?> <input type="text" name="background" value="<?php echo esc_attr( $editing->background ); ?>" class="small-text" /></label>
                                    <label><?php echo esc_html( bhg_t( 'background_hover', 'Background hover' ) ); ?> <input type="text" name="background_hover" value="<?php echo esc_attr( $editing->background_hover ); ?>" class="small-text" /></label>
                                    <label><?php echo esc_html( bhg_t( 'text_color', 'Text color' ) ); ?> <input type="text" name="text_color" value="<?php echo esc_attr( $editing->text_color ); ?>" class="small-text" /></label>
                                    <label><?php echo esc_html( bhg_t( 'text_hover', 'Text hover' ) ); ?> <input type="text" name="text_hover" value="<?php echo esc_attr( $editing->text_hover ); ?>" class="small-text" /></label>
                                    <label><?php echo esc_html( bhg_t( 'border_color', 'Border color' ) ); ?> <input type="text" name="border_color" value="<?php echo esc_attr( $editing->border_color ); ?>" class="small-text" /></label>
                                    <label><?php echo esc_html( bhg_t( 'text_size_px', 'Text size (px)' ) ); ?> <input type="number" name="text_size" value="<?php echo esc_attr( $editing->text_size ); ?>" class="small-text" /></label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-size"><?php echo esc_html( bhg_t( 'size', 'Size' ) ); ?></label></th>
                            <td>
                                <select name="size" id="bhg-button-size">
                                    <option value="small" <?php selected( $editing->size, 'small' ); ?>><?php echo esc_html( bhg_t( 'size_small', 'Small' ) ); ?></option>
                                    <option value="medium" <?php selected( $editing->size, 'medium' ); ?>><?php echo esc_html( bhg_t( 'size_medium', 'Medium' ) ); ?></option>
                                    <option value="big" <?php selected( $editing->size, 'big' ); ?>><?php echo esc_html( bhg_t( 'size_big', 'Big' ) ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bhg-button-active"><?php echo esc_html( bhg_t( 'active', 'Active' ) ); ?></label></th>
                            <td><input type="checkbox" name="active" id="bhg-button-active" value="1" <?php checked( (int) $editing->active, 1 ); ?> /></td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button( $editing->id ? bhg_t( 'update_button', 'Update Button' ) : bhg_t( 'add_button', 'Add Button' ) ); ?>
            </form>
        </div>
    </div>
</div>
