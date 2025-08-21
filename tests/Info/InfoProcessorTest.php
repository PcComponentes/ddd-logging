<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Info;

use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\SimpleMessage;
use PcComponentes\Ddd\Util\Message\ValueObject\AggregateId;
use PcComponentes\DddLogging\Info\InfoProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\TestCase;

final class InfoProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutMessage()
    {
        $record = LogRecordMother::default();

        $result = (new InfoProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithoutDDDMessage()
    {
        $record = LogRecordMother::default();

        $result = (new InfoProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithDDDMessageInfo()
    {
        $stringUuid = '04e6de1e-c5b9-42fc-ad43-e1ec4e64e121';
        $messageIdMock = $this->createMock(Uuid::class);
        $messageIdMock
            ->method('value')
            ->willReturn($stringUuid);
        $simpleMessage = SimpleMessageMock::fromPayload($messageIdMock, []);

        $record = LogRecordMother::withContext([
            'message' => $simpleMessage,
        ],);

        $result = (new InfoProcessor())($record);

        $this->assertArrayHasKey('extra', $result);
        $this->assertArrayHasKey('message', $result['extra']);
        $this->assertArrayHasKey('message_id', $result['extra']['message']);
        $this->assertEquals($stringUuid, $result['extra']['message']['message_id']);
        $this->assertArrayHasKey('name', $result['extra']['message']);
        $this->assertEquals(SimpleMessageMock::messageName(), $result['extra']['message']['name']);
        $this->assertArrayHasKey('type', $result['extra']['message']);
        $this->assertEquals(SimpleMessageMock::messageType(), $result['extra']['message']['type']);
        $this->assertArrayHasKey('payload', $result['extra']['message']);
    }

    public function testShouldReturnedWithAggregateMessageInfo()
    {
        $stringMessageUuid = '04e6de1e-c5b9-42fc-ad43-e1ec4e64e121';
        $stringAggregateUuid = '575f378a-e0b4-4197-89c3-5c3065fdaf1e';
        $occurredOn = DateTimeValueObject::from('now');

        $messageIdMock = $this->createMock(Uuid::class);
        $messageIdMock
            ->method('value')
            ->willReturn($stringMessageUuid);

        $aggregateIdMock = $this->createMock(AggregateId::class);
        $aggregateIdMock
            ->method('value')
            ->willReturn($stringAggregateUuid);

        $aggregateMessage = AggregateMessageMock::fromPayload(
            $messageIdMock,
            $aggregateIdMock,
            $occurredOn,
            [],
            1,
        );

        $record = LogRecordMother::withContext([
            'message' => $aggregateMessage,
        ],);

        $result = (new InfoProcessor())($record);

        $this->assertArrayHasKey('extra', $result);
        $this->assertArrayHasKey('message', $result['extra']);
        $this->assertArrayHasKey('aggregate_id', $result['extra']['message']);
        $this->assertEquals($aggregateIdMock, $result['extra']['message']['aggregate_id']);
        $this->assertArrayHasKey('aggregate_version', $result['extra']['message']);
        $this->assertEquals($aggregateMessage->aggregateVersion(), $result['extra']['message']['aggregate_version']);
        $this->assertArrayHasKey('occurred_on', $result['extra']['message']);
        $this->assertEquals($aggregateMessage->occurredOn()->format(\DateTime::ATOM), $result['extra']['message']['occurred_on']);
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

class AggregateMessageMock extends AggregateMessage
{
    public static function messageName(): string
    {
        return 'aggregate_message_name';
    }

    public static function messageVersion(): string
    {
        return 'aggregate_message_version';
    }

    public static function messageType(): string
    {
        return 'aggregate_message_type';
    }

    protected function assertPayload(): void
    {
        // TODO: Implement assertPayload() method.
    }
}
