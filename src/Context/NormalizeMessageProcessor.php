<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Context;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\Message;

final class NormalizeMessageProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        if (false === \array_key_exists('message', $record['context'])) {
            return $record;
        }

        $context = $record['context'];
        $message = $context['message'];

        if (false === ($message instanceof Message)) {
            return $record;
        }

        $context['message'] = [
            'message_id' => $message->messageId()->value(),
            'name' => $message::messageName(),
            'type' => $message::messageType(),
            'payload' => \json_encode($message->messagePayload()),
        ];

        if ($message instanceof AggregateMessage) {
            $context['message']['aggregate_id'] = $message->aggregateId();
            $context['message']['aggregate_version'] = $message->aggregateVersion();
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
