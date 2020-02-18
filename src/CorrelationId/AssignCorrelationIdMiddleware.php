<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\CorrelationId;

use Pccomponentes\Ddd\Util\Message\Message;
use PcComponentes\DddLogging\MessageTracker;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class AssignCorrelationIdMiddleware implements MiddlewareInterface
{
    private MessageTracker $traceMarkerCommunicator;

    public function __construct(MessageTracker $traceMarkerCommunicator)
    {
        $this->traceMarkerCommunicator = $traceMarkerCommunicator;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        if (false === $message instanceof Message) {
            throw new \InvalidArgumentException(
                \sprintf(
                    '%s only works with %s',
                    self::class,
                    Message::class
                )
            );
        }

        if (null === $this->traceMarkerCommunicator->correlationId()) {
            $this->traceMarkerCommunicator->assignCorrelationId(
                $message->messageId()->value()
            );
        }

        $this->traceMarkerCommunicator->assignReplyTo(
            $message->messageId()->value()
        );

        return $stack->next()->handle($envelope, $stack);
    }
}
