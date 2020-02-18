<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Info;

use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Domain\Model\DomainEvent;
use PcComponentes\Ddd\Util\Message\Message;

final class InfoProcessor implements ProcessorInterface
{
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (false === array_key_exists('message', $record['context'])) {
            return $record;
        }

        $record['extra']['payload'] = \json_encode($record['context']['message']);
        $record = $this->aggregateInfo($record);
        $record = $this->messageInfo($record);
        $record = $this->jsonInfo($record);

        return $record;
    }

    private function aggregateInfo(array $record): array
    {
        $message = $record['context']['message'];
        if (false === $message instanceof DomainEvent) {
            return $record;
        }

        $record['extra']['aggregate_id'] = $message->aggregateId();
        $record['extra']['occurred_on'] = $message->occurredOn()->format(\DateTime::ATOM);

        return $record;
    }

    private function messageInfo(array $record): array
    {
        $message = $record['context']['message'];
        if (false === $message instanceof Message) {
            return $record;
        }

        $record['extra']['name'] = $message::messageName();
        $record['extra']['message_id'] = $message->messageId()->value();
        $record['extra']['type'] = $message::messageType();

        return $record;
    }

    private function jsonInfo(array $record): array
    {
        $message = $record['context']['message'];
        if (false === $message instanceof \stdClass) {
            return $record;
        }

        $record['extra']['name'] = $message->name;
        $record['extra']['message_id'] = $message->message_id;
        $record['extra']['type'] = $message->type;

        return $record;
    }
}
