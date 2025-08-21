<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\ExecutionTime;

use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\Message;
use PcComponentes\DddLogging\ExecutionTime\ExecutionTimeProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class ExecutionTimeProcessorTest extends TestCase
{
    private MockObject $stopwatchMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->stopwatchMock = $this->createMock(Stopwatch::class);
    }

    public function testShouldReturnedRecordWithoutMessage()
    {
        $record = LogRecordMother::default();

        $result = (new ExecutionTimeProcessor($this->stopwatchMock))($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithoutDDDMessage()
    {
        $record = LogRecordMother::withContext([
            'message' => [],
        ]);

        $result = (new ExecutionTimeProcessor($this->stopwatchMock))($record);

        $this->assertEquals($record, $result);
        $this->assertNotInstanceOf(Message::class, $result['context']['message']);
    }

    public function testShouldReturnedRecordWithExecutionTime()
    {
        $milliseconds = 100000;
        $messageIdMock = $this->createMock(Uuid::class);
        $messageMock = $this->createMock(Message::class);
        $stopwatchEventMock = $this->createMock(StopwatchEvent::class);

        $messageIdMock
            ->expects($this->once())
            ->method('value')
            ->willReturn('id_value');

        $messageMock
            ->expects($this->once())
            ->method('messageId')
            ->willReturn($messageIdMock);

        $this->stopwatchMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($stopwatchEventMock);

        $stopwatchEventMock
            ->expects($this->once())
            ->method('getDuration')
            ->willReturn($milliseconds);

        $record = LogRecordMother::withContext([
            'message' => $messageMock,
        ],);

        $result = (new ExecutionTimeProcessor($this->stopwatchMock))($record);

        $this->assertArrayHasKey('execution_time', $result['extra']);
        $this->assertEquals($milliseconds / 1000, $result['extra']['execution_time']);
    }

    public function testShouldReturnedZeroExecutionTimeWhenLogicExceptionOccurred()
    {
        $messageIdMock = $this->createMock(Uuid::class);
        $messageMock = $this->createMock(Message::class);
        $stopwatchEventMock = $this->createMock(StopwatchEvent::class);

        $messageIdMock
            ->expects($this->once())
            ->method('value')
            ->willReturn('id_value');

        $messageMock
            ->expects($this->once())
            ->method('messageId')
            ->willReturn($messageIdMock);

        $this->stopwatchMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($stopwatchEventMock);

        $stopwatchEventMock
            ->expects($this->once())
            ->method('getDuration')
            ->willThrowException(new \LogicException());

        $record = LogRecordMother::withContext([
            'message' => $messageMock,
        ],);

        $result = (new ExecutionTimeProcessor($this->stopwatchMock))($record);

        $this->assertArrayHasKey('execution_time', $result['extra']);
        $this->assertEquals(0, $result['extra']['execution_time']);
    }
}
