<?php

namespace App\Repositories;

use App\Models\Team;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Support\Collection;

final class EloquentTeamRepository implements TeamRepositoryInterface
{
    public function count(): int
    {
        return Team::query()->count();
    }

    public function deleteAll(): void
    {
        Team::query()->delete();
    }

    public function createMany(array $teamsData): Collection
    {
        return collect($teamsData)->map(
            fn (array $row): Team => Team::query()->create([
                'name' => $row['name'],
                'power' => $row['power'],
            ])
        );
    }

    public function allOrderedById(): Collection
    {
        return Team::query()->orderBy('id')->get();
    }
}
