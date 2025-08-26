<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Context;

use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\SimpleMessage;
use PcComponentes\Ddd\Util\Message\ValueObject\AggregateId;
use PcComponentes\DddLogging\Context\NormalizeMessageProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\TestCase;

final class NormalizeContextProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutMessage()
    {
        $record = LogRecordMother::default();

        $result = (new NormalizeMessageProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithEncodedMessage()
    {
        $record = LogRecordMother::withContext([
            'message' => [
                'This is a message',
            ],
        ]);

        $result = (new NormalizeMessageProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithDDDMessage()
    {
        $stringUuid = '04e6de1e-c5b9-42fc-ad43-e1ec4e64e121';
        $messageId = Uuid::from($stringUuid);
        $simpleMessage = SimpleMessageMock::fromPayload($messageId, []);

        $record = LogRecordMother::withContext([
            'message' => $simpleMessage,
        ],);

        $result = (new NormalizeMessageProcessor())($record);

        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('message', $result['context']);
        $this->assertArrayHasKey('message_id', $result['context']['message']);
        $this->assertEquals($stringUuid, $result['context']['message']['message_id']);
        $this->assertArrayHasKey('type', $result['context']['message']);
        $this->assertEquals(SimpleMessageMock::messageType(), $result['context']['message']['type']);
        $this->assertArrayHasKey('payload', $result['context']['message']);
    }

    public function testShouldReturnedWithAggregateMessageInfo()
    {
        $stringMessageUuid = '04e6de1e-c5b9-42fc-ad43-e1ec4e64e121';
        $stringAggregateUuid = '575f378a-e0b4-4197-89c3-5c3065fdaf1e';
        $occurredOn = DateTimeValueObject::from('now');

        $messageId = Uuid::from($stringMessageUuid);
        $aggregateId = AggregateId::from($stringAggregateUuid);

        $aggregateMessage = AggregateMessageMock::fromPayload(
            $messageId,
            $aggregateId,
            $occurredOn,
            [],
            1,
        );

        $record = LogRecordMother::withContext([
            'message' => $aggregateMessage,
        ],);

        $result = (new NormalizeMessageProcessor())($record);

        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('message', $result['context']);
        $this->assertArrayHasKey('aggregate_id', $result['context']['message']);
        $this->assertArrayHasKey('message_id', $result['context']['message']);
        $this->assertEquals($stringMessageUuid, $result['context']['message']['message_id']);
        $this->assertArrayHasKey('type', $result['context']['message']);
        $this->assertEquals(AggregateMessageMock::messageType(), $result['context']['message']['type']);
        $this->assertArrayHasKey('payload', $result['context']['message']);
        $this->assertEquals($aggregateId, $result['context']['message']['aggregate_id']);
        $this->assertArrayHasKey('aggregate_version', $result['context']['message']);
        $this->assertEquals($aggregateMessage->aggregateVersion(), $result['context']['message']['aggregate_version']);
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
