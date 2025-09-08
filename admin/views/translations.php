<?php
/**
 * Admin view for managing plugin translations.
 *
 * Provides search, pagination and context-based grouping for translation keys.
 * Custom translations are highlighted and each row uses a nonce for security.
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
$table = esc_sql( $wpdb->prefix . 'bhg_translations' );

if ( function_exists( 'bhg_seed_default_translations_if_empty' ) ) {
	bhg_seed_default_translations_if_empty();
}

$default_translations = function_exists( 'bhg_get_default_translations' ) ? bhg_get_default_translations() : array();

// Pagination variables.
$allowed_per_page = array( 10, 20, 50 );
$items_per_page   = isset( $_GET['per_page'] ) ? absint( wp_unslash( $_GET['per_page'] ) ) : 20;
if ( ! in_array( $items_per_page, $allowed_per_page, true ) ) {
	$items_per_page = 20;
}
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
$offset       = ( $current_page - 1 ) * $items_per_page;

// Current search term.
$search_term = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

// Handle form submission.
if ( isset( $_POST['bhg_save_translation'] ) && check_admin_referer( 'bhg_save_translation_action', 'bhg_nonce' ) ) {
	$tkey   = isset( $_POST['tkey'] ) ? sanitize_text_field( wp_unslash( $_POST['tkey'] ) ) : '';
	$tvalue = isset( $_POST['tvalue'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tvalue'] ) ) : '';

	if ( '' === $tkey ) {
		$form_error = bhg_t( 'key_field_is_required', 'Key field is required.' );
	} else {
		$wpdb->replace(
			$table,
			array(
				'tkey'   => $tkey,
				'tvalue' => $tvalue,
			),
			array( '%s', '%s' )
		);
		wp_cache_delete( 'bhg_translation_' . $tkey );
		$notice = bhg_t( 'translation_saved', 'Translation saved.' );
	}
}

// Fetch rows with pagination and optional search.
if ( $search_term ) {
	$like  = '%' . $wpdb->esc_like( $search_term ) . '%';
	$total = (int) $wpdb->get_var(
		$wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe.
			"SELECT COUNT(*) FROM {$table} WHERE tkey LIKE %s OR tvalue LIKE %s",
			$like,
			$like
		)
	);
	$rows = $wpdb->get_results(
		$wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe.
			"SELECT tkey, tvalue FROM {$table} WHERE tkey LIKE %s OR tvalue LIKE %s ORDER BY tkey ASC LIMIT %d OFFSET %d",
			$like,
			$like,
			$items_per_page,
			$offset
		)
	);
} else {
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe.
			"SELECT tkey, tvalue FROM {$table} ORDER BY tkey ASC LIMIT %d OFFSET %d",
			$items_per_page,
			$offset
		)
	);
}

// Pagination links.
$total_pages = max( 1, ceil( $total / $items_per_page ) );
$pagination  = paginate_links(
	array(
		'base'     => add_query_arg( 'paged', '%#%' ),
		'format'   => '',
		'current'  => $current_page,
		'total'    => $total_pages,
		'add_args' => array(
			'per_page' => $items_per_page,
			's'        => $search_term,
		),
	)
);

// Group rows by context (prefix before the first underscore).
$grouped = array();
if ( $rows ) {
	foreach ( $rows as $r ) {
		list( $context )       = array_pad( explode( '_', $r->tkey, 2 ), 2, 'misc' );
		$grouped[ $context ][] = $r;
	}
	ksort( $grouped );
	foreach ( $grouped as &$items ) {
		usort(
			$items,
			static function ( $a, $b ) {
				return strcmp( $a->tkey, $b->tkey );
			}
		);
	}
	unset( $items );
}
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'menu_translations', 'Translations' ) ); ?></h1>

	<?php if ( ! empty( $notice ) ) : ?>
		<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $form_error ) ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html( $form_error ); ?></p></div>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="tkey"><?php echo esc_html( bhg_t( 'key', 'Key' ) ); ?></label></th>
					<td><input name="tkey" id="tkey" type="text" class="regular-text" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="tvalue"><?php echo esc_html( bhg_t( 'value', 'Value' ) ); ?></label></th>
					<td><textarea name="tvalue" id="tvalue" class="large-text" rows="4"></textarea></td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><button type="submit" name="bhg_save_translation" class="button button-primary"><?php echo esc_html( bhg_t( 'button_save', 'Save' ) ); ?></button></p>
	</form>

	<form method="get" class="bhg-translations-search">
		<input type="hidden" name="page" value="bhg-translations" />
		<p class="search-box">
			<label class="screen-reader-text" for="bhg-translation-search-input"><?php echo esc_html( bhg_t( 'label_search_translations', 'Search translations' ) ); ?></label>
			<input type="search" id="bhg-translation-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
			<label class="screen-reader-text" for="bhg-per-page"><?php echo esc_html( bhg_t( 'label_items_per_page', 'Items per page' ) ); ?></label>
			<select id="bhg-per-page" name="per_page">
				<option value="10" <?php selected( $items_per_page, 10 ); ?>>10</option>
				<option value="20" <?php selected( $items_per_page, 20 ); ?>>20</option>
				<option value="50" <?php selected( $items_per_page, 50 ); ?>>50</option>
			</select>
			<button class="button"><?php echo esc_html( bhg_t( 'button_filter', 'Filter' ) ); ?></button>
		</p>
	</form>

	<h2><?php echo esc_html( bhg_t( 'existing_keys', 'Existing keys' ) ); ?></h2>
	<?php if ( $pagination ) : ?>
		<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( $pagination ); ?></div></div>
	<?php endif; ?>

	<?php if ( ! empty( $grouped ) ) : ?>
		<?php foreach ( $grouped as $context => $items ) : ?>
			<div class="bhg-translation-group">
				<h3><?php echo esc_html( ucwords( str_replace( '_', ' ', $context ) ) ); ?></h3>
				<table class="widefat striped bhg-translations-table">
					<thead>
						<tr>
							<th><?php echo esc_html( bhg_t( 'label_key', 'Key' ) ); ?></th>
							<th><?php echo esc_html( bhg_t( 'label_default', 'Default' ) ); ?></th>
							<th><?php echo esc_html( bhg_t( 'label_custom', 'Custom' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $items as $r ) :
							$default_val = $default_translations[ $r->tkey ] ?? '';
							$row_class   = $r->tvalue === $default_val ? 'bhg-default-row' : 'bhg-custom-row';
							?>
							<tr class="<?php echo esc_attr( $row_class ); ?>">
								<td><code><?php echo esc_html( $r->tkey ); ?></code></td>
								<td><?php echo esc_html( $default_val ); ?></td>
								<td>
									<form method="post" class="bhg-inline-form">
										<?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
										<input type="hidden" name="tkey" value="<?php echo esc_attr( $r->tkey ); ?>" />
										<input type="text" name="tvalue" value="<?php echo esc_attr( $r->tvalue ); ?>" class="regular-text" data-original="<?php echo esc_attr( $r->tvalue ); ?>" placeholder="<?php echo esc_attr( bhg_t( 'placeholder_custom_value', 'Custom value' ) ); ?>" />
										<button type="submit" name="bhg_save_translation" class="button button-primary"><?php echo esc_html( bhg_t( 'button_update', 'Update' ) ); ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p><?php echo esc_html( bhg_t( 'no_translations_yet', 'No translations yet.' ) ); ?></p>
	<?php endif; ?>

	<?php if ( $pagination ) : ?>
		<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( $pagination ); ?></div></div>
	<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.bhg-inline-form input[type="text"]').forEach(function (input) {
		var original = input.getAttribute('data-original');
		var row      = input.closest('tr');
		input.addEventListener('input', function () {
			if (input.value !== original) {
				row.classList.add('bhg-modified-row');
			} else {
				row.classList.remove('bhg-modified-row');
			}
		});
	});
});
</script>

<style>
.bhg-modified-row {
	background-color: #fff3cd;
	border-left: 4px solid #d97706;
}
</style>

