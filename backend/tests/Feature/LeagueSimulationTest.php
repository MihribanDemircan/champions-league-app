<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_state_endpoint_initializes_league(): void
    {
        $response = $this->getJson('/api/league/state');

        $response
            ->assertOk()
            ->assertJsonPath('meta.totalWeeks', 6)
            ->assertJsonCount(4, 'teams')
            ->assertJsonCount(4, 'table')
            ->assertJsonCount(6, 'fixturesByWeek');
    }

    public function test_simulate_all_finishes_league(): void
    {
        $this->postJson('/api/league/reset')->assertOk();
        $response = $this->postJson('/api/league/simulate/all');

        $response
            ->assertOk()
            ->assertJsonPath('meta.isFinished', true)
            ->assertJsonPath('meta.currentWeek', 7);

        $table = $response->json('table');
        $this->assertNotEmpty($table);
        $this->assertGreaterThanOrEqual(0, $table[0]['points']);
    }

    public function test_fixture_update_changes_result(): void
    {
        $state = $this->postJson('/api/league/reset')->assertOk()->json();
        $fixtureId = $state['fixturesByWeek'][0][0]['id'];

        $response = $this->putJson("/api/league/fixtures/{$fixtureId}", [
            'homeGoals' => 3,
            'awayGoals' => 1,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('fixturesByWeek.0.0.home_goals', 3)
            ->assertJsonPath('fixturesByWeek.0.0.away_goals', 1);
    }
}
