<?php

namespace App\Providers;

use App\Repositories\Contracts\FixtureRepositoryInterface;
use App\Repositories\Contracts\MatchResultRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Repositories\EloquentFixtureRepository;
use App\Repositories\EloquentMatchResultRepository;
use App\Repositories\EloquentTeamRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TeamRepositoryInterface::class, EloquentTeamRepository::class);
        $this->app->bind(FixtureRepositoryInterface::class, EloquentFixtureRepository::class);
        $this->app->bind(MatchResultRepositoryInterface::class, EloquentMatchResultRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
