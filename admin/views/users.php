<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;

$paged    = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
$per_page = 30;
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
if ( isset( $_GET['s'] ) ) {
                check_admin_referer( 'bhg_users_search', 'bhg_users_search_nonce' );
}
$allowed_orderby = array( 'user_login', 'display_name', 'user_email' );
$orderby         = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'user_login';
if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
	$orderby = 'user_login';
}
$order = ( isset( $_GET['order'] ) && 'desc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ) ? 'DESC' : 'ASC';

$args = array(
	'number'         => $per_page,
	'offset'         => ( $paged - 1 ) * $per_page,
	'orderby'        => $orderby,
	'order'          => $order,
	'search'         => $search ? '*' . $search . '*' : '',
	'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
);

$user_query = new WP_User_Query( $args );
$users      = $user_query->get_results();
$total      = $user_query->get_total();

$affiliates_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
$affiliate_sites  = $wpdb->get_results( "SELECT id, name FROM {$affiliates_table} ORDER BY name ASC" );
$has_affiliates   = ! empty( $affiliate_sites );
$affiliate_column_count = $has_affiliates ? count( $affiliate_sites ) : 1;
$total_columns          = 4 + $affiliate_column_count + 1; // username, name, real name, email, affiliates, actions.

$base_url = remove_query_arg( array( 'paged' ) );
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_users', 'Users' ) ); ?></h1>

		<form method="get" class="bhg-margin-top-small">
		<input type="hidden" name="page" value="bhg-users" />
		<input type="hidden" name="action" value="bhg_users_search" />
		<?php wp_nonce_field( 'bhg_users_search', 'bhg_users_search_nonce' ); ?>
	<p class="search-box">
		<label class="screen-reader-text" for="user-search-input"><?php echo esc_html( bhg_t( 'search_users', 'Search Users' ) ); ?></label>
		<input type="search" id="user-search-input" name="s" value="<?php echo esc_attr( $search ); ?>" />
		<input type="submit" id="search-submit" class="button" value="<?php echo esc_attr( bhg_t( 'label_search', 'Search' ) ); ?>" />
	</p>
	</form>

	<table class="widefat striped">
	<thead>
		<tr>
		<th><a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'orderby' => 'user_login',
					'order'   => 'ASC' === $order ? 'desc' : 'asc',
				),
				$base_url
			)
		);
		?>
		"><?php echo esc_html( bhg_t( 'label_username', 'Username' ) ); ?></a></th>
		<th><a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'orderby' => 'display_name',
					'order'   => 'ASC' === $order ? 'desc' : 'asc',
				),
				$base_url
			)
		);
		?>
		"><?php echo esc_html( bhg_t( 'name', 'Name' ) ); ?></a></th>
		<th><?php echo esc_html( bhg_t( 'label_real_name', 'Real Name' ) ); ?></th>
		<th><a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'orderby' => 'user_email',
					'order'   => 'ASC' === $order ? 'desc' : 'asc',
				),
				$base_url
			)
		);
		?>
		"><?php echo esc_html( bhg_t( 'label_email', 'Email' ) ); ?></a></th>
		<?php if ( $has_affiliates ) : ?>
			<?php foreach ( $affiliate_sites as $site ) : ?>
				<?php $site_label = $site->name ? $site->name : sprintf( /* translators: %d: affiliate ID. */ esc_html( bhg_t( 'label_affiliate_site_number', 'Affiliate Site %d' ) ), (int) $site->id ); ?>
				<th><?php echo esc_html( $site_label ); ?></th>
			<?php endforeach; ?>
		<?php else : ?>
			<th><?php echo esc_html( bhg_t( 'affiliate_user', 'Affiliate' ) ); ?></th>
		<?php endif; ?>
		<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $users ) ) : ?>
		<tr><td colspan="<?php echo esc_attr( $total_columns ); ?>"><?php echo esc_html( bhg_t( 'no_users_found', 'No users found.' ) ); ?></td></tr>
			<?php
		else :
			foreach ( $users as $u ) :
				$form_id   = 'bhg-user-' . (int) $u->ID;
				$real_name = get_user_meta( $u->ID, 'bhg_real_name', true );
				$user_affiliates = function_exists( 'bhg_get_user_affiliate_websites' ) ? bhg_get_user_affiliate_websites( $u->ID ) : array();
                                $user_affiliates = array_map( 'intval', (array) $user_affiliates );
				?>
		<tr>
			<td><?php echo esc_html( $u->user_login ); ?></td>
			<td><?php echo esc_html( $u->display_name ); ?></td>
			<td><input type="text" name="bhg_real_name" form="<?php echo esc_attr( $form_id ); ?>" value="<?php echo esc_attr( $real_name ); ?>" /></td>
			<td><?php echo esc_html( $u->user_email ); ?></td>
			<?php if ( $has_affiliates ) : ?>
				<?php foreach ( $affiliate_sites as $site ) : ?>
					<?php $site_id = isset( $site->id ) ? (int) $site->id : 0; ?>
					<td class="bhg-text-center"><input type="checkbox" name="bhg_affiliate_sites[]" value="<?php echo esc_attr( $site_id ); ?>" form="<?php echo esc_attr( $form_id ); ?>" <?php checked( in_array( $site_id, $user_affiliates, true ) ); ?> /></td>
				<?php endforeach; ?>
			<?php else : ?>
				<td class="bhg-text-center"><?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
			<?php endif; ?>
			<td>
			<form id="<?php echo esc_attr( $form_id ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="bhg_save_user_meta" />
<input type="hidden" name="user_id" value="<?php echo esc_attr( (int) $u->ID ); ?>" />
								<?php wp_nonce_field( 'bhg_save_user_meta', 'bhg_save_user_meta_nonce' ); ?>
				<button type="submit" class="button button-primary"><?php echo esc_html( bhg_t( 'button_save', 'Save' ) ); ?></button>
			</form>
			<a class="button" href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $u->ID ) ); ?>"><?php echo esc_html( bhg_t( 'view_edit', 'View / Edit' ) ); ?></a>
			</td>
		</tr>
					<?php
		endforeach;
endif;
		?>
	</tbody>
	</table>

	<?php
	$total_pages = ceil( $total / $per_page );
	if ( $total_pages > 1 ) {
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo paginate_links(
			array(
				'base'      => add_query_arg( 'paged', '%#%', $base_url ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => $total_pages,
				'current'   => $paged,
			)
		);
		echo '</div></div>';
	}
	?>
</div>
