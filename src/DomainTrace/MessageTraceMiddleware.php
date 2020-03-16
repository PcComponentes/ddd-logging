<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use Pccomponentes\Ddd\Util\Message\Message;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessageTraceMiddleware implements MiddlewareInterface
{
    private Tracker $tracker;

    public function __construct(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $this->messageFromEnvelope($envelope);

        $this->tracker->assignReplyTo(
            $message->messageId()->value()
        );

        return $stack->next()->handle($envelope, $stack);
    }

    private function messageFromEnvelope(Envelope $envelope): Message
    {
        return $envelope->getMessage();
    }
}
