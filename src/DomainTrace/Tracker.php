<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class Tracker
{
    private array $tracking;
    private ?string $correlationId;
    private ?string $replyTo;

    public function __construct()
    {
        $this->tracking = [];
        $this->correlationId = null;
        $this->replyTo = null;
    }

    public function correlationId(?Uuid $messageId = null): ?string
    {
        if (null === $messageId || false === \array_key_exists($messageId->value(), $this->tracking)) {
            return $this->correlationId;
        }

        $tracking = $this->tracking[$messageId->value()];

        return true === \array_key_exists('correlation_id', $tracking)
            ? $tracking['correlation_id']
            : null
        ;
    }

    public function replyTo(?Uuid $messageId = null): ?string
    {
        if (null === $messageId) {
            return $this->replyTo;
        }

        if (true === \array_key_exists($messageId->value(), $this->tracking)) {
            $tracking = $this->tracking[$messageId->value()];

            return true === \array_key_exists('reply_to', $tracking)
                ? $tracking['reply_to']
                : null
            ;
        }

        return $messageId->value() !== $this->replyTo
            ? $this->replyTo
            : null
        ;
    }

    public function assignCorrelationId(string $correlationId, ?Uuid $messageId = null): void
    {
        $this->correlationId = $correlationId;

        if (null === $messageId) {
            return;
        }

        if (false === \array_key_exists($messageId->value(), $this->tracking)) {
            $this->tracking[$messageId->value()] = [];
        }

        $this->tracking[$messageId->value()]['correlation_id'] = $correlationId;
    }

    public function assignReplyTo(string $replyTo, ?Uuid $messageId = null): void
    {
        $this->replyTo = $replyTo;
        if (null === $messageId) {
            return;
        }

        if (false === \array_key_exists($messageId->value(), $this->tracking)) {
            $this->tracking[$messageId->value()] = [];
        }

        $this->tracking[$messageId->value()]['reply_to'] = $replyTo;
    }
}
