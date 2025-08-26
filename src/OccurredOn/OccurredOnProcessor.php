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
        $message = $record['context']['message'] ?? null;

        if ($message instanceof DomainEvent) {
            $record['extra']['occurred_on'] = $message->occurredOn()->format(self::TIME_FORMAT);

            return $record;
        }

        $record['extra']['occurred_on'] = DateTimeValueObject::now()->format(self::TIME_FORMAT);

        return $record;
    }
}
