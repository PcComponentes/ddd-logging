<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Context;

use Monolog\LogRecord;
use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
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

    public function testShouldReturnedRecordWithSmallPayloadWithoutTruncation()
    {
        $smallString = 'small-payload';

        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'attachment' => $smallString,
        ]));

        $payload = $this->decodePayload($result);

        $this->assertSame($smallString, $payload['attachment']);
    }

    public function testShouldReturnedRecordWithTruncatedLargeStringPayload()
    {
        $largeString = \str_repeat('a', 18000);

        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'attachment' => $largeString,
        ]));

        $payload = $this->decodePayload($result);

        $this->assertStringStartsWith(\substr($largeString, 0, 128), $payload['attachment']);
        $this->assertStringContainsString('[string truncated; original_length=', $payload['attachment']);
        $this->assertStringNotContainsString($largeString, $payload['attachment']);
    }

    public function testShouldReturnedRecordWithLargeNonBase64PayloadTruncated()
    {
        $largeString = \str_repeat('x-', 9000);

        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'attachment' => $largeString,
        ]));

        $payload = $this->decodePayload($result);

        $this->assertStringStartsWith(\substr($largeString, 0, 128), $payload['attachment']);
        $this->assertStringContainsString('[string truncated; original_length=', $payload['attachment']);
        $this->assertStringNotContainsString($largeString, $payload['attachment']);
    }

    /**
     * @dataProvider utf8TruncationProvider
     */
    public function testShouldTruncateLargeUtf8StringWithoutSplittingCharacters(
        string $prefix,
        string $character,
        string $expectedPreview
    ) {
        $largeString = $prefix . $character . \str_repeat('b', 18000);

        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'address' => $largeString,
        ]));

        $this->assertIsString($result['context']['message']['payload']);
        $payload = $this->decodePayload($result);

        $this->assertSame(
            $expectedPreview . '...[string truncated; original_length=' . \strlen($largeString) . ']',
            $payload['address'],
        );
    }

    public function utf8TruncationProvider(): array
    {
        return [
            'two-byte character crossing boundary' => [\str_repeat('a', 127), 'é', \str_repeat('a', 127)],
            'three-byte character crossing boundary' => [\str_repeat('a', 127), '€', \str_repeat('a', 127)],
            'four-byte character crossing boundary' => [\str_repeat('a', 127), '😀', \str_repeat('a', 127)],
            'two-byte character fitting boundary' => [\str_repeat('a', 126), 'é', \str_repeat('a', 126) . 'é'],
            'three-byte character fitting boundary' => [\str_repeat('a', 125), '€', \str_repeat('a', 125) . '€'],
            'four-byte character fitting boundary' => [\str_repeat('a', 124), '😀', \str_repeat('a', 124) . '😀'],
        ];
    }

    public function testShouldReplaceInvalidUtf8WithoutLosingOtherPayloadFields()
    {
        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'address' => "street \xB1 name",
            'city' => 'Madrid',
        ]));

        $this->assertIsString($result['context']['message']['payload']);
        $this->assertSame([
            'address' => "street \u{FFFD} name",
            'city' => 'Madrid',
        ], $this->decodePayload($result));
    }

    public function testShouldKeepSerializablePayloadFieldsWhenJsonEncodingFails()
    {
        $resource = \fopen('php://memory', 'r');

        try {
            $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
                'attachment' => $resource,
                'address' => 'street name',
            ]));
        } finally {
            \fclose($resource);
        }

        $this->assertIsString($result['context']['message']['payload']);
        $this->assertSame([
            'attachment' => null,
            'address' => 'street name',
        ], $this->decodePayload($result));
    }

    public function testShouldReturnedRecordWithTruncatedNestedLargeStringPayload()
    {
        $largeString = \str_repeat('b', 18000);

        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'evidence' => [
                'attachment' => $largeString,
            ],
        ]));

        $payload = $this->decodePayload($result);

        $this->assertStringStartsWith(\substr($largeString, 0, 128), $payload['evidence']['attachment']);
        $this->assertStringContainsString('[string truncated; original_length=', $payload['evidence']['attachment']);
    }

    public function testShouldReturnedRecordWithTruncatedLargeDataUriPayload()
    {
        $base64 = \base64_encode(\str_repeat('c', 14000));
        $dataUri = 'data:image/png;base64,' . $base64;

        $result = (new NormalizeMessageProcessor())($this->createRecordWithPayload([
            'attachment' => $dataUri,
        ]));

        $payload = $this->decodePayload($result);

        $this->assertStringStartsWith(\substr($dataUri, 0, 128), $payload['attachment']);
        $this->assertStringContainsString('[string truncated; original_length=', $payload['attachment']);
        $this->assertStringNotContainsString($dataUri, $payload['attachment']);
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

    private function decodePayload(LogRecord $record): array
    {
        return \json_decode($record->context['message']['payload'], true, 512, \JSON_THROW_ON_ERROR);
    }

    private function createRecordWithPayload(array $payload): LogRecord
    {
        return LogRecordMother::withContext([
            'message' => SimpleMessageMock::fromPayload(
                Uuid::from('04e6de1e-c5b9-42fc-ad43-e1ec4e64e121'),
                $payload,
            ),
        ]);
    }
}
