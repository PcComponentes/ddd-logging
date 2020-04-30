<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\OccurredOn;

use PcComponentes\Ddd\Domain\Model\DomainEvent;
use PcComponentes\DddLogging\OccurredOn\OccurredOnProcessor;
use PHPUnit\Framework\TestCase;

final class OccurredOnProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutMessage()
    {
        $record = [
            'context' => []
        ];

        $result = (new OccurredOnProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithOccurredOn()
    {
        $record = [
            'context' => [
                'message' => [],
            ]
        ];

        $result = (new OccurredOnProcessor())($record);

        $this->assertArrayHasKey('occurred_on', $result);
        $this->assertIsInt($result['occurred_on']);
    }

    public function testShouldReturnedRecordWithDomainEventOccurredOn()
    {
        $timestamp = 1582912634; //1582913896 876
        $milliseconds = '678';
        $occurredOnMock = $this->createMock(\DateTime::class);
        $occurredOnMock
            ->expects($this->once())
            ->method('getTimestamp')
            ->willReturn($timestamp);
        $occurredOnMock
            ->expects($this->once())
            ->method('format')
            ->with('v')
            ->willReturn($milliseconds);

        $domainEventMock = $this->createMock(DomainEvent::class);
        $domainEventMock
            ->expects($this->exactly(2))
            ->method('occurredOn')
            ->willReturn($occurredOnMock);

        $record = [
            'context' => [
                'message' => $domainEventMock
            ]
        ];

        $result = (new OccurredOnProcessor())($record);
        $this->assertArrayHasKey('occurred_on', $result);
        $this->assertEquals(
            $this->expectedOccurredOn(
                $timestamp,
                $milliseconds
            ),
            $result['occurred_on']
        );
    }

    private function expectedOccurredOn(int $timestamp, string $milliseconds)
    {
        return \intval(
            \sprintf(
                '%d%d',
                $timestamp,
                $milliseconds
            )
        );
    }
}
