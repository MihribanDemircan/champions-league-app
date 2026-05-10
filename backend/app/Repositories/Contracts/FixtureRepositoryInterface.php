<?php

namespace App\Repositories\Contracts;

use App\Models\Fixture;
use Illuminate\Support\Collection;

interface FixtureRepositoryInterface
{
    public function count(): int;

    public function deleteAll(): void;

    public function create(int $week, int $homeTeamId, int $awayTeamId): Fixture;

    public function findOrFail(int $id): Fixture;

    public function updateScore(Fixture $fixture, int $homeGoals, int $awayGoals): void;

    /**
     * @return Collection<int, Fixture>
     */
    public function allWithTeamsOrdered(): Collection;
}
