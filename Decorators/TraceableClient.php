<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcClientInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableClient implements RpcClientInterface
{
    const CATEGORY_REQUEST  = 'rpc_call';
    const CATEGORY_RESPONSE = 'rpc_response';

    /** @var  RpcClientInterface */
    private $client;
    /** @var  Stopwatch */
    private $stopwatch;
    /** @var  string */
    private $clientName;

    /**
     * TraceableClient constructor.
     *
     * @param RpcClientInterface $client
     * @param Stopwatch          $stopwatch
     * @param string             $clientName
     */
    public function __construct(RpcClientInterface $client, Stopwatch $stopwatch, $clientName = 'api_client')
    {
        $this->client     = $client;
        $this->stopwatch  = $stopwatch;
        $this->clientName = (string)$clientName;
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        $this->stopwatch->start($this->clientName, self::CATEGORY_REQUEST);
        $collection = new TraceableResponseCollection(
            $this->client->invoke($calls),
            $this->stopwatch,
            $this->clientName
        );
        $this->stopwatch->stop($this->clientName);

        return $collection;
    }
}
