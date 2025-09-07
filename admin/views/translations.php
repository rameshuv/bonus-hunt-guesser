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

if ( function_exists( 'bhg_seed_default_translations' ) ) {
		bhg_seed_default_translations();
}

$default_translations = function_exists( 'bhg_get_default_translations' ) ? bhg_get_default_translations() : array();
$default_keys         = array_keys( $default_translations );

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

// Fetch rows.
$query = "SELECT tkey, tvalue FROM {$table}";
if ( $search_term ) {
	$like   = '%' . $wpdb->esc_like( $search_term ) . '%';
	$query .= $wpdb->prepare( ' WHERE tkey LIKE %s OR tvalue LIKE %s', $like, $like );
}
$query .= ' ORDER BY tkey ASC';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
$rows = $wpdb->get_results( $query );
?>
<div class="wrap">
<h1><?php esc_html_e( 'Translations', 'bonus-hunt-guesser' ); ?></h1>

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
<label class="screen-reader-text" for="bhg-translation-search-input"><?php esc_html_e( 'Search translations', 'bonus-hunt-guesser' ); ?></label>
<input type="search" id="bhg-translation-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
<button class="button"><?php esc_html_e( 'Search', 'bonus-hunt-guesser' ); ?></button>
</p>
</form>

<h2><?php esc_html_e( 'Existing keys', 'bonus-hunt-guesser' ); ?></h2>
<table class="widefat striped bhg-translations-table">
<thead><tr><th><?php esc_html_e( 'Key', 'bonus-hunt-guesser' ); ?></th><th><?php esc_html_e( 'Value', 'bonus-hunt-guesser' ); ?></th></tr></thead>
<tbody>
<?php
if ( $rows ) :
	foreach ( $rows as $r ) :
		?>
<tr<?php echo in_array( $r->tkey, $default_keys, true ) ? ' class="bhg-default-row"' : ''; ?>>
<td><code><?php echo esc_html( $r->tkey ); ?></code></td>
<td>
<form method="post" class="bhg-inline-form">
		<?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
<input type="hidden" name="tkey" value="<?php echo esc_attr( $r->tkey ); ?>" />
<input type="text" name="tvalue" value="<?php echo esc_attr( $r->tvalue ); ?>" class="regular-text" />
<button type="submit" name="bhg_save_translation" class="button"><?php esc_html_e( 'Update', 'bonus-hunt-guesser' ); ?></button>
</form>
</td>
</tr>
<?php endforeach; else : ?>
<tr><td colspan="2"><?php esc_html_e( 'No translations yet.', 'bonus-hunt-guesser' ); ?></td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
