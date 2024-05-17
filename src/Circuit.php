<?php


namespace Leyton\LaravelCircuitBreaker;


use Leyton\LaravelCircuitBreaker\Concerns\HasLimit;
use Leyton\LaravelCircuitBreaker\Concerns\ManagesCircuit;
use Leyton\LaravelCircuitBreaker\Exceptions\RequestFailedException;
use Leyton\LaravelCircuitBreaker\Transporters\Packet;

readonly class Circuit
{
    public function __construct(protected ManagesCircuit&HasLimit $circuit)
    {
    }

    /**
     */
    public function status(string $service): CircuitStatus
    {
        return $this->circuit->status($service);
    }

    public function areAvailable(array $services): bool
    {
        foreach ($services as $service) {
            if($this->status($service) === CircuitStatus::OPEN) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $service
     * @param callable $function
     * @return mixed
     */
    public function run(string $service, callable $function): Packet
    {
        if($this->circuit->status($service) === CircuitStatus::OPEN){
            if($this->circuit->inWait($service)){
                return new Packet(
                    null, CircuitStatus::OPEN
                );
            }
            $this->circuit->halfOpen($service);
        }

        try {

            /** @throws RequestFailedException */
            $result = $function();

            if($this->circuit->status($service) === CircuitStatus::HALF_OPEN){
                $this->circuit->success($service);
                if($this->circuit->succeededEnough($service)){
                    $this->circuit->close($service);
                }
            }

            return new Packet(
                $result, $this->circuit->status($service)
            );
        }catch (RequestFailedException){
            $this->circuit->failed($service);
            if($this->circuit->thresholdExceeded($service)){
                $this->circuit->open($service);
                return new Packet(
                    null, CircuitStatus::OPEN
                );
            }

            return new Packet(
                null, $this->circuit->status($service)
            );
        }
    }
}
