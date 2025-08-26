<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\ExceptionCatcher;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PcComponentes\DddLogging\Util\AssocSerializer;

final class TraceOfExceptionProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        if (false === \array_key_exists('exception', $record['context'])) {
            return $record;
        }

        $context = $record->context;

        $exception = $context['exception'];
        $context['exception'] = AssocSerializer::from($context['exception']);

        if ($exception instanceof \JsonSerializable) {
            $context['exception']['data'] = \json_encode($exception, \JSON_THROW_ON_ERROR);
        }

        if (true === \array_key_exists('trace', $context['exception'])
            && false === \is_string($context['exception']['trace'])
        ) {
            $context['exception']['trace'] = \json_encode(
                $context['exception']['trace'],
                \JSON_THROW_ON_ERROR,
            );
        }

        return new LogRecord(
            $record->datetime,
            $record->channel,
            $record->level,
            $record->message,
            $context,
            $record->extra,
        );
    }
}
