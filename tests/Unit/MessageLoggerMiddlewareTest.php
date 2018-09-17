<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging\Tests\Unit;

use Pccomponentes\DddLogging\ExceptionSerialization;
use Pccomponentes\DddLogging\MessageLoggerMiddleware;
use Pccomponentes\DddLogging\SuccessSerialization;
use Pccomponentes\DddLogging\Tracker;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MessageLoggerMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function given_callable_when_callable_execution_not_throw_exception_then_serialize_success()
    {
        $successSerialization = $this->createMock(SuccessSerialization::class);
        $successSerialization
            ->expects($this->once())
            ->method('message');
        $successSerialization
            ->expects($this->once())
            ->method('context');

        (new MessageLoggerMiddleware(
            $this->createMock(LoggerInterface::class),
            $this->createMock(Tracker::class),
            $successSerialization,
            $this->createMock(ExceptionSerialization::class)
        ))->handle(
            new \stdClass(),
            function () {
                return 'foo';
            }
        );
    }

    /**
     * @test
     */
    public function given_callable_when_callable_execution_throw_exception_then_serialize_exception()
    {
        $exceptionMessage = 'exception from callable';

        $exceptionSerialization = $this->createMock(ExceptionSerialization::class);
        $exceptionSerialization
            ->expects($this->once())
            ->method('message');
        $exceptionSerialization
            ->expects($this->once())
            ->method('context');

        try {
            (new MessageLoggerMiddleware(
                $this->createMock(LoggerInterface::class),
                $this->createMock(Tracker::class),
                $this->createMock(SuccessSerialization::class),
                $exceptionSerialization
            ))->handle(
                new \stdClass(),
                function () use ($exceptionMessage) {
                    throw new \Exception($exceptionMessage);
                }
            );
        } catch (\Exception $e) {
            if ($exceptionMessage === $e->getMessage()) {
                //sshhhhh
            } else {
                throw $e;
            }
        }
    }

    /**
     * @test
     */
    public function given_callable_when_callable_execution_not_throw_exception_then_log_info()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('info');

        (new MessageLoggerMiddleware(
            $loggerMock,
            $this->createMock(Tracker::class),
            $this->createMock(SuccessSerialization::class),
            $this->createMock(ExceptionSerialization::class)
        ))->handle(
            new \stdClass(),
            function () {
                return 'foo';
            }
        );
    }

    /**
     * @test
     */
    public function given_callable_when_callable_execution_throw_exception_then_log_error()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error');

        try {
            (new MessageLoggerMiddleware(
                $loggerMock,
                $this->createMock(Tracker::class),
                $this->createMock(SuccessSerialization::class),
                $this->createMock(ExceptionSerialization::class)
            ))->handle(
                new \stdClass(),
                function () {
                    throw new CustomException();
                }
            );
        } catch (\Exception $e) {
            if ($e instanceof CustomException) {
                //sshhhhh
            } else {
                throw $e;
            }
        }
    }

    /**
     * @test
     * @expectedException Pccomponentes\DddLogging\Tests\Unit\CustomException
     */
    public function given_callable_when_callable_execution_throw_exception_then_exception_overpass_method()
    {
        (new MessageLoggerMiddleware(
            $this->createMock(LoggerInterface::class),
            $this->createMock(Tracker::class),
            $this->createMock(SuccessSerialization::class),
            $this->createMock(ExceptionSerialization::class)
        ))->handle(
            new \stdClass(),
            function () {
                throw new CustomException();
            }
        );
    }
}
