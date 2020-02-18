<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use Monolog\Processor\ProcessorInterface;
use PcComponentes\DddLogging\MessageTracker;

final class DomainTraceProcessor implements ProcessorInterface
{
    private MessageTracker $messageTracker;

    public function __construct(MessageTracker $traceMarkerCommunicator)
    {
        $this->messageTracker = $traceMarkerCommunicator;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['correlation_id'] = $this->messageTracker->correlationId();
        $record['extra']['reply_to'] = $this->messageTracker->replyTo();

        return $record;
    }
}
