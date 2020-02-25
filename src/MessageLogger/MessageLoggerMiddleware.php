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
    private Action $action;

    public function __construct(LoggerInterface $logger, Action $action)
    {
        $this->logger = $logger;
        $this->action = $action;
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
                $this->action->success() . ' "{name}"',
                $context,
            );
        } catch (\Throwable $e) {
            $context['exception'] = $e;
            $this->logger->error(
                $this->action->error() . ' "{name}"',
                $context,
            );

            throw $e;
        }

        return $result;
    }
}
