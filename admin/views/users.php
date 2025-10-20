<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

require_once BHG_PLUGIN_DIR . 'admin/class-bhg-users-table.php';

$users_table = new BHG_Users_Table();
$users_table->prepare_items();
?>
<div class="wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_users', 'Users' ) ); ?></h1>

        <form method="get">
                <input type="hidden" name="page" value="bhg-users" />
                <?php
                $users_table->search_box( bhg_t( 'search_users', 'Search Users' ), 'bhg-users' );
                $users_table->display();
                ?>
        </form>
</div>
