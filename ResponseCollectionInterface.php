<?php

namespace ScayTrase\Api\Rpc;

use ScayTrase\Api\Rpc\Exception\RpcExceptionInterface;

interface ResponseCollectionInterface extends \Traversable
{
    /**
     * @param RpcRequestInterface $request
     * @return RpcResponseInterface
     * @throws RpcExceptionInterface todo: narrow exception scope (@scaytrase)
     * @throws \OutOfBoundsException if request is not present in collection
     */
    public function getResponse(RpcRequestInterface $request);
}
