<?php

namespace App\Repositories\Contracts;

use App\Models\Team;
use Illuminate\Support\Collection;

interface TeamRepositoryInterface
{
    public function count(): int;

    public function deleteAll(): void;

    /**
     * @param  array<int, array{name: string, power: int}>  $teamsData
     * @return Collection<int, Team>
     */
    public function createMany(array $teamsData): Collection;

    /**
     * @return Collection<int, Team>
     */
    public function allOrderedById(): Collection;
}
