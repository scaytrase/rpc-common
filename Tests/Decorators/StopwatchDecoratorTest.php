<?php

namespace ScayTrase\Api\Rpc\Tests\Decorators;

use PHPUnit\Framework\TestCase;
use ScayTrase\Api\Rpc\Decorators\TraceableClient;
use ScayTrase\Api\Rpc\Test\RpcMockClient;
use ScayTrase\Api\Rpc\Tests\RpcRequestTrait;
use Symfony\Component\Stopwatch\Stopwatch;

final class StopwatchDecoratorTest extends TestCase
{
    use RpcRequestTrait;

    public function testStopwatchClientSections()
    {
        $innerClient = new RpcMockClient();
        $stopwatch   = new Stopwatch();
        $client      = new TraceableClient($innerClient, $stopwatch);
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

        $request = $this->getRequestMock('test', ['result' => 'value']);

        $response = $client->invoke($request)->getResponse($request);
        self::assertTrue($response->isSuccessful());
        self::assertInstanceOf(\stdClass::class, $response->getBody());
        self::assertObjectHasAttribute('result', $response->getBody());
        self::assertEquals('value', $response->getBody()->result);

        $sections = $stopwatch->getSections();
        self::assertNotEmpty($sections);
        $section = array_shift($sections);
        $events  = $section->getEvents();
        self::assertNotEmpty($events);
        self::assertArrayHasKey('api_client', $events);
        $event = $events['api_client'];

        self::assertNotNull($event->getDuration());
        self::assertNotNull($event->getMemory());
    }

    public function testStopwatchClientIterator()
    {
        $innerClient = new RpcMockClient();
        $stopwatch   = new Stopwatch();
        $client      = new TraceableClient($innerClient, $stopwatch);
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

        $collection = $client->invoke([$request1, $request2]);

        foreach ($collection as $response) {
            self::assertTrue($response->isSuccessful());

            self::assertInstanceOf(\stdClass::class, $response->getBody());
            self::assertObjectHasAttribute('result', $response->getBody());
            self::assertEquals('value', $response->getBody()->result);
        }

        $sections = $stopwatch->getSections();
        self::assertNotEmpty($sections);
        $section = array_shift($sections);
        $events  = $section->getEvents();
        self::assertNotEmpty($events);
        self::assertArrayHasKey('api_client', $events);
        $event = $events['api_client'];

        self::assertNotNull($event->getDuration());
        self::assertNotNull($event->getMemory());
    }
}
