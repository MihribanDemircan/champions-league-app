<?php

namespace App\Services;

final class MatchScoreSimulator
{
    /** Must stay in sync with UpdateFixtureRequest max (manual score edits). */
    public const MAX_GOALS_PER_TEAM = 20;

    /** Knuth Poisson loop guard (iterations), not the goal cap. */
    private const POISSON_MAX_ITERATIONS = 2000;
    /**
     * @return array{homeGoals: int, awayGoals: int}
     */
    public function simulate(int $homePower, int $awayPower): array
    {
        [$homeLambda, $awayLambda] = $this->lambdas($homePower, $awayPower);

        return $this->simulateFromLambdas($homeLambda, $awayLambda);
    }

    /**
     * @return array{0: float, 1: float}
     */
    public function lambdas(int $homePower, int $awayPower): array
    {
        $homeStrength = ($homePower * 1.12) + 4;
        $awayStrength = ($awayPower * 0.98) + 1;
        $totalStrength = max(1, $homeStrength + $awayStrength);
        $homeShare = $homeStrength / $totalStrength;
        $awayShare = $awayStrength / $totalStrength;

        return [
            0.25 + (3.2 * $homeShare),
            0.2 + (2.7 * $awayShare),
        ];
    }

    /**
     * @return array{homeGoals: int, awayGoals: int}
     */
    public function simulateFromLambdas(float $homeLambda, float $awayLambda): array
    {
        return [
            'homeGoals' => min(self::MAX_GOALS_PER_TEAM, $this->poisson($homeLambda)),
            'awayGoals' => min(self::MAX_GOALS_PER_TEAM, $this->poisson($awayLambda)),
        ];
    }

    public function poisson(float $lambda): int
    {
        $l = exp(-$lambda);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            if ($k >= self::POISSON_MAX_ITERATIONS) {
                return self::MAX_GOALS_PER_TEAM;
            }
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $l);

        return $k - 1;
    }
}
