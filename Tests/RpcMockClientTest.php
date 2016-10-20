<?php

namespace Scaytrase\Api\Rpc\Tests;

use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\Test\MockClientException;
use ScayTrase\Api\Rpc\Test\RpcMockClient;

class RpcMockClientTest extends AbstractRpcTest
{
    public function testClientReturnsResponse()
    {
        $client = new RpcMockClient();
        $client->push($this->getResponseMock(true, 5));
        $request  = $this->getRequestMock('test');
        $response = $client->invoke($request)->getResponse($request);
        self::assertTrue($response->isSuccessful());
        self::assertNull($response->getError());
        self::assertSame(5, $response->getBody());
    }

    /**
     * @expectedException \ScayTrase\Api\Rpc\Test\MockClientException
     */
    public function testClientFiltersResponse()
    {
        $client = new RpcMockClient();
        $client->push(
            $this->getResponseMock(true, 5),
            function (RpcRequestInterface $request) {
                return $request->getMethod() === 'entity';
            }
        );
        $request = $this->getRequestMock('test');
        $client->invoke($request)->getResponse($request);
    }

    public function testClientReturnsMultipleResponses()
    {
        $client = new RpcMockClient();
        $client->push($this->getResponseMock(true, 5));
        $client->push($this->getResponseMock(false, null, $this->getErrorMock(-1, 'invalid')));
        $client->push(
            $this->getResponseMock(true, []),
            function (RpcRequestInterface $request) {
                return $request->getMethod() === 'entity';
            }
        );
        self::assertCount(3, $client->getQueue());
        $request1  = $this->getRequestMock('test');
        $request2  = $this->getRequestMock('test2');
        $request3  = $this->getRequestMock('entity');
        $response1 = $client->invoke($request1)->getResponse($request1);
        self::assertCount(2, $client->getQueue());
        self::assertTrue($response1->isSuccessful());
        self::assertNull($response1->getError());
        self::assertSame(5, $response1->getBody());

        $coll = $client->invoke([$request2, $request3]);
        self::assertCount(0, $client->getQueue());
        $response2 = $coll->getResponse($request2);
        $response3 = $coll->getResponse($request3);

        self::assertFalse($response2->isSuccessful());
        self::assertNull($response2->getBody());
        self::assertNotNull($response2->getError());
        self::assertSame(-1, $response2->getError()->getCode());
        self::assertSame('invalid', $response2->getError()->getMessage());

        self::assertTrue($response3->isSuccessful());
        self::assertNull($response3->getError());
        self::assertSame([], $response3->getBody());

        $request4 = $this->getRequestMock('empty');
        self::assertCount(0, $client->getQueue());
        try {
            $client->invoke($request4);
        } catch (MockClientException $exception) {
            self::assertEquals($request4, $exception->getRequest());
            self::assertEquals('Mock queue is empty while calling "empty"', $exception->getMessage());
        }

        self::assertCount(2, $coll);
        foreach ($coll as $response) {
            self::assertContains($response, [$response2, $response3]);
        }
    }
}
