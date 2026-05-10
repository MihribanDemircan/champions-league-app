<?php

use App\Http\Controllers\Api\LeagueController;
use Illuminate\Support\Facades\Route;

Route::prefix('league')->group(function (): void {
    Route::get('/state', [LeagueController::class, 'state']);
    Route::post('/reset', [LeagueController::class, 'reset']);
    Route::post('/simulate/week', [LeagueController::class, 'simulateNextWeek']);
    Route::post('/simulate/all', [LeagueController::class, 'simulateAll']);
    Route::put('/fixtures/{fixture}', [LeagueController::class, 'updateFixture']);
});
