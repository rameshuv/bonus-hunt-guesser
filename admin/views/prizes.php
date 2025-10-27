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

$prizes        = BHG_Prizes::get_prizes();
$message_code  = isset( $_GET['bhg_msg'] ) ? sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) : '';
$css_defaults  = BHG_Prizes::default_css_settings();
$categories    = BHG_Prizes::get_categories();
$capability    = apply_filters( 'bhg_manage_prizes_capability', 'manage_options' );
$can_manage    = current_user_can( $capability );

$notices = array(
        'p_saved'   => array(
                'class' => 'notice-success',
                'text'  => bhg_t( 'prize_saved', 'Prize saved successfully.' ),
        ),
        'p_updated' => array(
                'class' => 'notice-success',
                'text'  => bhg_t( 'prize_updated', 'Prize updated successfully.' ),
        ),
        'p_deleted' => array(
                'class' => 'notice-success',
                'text'  => bhg_t( 'prize_deleted', 'Prize deleted successfully.' ),
        ),
        'p_error'   => array(
                'class' => 'notice-error',
                'text'  => bhg_t( 'prize_error', 'There was a problem saving the prize.' ),
        ),
        'nonce'     => array(
                'class' => 'notice-error',
                'text'  => bhg_t( 'nonce_error', 'Security check failed. Please try again.' ),
        ),
);

?>
<div class="wrap bhg-wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_prizes', 'Prizes' ) ); ?></h1>
        <?php if ( $can_manage ) : ?>
                <a href="#" class="page-title-action" id="bhg-add-prize"><?php echo esc_html( bhg_t( 'add_new_prize', 'Add New Prize' ) ); ?></a>
        <?php endif; ?>
        <hr class="wp-header-end" />

        <?php if ( $message_code && isset( $notices[ $message_code ] ) ) : ?>
                <div class="notice <?php echo esc_attr( $notices[ $message_code ]['class'] ); ?> is-dismissible">
                        <p><?php echo esc_html( $notices[ $message_code ]['text'] ); ?></p>
                </div>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped bhg-prize-table">
                <thead>
                        <tr>
                                <th scope="col" class="manage-column column-primary"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'category', 'Category' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'status', 'Status' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'image_small', 'Small' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'image_medium', 'Medium' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'image_large', 'Big' ) ); ?></th>
                                <th scope="col" class="column-actions"><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
                        </tr>
                </thead>
                <tbody>
                        <?php if ( empty( $prizes ) ) : ?>
                                <tr>
                                        <td colspan="7"><?php echo esc_html( bhg_t( 'no_prizes_yet', 'No prizes found.' ) ); ?></td>
                                </tr>
                        <?php else : ?>
                                <?php foreach ( $prizes as $row ) : ?>
                                        <?php $attachments = BHG_Prizes::get_attachment_sources( $row ); ?>
                                        <tr>
                                                <td class="column-primary" data-colname="<?php echo esc_attr( bhg_t( 'sc_title', 'Title' ) ); ?>">
                                                        <strong><?php echo esc_html( $row->title ); ?></strong>
                                                        <?php if ( ! empty( $row->description ) ) : ?>
                                                                <div class="description"><?php echo wp_kses_post( wp_trim_words( $row->description, 25 ) ); ?></div>
                                                        <?php endif; ?>
                                                </td>
                                                <td data-colname="<?php echo esc_attr( bhg_t( 'category', 'Category' ) ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $row->category ) ) ); ?></td>
                                                <td data-colname="<?php echo esc_attr( bhg_t( 'status', 'Status' ) ); ?>"><?php echo esc_html( $row->active ? bhg_t( 'active', 'Active' ) : bhg_t( 'inactive', 'Inactive' ) ); ?></td>
                                                <?php foreach ( array( 'small', 'medium', 'big' ) as $size ) : ?>
                                                        <td data-colname="<?php echo esc_attr( ucfirst( $size ) ); ?>">
                                                                <?php if ( ! empty( $attachments[ $size ]['url'] ) ) : ?>
                                                                        <img src="<?php echo esc_url( $attachments[ $size ]['url'] ); ?>" alt="" class="bhg-prize-thumb" />
                                                                <?php else : ?>
                                                                        &mdash;
                                                                <?php endif; ?>
                                                        </td>
                                                <?php endforeach; ?>
                                                <td class="column-actions" data-colname="<?php echo esc_attr( bhg_t( 'label_actions', 'Actions' ) ); ?>">
                                                        <?php if ( $can_manage ) : ?>
                                                                <button type="button" class="button button-small bhg-edit-prize" data-id="<?php echo esc_attr( (int) $row->id ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></button>
                                                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="inline-block" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'confirm_delete_prize', 'Are you sure you want to delete this prize?' ) ); ?>');">
                                                                        <?php wp_nonce_field( 'bhg_prize_delete', 'bhg_prize_delete_nonce' ); ?>
                                                                        <input type="hidden" name="action" value="bhg_prize_delete" />
                                                                        <input type="hidden" name="prize_id" value="<?php echo esc_attr( (int) $row->id ); ?>" />
                                                                        <button type="submit" class="button button-small button-secondary"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
                                                                </form>
                                                        <?php else : ?>
                                                                <em><?php echo esc_html( bhg_t( 'no_permission', 'No permission' ) ); ?></em>
                                                        <?php endif; ?>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                </tbody>
        </table>
</div>

<?php if ( $can_manage ) : ?>
<div id="bhg-prize-modal" class="bhg-prize-modal hidden" role="dialog" aria-modal="true" aria-labelledby="bhg-prize-modal-title">
        <div class="bhg-prize-modal__backdrop" data-action="close"></div>
        <div class="bhg-prize-modal__dialog">
                <button type="button" class="bhg-prize-modal__close" data-action="close" aria-label="<?php echo esc_attr( bhg_t( 'close_modal', 'Close modal' ) ); ?>">&times;</button>
                <h2 id="bhg-prize-modal-title"><?php echo esc_html( bhg_t( 'add_new_prize', 'Add New Prize' ) ); ?></h2>
                <div class="notice notice-error hidden" id="bhg-prize-error"><p></p></div>
                <form id="bhg-prize-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'bhg_prize_save', 'bhg_prize_nonce' ); ?>
                        <input type="hidden" name="action" value="bhg_prize_save" />
                        <input type="hidden" name="prize_id" id="bhg_prize_id" value="0" />

                        <table class="form-table" role="presentation">
                                <tbody>
                                        <tr>
                                                <th scope="row"><label for="bhg_prize_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
                                                <td><input type="text" class="regular-text" id="bhg_prize_title" name="title" value="" required /></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_prize_description"><?php echo esc_html( bhg_t( 'description', 'Description' ) ); ?></label></th>
                                                <td><textarea class="large-text" rows="5" id="bhg_prize_description" name="description"></textarea></td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_prize_category"><?php echo esc_html( bhg_t( 'category', 'Category' ) ); ?></label></th>
                                                <td>
                                                        <select id="bhg_prize_category" name="category">
                                                                <?php foreach ( $categories as $category ) : ?>
                                                                        <option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $category ) ) ); ?></option>
                                                                <?php endforeach; ?>
                                                        </select>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><?php echo esc_html( bhg_t( 'images', 'Images' ) ); ?></th>
                                                <td>
                                                        <?php
                                                        $image_fields = array(
                                                                'small'  => bhg_t( 'image_small', 'Small' ),
                                                                'medium' => bhg_t( 'image_medium', 'Medium' ),
                                                                'big'    => bhg_t( 'image_large', 'Big' ),
                                                        );
                                                        foreach ( $image_fields as $size => $label ) :
                                                                $field_id = 'bhg_image_' . $size;
                                                                ?>
                                                                <div class="bhg-media-control" data-size="<?php echo esc_attr( $size ); ?>">
                                                                        <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
                                                                        <div class="bhg-media-preview">
                                                                                <span class="bhg-media-placeholder"><?php echo esc_html( bhg_t( 'no_image_selected', 'No image selected' ) ); ?></span>
                                                                        </div>
                                                                        <input type="hidden" id="<?php echo esc_attr( $field_id ); ?>" name="image_<?php echo esc_attr( $size ); ?>" value="" />
                                                                        <div class="bhg-media-buttons">
                                                                                <button type="button" class="button bhg-select-media" data-target="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( bhg_t( 'select_image', 'Select Image' ) ); ?></button>
                                                                                <button type="button" class="button bhg-clear-media" data-target="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( bhg_t( 'clear', 'Clear' ) ); ?></button>
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
                                                                        <input type="text" id="bhg_css_border" name="css_border" value="<?php echo esc_attr( $css_defaults['border'] ); ?>" placeholder="1px solid #ccc" />
                                                                </p>
                                                                <p>
                                                                        <label for="bhg_css_border_color"><?php echo esc_html( bhg_t( 'border_color', 'Border Color' ) ); ?></label><br />
                                                                        <input type="text" id="bhg_css_border_color" name="css_border_color" value="<?php echo esc_attr( $css_defaults['border_color'] ); ?>" placeholder="#cccccc" />
                                                                </p>
                                                                <p>
                                                                        <label for="bhg_css_padding"><?php echo esc_html( bhg_t( 'padding', 'Padding' ) ); ?></label><br />
                                                                        <input type="text" id="bhg_css_padding" name="css_padding" value="<?php echo esc_attr( $css_defaults['padding'] ); ?>" placeholder="15px" />
                                                                </p>
                                                                <p>
                                                                        <label for="bhg_css_margin"><?php echo esc_html( bhg_t( 'margin', 'Margin' ) ); ?></label><br />
                                                                        <input type="text" id="bhg_css_margin" name="css_margin" value="<?php echo esc_attr( $css_defaults['margin'] ); ?>" placeholder="10px 0" />
                                                                </p>
                                                                <p>
                                                                        <label for="bhg_css_background"><?php echo esc_html( bhg_t( 'background_color', 'Background Color' ) ); ?></label><br />
                                                                        <input type="text" id="bhg_css_background" name="css_background" value="<?php echo esc_attr( $css_defaults['background'] ); ?>" placeholder="#ffffff" />
                                                                </p>
                                                        </fieldset>
                                                </td>
                                        </tr>
                                        <tr>
                                                <th scope="row"><label for="bhg_prize_active"><?php echo esc_html( bhg_t( 'active', 'Active' ) ); ?></label></th>
                                                <td><label><input type="checkbox" id="bhg_prize_active" name="active" value="1" checked /> <?php echo esc_html( bhg_t( 'available', 'Available' ) ); ?></label></td>
                                        </tr>
                                </tbody>
                        </table>

                        <div class="bhg-prize-modal__actions">
                                <span class="spinner" id="bhg-prize-spinner"></span>
                                <button type="submit" class="button button-primary" id="bhg-prize-submit"><?php echo esc_html( bhg_t( 'add_prize', 'Add Prize' ) ); ?></button>
                        </div>
                </form>
        </div>
</div>
<?php endif; ?>
