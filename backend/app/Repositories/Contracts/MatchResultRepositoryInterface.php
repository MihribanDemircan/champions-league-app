<?php

namespace App\Repositories\Contracts;

interface MatchResultRepositoryInterface
{
    public function deleteAll(): void;

    public function createForFixture(int $fixtureId, int $homeGoals, int $awayGoals, string $source): void;
}
