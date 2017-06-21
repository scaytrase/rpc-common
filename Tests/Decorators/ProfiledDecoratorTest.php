<?php

namespace ScayTrase\Api\Rpc\Tests\Decorators;

use PHPUnit\Framework\TestCase;
use ScayTrase\Api\Rpc\Decorators\ProfiledClient;
use ScayTrase\Api\Rpc\Decorators\ProfiledClientStorage;
use ScayTrase\Api\Rpc\RpcResponseInterface;
use ScayTrase\Api\Rpc\Test\RpcMockClient;
use ScayTrase\Api\Rpc\Tests\RpcRequestTrait;

final class ProfiledDecoratorTest extends TestCase
{
    use RpcRequestTrait;

    public function testProfiledClient()
    {
        $innerClient = new RpcMockClient();
        $storage     = new ProfiledClientStorage('mock_client');
        $client      = new ProfiledClient($innerClient, $storage);

        self::assertSame('mock_client', $storage->getClientName());

        $innerClient->push(
            $this->getResponseMock(
                true,
                (object)['result' => 'value']
            ),
            function () {
                usleep(1000);

                return true;
            }
        );

        $request  = $this->getRequestMock('test', ['result' => 'value']);
        $response = $client->invoke($request)->getResponse($request);

        self::assertTrue($response->isSuccessful());
        self::assertInstanceOf(\stdClass::class, $response->getBody());
        self::assertObjectHasAttribute('result', $response->getBody());
        self::assertEquals('value', $response->getBody()->result);

        self::assertNotEmpty($storage->getFullPairs());
        $pairs = $storage->getFullPairs();

        $pair = array_shift($pairs);
        self::assertSame($response, $pair['response']);
        self::assertSame($request, $pair['request']);
        self::assertGreaterThan($pair['start'], $pair['stop']);
    }

    public function testStopwatchClientIterator()
    {
        $innerClient = new RpcMockClient();
        $storage     = new ProfiledClientStorage('mock_client');
        $client      = new ProfiledClient($innerClient, $storage);
        $innerClient->push(
            $this->getResponseMock(
                true,
                (object)['result' => 'value']
            ),
            function () {
                usleep(1000);

                return true;
            }
        );
        $innerClient->push(
            $this->getResponseMock(
                true,
                (object)['result' => 'value']
            ),
            function () {
                usleep(1000);

                return true;
            }
        );

        $request1 = $this->getRequestMock('test', ['result' => 'value']);
        $request2 = $this->getRequestMock('test', ['result' => 'value']);

        /** @var RpcResponseInterface[] $collection */
        $collection = $client->invoke([$request1, $request2]);

        $responses = [];
        foreach ($collection as $response) {
            self::assertTrue($response->isSuccessful());

            self::assertInstanceOf(\stdClass::class, $response->getBody());
            self::assertObjectHasAttribute('result', $response->getBody());
            self::assertEquals('value', $response->getBody()->result);

            $responses[] = $response;
        }

        foreach ($storage->getUnmatchedRequestPairs() as $pair) {
            self::assertNotEmpty($pair['start']);
            self::assertArrayNotHasKey('stop', $pair);
            self::assertArrayNotHasKey('response', $pair);
            self::assertContains($pair['request'], [$request1, $request2]);
        }

        foreach ($storage->getUnmatchedResponsePairs() as $pair) {
            self::assertArrayNotHasKey('stop', $pair);
            self::assertArrayNotHasKey('request', $pair);
            self::assertArrayNotHasKey('start', $pair);
            self::assertContains($pair['response'], $responses);
        }
    }
}
