<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\CorrelationId;

use Pccomponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Pccomponentes\Ddd\Util\Message\Message;
use PcComponentes\DddLogging\CorrelationId\AssignCorrelationIdMiddleware;
use PcComponentes\DddLogging\MessageTracker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class AssignCorrelationIdMiddlewareTest extends TestCase
{
    private MockObject $mockEnvelope;
    private MockObject $mockStack;
    private MockObject $mockMessageTracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockEnvelope = $this->createMock(Envelope::class);
        $this->mockStack = $this->createMock(StackInterface::class);
        $this->mockMessageTracker = $this->createMock(MessageTracker::class);
    }

    public function testShouldFailNonExistingEnvelopeMessage()
    {
        $this->mockEnvelope
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        (new AssignCorrelationIdMiddleware($this->mockMessageTracker))
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
            ->expects($this->exactly(2))
            ->method('value')
            ->willReturn($toStringUuid);

        $mockMessage = $this->createMock(Message::class);
        $mockMessage
            ->expects($this->exactly(2))
            ->method('messageId')
            ->willReturn($mockMessageId);

        $this->mockEnvelope
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn($mockMessage);

        $this->mockMessageTracker
            ->expects($this->atLeastOnce())
            ->method('correlationId')
            ->willReturn(null);

        $this->mockMessageTracker
            ->expects($this->once())
            ->method('assignCorrelationId')
            ->with($toStringUuid);

        $this->mockMessageTracker
            ->expects($this->once())
            ->method('assignReplyTo')
            ->with($toStringUuid);

        (new AssignCorrelationIdMiddleware($this->mockMessageTracker))
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

        $this->mockMessageTracker
            ->expects($this->atLeastOnce())
            ->method('correlationId')
            ->willReturn($toStringUuid);

        $this->mockMessageTracker
            ->expects($this->once())
            ->method('assignReplyTo')
            ->with($toStringUuid);

        (new AssignCorrelationIdMiddleware($this->mockMessageTracker))
            ->handle(
                $this->mockEnvelope,
                $this->mockStack
            );
    }
}
