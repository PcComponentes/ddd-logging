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
            'context' => [],
        ];

        $result = (new OccurredOnProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithOccurredOn()
    {
        $record = [
            'context' => [
                'message' => [],
            ],
        ];

        $result = (new OccurredOnProcessor())($record);

        $this->assertArrayHasKey('occurred_on', $result);
        $this->assertIsInt($result['occurred_on']);
    }

    public function testShouldReturnedRecordWithDomainEventOccurredOn()
    {
        $timestamp = '1582912634.678';
        $expectedTimestamp = '1582912634678';
        $occurredOn = \DateTimeImmutable::createFromFormat('U.v', $timestamp, new \DateTimeZone('UTC'));

        $domainEventMock = $this->createMock(DomainEvent::class);
        $domainEventMock
            ->expects($this->once())
            ->method('occurredOn')
            ->willReturn($occurredOn);

        $record = [
            'context' => [
                'message' => $domainEventMock,
            ],
        ];

        $result = (new OccurredOnProcessor())($record);
        $this->assertArrayHasKey('occurred_on', $result);
        $this->assertEquals($expectedTimestamp, $result['occurred_on']);
    }
}
