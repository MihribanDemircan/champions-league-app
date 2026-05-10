<?php

namespace Tests\Unit;

use App\Services\MatchScoreSimulator;
use PHPUnit\Framework\TestCase;

class MatchScoreSimulatorTest extends TestCase
{
    public function test_simulate_returns_bounded_scores(): void
    {
        $simulator = new MatchScoreSimulator;

        for ($i = 0; $i < 50; $i++) {
            $result = $simulator->simulate(95, 60);
            $this->assertArrayHasKey('homeGoals', $result);
            $this->assertArrayHasKey('awayGoals', $result);
            $this->assertGreaterThanOrEqual(0, $result['homeGoals']);
            $this->assertLessThanOrEqual(20, $result['homeGoals']);
            $this->assertGreaterThanOrEqual(0, $result['awayGoals']);
            $this->assertLessThanOrEqual(20, $result['awayGoals']);
        }
    }

    public function test_poisson_returns_non_negative(): void
    {
        $simulator = new MatchScoreSimulator;
        for ($i = 0; $i < 30; $i++) {
            $k = $simulator->poisson(1.5);
            $this->assertGreaterThanOrEqual(0, $k);
        }
    }
}
