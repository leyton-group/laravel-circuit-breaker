<?php


namespace Leyton\LaravelCircuitBreaker\Drivers;


use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Leyton\LaravelCircuitBreaker\CircuitStatus;
use Leyton\LaravelCircuitBreaker\Concerns\HasLimit;
use Leyton\LaravelCircuitBreaker\Concerns\ManagesCircuit;
use Psr\SimpleCache\InvalidArgumentException;

class RedisOfficer implements ManagesCircuit, HasLimit
{
    public function __construct(protected Repository $cache)
    {

    }

    /**
     * @throws InvalidArgumentException
     */
    public function status(string $service):CircuitStatus
    {
        $status = $this->cache->get("circuit:{$service}:status");

        if(is_null($status)){
            return CircuitStatus::CLOSED;
        }
        return CircuitStatus::tryFrom($status);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function close(string $service): bool
    {
        $this->toStatus($service, CircuitStatus::CLOSED);
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function open(string $service): bool
    {
        $this->toStatus($service, CircuitStatus::OPEN);
        $this->cache->set("circuit:{$service}:expires_at", Carbon::now()->addSeconds(
            config('circuit-breaker.expires_after_seconds')
        ));
        $this->cache->delete("circuit:{$service}:failure");
        $this->cache->delete("circuit:{$service}:success");
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function toStatus(string $service, CircuitStatus $status): bool
    {
        $this->cache->set("circuit:{$service}:status", $status->value);
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function halfOpen(string $service): bool
    {
        $this->toStatus($service, CircuitStatus::HALF_OPEN);

        $this->cache->delete("circuit:{$service}:expires_at");

        $this->cache->delete("circuit:{$service}:success");

        $this->cache->set("circuit:{$service}:failure", 0);

        return true;
    }


    /**
     * @throws InvalidArgumentException
     */
    public function success(string $service): void
    {
        $this->cache->increment("circuit:{$service}:success");
        $this->cache->set("circuit:{$service}:failure", 0);
    }

    public function failed(string $service): void
    {
        $this->cache->increment("circuit:{$service}:failure");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function inWait(string $service): bool
    {
        $expires_at = Carbon::parse($this->cache->get("circuit:{$service}:expires_at"));

        return now()->lessThan($expires_at);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function thresholdExceeded(string $service): bool
    {
        $failure = $this->cache->get("circuit:{$service}:failure", 0);
        return (int) $failure >= config("circuit-breaker.threshold");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function succeededEnough(string $service): bool
    {
        $success = $this->cache->get("circuit:{$service}:success", 0);
        return (int) $success >= config("circuit-breaker.threshold");
    }
}
