<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Context;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\Message;

final class NormalizeMessageProcessor implements ProcessorInterface
{
    private const STRING_TRUNCATION_THRESHOLD = 16384;
    private const STRING_PREVIEW_LENGTH = 128;

    public function __invoke(LogRecord $record): LogRecord
    {
        if (false === \array_key_exists('message', $record->context)) {
            return $record;
        }

        $context = $record->context;
        $message = $context['message'];

        if (false === ($message instanceof Message)) {
            return $record;
        }

        $context['message'] = [
            'message_id' => $message->messageId()->value(),
            'name' => $message::messageName(),
            'type' => $message::messageType(),
            'payload' => \json_encode($this->sanitizePayloadForLogging($message->messagePayload())),
        ];

        if ($message instanceof AggregateMessage) {
            $context['message']['aggregate_id'] = $message->aggregateId();
            $context['message']['aggregate_version'] = $message->aggregateVersion();
        }

        return $record->with(context: $context);
    }

    private function sanitizePayloadForLogging(array $payload): array
    {
        foreach ($payload as $key => $value) {
            $payload[$key] = $this->sanitizeValueForLogging($value);
        }

        return $payload;
    }

    private function sanitizeValueForLogging(mixed $value): mixed
    {
        if (\is_array($value)) {
            return $this->sanitizePayloadForLogging($value);
        }

        if (false === \is_string($value) || self::STRING_TRUNCATION_THRESHOLD >= \strlen($value)) {
            return $value;
        }

        return $this->formatTruncatedString($value);
    }

    private function formatTruncatedString(string $value): string
    {
        $preview = \substr($value, 0, self::STRING_PREVIEW_LENGTH);

        return \sprintf(
            '%s...[string truncated; original_length=%d]',
            $preview,
            \strlen($value),
        );
    }
}
