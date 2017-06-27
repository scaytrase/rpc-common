<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcClientInterface;

final class ExtraLazyRpcClient implements RpcClientInterface
{
    /** @var ExtraLazyResponseCollection */
    private $lazyCollection;
    /** @var LazyRpcClient */
    private $client;

    /**
     * ExtraLazyRpcClient constructor.
     *
     * @param RpcClientInterface $client
     */
    public function __construct(RpcClientInterface $client)
    {
        $this->client = new LazyRpcClient($client);
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        $collection = $this->client->invoke($calls);

        if (!$this->lazyCollection || $this->lazyCollection->getInnerCollection() !== $collection) {
            $this->lazyCollection = new ExtraLazyResponseCollection($collection);
        }

        return $this->lazyCollection;
    }
}
