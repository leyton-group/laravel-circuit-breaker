<?php
namespace  Leyton\LaravelCircuitBreaker;

use Illuminate\Support\ServiceProvider;
use Leyton\LaravelCircuitBreaker\Drivers\CacheOfficer;

class LaravelCircuitBreakerServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(Circuit::class, function () {
           return new Circuit(
               new CacheOfficer(cache()->store(config('circuit-breaker.driver', 'redis')))
           );
        });
    }
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/circuit-breaker.php' => config_path('circuit-breaker.php')
        ], 'config');
    }
}
