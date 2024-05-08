<?php


namespace Leyton\LaravelCircuitBreaker\Transporters;


use Leyton\LaravelCircuitBreaker\CircuitStatus;

class Packet
{
    public function __construct(public readonly mixed $result, public readonly CircuitStatus $status)
    {
    }
}
