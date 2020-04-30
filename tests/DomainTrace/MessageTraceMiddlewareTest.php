<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\CorrelationId;

use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\Message;
use PcComponentes\DddLogging\DomainTrace\MessageTraceMiddleware;
use PcComponentes\DddLogging\DomainTrace\Tracker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessageTraceMiddlewareTest extends TestCase
{
    private MockObject $mockEnvelope;
    private MockObject $mockStack;
    private MockObject $mockTracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockEnvelope = $this->createMock(Envelope::class);
        $this->mockStack = $this->createMock(StackInterface::class);
        $this->mockTracker = $this->createMock(Tracker::class);
    }

    public function testShouldFailNonExistingEnvelopeMessage()
    {
        $this->mockEnvelope
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn(null);

        $this->expectException(\TypeError::class);

        (new MessageTraceMiddleware($this->mockTracker))
            ->handle(
                $this->mockEnvelope,
                $this->mockStack
            );
    }

    public function testShouldAssignFirstTimeCorrelationId()
    {
        $toStringUuid = 'e62d7245-57b3-4842-9c7f-f7a89a439450';
        $mockMessageId = $this->createMock(Uuid::class);
        $mockMessageId
            ->expects($this->exactly(1))
            ->method('value')
            ->willReturn($toStringUuid);

        $mockMessage = $this->createMock(Message::class);
        $mockMessage
            ->expects($this->exactly(1))
            ->method('messageId')
            ->willReturn($mockMessageId);

        $this->mockEnvelope
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn($mockMessage);

        $this->mockTracker
            ->expects($this->once())
            ->method('assignReplyTo')
            ->with($toStringUuid);

        (new MessageTraceMiddleware($this->mockTracker))
            ->handle(
                $this->mockEnvelope,
                $this->mockStack
            );
    }

    public function testShouldReturnedExecutionNextMiddleware()
    {
        $toStringUuid = 'e62d7245-57b3-4842-9c7f-f7a89a439450';
        $mockMessageId = $this->createMock(Uuid::class);
        $mockMessageId
            ->expects($this->exactly(1))
            ->method('value')
            ->willReturn($toStringUuid);

        $mockMessage = $this->createMock(Message::class);
        $mockMessage
            ->expects($this->exactly(1))
            ->method('messageId')
            ->willReturn($mockMessageId);

        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->with($this->mockEnvelope, $this->mockStack);

        $this->mockStack
            ->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware);

        $this->mockEnvelope
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn($mockMessage);

        $this->mockTracker
            ->expects($this->once())
            ->method('assignReplyTo')
            ->with($toStringUuid);

        (new MessageTraceMiddleware($this->mockTracker))
            ->handle(
                $this->mockEnvelope,
                $this->mockStack
            );
    }
}
