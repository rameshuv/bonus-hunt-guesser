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

        $second_call_winners = BHG_Models::close_hunt( $hunt_id, 1000.00 );

        $this->assertCount( 3, $this->wpdb->hunt_winners );

        $wins_after_second_call = $this->collectTournamentWins( $tournament_id );
        foreach ( $wins_after_second_call as $wins ) {
            $this->assertSame( 1, $wins );
        }

        sort( $first_call_winners );
        sort( $second_call_winners );

        $this->assertSame( $first_call_winners, $second_call_winners );
    }

    /**
     * Collect tournament wins for assertions.
     *
     * @param int $tournament_id Tournament identifier.
     *
     * @return int[]
     */
    private function collectTournamentWins( $tournament_id ) {
        $wins = array();

        foreach ( $this->wpdb->tournament_results as $row ) {
            if ( (int) $row['tournament_id'] !== (int) $tournament_id ) {
                continue;
            }

            $wins[ (int) $row['user_id'] ] = (int) $row['wins'];
        }

        return $wins;
    }
}
