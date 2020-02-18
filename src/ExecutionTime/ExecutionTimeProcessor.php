<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\ExecutionTime;

use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Util\Message\SimpleMessage;
use Symfony\Component\Stopwatch\Stopwatch;

final class ExecutionTimeProcessor implements ProcessorInterface
{
    private Stopwatch $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function __invoke(array $record): array
    {
        if (false === array_key_exists('message', $record['context'])) {
            return $record;
        }

        $message = $record['context']['message'];
        if (false === $message instanceof SimpleMessage) {
            return $record;
        }

        $record['extra']['execution_time'] = $this->getExecutionTime($message);

        return $record;
    }

    private function getExecutionTime(SimpleMessage $message): float
    {
        $duration = 0;

        try {
            $event = $this->stopwatch->getEvent(
                $message->messageId()->value()
            );

            $duration = $event->getDuration();
        } catch (\LogicException $exception) {
        }

        return \round(
            $duration / 1000,
            6
        );
    }
}
