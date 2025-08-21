<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Context;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class NormalizeMessageProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        $returnRecord = $record;

        if (false === \array_key_exists('message', $record['context'])) {
            return $returnRecord;
        }

        if (false === \is_string($record['context']['message'])) {
            $context = $record['context'];
            $context['message'] = \json_encode($record['context']['message']);

            $returnRecord = new LogRecord(
                $record->datetime,
                $record->channel,
                $record->level,
                $record->message,
                $context,
                $record->extra,
            );
        }

        return $returnRecord;
    }
}
