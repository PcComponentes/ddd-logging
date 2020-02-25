<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\ExceptionCatcher;

use PcComponentes\DddLogging\ExceptionCatcher\HandlerExceptionCatcherMiddleware;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class HandlerExceptionCatcherMiddlewareTest extends TestCase
{
    public function testShouldReturnedOriginalException()
    {
        $originalException = new TestException();
        $mockEnvelope = $this->createMock(Envelope::class);
        $middlewareMock = $this->createMock(MiddlewareInterface::class);
        $mockStack = $this->createMock(StackInterface::class);

        $middlewareMock
            ->expects($this->once())
            ->method('handle')
            ->with($mockEnvelope, $mockStack)
            ->willThrowException(new HandlerFailedException($mockEnvelope, [$originalException]));

        $mockStack
            ->expects($this->once())
            ->method('next')
            ->willReturn($middlewareMock);

        $this->expectException(TestException::class);

        (new HandlerExceptionCatcherMiddleware())->handle($mockEnvelope, $mockStack);
    }

    public function testShouldReturnedNextMiddleware()
    {
        $mockEnvelope = $this->createMock(Envelope::class);
        $middlewareMock = $this->createMock(MiddlewareInterface::class);
        $mockStack = $this->createMock(StackInterface::class);

        $middlewareMock
            ->expects($this->once())
            ->method('handle')
            ->willReturn($mockEnvelope);

        $mockStack
            ->expects($this->once())
            ->method('next')
            ->willReturn($middlewareMock);

        $result = (new HandlerExceptionCatcherMiddleware())->handle($mockEnvelope, $mockStack);

        $this->assertInstanceOf(Envelope::class, $result);
    }
}

class TestException extends \Exception
{
    public function __construct()
    {
        parent::__construct( 'An exception message');
    }
}
