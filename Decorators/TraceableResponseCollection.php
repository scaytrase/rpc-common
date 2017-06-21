<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var  ResponseCollectionInterface */
    private $collection;
    /** @var  Stopwatch */
    private $stopwatch;
    /** @var  string */
    private $client;

    /**
     * TraceableResponseCollection constructor.
     *
     * @param ResponseCollectionInterface $collection
     * @param Stopwatch                   $stopwatch
     * @param string                      $client
     */
    public function __construct(
        ResponseCollectionInterface $collection,
        Stopwatch $stopwatch,
        $client
    ) {
        $this->collection = $collection;
        $this->stopwatch  = $stopwatch;
        $this->client     = $client;
    }

    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        $this->stopwatch->start($this->client, TraceableClient::CATEGORY_RESPONSE);
        $response = $this->collection->getResponse($request);
        $this->stopwatch->stop($this->client);

        return $response;
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        $iterator = new TraceableCurrentIterator($this->collection, $this->stopwatch, $this->client);
        foreach ($iterator as $response) {
            yield $response;
        }
    }
}
