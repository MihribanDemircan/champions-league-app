<?php

namespace App\Repositories;

use App\Models\MatchResult;
use App\Repositories\Contracts\MatchResultRepositoryInterface;

final class EloquentMatchResultRepository implements MatchResultRepositoryInterface
{
    public function deleteAll(): void
    {
        MatchResult::query()->delete();
    }

    public function createForFixture(int $fixtureId, int $homeGoals, int $awayGoals, string $source): void
    {
        MatchResult::query()->create([
            'fixture_id' => $fixtureId,
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'source' => $source,
        ]);
    }
}
