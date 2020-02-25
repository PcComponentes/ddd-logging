<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\ExecutionTime;

use Pccomponentes\Ddd\Util\Message\Message;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class ExecutionTimeMiddleware implements MiddlewareInterface
{
    private Stopwatch $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $this->messageFromEnvelope($envelope);
        $messageId = $message->messageId()->value();

        if (true === $this->stopwatch->isStarted($messageId)) {
            $this->stopwatch->reset();
        }

        $this->stopwatch->start($messageId);
        $envelope = $stack->next()->handle($envelope, $stack);
        $this->stopwatch->stop($messageId);

        return $envelope;
    }

    private function messageFromEnvelope(Envelope $envelope): Message
    {
        return $envelope->getMessage();
    }
}
