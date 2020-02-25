<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Info;

use Monolog\Processor\ProcessorInterface;
use Pccomponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\Message;

final class InfoProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        if (false === \array_key_exists('message', $record['context'])) {
            return $record;
        }

        $record = $this->messageInfo($record);
        $record = $this->aggregateInfo($record);

        return $record;
    }

    private function messageInfo(array $record): array
    {
        $message = $record['context']['message'];

        if (false === $message instanceof Message) {
            return $record;
        }

        $record['extra']['message_id'] = $message->messageId()->value();
        $record['extra']['name'] = $message::messageName();
        $record['extra']['type'] = $message::messageType();
        $record['extra']['payload'] = \json_encode($record['context']['message']);

        return $record;
    }

    private function aggregateInfo(array $record): array
    {
        $message = $record['context']['message'];

        if (false === $message instanceof AggregateMessage) {
            return $record;
        }

        $record['extra']['aggregate_id'] = $message->aggregateId();
        $record['extra']['aggregate_version'] = $message->aggregateVersion();
        $record['extra']['occurred_on'] = $message->occurredOn()->format(\DateTime::ATOM);

        return $record;
    }
}
