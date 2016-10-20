<?php

namespace ScayTrase\Api\Rpc\Tests;

use Prophecy\Argument;
use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcErrorInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

abstract class AbstractRpcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array  $params
     *
     * @return RpcRequestInterface
     */
    protected function getRequestMock($method, array $params = [])
    {
        $request = $this->prophesize(RpcRequestInterface::class);
        $request->getMethod()->willReturn($method);
        $request->getParameters()->willReturn((object)$params);

        return $request->reveal();
    }

    /**
     * @param bool                       $success
     * @param \stdClass|array|null|mixed $body
     * @param RpcErrorInterface          $error
     *
     * @return RpcResponseInterface
     */
    protected function getResponseMock($success = true, $body = null, RpcErrorInterface $error = null)
    {
        $mock = $this->prophesize(RpcResponseInterface::class);
        $mock->isSuccessful()->willReturn($success);
        $mock->getError()->willReturn($success ? null : $error);
        $mock->getBody()->willReturn($success ? $body : null);

        return $mock->reveal();
    }

    protected function getErrorMock($code, $message)
    {
        $mock = $this->prophesize(RpcErrorInterface::class);
        $mock->getCode()->willReturn($code);
        $mock->getMessage()->willReturn($message);

        return $mock->reveal();
    }

    /**
     * @param RpcRequestInterface[]  $requests
     * @param RpcResponseInterface[] $responses
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|RpcClientInterface
     */
    protected function getClientMock(array $requests = [], array $responses = [])
    {
        self::assertEquals(count($requests), count($responses));

        $client = $this->prophesize(RpcClientInterface::class);
        $that   = $this;
        $client->invoke(Argument::type('array'))->will(
            function ($args) use ($that, $requests, $responses) {
                $collection = $that->prophesize(ResponseCollectionInterface::class);
                foreach ($requests as $key => $request) {
                    if (in_array($request, $args[0], true)) {
                        $collection->getResponse(Argument::exact($request))->willReturn($responses[$key]);
                    }
                }

                return $collection->reveal();
            }
        );

        return $client->reveal();
    }
}
