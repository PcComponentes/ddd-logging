<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging;

final class MessageTracker
{
    private ?string $correlationId;
    private ?string $replyTo;

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function replyTo(): ?string
    {
        return $this->replyTo;
    }

    public function assignCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }

    public function assignReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }
}
