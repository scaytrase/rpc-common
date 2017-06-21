<?php

namespace ScayTrase\Api\Rpc\Decorators;

use Symfony\Component\Stopwatch\Stopwatch;
use Traversable;

final class TraceableCurrentIterator extends \IteratorIterator
{
    /** @var Stopwatch */
    private $stopwatch;
    /** @var string */
    private $client;

    public function __construct(Traversable $iterator, Stopwatch $stopwatch, $client)
    {
        parent::__construct($iterator);
        $this->stopwatch = $stopwatch;
        $this->client    = $client;
    }

    public function current()
    {
        $this->stopwatch->start($this->client, TraceableClient::CATEGORY_RESPONSE);
        $value = parent::current();
        $this->stopwatch->stop($this->client);

        return $value;
    }
}
