<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use Monolog\Processor\ProcessorInterface;

final class DomainTraceProcessor implements ProcessorInterface
{
    private Tracker $tracker;

    public function __construct(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function __invoke(array $record): array
    {
        $record['extra']['correlation_id'] = $this->tracker->correlationId();
        $record['extra']['reply_to'] = $this->tracker->replyTo();

        return $record;
    }
}
