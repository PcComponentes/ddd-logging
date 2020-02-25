<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

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
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $stack->next()->handle($envelope, $stack);
        }

        $correlationId = $this->getCorrelationId($request);
        $replyTo = $this->getReplyTo($request);
        
        if (null !== $correlationId) {
            $this->tracker->assignCorrelationId($correlationId);
        }
        
        if (null !== $replyTo) {
            $this->tracker->assignReplyTo($replyTo);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function getCorrelationId(Request $request): string
    {
        return $request->headers->get('x-correlation-id');
    }

    private function getReplyTo(Request $request): string
    {
        return $request->headers->get('x-reply-to');
    }
}
