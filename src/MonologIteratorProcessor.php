<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class MonologIteratorProcessor implements ProcessorInterface
{
    private array $processors;

    public function __construct(ProcessorInterface ...$processors)
    {
        $this->processors = $processors;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }

        return $record;
    }
}
