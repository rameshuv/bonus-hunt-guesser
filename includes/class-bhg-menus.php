<?php
/**
 * Admin menu registration for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Menus' ) ) {
	/**
	 * Handles all admin menu pages for the plugin.
	 */
	class BHG_Menus {
		/**
		 * Class instance.
		 *
		 * @var BHG_Menus|null
		 */
		private static $instance = null;

		/**
		 * Whether the menus have been initialised.
		 *
		 * @var bool
		 */
		private $initialized = false;

		/**
		 * Retrieve the singleton instance.
		 *
		 * @return BHG_Menus Instance of the class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {}

		/**
		 * Initialise hooks.
		 *
		 * @return void
		 */
		public function init() {
			if ( $this->initialized ) {
				return;
			}

			$this->initialized = true;

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
			add_action( 'init', array( $this, 'register_locations' ), 5 );
		}

		/**
		 * Enqueue admin assets.
		 *
		 * @param string $hook Current admin page.
		 * @return void
		 */
		public function assets( $hook ) {
			if ( false !== strpos( $hook, 'bhg' ) ) {
				wp_enqueue_style( 'bhg-admin', BHG_PLUGIN_URL . 'assets/css/admin.css', array(), defined( 'BHG_VERSION' ) ? BHG_VERSION : null );
				wp_enqueue_script( 'bhg-admin', BHG_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), defined( 'BHG_VERSION' ) ? BHG_VERSION : null, true );
			}
		}

		/**
		 * Register admin menu pages.
		 *
		 * @return void
		 */
		public function admin_menu() {
			// Prevent duplicate top-level menu.
			global $menu;
			foreach ( (array) $menu as $item ) {
				if ( isset( $item[2] ) && 'bhg' === $item[2] ) {
					return;
				}
			}

			$cap  = $this->admin_capability();
			$slug = 'bhg';

			add_menu_page(
				esc_html__( 'Bonus Hunt', 'bonus-hunt-guesser' ),
				esc_html__( 'Bonus Hunt', 'bonus-hunt-guesser' ),
				$cap,
				$slug,
				array( $this, 'render_dashboard' ),
				'dashicons-awards',
				26
			);

			add_submenu_page( $slug, esc_html__( 'Dashboard', 'bonus-hunt-guesser' ), esc_html__( 'Dashboard', 'bonus-hunt-guesser' ), $cap, $slug, array( $this, 'render_dashboard' ) );
			add_submenu_page( $slug, esc_html__( 'Bonus Hunts', 'bonus-hunt-guesser' ), esc_html__( 'Bonus Hunts', 'bonus-hunt-guesser' ), $cap, 'bhg-bonus-hunts', array( $this, 'render_bonus_hunts' ) );
			add_submenu_page( $slug, esc_html__( 'Users', 'bonus-hunt-guesser' ), esc_html__( 'Users', 'bonus-hunt-guesser' ), $cap, 'bhg-users', array( $this, 'render_users' ) );
			add_submenu_page( $slug, esc_html__( 'Affiliate Websites', 'bonus-hunt-guesser' ), esc_html__( 'Affiliate Websites', 'bonus-hunt-guesser' ), $cap, 'bhg-affiliate-websites', array( $this, 'render_affiliates' ) );
			add_submenu_page( $slug, esc_html__( 'Tournaments', 'bonus-hunt-guesser' ), esc_html__( 'Tournaments', 'bonus-hunt-guesser' ), $cap, 'bhg-tournaments', array( $this, 'render_tournaments' ) );
			add_submenu_page( $slug, esc_html__( 'Translations', 'bonus-hunt-guesser' ), esc_html__( 'Translations', 'bonus-hunt-guesser' ), $cap, 'bhg-translations', array( $this, 'render_translations' ) );
			add_submenu_page( $slug, esc_html__( 'Settings', 'bonus-hunt-guesser' ), esc_html__( 'Settings', 'bonus-hunt-guesser' ), $cap, 'bhg-settings', array( $this, 'render_settings' ) );
			add_submenu_page( $slug, esc_html__( 'Database', 'bonus-hunt-guesser' ), esc_html__( 'Database', 'bonus-hunt-guesser' ), $cap, 'bhg-database', array( $this, 'render_database' ) );
			add_submenu_page( $slug, esc_html__( 'Tools', 'bonus-hunt-guesser' ), esc_html__( 'Tools', 'bonus-hunt-guesser' ), $cap, 'bhg-tools', array( $this, 'render_tools' ) );
		}

		/**
		 * Determine required capability.
		 *
		 * @return string
		 */
		private function admin_capability() {
			return apply_filters( 'bhg_admin_capability', 'manage_options' );
		}

		/**
		 * Render a view file.
		 *
		 * @param string $view View slug.
		 * @param array  $vars Variables to make available to the view.
		 * @return void
		 */
		public function view( $view, $vars = array() ) {
			if ( ! current_user_can( $this->admin_capability() ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
			}

			if ( is_array( $vars ) ) {
				foreach ( $vars as $key => $value ) {
					${$key} = $value; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				}
			}

			$header_path = BHG_PLUGIN_DIR . 'admin/views/header.php';
			if ( file_exists( $header_path ) ) {
				include $header_path;
			}

			$view_path = BHG_PLUGIN_DIR . 'admin/views/' . $view . '.php';
			if ( file_exists( $view_path ) ) {
				include $view_path;
			} else {
				echo '<div class="wrap"><h2>' . esc_html__( 'View Not Found', 'bonus-hunt-guesser' ) . '</h2>';
				// translators: %s: view slug.
				echo '<p>' . sprintf( esc_html__( 'The requested view "%s" was not found.', 'bonus-hunt-guesser' ), esc_html( $view ) ) . '</p></div>';
			}
		}

		/**
		 * Render dashboard view.
		 *
		 * @return void
		 */
		public function render_dashboard() {
			$this->view( 'dashboard' );
		}

		/**
		 * Render bonus hunts view.
		 *
		 * @return void
		 */
		public function render_bonus_hunts() {
			$this->view( 'bonus-hunts' );
		}

		/**
		 * Render users view.
		 *
		 * @return void
		 */
		public function render_users() {
			$this->view( 'users' );
		}

		/**
		 * Render affiliates view.
		 *
		 * @return void
		 */
		public function render_affiliates() {
			$this->view( 'affiliate-websites' );
		}

		/**
		 * Render tournaments view.
		 *
		 * @return void
		 */
		public function render_tournaments() {
			$this->view( 'tournaments' );
		}

		/**
		 * Render translations view.
		 *
		 * @return void
		 */
		public function render_translations() {
			$this->view( 'translations' );
		}

		/**
		 * Render settings view.
		 *
		 * @return void
		 */
		public function render_settings() {
			$this->view( 'settings' );
		}

		/**
		 * Render database view.
		 *
		 * @return void
		 */
		public function render_database() {
			$this->view( 'database' );
		}

		/**
		 * Render tools view.
		 *
		 * @return void
		 */
		public function render_tools() {
			$this->view( 'tools' );
		}

		/**
		 * Register navigation menu locations.
		 *
		 * @return void
		 */
		public function register_locations() {
			static $done = false;
			if ( $done ) {
				return;
			}

			$done = true;
			register_nav_menus(
				array(
					'bhg_menu_admin'    => esc_html__( 'BHG Menu — Admin/Moderators', 'bonus-hunt-guesser' ),
					'bhg_menu_loggedin' => esc_html__( 'BHG Menu — Logged-in Users', 'bonus-hunt-guesser' ),
					'bhg_menu_guests'   => esc_html__( 'BHG Menu — Guests', 'bonus-hunt-guesser' ),
				)
			);
		}
	}
}

// Bootstrap once.
if ( class_exists( 'BHG_Menus' ) ) {
	BHG_Menus::get_instance()->init();
}
