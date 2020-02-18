<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use PcComponentes\DddLogging\MessageTracker;

final class DomainTraceMiddleware
{
    public static function trace(MessageTracker $messageTracker): \Closure
    {
        return static function (callable $handler) use ($messageTracker) {
            return static function ($request, array $options) use ($handler, $messageTracker) {
                $request = $request->withHeader(
                    'x-correlation-id',
                    $messageTracker->correlationId()
                );

                $request = $request->withHeader(
                    'x-reply-to',
                    $messageTracker->replyTo()
                );

                return $handler($request, $options);
            };
        };
    }
}
