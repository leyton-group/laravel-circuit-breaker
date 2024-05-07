## Motivation
TBD
## Installation
````
composer require leyton/laravel-circuit-breaker
````
After the installation make sure to publish the assets
````
php artisan vendor:publish --provider="Leyton\LaravelCircuitBreaker\LaravelCircuitBreakerServiceProvider"
````

You will find the ````config/circuit-breaker.php```` file containing all the configurations needed.
````php
<?php
return [
  'threshold' => 10,
  'expires_after_seconds' => 10,
  'driver' => 'redis',
];
````
## Usage

TBD
