## Motivation
The Circuit Breaker Pattern is essential for ensuring your software's resilience. It prevents failures from spreading, maintaining operational stability even when services encounter issues. By providing visual feedback and improving user experience, it keeps your application running smoothly. Additionally, it simplifies maintenance and troubleshooting, enabling quicker problem resolution. Overall, integrating the Circuit Breaker Pattern is crucial for enhancing reliability and user satisfaction.

![Circuit Breaker states](https://github.com/leyton-group/laravel-circuit-breaker/assets/12276076/64e09281-f2c0-4cd2-9b7f-f268bc6e779a)

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

The package provides you with a straightforward API to use.

````php
<?php

use Leyton\LaravelCircuitBreaker\Circuit;
use Leyton\LaravelCircuitBreaker\Exceptions\RequestFailedException;


function goToGoogle(){
  try{
    $response = Http::get("https://gooogle.com");

    if($response->status() === 500){
        throw new RequestFailedException();
    }

  return "all is good";
  }catch(\Exception $exception){
    throw new RequestFailedException();
  }
}

// The Circuit is resolved out of the service container

$circuit = app()->make(Circuit::class);

//The run method expects the service name and the function that wraps the service
//It should throw the RequestFailedException when the service is not responding as expected
$packet =  $circuit->run("go-to-google", fn() => goToGoogle());

````
The packet object holds the result of the callback and the status of the service

````php
Leyton\LaravelCircuitBreaker\Transporters\Packet {#2939
    +result: "all is good",
    +status: Leyton\LaravelCircuitBreaker\CircuitStatus {#2943
        +name: "CLOSED",
        +value: "closed",
    },
}

````
