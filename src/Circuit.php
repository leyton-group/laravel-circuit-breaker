<?php


namespace Leyton\LaravelCircuitBreaker;


use Closure;
use Leyton\LaravelCircuitBreaker\Concerns\HasLimit;
use Leyton\LaravelCircuitBreaker\Concerns\ManagesCircuit;
use Leyton\LaravelCircuitBreaker\Exceptions\CircuitOpenedException;
use Leyton\LaravelCircuitBreaker\Exceptions\RequestFailedException;
use Leyton\LaravelCircuitBreaker\Exceptions\StillOnHoldException;
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

    /**
     * @param string $service
     * @param Closure $function
     * @return mixed
     * @throws CircuitOpenedException
     * @throws StillOnHoldException
     */
    public function run(string $service, Closure $function): Packet
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
                null, CircuitStatus::HALF_OPEN
            );
        }
    }
}
