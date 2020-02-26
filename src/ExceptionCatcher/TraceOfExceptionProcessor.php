<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\ExceptionCatcher;

use Monolog\Processor\ProcessorInterface;
use Pccomponentes\Apixception\Core\Exception\SerializableException;
use PcComponentes\DddLogging\Util\AssocSerializer;

final class TraceOfExceptionProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        if (false === \array_key_exists('exception', $record['context'])) {
            return $record;
        }

        $exception = $record['context']['exception'];
        $record['context']['exception'] = AssocSerializer::from($record['context']['exception']);

        if ($exception instanceof SerializableException) {
            $record['context']['exception']['data'] = \json_encode($exception->serialice());
        }

        if (true === \array_key_exists('trace', $record['context']['exception'])) {
            $record['context']['exception']['trace'] = \json_encode($record['context']['exception']['trace']);
        }

        return $record;
    }
}
