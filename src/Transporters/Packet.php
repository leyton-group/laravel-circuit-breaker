<?php


namespace Leyton\LaravelCircuitBreaker\Transporters;


use Leyton\LaravelCircuitBreaker\CircuitStatus;

class Packet
{
    public function __construct(public mixed $result, public CircuitStatus $status)
    {
    }
}
