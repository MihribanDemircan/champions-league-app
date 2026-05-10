<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueResetValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_rejects_wrong_team_count(): void
    {
        $response = $this->postJson('/api/league/reset', [
            'teams' => [
                ['name' => 'A', 'power' => 80],
                ['name' => 'B', 'power' => 70],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_update_fixture_rejects_invalid_scores(): void
    {
        $this->postJson('/api/league/reset')->assertOk();
        $state = $this->getJson('/api/league/state')->assertOk()->json();
        $fixtureId = $state['fixturesByWeek'][0][0]['id'];

        $response = $this->putJson("/api/league/fixtures/{$fixtureId}", [
            'homeGoals' => -1,
            'awayGoals' => 0,
        ]);

        $response->assertStatus(422);
    }
}
