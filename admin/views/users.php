<?php
/**
 * Users management view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$current_page   = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
$items_per_page = 30;
$search_term    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

if ( isset( $_GET['s'] ) ) {
	check_admin_referer( 'bhg_users_search', 'bhg_users_search_nonce' );
}

$allowed_orderby = array( 'user_login', 'display_name', 'user_email' );
$order_by        = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'user_login';

if ( ! in_array( $order_by, $allowed_orderby, true ) ) {
	$order_by = 'user_login';
}

$order_direction = ( isset( $_GET['order'] ) && 'desc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ) ? 'DESC' : 'ASC';

$args = array(
	'number'         => $items_per_page,
	'offset'         => ( $current_page - 1 ) * $items_per_page,
	'orderby'        => $order_by,
	'order'          => $order_direction,
	'search'         => $search_term ? '*' . $search_term . '*' : '',
	'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
);

$user_query = new WP_User_Query( $args );
$users      = $user_query->get_results();
$total      = (int) $user_query->get_total();

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
				<input type="search" id="user-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
				<input type="submit" id="search-submit" class="button" value="<?php echo esc_attr( bhg_t( 'label_search', 'Search' ) ); ?>" />
</p>
			</form>

<table class="widefat striped">
<thead>
	<tr>
<th scope="col">
<?php
$login_url = add_query_arg(
	array(
		'orderby' => 'user_login',
		'order'   => ( 'ASC' === $order_direction ) ? 'desc' : 'asc',
	),
	$base_url
);
?>
<a href="<?php echo esc_url( $login_url ); ?>"><?php echo esc_html( bhg_t( 'label_username', 'Username' ) ); ?></a>
</th>
<th scope="col">
<?php
$name_url = add_query_arg(
	array(
		'orderby' => 'display_name',
		'order'   => ( 'ASC' === $order_direction ) ? 'desc' : 'asc',
	),
	$base_url
);
?>
<a href="<?php echo esc_url( $name_url ); ?>"><?php echo esc_html( bhg_t( 'name', 'Name' ) ); ?></a>
</th>
<th scope="col"><?php echo esc_html( bhg_t( 'label_real_name', 'Real Name' ) ); ?></th>
<th scope="col">
<?php
$email_url = add_query_arg(
	array(
		'orderby' => 'user_email',
		'order'   => ( 'ASC' === $order_direction ) ? 'desc' : 'asc',
	),
	$base_url
);
?>
<a href="<?php echo esc_url( $email_url ); ?>"><?php echo esc_html( bhg_t( 'label_email', 'Email' ) ); ?></a>
</th>
<th scope="col"><?php echo esc_html( bhg_t( 'affiliate_user', 'Affiliate' ) ); ?></th>
<th scope="col"><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
	</tr>
</thead>
<tbody>
<?php if ( empty( $users ) ) : ?>
	<tr>
		<td colspan="6"><?php echo esc_html( bhg_t( 'no_users_found', 'No users found.' ) ); ?></td>
	</tr>
<?php else : ?>
	<?php foreach ( $users as $user ) : ?>
		<?php
		$form_id   = 'bhg-user-' . (int) $user->ID;
		$real_name = get_user_meta( $user->ID, 'bhg_real_name', true );
		$is_aff    = (int) get_user_meta( $user->ID, 'bhg_is_affiliate', true );
		?>
	<tr>
		<td><?php echo esc_html( $user->user_login ); ?></td>
		<td><?php echo esc_html( $user->display_name ); ?></td>
		<td><input type="text" name="bhg_real_name" form="<?php echo esc_attr( $form_id ); ?>" value="<?php echo esc_attr( $real_name ); ?>" /></td>
		<td><?php echo esc_html( $user->user_email ); ?></td>
		<td class="bhg-text-center"><input type="checkbox" name="bhg_is_affiliate" value="1" form="<?php echo esc_attr( $form_id ); ?>" <?php checked( $is_aff, 1 ); ?> /></td>
		<td>
			<form id="<?php echo esc_attr( $form_id ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="bhg_save_user_meta" />
				<input type="hidden" name="user_id" value="<?php echo esc_attr( (int) $user->ID ); ?>" />
		<?php wp_nonce_field( 'bhg_save_user_meta', 'bhg_save_user_meta_nonce' ); ?>
				<button type="submit" class="button button-primary"><?php echo esc_html( bhg_t( 'button_save', 'Save' ) ); ?></button>
			</form>
			<a class="button" href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $user->ID ) ); ?>"><?php echo esc_html( bhg_t( 'view_edit', 'View / Edit' ) ); ?></a>
		</td>
	</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

<?php
$total_pages = (int) ceil( $total / $items_per_page );

if ( $total_pages > 1 ) {
	$pagination = paginate_links(
		array(
			'base'      => add_query_arg( 'paged', '%#%', $base_url ),
			'format'    => '',
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'total'     => $total_pages,
			'current'   => $current_page,
		)
	);

	if ( $pagination ) {
		echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $pagination ) . '</div></div>';
	}
}
?>
</div>
