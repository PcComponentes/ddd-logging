<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Util\EventSubscriber;


use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\DddLogging\MessageTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;


//TODO: Cargarselo y meter el middleware como lo hizo vioque
final class TraceRequestListener implements EventSubscriberInterface
{
    private MessageTracker $traceMarkerCommunicator;

    public function __construct(MessageTracker $traceMarkerCommunicator)
    {
        $this->traceMarkerCommunicator = $traceMarkerCommunicator;
    }

    public function onKernelController(RequestEvent $event)
    {
        $request = $event->getRequest();

        $this->traceMarkerCommunicator->assignCorrelationId(
            $this->getCorrelationId($request)
        );

        $this->traceMarkerCommunicator->assignReplyTo(
            $this->getReplyTo($request)
        );
    }

    private function getCorrelationId(Request $request): string
    {
        $headers = $request->headers->all();
        if (\array_key_exists('x-correlation-id', $headers)) {
            return $headers['x-correlation-id'][0];
        }

        return Uuid::v4()->value();
    }

    private function getReplyTo(Request $request): string
    {
        $headers = $request->headers->all();
        if (\array_key_exists('x-reply-to', $headers)) {
            return $headers['x-reply-to'][0];
        }

        return '';
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
