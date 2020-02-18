<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessageLoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $context = [
            'message' => $message,
            'name' => $message::messageName(),
        ];

        try {
            $result = $stack->next()->handle($envelope, $stack);
            $this->logger->info(
                'A message has been processed "{name}"',
                $context
            );
        } catch (\Throwable $e) {
            $context['exception'] = $e;
            $this->logger->error(
                'An exception occurred while processing the message "{name}"',
                $context
            );

            throw $e;
        }

        return $result;
    }
}
