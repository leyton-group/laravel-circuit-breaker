<?php


namespace Leyton\LaravelCircuitBreaker;


use Closure;
use Leyton\LaravelCircuitBreaker\Concerns\HasLimit;
use Leyton\LaravelCircuitBreaker\Concerns\ManagesCircuit;
use Leyton\LaravelCircuitBreaker\Exceptions\CircuitOpenedException;
use Leyton\LaravelCircuitBreaker\Exceptions\RequestFailedException;
use Leyton\LaravelCircuitBreaker\Exceptions\StillOnHoldException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

readonly class Circuit
{
    public function __construct(protected ManagesCircuit&HasLimit $circuit)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws StillOnHoldException
     */
    public function run(string $service, Closure $function): mixed
    {
        if($this->circuit->status($service) === CircuitStatus::OPEN){
            if($this->circuit->inWait($service)){
                throw new StillOnHoldException();
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

            return $result;
        }catch (RequestFailedException){
            $this->circuit->failed($service);
            if($this->circuit->thresholdExceeded($service)){
                $this->circuit->open($service);
                throw new CircuitOpenedException();
            }

            return null;
        }
    }
}