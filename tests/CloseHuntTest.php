<?php

use PHPUnit\Framework\TestCase;

final class CloseHuntTest extends TestCase {
    /**
     * @var MockWPDB
     */
    private $wpdb;

    protected function setUp(): void {
        global $wpdb;

        $this->wpdb = new MockWPDB();
        $wpdb       = $this->wpdb;
    }

    public function test_closing_hunt_twice_does_not_duplicate_winners_or_wins(): void {
        global $wpdb;

        $hunt_id       = 1;
        $tournament_id = 5;

        $this->wpdb->bonus_hunts[ $hunt_id ] = array(
            'id'             => $hunt_id,
            'winners_count'  => 3,
            'tournament_id'  => $tournament_id,
            'status'         => 'open',
            'final_balance'  => null,
            'closed_at'      => null,
            'updated_at'     => null,
            'created_at'     => '2024-01-01 00:00:00',
        );

        $this->wpdb->hunt_tournaments[] = array(
            'hunt_id'       => $hunt_id,
            'tournament_id' => $tournament_id,
        );

        $this->wpdb->tournaments[ $tournament_id ] = array(
            'id'                => $tournament_id,
            'participants_mode' => 'winners',
        );

        $this->wpdb->guesses = array(
            array( 'id' => 1, 'hunt_id' => $hunt_id, 'user_id' => 101, 'guess' => 1000.00 ),
            array( 'id' => 2, 'hunt_id' => $hunt_id, 'user_id' => 102, 'guess' => 995.00 ),
            array( 'id' => 3, 'hunt_id' => $hunt_id, 'user_id' => 103, 'guess' => 1002.50 ),
            array( 'id' => 4, 'hunt_id' => $hunt_id, 'user_id' => 104, 'guess' => 1500.00 ),
        );

        $first_call_winners = BHG_Models::close_hunt( $hunt_id, 1000.00 );

        $this->assertSame( 'closed', $this->wpdb->bonus_hunts[ $hunt_id ]['status'] );
        $this->assertCount( 3, $this->wpdb->hunt_winners );
        $this->assertSame( 3, count( $this->collectTournamentWins( $tournament_id ) ) );

        $expected_diffs = array( 0.0, -2.5, 5.0 );
        foreach ( $expected_diffs as $index => $expected_diff ) {
            $this->assertArrayHasKey( 'diff', $this->wpdb->hunt_winners[ $index ] );
            $this->assertEqualsWithDelta( $expected_diff, (float) $this->wpdb->hunt_winners[ $index ]['diff'], 0.00001 );
        }

        $expected_points = array( 10, 8, 6 );
        foreach ( $expected_points as $index => $expected_points_value ) {
            $this->assertArrayHasKey( 'points', $this->wpdb->hunt_winners[ $index ] );
            $this->assertSame( $expected_points_value, (int) $this->wpdb->hunt_winners[ $index ]['points'] );
        }

        $expected_points_map = array(
            101 => 10,
            102 => 6,
            103 => 8,
        );

        $points_map = $this->collectTournamentPoints( $tournament_id );
        $this->assertSame( $expected_points_map, $points_map );

        $second_call_winners = BHG_Models::close_hunt( $hunt_id, 1000.00 );

        $this->assertCount( 3, $this->wpdb->hunt_winners );

        $wins_after_second_call = $this->collectTournamentWins( $tournament_id );
        foreach ( $wins_after_second_call as $wins ) {
            $this->assertSame( 1, $wins );
        }

        $points_after_second_call = $this->collectTournamentPoints( $tournament_id );

        $this->assertSame( $expected_points_map, $points_after_second_call );

        sort( $first_call_winners );
        sort( $second_call_winners );

        $this->assertSame( $first_call_winners, $second_call_winners );
    }

    public function test_close_hunt_all_mode_records_all_positions(): void {
        global $wpdb;

        $hunt_id        = 2;
        $tournament_id  = 7;
        $winners_count  = 2;
        $guesser_ids    = array( 201, 202, 203, 204 );

        $this->wpdb->bonus_hunts[ $hunt_id ] = array(
            'id'            => $hunt_id,
            'winners_count' => $winners_count,
            'tournament_id' => $tournament_id,
            'status'        => 'open',
            'created_at'    => '2024-01-01 00:00:00',
        );

        $this->wpdb->hunt_tournaments[] = array(
            'hunt_id'       => $hunt_id,
            'tournament_id' => $tournament_id,
        );

        $this->wpdb->tournaments[ $tournament_id ] = array(
            'id'                => $tournament_id,
            'participants_mode' => 'all',
        );

        foreach ( $guesser_ids as $index => $user_id ) {
            $this->wpdb->guesses[] = array(
                'id'      => $index + 1,
                'hunt_id' => $hunt_id,
                'user_id' => $user_id,
                'guess'   => 1000 + ( $index - 1 ) * 10,
            );
        }

        $winners = BHG_Models::close_hunt( $hunt_id, 1000.00 );

        $this->assertCount( count( $guesser_ids ), $winners );
        $this->assertCount( count( $guesser_ids ), $this->wpdb->hunt_winners );

        $positions = array_column( $this->wpdb->hunt_winners, 'position' );
        $this->assertSame( range( 1, count( $guesser_ids ) ), $positions );

        $points = array_column( $this->wpdb->hunt_winners, 'points' );
        $this->assertSame( array( 10, 8, 6, 5 ), $points );

        $wins = $this->collectTournamentWins( $tournament_id );
        $this->assertCount( count( $guesser_ids ), $wins );
        foreach ( $wins as $win_count ) {
            $this->assertSame( 1, $win_count );
        }

        $expected_points_map = array(
            201 => 8,
            202 => 10,
            203 => 6,
            204 => 5,
        );

        $points_map = $this->collectTournamentPoints( $tournament_id );
        ksort( $expected_points_map );
        $this->assertSame( $expected_points_map, $points_map );
    }

    public function test_recalculate_tournament_results_respects_participants_mode(): void {
        global $wpdb;

        $hunt_id              = 3;
        $winners_tournament   = 8;
        $all_tournament       = 9;
        $this->wpdb->bonus_hunts[ $hunt_id ] = array(
            'id'            => $hunt_id,
            'winners_count' => 2,
            'status'        => 'open',
            'created_at'    => '2024-01-01 00:00:00',
        );

        $this->wpdb->hunt_tournaments[] = array(
            'hunt_id'       => $hunt_id,
            'tournament_id' => $winners_tournament,
        );
        $this->wpdb->hunt_tournaments[] = array(
            'hunt_id'       => $hunt_id,
            'tournament_id' => $all_tournament,
        );

        $this->wpdb->tournaments[ $winners_tournament ] = array(
            'id'                => $winners_tournament,
            'participants_mode' => 'winners',
        );
        $this->wpdb->tournaments[ $all_tournament ] = array(
            'id'                => $all_tournament,
            'participants_mode' => 'all',
        );

        $this->wpdb->guesses = array(
            array( 'id' => 1, 'hunt_id' => $hunt_id, 'user_id' => 301, 'guess' => 1000.00 ),
            array( 'id' => 2, 'hunt_id' => $hunt_id, 'user_id' => 302, 'guess' => 995.00 ),
            array( 'id' => 3, 'hunt_id' => $hunt_id, 'user_id' => 303, 'guess' => 1002.50 ),
            array( 'id' => 4, 'hunt_id' => $hunt_id, 'user_id' => 304, 'guess' => 1500.00 ),
        );

        BHG_Models::close_hunt( $hunt_id, 1000.00 );

        $winners_wins    = $this->collectTournamentWins( $winners_tournament );
        $all_wins        = $this->collectTournamentWins( $all_tournament );
        $winners_points  = $this->collectTournamentPoints( $winners_tournament );
        $all_points      = $this->collectTournamentPoints( $all_tournament );

        $this->assertCount( 2, $winners_wins );
        $this->assertCount( 4, $all_wins );

        $this->wpdb->tournament_results = array();

        BHG_Models::recalculate_tournament_results( array( $winners_tournament, $all_tournament ) );

        $recalc_winners        = $this->collectTournamentWins( $winners_tournament );
        $recalc_all            = $this->collectTournamentWins( $all_tournament );
        $recalc_winners_points = $this->collectTournamentPoints( $winners_tournament );
        $recalc_all_points     = $this->collectTournamentPoints( $all_tournament );

        $this->assertSame( $winners_wins, $recalc_winners );
        $this->assertSame( $all_wins, $recalc_all );
        $this->assertSame( $winners_points, $recalc_winners_points );
        $this->assertSame( $all_points, $recalc_all_points );
    }

    /**
     * Collect tournament wins for assertions.
     *
     * @param int $tournament_id Tournament identifier.
     *
     * @return int[]
     */
    private function collectTournamentWins( $tournament_id ) {
        $tallies = $this->collectTournamentTallies( $tournament_id );

        return array_map(
            static function ( $entry ) {
                return $entry['wins'];
            },
            $tallies
        );
    }

    /**
     * Collect tournament points for assertions.
     *
     * @param int $tournament_id Tournament identifier.
     *
     * @return int[]
     */
    private function collectTournamentPoints( $tournament_id ) {
        $tallies = $this->collectTournamentTallies( $tournament_id );

        return array_map(
            static function ( $entry ) {
                return $entry['points'];
            },
            $tallies
        );
    }

    /**
     * Collect raw tournament tallies for assertions.
     *
     * @param int $tournament_id Tournament identifier.
     *
     * @return array<int, array{wins:int, points:int}>
     */
    private function collectTournamentTallies( $tournament_id ) {
        $tallies = array();

        foreach ( $this->wpdb->tournament_results as $row ) {
            if ( (int) $row['tournament_id'] !== (int) $tournament_id ) {
                continue;
            }

            $user_id = (int) $row['user_id'];
            if ( $user_id <= 0 ) {
                continue;
            }

            $tallies[ $user_id ] = array(
                'wins'   => isset( $row['wins'] ) ? (int) $row['wins'] : 0,
                'points' => isset( $row['points'] ) ? (int) $row['points'] : 0,
            );
        }

        ksort( $tallies );

        return $tallies;
    }
}
