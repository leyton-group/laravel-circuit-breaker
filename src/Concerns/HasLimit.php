<?php


namespace Leyton\LaravelCircuitBreaker\Concerns;


interface HasLimit
{

    public function inWait(string $service): bool;
    public function thresholdExceeded(string $service): bool;
    public function succeededEnough(string $service): bool;
    public function success(string $service);
    public function failed(string $service);
}
