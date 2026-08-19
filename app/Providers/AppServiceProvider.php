<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GameService;
use App\Services\ScoreCalculator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services
        $this->app->singleton(GameService::class, function ($app) {
            return new GameService($app->make(ScoreCalculator::class));
        });

        $this->app->singleton(ScoreCalculator::class, function ($app) {
            return new ScoreCalculator();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
