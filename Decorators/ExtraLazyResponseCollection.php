<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class ExtraLazyResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var LazyResponseCollection */
    private $collection;

    /**
     * ExtraLazyResponseCollection constructor.
     *
     * @param LazyResponseCollection $collection
     */
    public function __construct(LazyResponseCollection $collection)
    {
        $this->collection = $collection;
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        return $this->collection;
    }

    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        return new ExtraLazyResponseProxy($request, $this->collection);
    }
}
