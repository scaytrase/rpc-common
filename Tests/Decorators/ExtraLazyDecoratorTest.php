<?php

namespace ScayTrase\Api\Rpc\Tests\Decorators;

use PHPUnit\Framework\TestCase;
use ScayTrase\Api\Rpc\Decorators\ExtraLazyResponseCollection;
use ScayTrase\Api\Rpc\Decorators\ExtraLazyRpcClient;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;
use ScayTrase\Api\Rpc\Test\RpcMockClient;
use ScayTrase\Api\Rpc\Tests\RpcRequestTrait;

final class ExtraLazyDecoratorTest extends TestCase
{
    use RpcRequestTrait;

    /** @var RpcMockClient */
    private $client;

    public function setUp()
    {
        $this->client = new RpcMockClient();
    }

    public function tearDown()
    {
        self::assertCount(0, $this->client);
        $this->client = null;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rq2 = $this->getRequestMock('/test2', ['param2' => 'test']);
        $rq3 = $this->getRequestMock('/test3', ['param3' => 'test']);

        $rs1 = $this->getResponseMock(true, (object)['param1' => 'test']);
        $rs2 = $this->getResponseMock(true, (object)['param2' => 'test']);
        $rs3 = $this->getResponseMock(true, (object)['param3' => 'test']);

        /** @var RpcRequestInterface[] $requests */
        $requests = [$rq1, $rq2, $rq3];
        /** @var RpcResponseInterface[] $responses */
        $responses = [$rs1, $rs2, $rs3];

        $client = $this->client;
        $client->push($rs1);
        $client->push($rs2);
        $client->push($rs3);

        $lazyClient = new ExtraLazyRpcClient($client);

        $c1 = $lazyClient->invoke($rq1);
        self::assertCount(3, $client);
        $c2 = $lazyClient->invoke($rq2);
        self::assertCount(3, $client);
        $c3 = $lazyClient->invoke($rq3);
        self::assertCount(3, $client);

        self::assertEquals($c1, $c2);
        self::assertEquals($c1, $c3);

        return [$requests, $responses, $c1];
    }

    public function testCollectionReturnsProxyResponses()
    {
        /** @var ExtraLazyResponseCollection $c1 */
        /** @var RpcResponseInterface[] $responses */
        /** @var RpcRequestInterface[] $requests */
        list($requests, $responses, $c1) = $this->getCollection();

        self::assertCount(3, $this->client);

        foreach ($requests as $id => $rs) {
            self::assertEquals($c1->getResponse($rs)->isSuccessful(), $responses[$id]->isSuccessful());
            self::assertCount(0, $this->client);
            self::assertEquals($c1->getResponse($rs)->getError(), $responses[$id]->getError());
            self::assertEquals($c1->getResponse($rs)->getBody(), $responses[$id]->getBody());
        }
    }

    public function testCollectionIteratorInvokesProxy()
    {
        /** @var ExtraLazyResponseCollection|RpcResponseInterface[] $c1 */
        /** @var RpcResponseInterface[] $responses */
        /** @var RpcRequestInterface[] $requests */
        list($requests, $responses, $c1) = $this->getCollection();

        self::assertCount(3, $this->client);
        foreach ($c1 as $id => $response) {
            self::assertCount(0, $this->client);
            self::assertEquals($response->isSuccessful(), $responses[$id]->isSuccessful());
            self::assertEquals($response->getError(), $responses[$id]->getError());
            self::assertEquals($response->getBody(), $responses[$id]->getBody());
        }
    }
}
