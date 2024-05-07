<?php


namespace Leyton\LaravelCircuitBreaker;


Enum CircuitStatus : string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case HALF_OPEN = 'half-open';
}
