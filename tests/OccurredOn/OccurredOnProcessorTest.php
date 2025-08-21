<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\OccurredOn;

use PcComponentes\Ddd\Domain\Model\DomainEvent;
use PcComponentes\DddLogging\OccurredOn\OccurredOnProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\TestCase;

final class OccurredOnProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutMessage()
    {
        $record = LogRecordMother::default();

        $result = (new OccurredOnProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithOccurredOn()
    {
        $record = LogRecordMother::withContext(
            [
                'message' => [],
            ],
        );

        $result = (new OccurredOnProcessor())($record);


        $this->assertIsInt($result['extra']['occurred_on']);
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

        $record = LogRecordMother::withContext(
            [
                'message' => $domainEventMock,
            ],
        );

        $result = (new OccurredOnProcessor())($record);
        $this->assertArrayHasKey('occurred_on', $result['extra']);
        $this->assertEquals($expectedTimestamp, $result['extra']['occurred_on']);
    }
}
