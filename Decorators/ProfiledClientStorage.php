<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

final class ProfiledClientStorage
{
    /** @var array[] */
    private $pairs = [];
    /** @var array[] */
    private $unmatchedResponse = [];
    /** @var  string */
    private $clientName;

    /**
     * RpcProfiler constructor.
     *
     * @param string $clientName
     */
    public function __construct($clientName)
    {
        $this->clientName = (string)$clientName;
    }

    /**
     * @param RpcRequestInterface|RpcRequestInterface[] $calls
     */
    public function registerCalls($calls)
    {
        if (!is_array($calls)) {
            $calls = [$calls];
        }

        $time = microtime(true);
        foreach ($calls as $call) {
            $this->pairs[spl_object_hash($call)]['start']   = $time;
            $this->pairs[spl_object_hash($call)]['request'] = $call;
        }
    }

    /**
     * @param RpcResponseInterface $response
     * @param RpcRequestInterface  $request
     */
    public function registerResponse(RpcResponseInterface $response, RpcRequestInterface $request = null)
    {
        $time = microtime(true);

        if (null === $request) {
            $this->unmatchedResponse[]['response'] = $response;

            return;
        }

        $this->pairs[spl_object_hash($request)]['stop']     = $time;
        $this->pairs[spl_object_hash($request)]['response'] = $response;
    }

    /**
     * @return array[]
     */
    public function getFullPairs()
    {
        return array_filter(
            $this->pairs,
            function (array $row) {
                return isset($row['response']);
            }
        );
    }

    public function getUnmatchedRequestPairs()
    {
        return array_filter(
            $this->pairs,
            function (array $row) {
                return !isset($row['response']);
            }
        );
    }

    /**
     * @return array[]
     */
    public function getUnmatchedResponsePairs()
    {
        return $this->unmatchedResponse;
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }
}
