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

        $this->assertTrue(false !== \DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i:s.uP',
            $result['extra']['occurred_on'],
            new \DateTimeZone('UTC'),
        ));
    }

    public function testShouldReturnedRecordWithDomainEventOccurredOn()
    {
        $timestamp = '1582912634.678';
        $expectedValue = '2020-02-28T17:57:14.678000+00:00';
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
        $this->assertSame($expectedValue, $result['extra']['occurred_on']);
    }
}
