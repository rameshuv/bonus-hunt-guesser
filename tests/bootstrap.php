<?php // phpcs:ignore WordPress.NamingConventions.ValidFileName.ClassFileName

/**
 * Test bootstrap helpers.
 *
 * Provides minimal WordPress function shims required for the test suite to run
 * in isolation from a full WordPress installation.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'esc_sql' ) ) {
	/**
	 * Return the provided value without modification.
	 *
	 * This is a no-op shim for test environments.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	function esc_sql( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Return a deterministic timestamp for tests.
	 *
	 * @param string $type Unused context type.
	 * @return string Fixed timestamp.
	 */
	function current_time( $type ) {
		if ( 'timestamp' === $type ) {
			return '1704067200';
		}

		return '2024-01-01 00:00:00';
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * Basic implementation of sanitize_key().
	 *
	 * @param string $key Key to sanitize.
	 * @return string Sanitized key.
	 */
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );

		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'absint' ) ) {
	/**
	 * Absolute integer helper.
	 *
	 * @param int|string $value Value to convert.
	 * @return int Absolute integer.
	 */
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'bhg_get_hunt_tournament_ids' ) ) {
	/**
	 * Retrieve tournament IDs linked to a hunt.
	 *
	 * @param int $hunt_id Hunt identifier.
	 * @return int[] Linked tournament IDs.
	 */
	function bhg_get_hunt_tournament_ids( $hunt_id ) {
		global $wpdb;

		if ( ! isset( $wpdb->hunt_tournaments ) ) {
			return array();
		}

		$ids = array();
		foreach ( $wpdb->hunt_tournaments as $map ) {
			if ( (int) $map['hunt_id'] === (int) $hunt_id ) {
				$ids[] = (int) $map['tournament_id'];
			}
		}

		return array_values( array_unique( $ids ) );
	}
}

if ( ! function_exists( 'bhg_log' ) ) {
	/**
	 * Stub logger used during testing.
	 *
	 * @param mixed $message Message payload.
	 * @return void
	 */
	function bhg_log( $message ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Intentionally left blank for tests.
	}
}

if ( ! function_exists( 'wp_list_pluck' ) ) {
	/**
	 * Minimal implementation of wp_list_pluck().
	 *
	 * @param array  $input_list Source array or list of objects.
	 * @param string $field      Field to pluck.
	 * @return array
	 */
	function wp_list_pluck( $input_list, $field ) {
		$values = array();

		foreach ( (array) $input_list as $item ) {
			if ( is_object( $item ) && isset( $item->{$field} ) ) {
				$values[] = $item->{$field};
			} elseif ( is_array( $item ) && isset( $item[ $field ] ) ) {
				$values[] = $item[ $field ];
			}
		}

		return $values;
	}
}

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed,Generic.Files.OneObjectStructurePerFile.MultipleFound
if ( ! class_exists( 'BHG_DB' ) ) {
	/**
	 * Minimal database migration shim for tests.
	 */
	class BHG_DB {
		/**
		 * Placeholder migrate method.
		 *
		 * @return void
		 */
		public static function migrate() {}
	}
}
// phpcs:enable Universal.Files.SeparateFunctionsFromOO.Mixed,Generic.Files.OneObjectStructurePerFile.MultipleFound

require_once __DIR__ . '/../includes/class-bhg-models.php';
require_once __DIR__ . '/support/class-mock-wpdb.php';
