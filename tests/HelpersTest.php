<?php

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {
    public function test_sql_tournament_period_case_ends_with_keyword(): void {
        $expression = bhg_sql_tournament_period_case( 't.start_date', 't.end_date', 'monthly' );

        $this->assertStringContainsString( 'CASE', $expression );
        $this->assertStringContainsString( "ELSE 'alltime'", $expression );
        $this->assertStringContainsString( "THEN 'monthly'", $expression );
        $this->assertStringEndsWith( 'END', trim( $expression ) );
    }

    public function test_sql_tournament_period_case_sanitizes_column_names(): void {
        $expression = bhg_sql_tournament_period_case( 't.start_date;DROP', 't.end_date--', 'weekly' );

        $this->assertStringContainsString( 't.start_dateDROP', $expression );
        $this->assertStringContainsString( 't.end_date', $expression );
        $this->assertStringNotContainsString( ';', $expression );
        $this->assertStringNotContainsString( '--', $expression );
    }
}
