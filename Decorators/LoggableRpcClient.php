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
    /** @var LoggerInterface */
    private $debugLogger;

    /**
     * LoggableRpcClient constructor.
     *
     * @param RpcClientInterface $decoratedClient
     * @param LoggerInterface    $logger
     * @param bool               $debug
     */
    public function __construct(RpcClientInterface $decoratedClient, LoggerInterface $logger = null, $debug = false)
    {
        $this->decoratedClient = $decoratedClient;
        $this->logger          = $logger ?: new NullLogger();
        $this->debugLogger     = $debug ? $this->logger : new NullLogger();
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
            $this->logger->info(sprintf('%s Invoking RPC method "%s"', spl_object_hash($call), $call->getMethod()));
            $this->debugLogger->debug(
                sprintf(
                    '%s RPC parameters',
                    spl_object_hash($call)
                ),
                json_decode(
                    json_encode($call->getParameters()),
                    true
                )
            );
        }

        return new LoggableResponseCollection(
            $this->decoratedClient->invoke($calls), $this->logger, $this->debugLogger
        );
    }
}
