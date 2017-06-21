<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class ProfiledResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var  ResponseCollectionInterface */
    private $collection;
    /** @var  ProfiledClientStorage */
    private $profiler;

    /**
     * ProfiledResponseCollection constructor.
     *
     * @param ResponseCollectionInterface $collection
     * @param ProfiledClientStorage                 $profiler
     */
    public function __construct(ResponseCollectionInterface $collection, ProfiledClientStorage $profiler)
    {
        $this->collection = $collection;
        $this->profiler   = $profiler;
    }


    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        $response = $this->collection->getResponse($request);
        $this->profiler->registerResponse($response, $request);

        return $response;
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        foreach ($this->collection as $response) {
            $this->profiler->registerResponse($response);
            yield $response;
        }
    }
}
