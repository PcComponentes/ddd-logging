<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use Pccomponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Pccomponentes\Ddd\Util\Message\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class RequestTraceMiddleware implements MiddlewareInterface
{
    private RequestStack $requestStack;
    private Tracker $tracker;

    public function __construct(RequestStack $requestStack, Tracker $tracker)
    {
        $this->requestStack = $requestStack;
        $this->tracker = $tracker;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->assertCorrelationId();

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $stack->next()->handle($envelope, $stack);
        }

        $message = $this->messageFromEnvelope($envelope);

        $correlationId = $this->correlationIdFromRequest($request);
        if (null !== $correlationId) {
            $this->tracker->assignCorrelationId($correlationId, $message->messageId());
        }

        $replyTo = $this->replyToFromRequest($request);
        if (null !== $replyTo) {
            $this->tracker->assignReplyTo($replyTo, $message->messageId());
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function messageFromEnvelope(Envelope $envelope): Message
    {
        return $envelope->getMessage();
    }

    private function assertCorrelationId(): void
    {
        if (null !== $this->tracker->correlationId()) {
            return;
        }

        $this->tracker->assignCorrelationId(
            Uuid::v4()->value()
        );
    }

    private function correlationIdFromRequest(Request $request): ?string
    {
        return $request->headers->get('x-correlation-id');
    }

    private function replyToFromRequest(Request $request): ?string
    {
        return $request->headers->get('x-reply-to');
    }
}
