<?php
/**
 * Advertising management view.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;

$ads_table = $wpdb->prefix . 'bhg_ads';

$edit_id = 0;

if ( isset( $_GET['edit'] ) ) {
	$nonce = isset( $_GET['bhg_edit_ad_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_edit_ad_nonce'] ) ) : '';

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'bhg_edit_ad' ) ) {
		wp_die( esc_html( bhg_t( 'notice_invalid_nonce', 'Invalid nonce.' ) ) );
	}

	$edit_id = absint( wp_unslash( $_GET['edit'] ) );
}

$ads = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated from the $wpdb prefix.
	"SELECT * FROM {$ads_table} ORDER BY id DESC"
);

$placement_labels = array(
	'none'      => bhg_t( 'none', 'None' ),
	'footer'    => bhg_t( 'label_footer', 'Footer' ),
	'bottom'    => bhg_t( 'label_bottom', 'Bottom' ),
	'sidebar'   => bhg_t( 'label_sidebar', 'Sidebar' ),
	'shortcode' => bhg_t( 'label_shortcode', 'Shortcode' ),
);

$visible_labels = array(
	'all'            => bhg_t( 'label_all', 'All' ),
	'guests'         => bhg_t( 'label_guests', 'Guests' ),
	'logged_in'      => bhg_t( 'label_logged_in', 'Logged In' ),
	'affiliates'     => bhg_t( 'label_affiliates', 'Affiliates' ),
	'non_affiliates' => bhg_t( 'label_non_affiliates', 'Non Affiliates' ),
);

$ad = null;

if ( $edit_id ) {
		$ad = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$ads_table} WHERE id = %d",
				$edit_id
			)
		);
}
?>
<div class="wrap bhg-wrap">
<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_advertising', 'Advertising' ) ); ?></h1>

<h2 class="screen-reader-text"><?php echo esc_html( bhg_t( 'existing_ads', 'Existing Ads' ) ); ?></h2>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<?php wp_nonce_field( 'bhg_delete_ad', 'bhg_delete_ad_nonce' ); ?>
<input type="hidden" name="action" value="bhg_delete_ad" />
<div class="tablenav top">
<div class="alignleft actions bulkactions">
<label for="bulk-action-selector-top" class="screen-reader-text"><?php echo esc_html( bhg_t( 'select_bulk_action', 'Select bulk action' ) ); ?></label>
<select name="bulk_action" id="bulk-action-selector-top">
<option value=""><?php echo esc_html( bhg_t( 'bulk_actions', 'Bulk actions' ) ); ?></option>
<option value="delete"><?php echo esc_html( bhg_t( 'remove', 'Remove' ) ); ?></option>
</select>
<input type="submit" class="button action" value="<?php echo esc_attr( bhg_t( 'apply', 'Apply' ) ); ?>" />
</div>
</div>

<table class="widefat striped">
<thead>
<tr>
<td id="cb" class="check-column"><input type="checkbox" class="bhg-select-all" /></td>
<th><?php echo esc_html( bhg_t( 'id', 'ID' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'titlecontent', 'Title/Content' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'placement', 'Placement' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_visible_to', 'Visible To' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_active', 'Active' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
</tr>
</thead>
<tbody>
<?php if ( empty( $ads ) ) : ?>
<tr>
<td colspan="7"><?php echo esc_html( bhg_t( 'notice_no_ads_yet', 'No ads yet.' ) ); ?></td>
</tr>
<?php else : ?>
	<?php foreach ( $ads as $ad_row ) : ?>
<tr>
<th scope="row" class="check-column">
<input type="checkbox" class="bhg-ad-checkbox" name="ad_ids[]" value="<?php echo esc_attr( (int) $ad_row->id ); ?>" />
</th>
<td><?php echo esc_html( (int) $ad_row->id ); ?></td>
<td>
		<?php
		if ( isset( $ad_row->title ) && '' !== $ad_row->title ) {
			echo esc_html( $ad_row->title );
		} else {
			echo wp_kses_post( wp_trim_words( (string) $ad_row->content, 12 ) );
		}
		?>
</td>
<td><?php echo esc_html( $placement_labels[ $ad_row->placement ?? 'none' ] ?? ( $ad_row->placement ?? 'none' ) ); ?></td>
<td><?php echo esc_html( $visible_labels[ $ad_row->visible_to ?? 'all' ] ?? ( $ad_row->visible_to ?? 'all' ) ); ?></td>
<td><?php echo ! empty( $ad_row->active ) ? esc_html__( 'Yes', 'bonus-hunt-guesser' ) : esc_html__( 'No', 'bonus-hunt-guesser' ); ?></td>
<td>
		<?php
		$edit_url = wp_nonce_url(
			add_query_arg( array( 'edit' => (int) $ad_row->id ) ),
			'bhg_edit_ad',
			'bhg_edit_ad_nonce'
		);
		?>
<a class="button" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
<button type="submit" name="ad_id" value="<?php echo esc_attr( (int) $ad_row->id ); ?>" class="button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'delete_this_ad', 'Delete this ad?' ) ); ?>');"><?php echo esc_html( bhg_t( 'remove', 'Remove' ) ); ?></button>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</form>

<h2 style="margin-top:2em"><?php echo $ad ? esc_html( bhg_t( 'edit_ad', 'Edit Ad' ) ) : esc_html( bhg_t( 'add_ad', 'Add Ad' ) ); ?></h2>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:800px">
<?php wp_nonce_field( 'bhg_save_ad', 'bhg_save_ad_nonce' ); ?>
<input type="hidden" name="action" value="bhg_save_ad" />
<?php if ( $ad ) : ?>
<input type="hidden" name="id" value="<?php echo esc_attr( (int) $ad->id ); ?>" />
<?php endif; ?>

<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><label for="bhg_ad_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
<td><input class="regular-text" id="bhg_ad_title" name="title" value="<?php echo esc_attr( $ad ? ( $ad->title ?? '' ) : '' ); ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_ad_content"><?php echo esc_html( bhg_t( 'content', 'Content' ) ); ?></label></th>
<td><?php wp_editor( $ad ? $ad->content : '', 'bhg_ad_content', array( 'textarea_name' => 'content' ) ); ?></td>
</tr>
<tr>
<th scope="row"><label for="bhg_ad_link"><?php echo esc_html( bhg_t( 'link_url_optional', 'Link URL (optional)' ) ); ?></label></th>
<td><input class="regular-text" id="bhg_ad_link" name="link_url" value="<?php echo esc_attr( $ad ? ( $ad->link_url ?? '' ) : '' ); ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_ad_place"><?php echo esc_html( bhg_t( 'placement', 'Placement' ) ); ?></label></th>
<td>
<select id="bhg_ad_place" name="placement">
<?php
$placement_options  = BHG_Ads::get_allowed_placements();
$selected_placement = $ad ? ( $ad->placement ?? 'none' ) : 'none';

foreach ( $placement_options as $placement_value ) {
	$label = $placement_labels[ $placement_value ] ?? $placement_value;
	printf(
		'<option value="%1$s" %2$s>%3$s</option>',
		esc_attr( $placement_value ),
		selected( $selected_placement, $placement_value, false ),
		esc_html( $label )
	);
}
?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_ad_vis"><?php echo esc_html( bhg_t( 'label_visible_to', 'Visible To' ) ); ?></label></th>
<td>
<select id="bhg_ad_vis" name="visible_to">
<?php
$visible_options  = array( 'all', 'guests', 'logged_in', 'affiliates', 'non_affiliates' );
$selected_visible = $ad ? ( $ad->visible_to ?? 'all' ) : 'all';

foreach ( $visible_options as $visible_value ) {
	$label = $visible_labels[ $visible_value ] ?? $visible_value;
	printf(
		'<option value="%1$s" %2$s>%3$s</option>',
		esc_attr( $visible_value ),
		selected( $selected_visible, $visible_value, false ),
		esc_html( $label )
	);
}
?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_ad_targets"><?php echo esc_html( bhg_t( 'target_page_slugs', 'Target Page Slugs' ) ); ?></label></th>
<td><input class="regular-text" id="bhg_ad_targets" name="target_pages" value="<?php echo esc_attr( $ad ? ( $ad->target_pages ?? '' ) : '' ); ?>" placeholder="page-slug-1,page-slug-2" /></td>
</tr>
<tr>
<th scope="row"><label for="bhg_ad_active"><?php echo esc_html( bhg_t( 'label_active', 'Active' ) ); ?></label></th>
<td><input type="checkbox" id="bhg_ad_active" name="active" value="1" <?php checked( $ad ? ( $ad->active ?? 1 ) : 1, 1 ); ?> /></td>
</tr>
</tbody>
</table>

<?php submit_button( $ad ? bhg_t( 'update_ad', 'Update Ad' ) : bhg_t( 'save_ad', 'Save Ad' ) ); ?>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
const toggle = document.querySelector('.bhg-select-all');
if (!toggle) {
return;
}
toggle.addEventListener('change', function () {
document.querySelectorAll('.bhg-ad-checkbox').forEach(function (checkbox) {
checkbox.checked = toggle.checked;
});
});
});
</script>
