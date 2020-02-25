<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\OccurredOn;

use Monolog\Processor\ProcessorInterface;

final class OccurredOnProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        $record['occurred_on'] = \round(
            \microtime(true) * 1000,
        );

        return $record;
    }
}
