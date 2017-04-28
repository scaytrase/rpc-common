<?php

namespace ScayTrase\Api\Rpc\Tests\Decorators;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use ScayTrase\Api\Rpc\Decorators\LoggableRpcClient;
use ScayTrase\Api\Rpc\Tests\RpcRequestTrait;

final class LoggerDecoratorTest extends TestCase
{
    use RpcRequestTrait;

    public function testLoggingSuccessfulResponseArray()
    {
        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(true, ['param1' => 'test']);
        $client = new LoggableRpcClient($this->getClientMock([$rq1], [$rs1]), $this->createLoggerMock());

        $collection = $client->invoke([$rq1]);
        self::assertEquals($rs1, $collection->getResponse($rq1));
    }

    public function testLoggingFailedResponseArray()
    {
        $rq1 = $this->getRequestMock('/test2', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(false, null, $this->getErrorMock(0, 'invalid'));
        $client = new LoggableRpcClient($this->getClientMock([$rq1], [$rs1]), $this->createLoggerMock());

        $collection = $client->invoke([$rq1]);
        self::assertEquals($rs1, $collection->getResponse($rq1));
    }

    public function testLoggingSuccessfulResponseIterator()
    {
        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(true, ['param1' => 'test']);
        $client = new LoggableRpcClient($this->getClientMock([$rq1], [$rs1]), $this->createLoggerMock());

        $collection = $client->invoke($rq1);
        foreach ($collection as $response) {
            self::assertEquals($rs1, $response);
        }

        // add check on log count
        self::assertEquals($rs1, $collection->getResponse($rq1));

        foreach ($collection as $response) {
            self::assertEquals($rs1, $response);
        }
    }

    public function testLoggingFailedResponseIterator()
    {
        $rq1 = $this->getRequestMock('/test2', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(false, null, $this->getErrorMock(0, 'invalid'));
        $client = new LoggableRpcClient($this->getClientMock([$rq1], [$rs1]), $this->createLoggerMock());

        $collection = $client->invoke($rq1);
        foreach ($collection as $response) {
            self::assertEquals($rs1, $response);
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createLoggerMock()
    {
        $logger = self::getMockBuilder(AbstractLogger::class)->setMethods(['log'])->getMock();
        $logger->expects(self::atLeastOnce())->method('log');

        return $logger;
    }
}
