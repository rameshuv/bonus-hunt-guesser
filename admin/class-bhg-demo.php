<?php
/**
 * Demo Tools Admin Class
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BHG_Demo
 *
 * Handles demo tools for admin.
 */
class BHG_Demo {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'demo_menu' ) );
		add_action( 'admin_post_bhg_demo_reseed', array( $this, 'reseed' ) );
	}

	/**
	 * Add demo submenu page.
	 *
	 * @return void
	 */
	public function demo_menu() {
		add_submenu_page(
			'bhg_dashboard',
			__( 'Demo Tools', 'bonus-hunt-guesser' ),
			__( 'Demo Tools', 'bonus-hunt-guesser' ),
			'manage_options',
			'bhg_demo',
			array( $this, 'render_demo' )
		);
	}

	/**
	 * Render the demo tools page.
	 *
	 * @return void
	 */
	public function render_demo() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Demo Tools', 'bonus-hunt-guesser' ) . '</h1>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="bhg_demo_reseed" />';
		wp_nonce_field( 'bhg_demo_reseed', 'bhg_demo_reseed_nonce' );
		submit_button( __( 'Reset & Reseed Demo', 'bonus-hunt-guesser' ) );
		echo '</form></div>';
	}

	/**
	 * Reset and reseed demo data.
	 *
	 * @return void
	 */
	public function reseed() {
		check_admin_referer( 'bhg_demo_reseed', 'bhg_demo_reseed_nonce' );
		global $wpdb;

                // Wipe demo data.
                $hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
                $tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

                                $wpdb->query(
                                        $wpdb->prepare(
                                                "DELETE FROM {$hunts_table} WHERE title LIKE %s",
                                                '%\(Demo\)%'
                                        )
                                );

                                $wpdb->query(
                                        $wpdb->prepare(
                                                "DELETE FROM {$tournaments_table} WHERE title LIKE %s",
                                                '%\(Demo\)%'
                                        )
                                );

		// Insert demo hunt.
		$wpdb->insert(
			$hunts_table,
			array(
				'title'            => 'Sample Hunt (Demo)',
				'starting_balance' => 1000,
				'num_bonuses'      => 5,
				'status'           => 'open',
			)
		);

		// Insert demo tournament.
		$wpdb->insert(
			$tournaments_table,
			array(
				'title'  => 'August Tournament (Demo)',
				'status' => 'active',
			)
		);

			wp_safe_redirect( esc_url_raw( BHG_Utils::admin_url( 'admin.php?page=bhg_demo&demo_reset=1' ) ) );
		exit;
	}
}

new BHG_Demo();
