<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\MessageLogger;

use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\SimpleMessage;
use PcComponentes\DddLogging\MessageLogger\Action;
use PcComponentes\DddLogging\MessageLogger\MessageLoggerMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessageLoggerMiddlewareTest extends TestCase
{
    private MockObject $loggerMock;
    private MockObject $actionMock;
    private MockObject $envelopeMock;
    private MockObject $stackMock;
    private MockObject $messageIdMock;
    private MockObject $middlewareMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->actionMock = $this->createMock(Action::class);
        $this->envelopeMock = $this->createMock(Envelope::class);
        $this->stackMock = $this->createMock(StackInterface::class);
        $this->messageIdMock = $this->createMock(Uuid::class);
        $this->middlewareMock = $this->createMock(MiddlewareInterface::class);
    }

    public function testShouldLoggedErrorWhenThrowsException()
    {
        $this->middlewareMock
            ->expects($this->once())
            ->method('handle')
            ->with($this->envelopeMock, $this->stackMock)
            ->willThrowException(new \Exception(""));

        $this->envelopeMock
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn(SimpleMessageMock::fromPayload(
                $this->messageIdMock,
                []
            ));

        $this->stackMock
            ->expects($this->once())
            ->method('next')
            ->willReturn($this->middlewareMock);

        $this->actionMock
            ->expects($this->once())
            ->method('error')
            ->willReturn('Error message');

        $this->loggerMock
            ->expects($this->once())
            ->method('error');

        $this->expectException(\Throwable::class);
        (new MessageLoggerMiddleware($this->loggerMock, $this->actionMock))
            ->handle($this->envelopeMock, $this->stackMock);
    }

    public function testShouldLoggedInfo()
    {
        $this->middlewareMock
            ->expects($this->once())
            ->method('handle')
            ->with($this->envelopeMock, $this->stackMock);

        $this->envelopeMock
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn(SimpleMessageMock::fromPayload(
                $this->messageIdMock,
                []
            ));

        $this->stackMock
            ->expects($this->once())
            ->method('next')
            ->willReturn($this->middlewareMock);

        $this->actionMock
            ->expects($this->once())
            ->method('success')
            ->willReturn('Info message');

        $this->loggerMock
            ->expects($this->once())
            ->method('info');

        (new MessageLoggerMiddleware($this->loggerMock, $this->actionMock))
            ->handle($this->envelopeMock, $this->stackMock);
    }
}

class SimpleMessageMock extends SimpleMessage
{
    public static function messageName(): string
    {
        return 'message_name';
    }

    public static function messageVersion(): string
    {
        return 'message_version';
    }

    public static function messageType(): string
    {
        return 'message_type';
    }

    protected function assertPayload(): void
    {
        // TODO: Implement assertPayload() method.
    }
}
