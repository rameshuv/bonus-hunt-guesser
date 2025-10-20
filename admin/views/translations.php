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
		$slug   = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$locale = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : get_locale();
		$text   = isset( $_POST['text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) : '';

	if ( '' === $slug ) {
			$form_error = bhg_t( 'key_field_is_required', 'Key field is required.' );
	} else {
                        $exists = (int) $wpdb->get_var(
                                $wpdb->prepare(
                                        "SELECT COUNT(*) FROM {$table} WHERE slug = %s AND locale = %s",
                                        $slug,
                                        $locale
                                )
                        );

		if ( $exists ) {
				$wpdb->update(
					$table,
					array( 'text' => $text ),
					array(
						'slug'   => $slug,
						'locale' => $locale,
					),
					array( '%s' ),
					array( '%s', '%s' )
				);
		} else {
					$wpdb->insert(
						$table,
						array(
							'slug'   => $slug,
							'text'   => $text,
							'locale' => $locale,
						),
						array( '%s', '%s', '%s' )
					);
		}

                        // Invalidate cached values so updates appear immediately.
                        bhg_clear_translation_cache();
                        $notice = bhg_t( 'translation_saved', 'Translation saved.' );
        }
}

// Fetch rows with pagination and optional search.
if ( $search_term ) {
                $like  = '%' . $wpdb->esc_like( $search_term ) . '%';
                $total = (int) $wpdb->get_var(
                        $wpdb->prepare(
                                "SELECT COUNT(*) FROM {$table} WHERE slug LIKE %s OR text LIKE %s OR default_text LIKE %s",
                                $like,
                                $like,
                                $like
                        )
                );
                $rows  = $wpdb->get_results(
                        $wpdb->prepare(
                                "SELECT slug, default_text, text, locale FROM {$table} WHERE slug LIKE %s OR text LIKE %s OR default_text LIKE %s ORDER BY slug ASC LIMIT %d OFFSET %d",
                                $like,
                                $like,
                                $like,
                                $items_per_page,
                                $offset
                        )
                );
} else {
                $total = (int) $wpdb->get_var(
                        "SELECT COUNT(*) FROM {$table}"
                );
                $rows  = $wpdb->get_results(
                        $wpdb->prepare(
                                "SELECT slug, default_text, text, locale FROM {$table} ORDER BY slug ASC LIMIT %d OFFSET %d",
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

// Group rows by contextual classification with inline help text.
$grouped = array();
if ( $rows ) {
        foreach ( $rows as $r ) {
                $classification = function_exists( 'bhg_classify_translation_slug' ) ? bhg_classify_translation_slug( $r->slug ) : array();
                $group_key      = isset( $classification['group'] ) && '' !== $classification['group'] ? $classification['group'] : 'general';
                $group_title    = isset( $classification['title'] ) && '' !== $classification['title'] ? $classification['title'] : ucwords( str_replace( '_', ' ', $group_key ) );
                $group_desc     = isset( $classification['description'] ) ? (string) $classification['description'] : '';
                $row_help       = isset( $classification['help'] ) ? (string) $classification['help'] : $group_desc;

                if ( ! isset( $grouped[ $group_key ] ) ) {
                        $grouped[ $group_key ] = array(
                                'title'       => $group_title,
                                'description' => $group_desc,
                                'rows'        => array(),
                        );
                }

                $grouped[ $group_key ]['rows'][] = array(
                        'row'  => $r,
                        'help' => $row_help,
                );
        }

        uasort(
                $grouped,
                static function ( $a, $b ) {
                        $title_a = isset( $a['title'] ) ? (string) $a['title'] : '';
                        $title_b = isset( $b['title'] ) ? (string) $b['title'] : '';
                        return strcmp( $title_a, $title_b );
                }
        );

        foreach ( $grouped as &$group_data ) {
                if ( empty( $group_data['rows'] ) ) {
                        continue;
                }

                usort(
                        $group_data['rows'],
                        static function ( $a, $b ) {
                                $slug_a = isset( $a['row']->slug ) ? (string) $a['row']->slug : '';
                                $slug_b = isset( $b['row']->slug ) ? (string) $b['row']->slug : '';
                                return strcmp( $slug_a, $slug_b );
                        }
                );
        }
        unset( $group_data );
}
?>
<div class="wrap">
        <h1><?php echo esc_html( bhg_t( 'menu_translations', 'Translations' ) ); ?></h1>

        <p class="description"><?php echo esc_html( bhg_t( 'translations_context_help', 'Each translation row includes a contextual note describing where it appears on the site.' ) ); ?></p>

        <?php if ( ! empty( $notice ) ) : ?>
		<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $form_error ) ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html( $form_error ); ?></p></div>
	<?php endif; ?>

		<form method="post">
				<?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
				<input type="hidden" name="locale" value="<?php echo esc_attr( get_locale() ); ?>" />
				<table class="form-table" role="presentation">
						<tbody>
								<tr>
										<th scope="row"><label for="slug"><?php echo esc_html( bhg_t( 'slug', 'Slug' ) ); ?></label></th>
										<td><input name="slug" id="slug" type="text" class="regular-text" required></td>
								</tr>
								<tr>
										<th scope="row"><label for="text"><?php echo esc_html( bhg_t( 'value', 'Value' ) ); ?></label></th>
										<td><textarea name="text" id="text" class="large-text" rows="4"></textarea></td>
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
		<p class="description"><?php echo esc_html( bhg_t( 'custom_translations_highlighted', 'Custom translations are highlighted.' ) ); ?></p>
	<?php if ( $pagination ) : ?>
		<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( $pagination ); ?></div></div>
	<?php endif; ?>

        <?php if ( ! empty( $grouped ) ) : ?>
                <?php foreach ( $grouped as $group_key => $group_data ) : ?>
                        <?php
                        $group_title = isset( $group_data['title'] ) ? (string) $group_data['title'] : $group_key;
                        $group_desc  = isset( $group_data['description'] ) ? (string) $group_data['description'] : '';
                        $group_id    = sanitize_html_class( $group_key );
                        if ( '' === $group_id ) {
                                $group_id = 'bhg-translation-group-' . md5( $group_key );
                        } else {
                                $group_id = 'bhg-translation-group-' . $group_id;
                        }
                        ?>
                        <div class="bhg-translation-group" id="<?php echo esc_attr( $group_id ); ?>">
                                <h3><?php echo esc_html( $group_title ); ?></h3>
                                <?php if ( '' !== $group_desc ) : ?>
                                        <p class="description"><?php echo esc_html( $group_desc ); ?></p>
                                <?php endif; ?>
                                <table class="widefat striped bhg-translations-table">
                                        <thead>
                                                <tr>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_key', 'Key' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_default', 'Default' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_custom', 'Custom' ) ); ?></th>
                                                        <th scope="col"><?php echo esc_html( bhg_t( 'label_notes', 'Notes' ) ); ?></th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ( $group_data['rows'] as $item ) :
                                                        $r               = $item['row'];
                                                        $help_text       = isset( $item['help'] ) ? (string) $item['help'] : '';
                                                        $current_text    = isset( $r->text ) ? (string) $r->text : '';
                                                        $row_class       = ( '' === $current_text || $current_text === $r->default_text ) ? 'bhg-default-row' : 'bhg-custom-row';
                                                        $slug_for_id     = sanitize_html_class( $r->slug );
                                                        $input_id        = $slug_for_id ? 'bhg-translation-' . $slug_for_id : 'bhg-translation-' . md5( $r->slug );
                                                        $help_id         = $slug_for_id ? 'bhg-translation-help-' . $slug_for_id : 'bhg-translation-help-' . md5( $r->slug );
                                                        $locale_value    = isset( $r->locale ) ? (string) $r->locale : get_locale();
                                                        ?>
                                                        <tr class="<?php echo esc_attr( trim( $row_class . ' bhg-translation-row' ) ); ?>">
                                                                <td><code><?php echo esc_html( $r->slug ); ?></code></td>
                                                                <td><?php echo esc_html( $r->default_text ); ?></td>
                                                                <td>
                                                                        <form method="post" class="bhg-inline-form">
                                                                                <?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
                                                                                <input type="hidden" name="slug" value="<?php echo esc_attr( $r->slug ); ?>" />
                                                                                <input type="hidden" name="locale" value="<?php echo esc_attr( $locale_value ); ?>" />
                                                                                <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
                                                                                        <?php echo esc_html( sprintf( bhg_t( 'label_custom_value_for', 'Custom value for %s' ), $r->slug ) ); ?>
                                                                                </label>
                                                                                <input
                                                                                        type="text"
                                                                                        id="<?php echo esc_attr( $input_id ); ?>"
                                                                                        name="text"
                                                                                        value="<?php echo esc_attr( $current_text ); ?>"
                                                                                        class="regular-text"
                                                                                        data-original="<?php echo esc_attr( $current_text ); ?>"
                                                                                        placeholder="<?php echo esc_attr( bhg_t( 'placeholder_custom_value', 'Custom value' ) ); ?>"
                                                                                        aria-describedby="<?php echo esc_attr( $help_id ); ?>"
                                                                                />
                                                                                <button type="submit" name="bhg_save_translation" class="button button-primary"><?php echo esc_html( bhg_t( 'button_update', 'Update' ) ); ?></button>
                                                                        </form>
                                                                </td>
                                                                <td class="bhg-translation-help" id="<?php echo esc_attr( $help_id ); ?>">
                                                                        <span class="description"><?php echo esc_html( $help_text ); ?></span>
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
