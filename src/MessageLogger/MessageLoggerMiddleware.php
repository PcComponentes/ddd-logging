<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\MessageLogger;

use PcComponentes\Ddd\Util\Message\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

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
            'retry_count' => $this->extractEnvelopeRetryCount($envelope),
        ];

        $messageName = $message instanceof Message
            ? $message::messageName()
            : '';

        try {
            $result = $stack->next()->handle($envelope, $stack);
            $this->logger->info(
                \sprintf('%s "%s"', $this->action->success(), $messageName),
                $context,
            );
        } catch (\Throwable $e) {
            $context['exception'] = $e;
            $this->logger->error(
                \sprintf('%s "%s"', $this->action->error(), $messageName),
                $context,
            );

            throw $e;
        }

        return $result;
    }

    private function extractEnvelopeRetryCount(Envelope $envelope): int
    {
        $retryCountStamp = $envelope->last(RedeliveryStamp::class);

        return null !== $retryCountStamp
            ? $retryCountStamp->getRetryCount()
            : 0;
    }
}
