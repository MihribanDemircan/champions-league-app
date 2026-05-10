<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Team;
use App\Repositories\Contracts\FixtureRepositoryInterface;
use App\Repositories\Contracts\MatchResultRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeagueSimulationService
{
    public const TOTAL_WEEKS = 6;

    public const PREDICTION_START_REMAINING_WEEKS = 3;

    public function __construct(
        private readonly TeamRepositoryInterface $teams,
        private readonly FixtureRepositoryInterface $fixtures,
        private readonly MatchResultRepositoryInterface $matchResults,
        private readonly MatchScoreSimulator $scoreSimulator,
    ) {}

    public function ensureInitialized(): void
    {
        if ($this->teams->count() > 0 && $this->fixtures->count() > 0) {
            return;
        }

        $this->resetLeague();
    }

    public function resetLeague(?array $teamsData = null): array
    {
        $teamsData = $teamsData ?? [
            ['name' => 'Manchester City', 'power' => 95],
            ['name' => 'Bayern Munich', 'power' => 91],
            ['name' => 'Real Madrid', 'power' => 89],
            ['name' => 'PSG', 'power' => 86],
        ];

        if (count($teamsData) !== 4) {
            throw new \InvalidArgumentException('Takim sayisi 4 olmalidir.');
        }

        DB::transaction(function () use ($teamsData): void {
            $this->matchResults->deleteAll();
            $this->fixtures->deleteAll();
            $this->teams->deleteAll();

            $createdTeams = $this->teams->createMany($teamsData);
            $this->createFixtures($createdTeams->values());
        });

        return $this->getState();
    }

    public function getState(): array
    {
        $teams = $this->teams->allOrderedById();
        $fixtures = $this->fixtures->allWithTeamsOrdered();

        $table = $this->buildStandings($teams, $fixtures);
        $currentWeek = $this->getCurrentWeekFromFixtures($fixtures);
        $remainingWeeks = max(0, self::TOTAL_WEEKS - $currentWeek + 1);

        return [
            'meta' => [
                'totalWeeks' => self::TOTAL_WEEKS,
                'currentWeek' => $currentWeek,
                'remainingWeeks' => $remainingWeeks,
                'isFinished' => $currentWeek > self::TOTAL_WEEKS,
            ],
            'teams' => $teams,
            'table' => $table,
            'fixturesByWeek' => $fixtures
                ->groupBy('week')
                ->map(fn (Collection $weekFixtures): Collection => $weekFixtures->values())
                ->values(),
            'predictions' => $remainingWeeks <= self::PREDICTION_START_REMAINING_WEEKS
                ? $this->buildPredictions($teams, $fixtures)
                : [],
        ];
    }

    public function simulateNextWeek(): array
    {
        $this->ensureInitialized();
        $fixtures = $this->fixtures->allWithTeamsOrdered();

        $targetWeek = $this->getCurrentWeekFromFixtures($fixtures);
        if ($targetWeek > self::TOTAL_WEEKS) {
            return $this->getState();
        }

        $weekFixtures = $fixtures
            ->where('week', $targetWeek)
            ->whereNull('home_goals')
            ->values();

        foreach ($weekFixtures as $fixture) {
            $result = $this->scoreSimulator->simulate(
                $fixture->homeTeam->power,
                $fixture->awayTeam->power
            );
            $this->saveFixtureResult($fixture, $result['homeGoals'], $result['awayGoals'], 'simulated');
        }

        return $this->getState();
    }

    public function simulateAll(): array
    {
        $this->ensureInitialized();
        while ($this->getState()['meta']['currentWeek'] <= self::TOTAL_WEEKS) {
            $this->simulateNextWeek();
        }

        return $this->getState();
    }

    public function updateFixture(int $fixtureId, int $homeGoals, int $awayGoals): array
    {
        $this->ensureInitialized();
        $fixture = $this->fixtures->findOrFail($fixtureId);
        $this->saveFixtureResult($fixture, $homeGoals, $awayGoals, 'manual');

        return $this->getState();
    }

    private function createFixtures(Collection $teams): void
    {
        $teamIds = $teams->pluck('id')->values()->all();
        shuffle($teamIds);
        [$a, $b, $c, $d] = $teamIds;

        // Klasik UEFA grup asamasi deseni:
        // - Ilk 3 hafta birinci yarinin round-robin'i; her takim icin ev/dep
        //   mumkun oldugunca alternate (4 takimda matematiksel olarak 2 takim
        //   tam alternate, 2 takim 1 hafta esnek olabilir).
        // - Son 3 hafta tamamen rovansallar:
        //     H4 = H3 rovansi, H5 = H2 rovansi, H6 = H1 rovansi.
        //   Bu sayede her takim hafta hafta surekli ev/dep degistirir.
        $weeks = [
            1 => [[$a, $b], [$d, $c]],
            2 => [[$c, $a], [$b, $d]],
            3 => [[$a, $d], [$b, $c]],
            4 => [[$d, $a], [$c, $b]],
            5 => [[$a, $c], [$d, $b]],
            6 => [[$b, $a], [$c, $d]],
        ];

        foreach ($weeks as $week => $pairings) {
            shuffle($pairings);
            foreach ($pairings as $pairing) {
                $this->fixtures->create($week, $pairing[0], $pairing[1]);
            }
        }
    }

    private function saveFixtureResult(Fixture $fixture, int $homeGoals, int $awayGoals, string $source): void
    {
        $this->fixtures->updateScore($fixture, $homeGoals, $awayGoals);
        $this->matchResults->createForFixture($fixture->id, $homeGoals, $awayGoals, $source);
    }

    private function getCurrentWeekFromFixtures(Collection $fixtures): int
    {
        $nextFixture = $fixtures->first(fn (Fixture $fixture): bool => $fixture->home_goals === null);
        if (!$nextFixture) {
            return self::TOTAL_WEEKS + 1;
        }

        return $nextFixture->week;
    }

    private function buildStandings(Collection $teams, Collection $fixtures): array
    {
        $rows = $teams->mapWithKeys(function (Team $team): array {
            return [
                $team->id => [
                    'teamId' => $team->id,
                    'name' => $team->name,
                    'power' => $team->power,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'goalsFor' => 0,
                    'goalsAgainst' => 0,
                    'goalDifference' => 0,
                    'points' => 0,
                ],
            ];
        });

        foreach ($fixtures as $fixture) {
            if ($fixture->home_goals === null || $fixture->away_goals === null) {
                continue;
            }

            $homeRow = $rows[$fixture->home_team_id];
            $awayRow = $rows[$fixture->away_team_id];

            $homeRow['played']++;
            $awayRow['played']++;
            $homeRow['goalsFor'] += $fixture->home_goals;
            $homeRow['goalsAgainst'] += $fixture->away_goals;
            $awayRow['goalsFor'] += $fixture->away_goals;
            $awayRow['goalsAgainst'] += $fixture->home_goals;

            if ($fixture->home_goals > $fixture->away_goals) {
                $homeRow['won']++;
                $homeRow['points'] += 3;
                $awayRow['lost']++;
            } elseif ($fixture->home_goals < $fixture->away_goals) {
                $awayRow['won']++;
                $awayRow['points'] += 3;
                $homeRow['lost']++;
            } else {
                $homeRow['drawn']++;
                $awayRow['drawn']++;
                $homeRow['points']++;
                $awayRow['points']++;
            }

            $homeRow['goalDifference'] = $homeRow['goalsFor'] - $homeRow['goalsAgainst'];
            $awayRow['goalDifference'] = $awayRow['goalsFor'] - $awayRow['goalsAgainst'];

            $rows[$fixture->home_team_id] = $homeRow;
            $rows[$fixture->away_team_id] = $awayRow;
        }

        return collect($rows->values()->all())
            ->sortBy([
                ['points', 'desc'],
                ['goalDifference', 'desc'],
                ['goalsFor', 'desc'],
                ['name', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function buildPredictions(Collection $teams, Collection $fixtures): array
    {
        $iterations = max(1, (int) config('league.prediction_iterations', 10000));

        $cacheEnabled = (bool) config('league.prediction_cache.enabled', true);
        if (!$cacheEnabled) {
            return $this->computePredictions($teams, $fixtures, $iterations);
        }

        $key = $this->predictionCacheKey($teams, $fixtures, $iterations);
        $ttl = (int) config('league.prediction_cache.ttl_seconds', 600);

        return Cache::remember(
            $key,
            $ttl,
            fn (): array => $this->computePredictions($teams, $fixtures, $iterations),
        );
    }

    private function computePredictions(Collection $teams, Collection $fixtures, int $iterations): array
    {
        $powerById = $teams->mapWithKeys(fn (Team $team): array => [$team->id => $team->power])->all();
        $teamIds = array_keys($powerById);
        $championCount = array_fill_keys($teamIds, 0);

        $resolved = $fixtures->filter(fn (Fixture $fixture): bool => $fixture->home_goals !== null)->values();
        $pending = $fixtures->filter(fn (Fixture $fixture): bool => $fixture->home_goals === null)->values();

        if ($pending->isEmpty()) {
            $table = $this->buildStandings($teams, $resolved);
            if (!empty($table)) {
                $championCount[$table[0]['teamId']] = $iterations;
            }

            return $this->predictionsFromCounts($teams, $championCount, $iterations);
        }

        $resolvedRaw = $resolved->map(fn (Fixture $fixture): array => [
            'home_team_id' => $fixture->home_team_id,
            'away_team_id' => $fixture->away_team_id,
            'home_goals' => $fixture->home_goals,
            'away_goals' => $fixture->away_goals,
        ])->all();

        $pendingPlans = [];
        foreach ($pending as $fixture) {
            [$homeLambda, $awayLambda] = $this->scoreSimulator->lambdas(
                $powerById[$fixture->home_team_id],
                $powerById[$fixture->away_team_id],
            );
            $pendingPlans[] = [
                'home_team_id' => $fixture->home_team_id,
                'away_team_id' => $fixture->away_team_id,
                'home_lambda' => $homeLambda,
                'away_lambda' => $awayLambda,
            ];
        }

        for ($i = 0; $i < $iterations; $i++) {
            $simulated = $resolvedRaw;

            foreach ($pendingPlans as $plan) {
                $result = $this->scoreSimulator->simulateFromLambdas(
                    $plan['home_lambda'],
                    $plan['away_lambda'],
                );
                $simulated[] = [
                    'home_team_id' => $plan['home_team_id'],
                    'away_team_id' => $plan['away_team_id'],
                    'home_goals' => $result['homeGoals'],
                    'away_goals' => $result['awayGoals'],
                ];
            }

            $champion = $this->championIdFromRawFixtures($simulated, $powerById);
            $championCount[$champion]++;
        }

        return $this->predictionsFromCounts($teams, $championCount, $iterations);
    }

    /**
     * @param  array<int, array{home_team_id: int, away_team_id: int, home_goals: int, away_goals: int}>  $rawFixtures
     * @param  array<int, int>  $powerById
     */
    private function championIdFromRawFixtures(array $rawFixtures, array $powerById): int
    {
        $points = [];
        $goalDiff = [];
        $goalsFor = [];
        foreach ($powerById as $teamId => $_power) {
            $points[$teamId] = 0;
            $goalDiff[$teamId] = 0;
            $goalsFor[$teamId] = 0;
        }

        foreach ($rawFixtures as $f) {
            $h = $f['home_team_id'];
            $a = $f['away_team_id'];
            $hg = $f['home_goals'];
            $ag = $f['away_goals'];

            $goalsFor[$h] += $hg;
            $goalsFor[$a] += $ag;
            $goalDiff[$h] += $hg - $ag;
            $goalDiff[$a] += $ag - $hg;

            if ($hg > $ag) {
                $points[$h] += 3;
            } elseif ($hg < $ag) {
                $points[$a] += 3;
            } else {
                $points[$h]++;
                $points[$a]++;
            }
        }

        $championId = array_key_first($points);
        foreach ($points as $teamId => $pts) {
            if ($pts > $points[$championId]
                || ($pts === $points[$championId] && $goalDiff[$teamId] > $goalDiff[$championId])
                || ($pts === $points[$championId]
                    && $goalDiff[$teamId] === $goalDiff[$championId]
                    && $goalsFor[$teamId] > $goalsFor[$championId])
            ) {
                $championId = $teamId;
            }
        }

        return $championId;
    }

    /**
     * @param  array<int, int>  $championCount
     * @return array<int, array{teamId: int, name: string, percentage: float}>
     */
    private function predictionsFromCounts(Collection $teams, array $championCount, int $iterations): array
    {
        return collect($teams)->map(function (Team $team) use ($championCount, $iterations): array {
            return [
                'teamId' => $team->id,
                'name' => $team->name,
                'percentage' => round(($championCount[$team->id] / max(1, $iterations)) * 100, 2),
            ];
        })->sortByDesc('percentage')->values()->all();
    }

    private function predictionCacheKey(Collection $teams, Collection $fixtures, int $iterations): string
    {
        $prefix = (string) config('league.prediction_cache.prefix', 'league:predictions');

        $teamSig = $teams
            ->map(fn (Team $team): string => $team->id.':'.$team->power)
            ->implode('|');

        $fixtureSig = $fixtures
            ->map(function (Fixture $fixture): string {
                $home = $fixture->home_goals === null ? 'x' : (string) $fixture->home_goals;
                $away = $fixture->away_goals === null ? 'x' : (string) $fixture->away_goals;

                return $fixture->id.':'.$home.'-'.$away;
            })
            ->implode('|');

        return $prefix.':'.sha1($teamSig.'#'.$fixtureSig.'#'.$iterations);
    }
}
