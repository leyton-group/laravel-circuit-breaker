<?php


namespace Leyton\LaravelCircuitBreaker\Concerns;


use Leyton\LaravelCircuitBreaker\CircuitStatus;

interface ManagesCircuit
{
    public function close(string $service): bool;
    public function open(string $service): bool;
    public function halfOpen(string $service): bool;
    public function toStatus(string $service, CircuitStatus $status): bool;
    public function status(string $service):CircuitStatus;
}
