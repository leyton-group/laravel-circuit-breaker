<?php


namespace Leyton\LaravelCircuitBreaker\Concerns;


use Leyton\LaravelCircuitBreaker\CircuitStatus;

interface ManagesCircuit
{
    public function close(string $service): void;
    public function open(string $service): void;
    public function halfOpen(string $service): void;
    public function toStatus(string $service, CircuitStatus $status): void;
    public function status(string $service):CircuitStatus;
}
