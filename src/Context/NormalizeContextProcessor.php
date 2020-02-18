<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Context;

use Monolog\Processor\ProcessorInterface;

final class NormalizeContextProcessor implements ProcessorInterface
{
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (false === \array_key_exists('message', $record['context'])) {
            return $record;
        }

        if (false === \is_string($record['context']['message'])) {
            $record['context']['message'] = \json_encode($record['context']['message']);
        }

        return $record;
    }
}
