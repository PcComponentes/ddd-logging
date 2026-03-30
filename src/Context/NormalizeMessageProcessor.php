<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Context;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\Message;

final class NormalizeMessageProcessor implements ProcessorInterface
{
    private const BASE64_TRUNCATION_THRESHOLD = 16384;
    private const BASE64_PREVIEW_LENGTH = 128;
    private const BASE64_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    private const BASE64_DATA_URI_SEPARATOR = ';base64,';

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

        if (false === \is_string($value) || self::BASE64_TRUNCATION_THRESHOLD >= \strlen($value)) {
            return $value;
        }

        [$prefix, $candidate] = $this->extractBase64Candidate($value);
        $normalizedCandidate = $this->normalizeBase64Candidate($candidate);

        if (false === $this->shouldTruncateBase64Candidate($normalizedCandidate)) {
            return $value;
        }

        return $this->formatTruncatedBase64($prefix, $normalizedCandidate, \strlen($value));
    }

    /** @return array{string, string} */
    private function extractBase64Candidate(string $value): array
    {
        if (false === \str_starts_with($value, 'data:')) {
            return ['', $value];
        }

        $separatorPosition = \strpos($value, self::BASE64_DATA_URI_SEPARATOR);

        if (false === $separatorPosition) {
            return ['', $value];
        }

        $payloadPosition = $separatorPosition + \strlen(self::BASE64_DATA_URI_SEPARATOR);

        return [
            \substr($value, 0, $payloadPosition),
            \substr($value, $payloadPosition),
        ];
    }

    private function normalizeBase64Candidate(string $candidate): string
    {
        return \str_replace(["\r", "\n"], '', \trim($candidate));
    }

    private function shouldTruncateBase64Candidate(string $candidate): bool
    {
        $candidateLength = \strlen($candidate);

        if (self::BASE64_TRUNCATION_THRESHOLD >= $candidateLength || 0 !== $candidateLength % 4) {
            return false;
        }

        if ($candidateLength !== \strspn($candidate, self::BASE64_CHARACTERS)) {
            return false;
        }

        return false !== \base64_decode($candidate, true);
    }

    private function formatTruncatedBase64(string $prefix, string $candidate, int $originalLength): string
    {
        $preview = \substr($candidate, 0, self::BASE64_PREVIEW_LENGTH);

        return \sprintf(
            '%s%s...[base64 truncated; original_length=%d]',
            $prefix,
            $preview,
            $originalLength,
        );
    }
}
