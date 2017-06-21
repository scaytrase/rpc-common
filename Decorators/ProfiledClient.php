<?php

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcClientInterface;

final class ProfiledClient implements RpcClientInterface
{
    /** @var  RpcClientInterface */
    private $client;
    /** @var  ProfiledClientStorage */
    private $profiler;

    /**
     * ProfiledClient constructor.
     *
     * @param RpcClientInterface $client
     * @param ProfiledClientStorage        $profiler
     */
    public function __construct(RpcClientInterface $client, ProfiledClientStorage $profiler)
    {
        $this->client   = $client;
        $this->profiler = $profiler;
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        $this->profiler->registerCalls($calls);

        return new ProfiledResponseCollection($this->client->invoke($calls), $this->profiler);
    }
}
