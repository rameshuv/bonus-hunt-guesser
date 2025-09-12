<?php
/**
 * Database schema management for Bonus Hunt Guesser.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Handles database schema creation and migrations for the plugin.
 */
class BHG_DB {

	/**
	 * Static wrapper to support legacy static calls.
	 *
	 * @return void
	 */
	public static function migrate() {
		$db = new self();
		$db->create_tables();

				global $wpdb;
				$tours_table = $wpdb->prefix . 'bhg_tournaments';

		// Drop legacy "period" column and related index if they exist.
		if ( $db->column_exists( $tours_table, 'period' ) ) {
			// Remove unique index first if present.
			if ( $db->index_exists( $tours_table, 'type_period' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tours_table}` DROP INDEX type_period" );
			}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$tours_table}` DROP COLUMN period" );
		}
	}

	/**
	 * Create or update required database tables.
	 *
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

				$hunts_table        = $wpdb->prefix . 'bhg_bonus_hunts';
				$guesses_table      = $wpdb->prefix . 'bhg_guesses';
				$tours_table        = $wpdb->prefix . 'bhg_tournaments';
				$tres_table         = $wpdb->prefix . 'bhg_tournament_results';
				$ads_table          = $wpdb->prefix . 'bhg_ads';
				$trans_table        = $wpdb->prefix . 'bhg_translations';
				$aff_websites_table = $wpdb->prefix . 'bhg_affiliate_websites';
				$winners_table      = $wpdb->prefix . 'bhg_hunt_winners';

		$sql = array();

				// Bonus Hunts.
				$sql[] = "CREATE TABLE `{$hunts_table}` (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
title VARCHAR(190) NOT NULL,
starting_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
num_bonuses INT UNSIGNED NOT NULL DEFAULT 0,
prizes TEXT NULL,
affiliate_site_id BIGINT UNSIGNED NULL,
tournament_id BIGINT UNSIGNED NULL,
winners_count INT UNSIGNED NOT NULL DEFAULT 3,
guessing_enabled TINYINT(1) NOT NULL DEFAULT 1,
final_balance DECIMAL(12,2) NULL,
status VARCHAR(20) NOT NULL DEFAULT 'open',
created_at DATETIME NULL,
updated_at DATETIME NULL,
closed_at DATETIME NULL,
PRIMARY KEY  (id),
KEY status (status),
KEY tournament_id (tournament_id)
) {$charset_collate};";

				// Guesses.
				$sql[] = "CREATE TABLE `{$guesses_table}` (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			hunt_id BIGINT UNSIGNED NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			guess DECIMAL(12,2) NOT NULL,
			created_at DATETIME NULL,
			PRIMARY KEY  (id),
			KEY hunt_id (hunt_id),
			KEY user_id (user_id)
		) {$charset_collate};";

		// Tournaments.
		$sql[] = "CREATE TABLE `{$tours_table}` (
                                                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                title VARCHAR(190) NOT NULL,
                                                description TEXT NULL,
                                                type VARCHAR(20) NOT NULL,
                                                participants_mode VARCHAR(20) NOT NULL DEFAULT 'winners',
                                                start_date DATE NULL,
                                                end_date DATE NULL,
                                                status VARCHAR(20) NOT NULL DEFAULT 'active',
                                                created_at DATETIME NULL,
                                                updated_at DATETIME NULL,
                                                PRIMARY KEY  (id),
                                                KEY type (type),
                                                KEY status (status)
                                ) {$charset_collate};";

		// Tournament Results.
		$sql[] = "CREATE TABLE `{$tres_table}` (
						id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						tournament_id BIGINT UNSIGNED NOT NULL,
						user_id BIGINT UNSIGNED NOT NULL,
						wins INT UNSIGNED NOT NULL DEFAULT 0,
						last_win_date DATETIME NULL,
						PRIMARY KEY  (id),
						KEY tournament_id (tournament_id),
						KEY user_id (user_id)
				) {$charset_collate};";

		// Ads.
		$sql[] = "CREATE TABLE `{$ads_table}` (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(190) NOT NULL,
			content TEXT NULL,
			link_url VARCHAR(255) NULL,
			placement VARCHAR(50) NOT NULL DEFAULT 'none',
			visible_to VARCHAR(30) NOT NULL DEFAULT 'all',
			target_pages TEXT NULL,
			active TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY  (id),
			KEY placement (placement),
			KEY visible_to (visible_to)
		) {$charset_collate};";

		// Affiliate Websites.
		$sql[] = "CREATE TABLE `{$aff_websites_table}` (
					   id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					   name VARCHAR(190) NOT NULL,
					   slug VARCHAR(190) NOT NULL,
					   url VARCHAR(255) NULL,
					   status VARCHAR(20) NOT NULL DEFAULT 'active',
					   created_at DATETIME NULL,
					   updated_at DATETIME NULL,
					   PRIMARY KEY  (id),
					   UNIQUE KEY slug_unique (slug)
			   ) {$charset_collate};";

		// Hunt Winners.
		$sql[] = "CREATE TABLE `{$winners_table}` (
					   id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					   hunt_id BIGINT UNSIGNED NOT NULL,
					   user_id BIGINT UNSIGNED NOT NULL,
					   position INT UNSIGNED NOT NULL,
					   guess DECIMAL(12,2) NOT NULL,
					   diff DECIMAL(12,2) NOT NULL,
					   created_at DATETIME NULL,
					   PRIMARY KEY  (id),
					   KEY hunt_id (hunt_id),
					   KEY user_id (user_id)
			   ) {$charset_collate};";

		foreach ( $sql as $statement ) {
				dbDelta( $statement );
		}

				// Translations table handled separately.
				$this->create_table_translations();

				// Idempotent ensure for columns/indexes.
		try {
						// Hunts: winners_count, affiliate_site_id, tournament_id.
			$need = array(
				'winners_count'     => "ALTER TABLE `{$hunts_table}` ADD COLUMN winners_count INT UNSIGNED NOT NULL DEFAULT 3",
				'affiliate_site_id' => "ALTER TABLE `{$hunts_table}` ADD COLUMN affiliate_site_id BIGINT UNSIGNED NULL",
				'tournament_id'     => "ALTER TABLE `{$hunts_table}` ADD COLUMN tournament_id BIGINT UNSIGNED NULL",
				'guessing_enabled'  => "ALTER TABLE `{$hunts_table}` ADD COLUMN guessing_enabled TINYINT(1) NOT NULL DEFAULT 1",
				'final_balance'     => "ALTER TABLE `{$hunts_table}` ADD COLUMN final_balance DECIMAL(12,2) NULL",
				'status'            => "ALTER TABLE `{$hunts_table}` ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'open'",
			);
			foreach ( $need as $c => $alter ) {
				if ( ! $this->column_exists( $hunts_table, $c ) ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
								$wpdb->query( $alter );
				}
			}
			if ( ! $this->index_exists( $hunts_table, 'tournament_id' ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunts_table}` ADD KEY tournament_id (tournament_id)" );
			}

						// Tournaments: make sure common columns exist.
						$tneed = array(
							'title'             => "ALTER TABLE `{$tours_table}` ADD COLUMN title VARCHAR(190) NOT NULL",
							'description'       => "ALTER TABLE `{$tours_table}` ADD COLUMN description TEXT NULL",
							'type'              => "ALTER TABLE `{$tours_table}` ADD COLUMN type VARCHAR(20) NOT NULL",
							'participants_mode' => "ALTER TABLE `{$tours_table}` ADD COLUMN participants_mode VARCHAR(20) NOT NULL DEFAULT 'winners'",
							'start_date'        => "ALTER TABLE `{$tours_table}` ADD COLUMN start_date DATE NULL",
							'end_date'          => "ALTER TABLE `{$tours_table}` ADD COLUMN end_date DATE NULL",
							'status'            => "ALTER TABLE `{$tours_table}` ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'",
						);
						foreach ( $tneed as $c => $alter ) {
							if ( ! $this->column_exists( $tours_table, $c ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( $alter );
							}
						}

												// Tournament results columns.
						$trrneed = array(
							'tournament_id' => "ALTER TABLE `{$tres_table}` ADD COLUMN tournament_id BIGINT UNSIGNED NOT NULL",
							'user_id'       => "ALTER TABLE `{$tres_table}` ADD COLUMN user_id BIGINT UNSIGNED NOT NULL",
							'wins'          => "ALTER TABLE `{$tres_table}` ADD COLUMN wins INT UNSIGNED NOT NULL DEFAULT 0",
							'last_win_date' => "ALTER TABLE `{$tres_table}` ADD COLUMN last_win_date DATETIME NULL",
						);
						foreach ( $trrneed as $c => $alter ) {
							if ( ! $this->column_exists( $tres_table, $c ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( $alter );
							}
						}
						if ( ! $this->index_exists( $tres_table, 'tournament_id' ) ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
								$wpdb->query( "ALTER TABLE `{$tres_table}` ADD KEY tournament_id (tournament_id)" );
						}
						if ( ! $this->index_exists( $tres_table, 'user_id' ) ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
								$wpdb->query( "ALTER TABLE `{$tres_table}` ADD KEY user_id (user_id)" );
						}

												// Ads columns.
												$aneed = array(
													'title'        => "ALTER TABLE `{$ads_table}` ADD COLUMN title VARCHAR(190) NOT NULL",
													'content'      => "ALTER TABLE `{$ads_table}` ADD COLUMN content TEXT NULL",
													'link_url'     => "ALTER TABLE `{$ads_table}` ADD COLUMN link_url VARCHAR(255) NULL",
													'placement'    => "ALTER TABLE `{$ads_table}` ADD COLUMN placement VARCHAR(50) NOT NULL DEFAULT 'none'",
													'visible_to'   => "ALTER TABLE `{$ads_table}` ADD COLUMN visible_to VARCHAR(30) NOT NULL DEFAULT 'all'",
													'target_pages' => "ALTER TABLE `{$ads_table}` ADD COLUMN target_pages TEXT NULL",
													'active'       => "ALTER TABLE `{$ads_table}` ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1",
													'created_at'   => "ALTER TABLE `{$ads_table}` ADD COLUMN created_at DATETIME NULL",
													'updated_at'   => "ALTER TABLE `{$ads_table}` ADD COLUMN updated_at DATETIME NULL",
												);
												foreach ( $aneed as $c => $alter ) {
													if ( ! $this->column_exists( $ads_table, $c ) ) {
															// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
															$wpdb->query( $alter );
													}
												}

												// Translations columns.
												$trneed = array(
													'slug' => "ALTER TABLE `{$trans_table}` ADD COLUMN slug VARCHAR(191) NOT NULL",
													'default_text' => "ALTER TABLE `{$trans_table}` ADD COLUMN default_text LONGTEXT NOT NULL",
													'text' => "ALTER TABLE `{$trans_table}` ADD COLUMN `text` LONGTEXT NULL",
													'locale' => "ALTER TABLE `{$trans_table}` ADD COLUMN locale VARCHAR(20) NOT NULL",
													'created_at' => "ALTER TABLE `{$trans_table}` ADD COLUMN created_at DATETIME NULL",
													'updated_at' => "ALTER TABLE `{$trans_table}` ADD COLUMN updated_at DATETIME NULL",
												);
												foreach ( $trneed as $c => $alter ) {
													if ( ! $this->column_exists( $trans_table, $c ) ) {
                                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( $alter );
													}
												}
												// Ensure composite unique index on (slug, locale).
												// Drop legacy single-column indexes if present first.
												if ( $this->index_exists( $trans_table, 'slug' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$trans_table}` DROP INDEX slug" );
												}
												if ( $this->index_exists( $trans_table, 'slug_unique' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$trans_table}` DROP INDEX slug_unique" );
												}
												if ( $this->index_exists( $trans_table, 'tkey_locale' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$trans_table}` DROP INDEX tkey_locale" );
												}
												if ( ! $this->index_exists( $trans_table, 'slug_locale' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$trans_table}` ADD UNIQUE KEY slug_locale (slug, locale)" );
												}

												// Affiliate websites columns / unique index.
												$afw_need = array(
													'name' => "ALTER TABLE `{$aff_websites_table}` ADD COLUMN name VARCHAR(190) NOT NULL",
													'slug' => "ALTER TABLE `{$aff_websites_table}` ADD COLUMN slug VARCHAR(190) NOT NULL",
													'url'  => "ALTER TABLE `{$aff_websites_table}` ADD COLUMN url VARCHAR(255) NULL",
													'status' => "ALTER TABLE `{$aff_websites_table}` ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'",
													'created_at' => "ALTER TABLE `{$aff_websites_table}` ADD COLUMN created_at DATETIME NULL",
													'updated_at' => "ALTER TABLE `{$aff_websites_table}` ADD COLUMN updated_at DATETIME NULL",
												);
												foreach ( $afw_need as $c => $alter ) {
													if ( ! $this->column_exists( $aff_websites_table, $c ) ) {
                                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( $alter );
													}
												}
												if ( ! $this->index_exists( $aff_websites_table, 'slug_unique' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$aff_websites_table}` ADD UNIQUE KEY slug_unique (slug)" );
												}

												// Hunt winners columns / indexes.
												$hwneed = array(
													'hunt_id' => "ALTER TABLE `{$winners_table}` ADD COLUMN hunt_id BIGINT UNSIGNED NOT NULL",
													'user_id' => "ALTER TABLE `{$winners_table}` ADD COLUMN user_id BIGINT UNSIGNED NOT NULL",
													'position' => "ALTER TABLE `{$winners_table}` ADD COLUMN position INT UNSIGNED NOT NULL",
													'guess' => "ALTER TABLE `{$winners_table}` ADD COLUMN guess DECIMAL(12,2) NOT NULL",
													'diff' => "ALTER TABLE `{$winners_table}` ADD COLUMN diff DECIMAL(12,2) NOT NULL",
													'created_at' => "ALTER TABLE `{$winners_table}` ADD COLUMN created_at DATETIME NULL",
												);
												foreach ( $hwneed as $c => $alter ) {
													if ( ! $this->column_exists( $winners_table, $c ) ) {
                                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( $alter );
													}
												}
												if ( ! $this->index_exists( $winners_table, 'hunt_id' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$winners_table}` ADD KEY hunt_id (hunt_id)" );
												}
												if ( ! $this->index_exists( $winners_table, 'user_id' ) ) {
														// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
														$wpdb->query( "ALTER TABLE `{$winners_table}` ADD KEY user_id (user_id)" );
												}
		} catch ( Throwable $e ) {
			if ( function_exists( 'error_log' ) ) {
                                                                                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
																				error_log( '[BHG] Schema ensure error: ' . $e->getMessage() );
			}
		}
	}

		/**
		 * Create or update the translations table.
		 *
		 * @return void
		 */
	private function create_table_translations() {
			global $wpdb;

						$table           = $wpdb->prefix . 'bhg_translations';
						$charset_collate = $wpdb->get_charset_collate();

						$sql = "CREATE TABLE `{$table}` (
					   id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					   slug VARCHAR(191) NOT NULL,
					   default_text LONGTEXT NOT NULL,
					   text LONGTEXT NULL,
					   locale VARCHAR(20) NOT NULL,
					   created_at DATETIME NULL,
					   updated_at DATETIME NULL,
					   PRIMARY KEY  (id),
					   UNIQUE KEY slug_locale (slug, locale)
			   ) {$charset_collate};";

			dbDelta( $sql );
	}

		/**
		 * Retrieve all affiliate websites.
		 *
		 * @return array List of affiliate website objects.
		 */
	public function get_affiliate_websites() {
						global $wpdb;

						$table = $wpdb->prefix . 'bhg_affiliate_websites';

                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
												return $wpdb->get_results( "SELECT id, name, slug, url, status FROM `{$table}` ORDER BY name ASC" );
	}

		/**
		 * Check if a column exists, falling back when information_schema is not accessible.
		 *
		 * @param string $table  Table name.
		 * @param string $column Column to check.
		 * @return bool
		 */
	private function column_exists( $table, $column ) {
		global $wpdb;

		$table  = esc_sql( $table );
		$column = esc_sql( $column );

		$wpdb->last_error = '';
		$sql              = $wpdb->prepare(
			'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME=%s',
			DB_NAME,
			$table,
			$column
		);
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$exists = $wpdb->get_var( $sql );

		if ( $wpdb->last_error ) {
			$wpdb->last_error = '';
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
						$exists = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", $column ) );
		}

		return ! empty( $exists );
	}

	/**
	 * Check if an index exists, falling back when information_schema is not accessible.
	 *
	 * @param string $table Table name.
	 * @param string $index Index to check.
	 * @return bool
	 */
	private function index_exists( $table, $index ) {
		global $wpdb;

		$table = esc_sql( $table );
		$index = esc_sql( $index );

		$wpdb->last_error = '';
		$sql              = $wpdb->prepare(
			'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND INDEX_NAME=%s',
			DB_NAME,
			$table,
			$index
		);
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$exists = $wpdb->get_var( $sql );

		if ( $wpdb->last_error ) {
			$wpdb->last_error = '';
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
						$exists = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM `{$table}` WHERE Key_name=%s", $index ) );
		}

		return ! empty( $exists );
	}
}
