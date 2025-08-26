<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\OccurredOn;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Domain\Model\DomainEvent;
use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;

final class OccurredOnProcessor implements ProcessorInterface
{
    private const TIME_FORMAT = 'Y-m-d\TH:i:s.uP';

    public function __invoke(LogRecord $record): LogRecord
    {
        if (false === \array_key_exists('message', $record['context'])) {
            return $record;
        }

        $message = $record['context']['message'];

        if ($message instanceof DomainEvent) {
            $record['extra']['occurred_on'] = $message->occurredOn()->format(self::TIME_FORMAT);

            return $record;
        }

        $record['extra']['occurred_on'] = DateTimeValueObject::now()->format(self::TIME_FORMAT);

        return $record;
    }
}
