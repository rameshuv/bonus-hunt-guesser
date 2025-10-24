<?php
/**
 * Admin view for managing prizes.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Prizes' ) ) {
	return;
}

$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
$prize  = null;
$edit   = false;

if ( 'edit' === $action ) {
        $prize_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        if ( $prize_id ) {
                $prize = BHG_Prizes::get_prize( $prize_id );
                if ( $prize ) {
                        $edit = true;
                }
        }
}

$categories   = BHG_Prizes::get_categories();
$css_defaults = BHG_Prizes::default_css_settings();
$css_values   = $css_defaults;

if ( $prize ) {
        $css_values['border']       = isset( $prize->css_border ) ? $prize->css_border : '';
        $css_values['border_color'] = isset( $prize->css_border_color ) ? $prize->css_border_color : '';
        $css_values['padding']      = isset( $prize->css_padding ) ? $prize->css_padding : '';
        $css_values['margin']       = isset( $prize->css_margin ) ? $prize->css_margin : '';
        $css_values['background']   = isset( $prize->css_background ) ? $prize->css_background : '';
}

$prizes = BHG_Prizes::get_prizes();

?>
<div class="wrap bhg-wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html( $edit ? bhg_t( 'edit_prize', 'Edit Prize' ) : bhg_t( 'add_new_prize', 'Add New Prize' ) ); ?></h1>
        <?php if ( $edit ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-prizes' ) ); ?>" class="page-title-action"><?php echo esc_html( bhg_t( 'add_new', 'Add New' ) ); ?></a>
        <?php endif; ?>
        <hr class="wp-header-end" />

        <div class="bhg-prizes-grid">
                <div class="bhg-prize-form">
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                <?php wp_nonce_field( 'bhg_save_prize', 'bhg_save_prize_nonce' ); ?>
                                <input type="hidden" name="action" value="bhg_save_prize" />
                                <input type="hidden" name="prize_id" value="<?php echo esc_attr( $edit ? (int) $prize->id : 0 ); ?>" />

                                <table class="form-table" role="presentation">
                                        <tbody>
                                                <tr>
                                                        <th scope="row"><label for="bhg_prize_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
                                                        <td><input type="text" class="regular-text" id="bhg_prize_title" name="title" value="<?php echo esc_attr( $prize ? $prize->title : '' ); ?>" required /></td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><label for="bhg_prize_description"><?php echo esc_html( bhg_t( 'description', 'Description' ) ); ?></label></th>
                                                        <td><textarea class="large-text" rows="5" id="bhg_prize_description" name="description"><?php echo esc_textarea( $prize ? $prize->description : '' ); ?></textarea></td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><label for="bhg_prize_category"><?php echo esc_html( bhg_t( 'category', 'Category' ) ); ?></label></th>
                                                        <td>
                                                                <select id="bhg_prize_category" name="category">
                                                                        <?php foreach ( $categories as $category ) : ?>
                                                                                <option value="<?php echo esc_attr( $category ); ?>" <?php selected( $prize ? $prize->category : '', $category ); ?>><?php echo esc_html( ucwords( str_replace( '_', ' ', $category ) ) ); ?></option>
                                                                        <?php endforeach; ?>
                                                                </select>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'images', 'Images' ) ); ?></th>
                                                        <td>
                                                                <?php
                                                                $image_fields = array(
                                                                        'image_small'  => bhg_t( 'image_small', 'Small' ),
                                                                        'image_medium' => bhg_t( 'image_medium', 'Medium' ),
                                                                        'image_large'  => bhg_t( 'image_large', 'Big' ),
                                                                );
                                                                foreach ( $image_fields as $field => $label ) :
                                                                        $attachment_id = $prize ? absint( $prize->$field ) : 0;
                                                                        $image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';
                                                                        ?>
                                                                        <div class="bhg-prize-image-field">
                                                                                <label for="bhg_<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $label ); ?></label>
                                                                                <div class="bhg-media-control" data-target="bhg_<?php echo esc_attr( $field ); ?>">
                                                                                        <div class="bhg-media-preview">
                                                                                                <?php if ( $image_url ) : ?>
                                                                                                        <img src="<?php echo esc_url( $image_url ); ?>" alt="" />
                                                                                                <?php else : ?>
                                                                                                        <span class="bhg-media-placeholder"><?php echo esc_html( bhg_t( 'no_image_selected', 'No image selected' ) ); ?></span>
                                                                                                <?php endif; ?>
                                                                                        </div>
                                                                                        <div class="bhg-media-buttons">
                                                                                                <button type="button" class="button bhg-select-media" data-target="bhg_<?php echo esc_attr( $field ); ?>"><?php echo esc_html( bhg_t( 'select_image', 'Select Image' ) ); ?></button>
                                                                                                <button type="button" class="button bhg-clear-media" data-target="bhg_<?php echo esc_attr( $field ); ?>"><?php echo esc_html( bhg_t( 'clear', 'Clear' ) ); ?></button>
                                                                                        </div>
                                                                                        <input type="hidden" id="bhg_<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>" />
                                                                                </div>
                                                                        </div>
                                                                <?php endforeach; ?>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><?php echo esc_html( bhg_t( 'css_settings', 'CSS Settings' ) ); ?></th>
                                                        <td>
                                                                <fieldset>
                                                                        <legend class="screen-reader-text"><?php echo esc_html( bhg_t( 'css_settings', 'CSS Settings' ) ); ?></legend>
                                                                        <p>
                                                                                <label for="bhg_css_border"><?php echo esc_html( bhg_t( 'border', 'Border' ) ); ?></label><br />
                                                                                <input type="text" id="bhg_css_border" name="css_border" value="<?php echo esc_attr( $css_values['border'] ); ?>" placeholder="1px solid #ccc" />
                                                                        </p>
                                                                        <p>
                                                                                <label for="bhg_css_border_color"><?php echo esc_html( bhg_t( 'border_color', 'Border Color' ) ); ?></label><br />
                                                                                <input type="text" id="bhg_css_border_color" name="css_border_color" value="<?php echo esc_attr( $css_values['border_color'] ); ?>" placeholder="#cccccc" />
                                                                        </p>
                                                                        <p>
                                                                                <label for="bhg_css_padding"><?php echo esc_html( bhg_t( 'padding', 'Padding' ) ); ?></label><br />
                                                                                <input type="text" id="bhg_css_padding" name="css_padding" value="<?php echo esc_attr( $css_values['padding'] ); ?>" placeholder="15px" />
                                                                        </p>
                                                                        <p>
                                                                                <label for="bhg_css_margin"><?php echo esc_html( bhg_t( 'margin', 'Margin' ) ); ?></label><br />
                                                                                <input type="text" id="bhg_css_margin" name="css_margin" value="<?php echo esc_attr( $css_values['margin'] ); ?>" placeholder="10px 0" />
                                                                        </p>
                                                                        <p>
                                                                                <label for="bhg_css_background"><?php echo esc_html( bhg_t( 'background_color', 'Background Color' ) ); ?></label><br />
                                                                                <input type="text" id="bhg_css_background" name="css_background" value="<?php echo esc_attr( $css_values['background'] ); ?>" placeholder="#ffffff" />
                                                                        </p>
                                                                </fieldset>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <th scope="row"><label for="bhg_prize_active"><?php echo esc_html( bhg_t( 'active', 'Active' ) ); ?></label></th>
                                                        <td><label><input type="checkbox" id="bhg_prize_active" name="active" value="1" <?php checked( $prize ? (int) $prize->active : 1 ); ?> /> <?php echo esc_html( bhg_t( 'available', 'Available' ) ); ?></label></td>
                                                </tr>
                                        </tbody>
                                </table>

                                <?php submit_button( $edit ? bhg_t( 'update_prize', 'Update Prize' ) : bhg_t( 'add_prize', 'Add Prize' ) ); ?>
                        </form>
                </div>

                <div class="bhg-prize-list">
                        <h2><?php echo esc_html( bhg_t( 'prize_list', 'Prize List' ) ); ?></h2>
                        <?php if ( empty( $prizes ) ) : ?>
                                <p><?php echo esc_html( bhg_t( 'no_prizes_yet', 'No prizes found.' ) ); ?></p>
                        <?php else : ?>
                                <table class="widefat fixed striped">
                                        <thead>
                                                <tr>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'category', 'Category' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'status', 'Status' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'images', 'Images' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ( $prizes as $row ) : ?>
                                                        <tr>
                                                                <td>
                                                                        <strong><?php echo esc_html( $row->title ); ?></strong>
                                                                        <?php if ( ! empty( $row->description ) ) : ?>
                                                                                <div class="description"><?php echo wp_kses_post( wp_trim_words( $row->description, 25 ) ); ?></div>
                                                                        <?php endif; ?>
                                                                </td>
                                                                <td><?php echo esc_html( ucwords( str_replace( '_', ' ', $row->category ) ) ); ?></td>
                                                                <td><?php echo esc_html( $row->active ? bhg_t( 'active', 'Active' ) : bhg_t( 'inactive', 'Inactive' ) ); ?></td>
                                                                <td>
                                                                        <?php
                                                                        $thumbs = array();
                                                                        foreach ( array( 'image_small', 'image_medium', 'image_large' ) as $field ) {
                                                                                $attachment_id = absint( $row->$field );
                                                                                if ( ! $attachment_id ) {
                                                                                        continue;
                                                                                }
                                                                                $thumb_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
                                                                                if ( $thumb_url ) {
                                                                                        $thumbs[] = '<img src="' . esc_url( $thumb_url ) . '" alt="" />';
                                                                                }
                                                                        }
                                                                        echo $thumbs ? implode( '', $thumbs ) : '&mdash;';
                                                                        ?>
                                                                </td>
                                                                <td>
                                                                        <a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => (int) $row->id ), admin_url( 'admin.php?page=bhg-prizes' ) ) ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
                                                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="inline-block" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'confirm_delete_prize', 'Are you sure you want to delete this prize?' ) ); ?>');">
                                                                                <?php wp_nonce_field( 'bhg_delete_prize', 'bhg_delete_prize_nonce' ); ?>
                                                                                <input type="hidden" name="action" value="bhg_delete_prize" />
                                                                                <input type="hidden" name="prize_id" value="<?php echo esc_attr( (int) $row->id ); ?>" />
                                                                                <button type="submit" class="button button-small button-secondary"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
                                                                        </form>
                                                                </td>
                                                        </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>
                        <?php endif; ?>
                </div>
        </div>
</div>
