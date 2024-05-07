## Motivation
The Circuit Breaker Pattern is essential for ensuring your software's resilience. It prevents failures from spreading, maintaining operational stability even when services encounter issues. By providing visual feedback and improving user experience, it keeps your application running smoothly. Additionally, it simplifies maintenance and troubleshooting, enabling quicker problem resolution. Overall, integrating the Circuit Breaker Pattern is crucial for enhancing reliability and user satisfaction.
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
