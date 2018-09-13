<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

class MessageLoggerMiddleware implements MiddlewareInterface
{
    private $logger;
    private $tracker;
    private $successSerialization;
    private $exceptionSerialization;

    public function __construct(
        LoggerInterface $logger,
        Tracker $tracker,
        SuccessSerialization $successSerialization,
        ExceptionSerialization $exceptionSerialization
    ) {
        $this->logger = $logger;
        $this->tracker = $tracker;
        $this->successSerialization = $successSerialization;
        $this->exceptionSerialization = $exceptionSerialization;
    }

    public function handle($message, callable $next)
    {
        $parentOperationId = $this->tracker->parentOperationId();

        try {
            $result = $next($message);
            $this->logger->info(
                $this->successSerialization->message($parentOperationId, $message),
                $this->successSerialization->context($parentOperationId, $message)
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                $this->exceptionSerialization->message($parentOperationId, $message, $e),
                $this->exceptionSerialization->context($parentOperationId, $message, $e)
            );

            throw $e;
        }

        return $result;
    }
}
