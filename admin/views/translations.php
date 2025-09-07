<?php
/**
 * Admin view for managing plugin translations.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit; }

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
}

global $wpdb;
$table = $wpdb->prefix . 'bhg_translations';

if ( function_exists( 'bhg_seed_default_translations_if_empty' ) ) {
       bhg_seed_default_translations_if_empty();
}

$default_translations = function_exists( 'bhg_get_default_translations' ) ? bhg_get_default_translations() : array();
$default_keys         = array_keys( $default_translations );

// Pagination variables.
$allowed_per_page = array( 10, 20, 50 );
$items_per_page   = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 20;
if ( ! in_array( $items_per_page, $allowed_per_page, true ) ) {
	$items_per_page = 20;
}
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$offset       = ( $current_page - 1 ) * $items_per_page;

// Current search term.
$search_term = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

// Handle form submission.

if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['bhg_save_translation'] ) ) {
	check_admin_referer( 'bhg_save_translation_action', 'bhg_nonce' );

	// Sanitize input.
		$tkey   = isset( $_POST['tkey'] ) ? sanitize_text_field( wp_unslash( $_POST['tkey'] ) ) : '';
		$tvalue = isset( $_POST['tvalue'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tvalue'] ) ) : '';

		// Validate input.
	if ( '' === $tkey ) {
			$form_error = __( 'Key field is required.', 'bonus-hunt-guesser' );
	} else {
			$wpdb->replace(
				$table,
				array(
					'tkey'   => $tkey,
					'tvalue' => $tvalue,
				),
				array( '%s', '%s' )
			);
			$notice = __( 'Translation saved.', 'bonus-hunt-guesser' );
	}
}

// Fetch rows with pagination.
if ( $search_term ) {
		$like  = '%' . $wpdb->esc_like( $search_term ) . '%';
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE tkey LIKE %s OR tvalue LIKE %s',
				$table,
				$like,
				$like
			)
		);
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT tkey, tvalue FROM %i WHERE tkey LIKE %s OR tvalue LIKE %s ORDER BY tkey ASC LIMIT %d OFFSET %d',
				$table,
				$like,
				$like,
				$items_per_page,
				$offset
			)
		);
} else {
		$total = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table ) );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT tkey, tvalue FROM %i ORDER BY tkey ASC LIMIT %d OFFSET %d',
				$table,
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
<th scope="row"><label for="tkey"><?php esc_html_e( 'Key', 'bonus-hunt-guesser' ); ?></label></th>
<td><input name="tkey" id="tkey" type="text" class="regular-text" required></td>
</tr>
<tr>
<th scope="row"><label for="tvalue"><?php esc_html_e( 'Value', 'bonus-hunt-guesser' ); ?></label></th>
<td><textarea name="tvalue" id="tvalue" class="large-text" rows="4"></textarea></td>
</tr>
</tbody>
</table>
<p class="submit"><button type="submit" name="bhg_save_translation" class="button button-primary"><?php esc_html_e( 'Save', 'bonus-hunt-guesser' ); ?></button></p>
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
<?php
$grouped = array();
if ( $rows ) {
	foreach ( $rows as $r ) {
			$context = 'misc';
		if ( false !== strpos( $r->tkey, '_' ) ) {
				$context = substr( $r->tkey, 0, strpos( $r->tkey, '_' ) );
		}
			$grouped[ $context ][] = $r;
	}
}

if ( ! empty( $grouped ) ) :
	foreach ( $grouped as $context => $items ) :
		?>
<div class="bhg-translation-group">
<h3><?php echo esc_html( ucwords( str_replace( '_', ' ', $context ) ) ); ?></h3>
<table class="widefat striped bhg-translations-table">
<thead><tr><th><?php echo esc_html( bhg_t( 'label_key', 'Key' ) ); ?></th><th><?php echo esc_html( bhg_t( 'label_default', 'Default' ) ); ?></th><th><?php echo esc_html( bhg_t( 'label_custom', 'Custom' ) ); ?></th></tr></thead>
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
<input type="text" name="tvalue" value="<?php echo esc_attr( $r->tvalue ); ?>" class="regular-text" placeholder="<?php echo esc_attr( bhg_t( 'placeholder_custom_value', 'Custom value' ) ); ?>" />
<button type="submit" name="bhg_save_translation" class="button button-primary"><?php echo esc_html( bhg_t( 'button_update', 'Update' ) ); ?></button>
</form>
</td>
</tr>
			<?php
					endforeach;
		?>
</tbody>
</table>
</div>
			<?php
				endforeach;
else :
	?>
<p><?php echo esc_html( bhg_t( 'no_translations_yet', 'No translations yet.' ) ); ?></p>
<?php endif; ?>

<?php if ( $pagination ) : ?>
<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( $pagination ); ?></div></div>
<?php endif; ?>
</div>
