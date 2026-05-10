<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResetLeagueRequest;
use App\Http\Requests\Api\UpdateFixtureRequest;
use App\Services\LeagueSimulationService;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    public function __construct(private readonly LeagueSimulationService $leagueService)
    {
    }

    public function state(): JsonResponse
    {
        $this->leagueService->ensureInitialized();

        return response()->json($this->leagueService->getState());
    }

    public function reset(ResetLeagueRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json(
            $this->leagueService->resetLeague($validated['teams'] ?? null)
        );
    }

    public function simulateNextWeek(): JsonResponse
    {
        return response()->json($this->leagueService->simulateNextWeek());
    }

    public function simulateAll(): JsonResponse
    {
        return response()->json($this->leagueService->simulateAll());
    }

    public function updateFixture(UpdateFixtureRequest $request, int $fixture): JsonResponse
    {
        $validated = $request->validated();

        return response()->json(
            $this->leagueService->updateFixture(
                $fixture,
                $validated['homeGoals'],
                $validated['awayGoals']
            )
        );
    }
}
