<?php // phpcs:ignoreFile
/**
 * Data layer utilities for Bonus Hunt Guesser.
 *
 * This class previously handled guess submissions directly. Guess handling is
 * now centralized through {@see bhg_handle_submit_guess()} in
 * `bonus-hunt-guesser.php`. The methods related to form handling and request
 * routing were removed to avoid duplication and ensure a single canonical
 * implementation.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class providing data layer utilities for Bonus Hunt Guesser.
 */
class BHG_Models {

    /**
     * Close a bonus hunt and determine winners.
     *
     * @param int   $hunt_id       Hunt identifier.
     * @param float $final_balance Final balance for the hunt.
     *
     * @return int[]|false Array of winning user IDs or false on failure.
     */
    public static function close_hunt( $hunt_id, $final_balance ) {
        global $wpdb;

        if ( class_exists( 'BHG_DB' ) ) {
            BHG_DB::migrate();
        }

        $hunt_id       = (int) $hunt_id;
        $final_balance = (float) $final_balance;

        if ( $hunt_id <= 0 ) {
            return array();
        }

        $hunts_tbl   = $wpdb->prefix . 'bhg_bonus_hunts';
        $guesses_tbl = $wpdb->prefix . 'bhg_guesses';
        $winners_tbl = $wpdb->prefix . 'bhg_hunt_winners';
        $tres_tbl    = $wpdb->prefix . 'bhg_tournament_results';

        // Determine number of winners and tournament association for this hunt.
        $hunt_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT winners_count, tournament_id FROM {$hunts_tbl} WHERE id=%d",
                $hunt_id
            )
        );
        $winners_count = $hunt_row ? (int) $hunt_row->winners_count : 0;
        if ( $winners_count <= 0 ) {
            $winners_count = 1;
        }
        $tournament_id = $hunt_row ? (int) $hunt_row->tournament_id : 0;

        // Update hunt status and final details.
        $now = current_time( 'mysql' );
        $updated = $wpdb->update(
            $hunts_tbl,
            array(
                'status'        => 'closed',
                'final_balance' => $final_balance,
                'closed_at'     => $now,
                'updated_at'    => $now,
            ),
            array( 'id' => $hunt_id ),
            array( '%s', '%f', '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            bhg_log( $wpdb->last_error );
            return false;
        }

        // Fetch winners based on proximity to final balance.
        // phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsMismatch
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                sprintf(
                    'SELECT user_id, guess, ABS(guess - %%f) AS diff FROM %s WHERE hunt_id = %%d ORDER BY diff ASC, id ASC LIMIT %%d',
                    esc_sql( $guesses_tbl )
                ),
                $final_balance,
                $hunt_id,
                $winners_count
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsMismatch

        if ( empty( $rows ) ) {
            return array();
        }

        // Record winners and update tournament results.
        $position = 1;
        foreach ( (array) $rows as $row ) {
            $wpdb->insert(
                $winners_tbl,
                array(
                    'hunt_id'    => $hunt_id,
                    'user_id'    => (int) $row->user_id,
                    'position'   => $position,
                    'guess'      => (float) $row->guess,
                    'diff'       => (float) $row->diff,
                    'created_at' => $now,
                ),
                array( '%d', '%d', '%d', '%f', '%f', '%s' )
            );

            if ( $tournament_id > 0 ) {
                $existing = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT id, wins FROM {$tres_tbl} WHERE tournament_id = %d AND user_id = %d",
                        $tournament_id,
                        $row->user_id
                    )
                );
                if ( $existing ) {
                    $wpdb->update(
                        $tres_tbl,
                        array(
                            'wins'         => (int) $existing->wins + 1,
                            'last_win_date' => $now,
                        ),
                        array( 'id' => (int) $existing->id ),
                        array( '%d', '%s' ),
                        array( '%d' )
                    );
                } else {
                    $wpdb->insert(
                        $tres_tbl,
                        array(
                            'tournament_id' => $tournament_id,
                            'user_id'       => (int) $row->user_id,
                            'wins'          => 1,
                            'last_win_date' => $now,
                        ),
                        array( '%d', '%d', '%d', '%s' )
                    );
                }
            }

            $position++;
        }

        return array_map( 'intval', wp_list_pluck( $rows, 'user_id' ) );
    }
}

