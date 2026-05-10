<?php

namespace App\Repositories;

use App\Models\Fixture;
use App\Repositories\Contracts\FixtureRepositoryInterface;
use Illuminate\Support\Collection;

final class EloquentFixtureRepository implements FixtureRepositoryInterface
{
    public function count(): int
    {
        return Fixture::query()->count();
    }

    public function deleteAll(): void
    {
        Fixture::query()->delete();
    }

    public function create(int $week, int $homeTeamId, int $awayTeamId): Fixture
    {
        return Fixture::query()->create([
            'week' => $week,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
        ]);
    }

    public function findOrFail(int $id): Fixture
    {
        return Fixture::query()->findOrFail($id);
    }

    public function updateScore(Fixture $fixture, int $homeGoals, int $awayGoals): void
    {
        $fixture->update([
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'played_at' => now(),
        ]);
    }

    public function allWithTeamsOrdered(): Collection
    {
        return Fixture::query()
            ->with(['homeTeam:id,name,power', 'awayTeam:id,name,power'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();
    }
}
