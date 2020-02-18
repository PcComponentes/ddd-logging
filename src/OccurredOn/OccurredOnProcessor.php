<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\OccurredOn;

use Monolog\Processor\ProcessorInterface;

final class OccurredOnProcessor implements ProcessorInterface
{
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['occurred_on'] = round(
            microtime(true) * 1000
        );

        return $record;
    }
}
