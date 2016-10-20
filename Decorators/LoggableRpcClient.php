<?php

namespace ScayTrase\Api\Rpc\Decorators;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class LoggableRpcClient implements RpcClientInterface
{
    /** @var  LoggerInterface */
    private $logger;
    /** @var  RpcClientInterface */
    private $decoratedClient;

    /**
     * LoggableRpcClient constructor.
     *
     * @param RpcClientInterface $decoratedClient
     * @param LoggerInterface    $logger
     */
    public function __construct(RpcClientInterface $decoratedClient, LoggerInterface $logger = null)
    {
        $this->decoratedClient = $decoratedClient;
        $this->logger          = $logger;

        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        /** @var RpcRequestInterface[] $loggedCalls */
        $loggedCalls = $calls;
        if (!is_array($loggedCalls)) {
            $loggedCalls = [$loggedCalls];
        }

        foreach ($loggedCalls as $call) {
            $this->logger->debug(
                sprintf('%s Invoking RPC method "%s"', spl_object_hash($call), $call->getMethod()),
                json_decode(json_encode($call->getParameters()), true)
            );
        }

        return new LoggableResponseCollection($this->decoratedClient->invoke($calls), $this->logger);
    }
}
