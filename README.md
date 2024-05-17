## Motivation
The Circuit Breaker Pattern is essential for ensuring your software's resilience. It prevents failures from spreading, maintaining operational stability even when services encounter issues. By providing visual feedback and improving user experience, it keeps your application running smoothly. Additionally, it simplifies maintenance and troubleshooting, enabling quicker problem resolution. Overall, integrating the Circuit Breaker Pattern is crucial for enhancing reliability and user satisfaction.

![Circuit Breaker states](https://github.com/leyton-group/laravel-circuit-breaker/assets/12276076/64e09281-f2c0-4cd2-9b7f-f268bc6e779a)


You can find more detailes about this pattern here [Circuit Breaker Pattern](https://learn.microsoft.com/en-us/azure/architecture/patterns/circuit-breaker)
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
  'threshold' => 10, // number of trials to pass from half-open to closed/open and from closed to half-open
  'available_after_seconds' => 10, // the seconds it takes while in the open status
  'driver' => 'redis', // the cache store driver
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
    +success: true,
}

````

One of the benefits of this pattern is to prevent the system from performing unnecessary actions when there are multiple transactions.
This is an example of the usage

````php
<?php

namespace App\Http\Controllers;

use Leyton\LaravelCircuitBreaker\Circuit;
use App\Services\LocationServiceClient;
use App\Services\PaymentGatewayClient;
use Illuminate\Http\Request;
use Exception;


class MakeOrderController extends Controller
{
    public function __construct(
        protected LocationServiceClient $locationServiceClient,
        protected PaymentGatewayClient $paymentGatewayClient,
        protected Circuit $Circuit,
    ) {
        
    }

    public function __invoke(Request $request)
    {
        $location = $request->get('location');
        $paymentDetails = $request->get('payment_details');
        $client = $request->get('client');

        if(!$this->circuit->available(['location-service', 'payment-service'])){
            return response()->json([
                'message' => 'Services are un-available, please retry later'
            ]);
        }
        
        $withdrawalPoint = $this->circuit->run("location-service", fn() => $this->locationServiceClient->getNearWithdrawalPoint($location));
        
        $payment = $this->circuit->run(
                "payment-service", 
                fn() => $this->paymentGatewayClient->processPayment($client, $order, $withdrawalPoint->result)
        );
        
        return response()->json($data);
    }
}

````
