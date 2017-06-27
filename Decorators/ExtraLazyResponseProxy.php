<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

final class ExtraLazyResponseProxy implements RpcResponseInterface
{
    /**
     * @var RpcRequestInterface
     */
    private $request;
    /**
     * @var LazyResponseCollection
     */
    private $collection;

    /** @var bool */
    private $initialized = false;
    /** @var RpcResponseInterface */
    private $response;

    /**
     * ExtraLazyResponseProxy constructor.
     *
     * @param RpcRequestInterface    $request
     * @param LazyResponseCollection $collection
     */
    public function __construct(RpcRequestInterface $request, LazyResponseCollection $collection)
    {
        $this->request    = $request;
        $this->collection = $collection;
    }

    /** {@inheritdoc} */
    public function isSuccessful()
    {
        return $this->getInternalResponse()->isSuccessful();
    }

    /** {@inheritdoc} */
    public function getError()
    {
        return $this->getInternalResponse()->getError();
    }

    /** {@inheritdoc} */
    public function getBody()
    {
        return $this->getInternalResponse()->getBody();
    }

    private function getInternalResponse()
    {
        if (!$this->initialized) {
            $this->response    = $this->collection->getResponse($this->request);
            $this->initialized = true;
        }

        return $this->response;
    }
}
