<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\EndExpiredRounds;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The console commands for the application.
     *
     * @var array<class-string>
     */
    protected $commands = [
        EndExpiredRounds::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands($this->commands);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
