<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\ExecutionTime;

use PcComponentes\Ddd\Util\Message\SimpleMessage;
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
        $message = $envelope->getMessage();
        if (false === $message instanceof SimpleMessage) {
            throw new \InvalidArgumentException(
                \sprintf(
                    '%s only works with %s',
                    self::class,
                    SimpleMessage::class
                )
            );
        }

        $messageId = $message->messageId()->value();

        if (true === $this->stopwatch->isStarted($messageId)) {
            $this->stopwatch->reset();
        }

        $this->stopwatch->start($messageId);
        $envelope = $stack->next()->handle($envelope, $stack);
        $this->stopwatch->stop($messageId);

        return $envelope;
    }
}
