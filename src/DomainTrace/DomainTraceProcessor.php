<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\DomainTrace;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\Message;

final class DomainTraceProcessor implements ProcessorInterface
{
    private Tracker $tracker;

    public function __construct(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $messageId = $this->getMessageId($record);

        $record['extra']['trace']['correlation_id'] = $this->tracker->correlationId($messageId);
        $record['extra']['trace']['reply_to'] = $this->tracker->replyTo($messageId);

        return $record;
    }

    private function getMessageId(LogRecord $record): ?Uuid
    {
        if (false === \array_key_exists('message', $record['context'])) {
            return null;
        }

        $message = $record['context']['message'];

        if (false === $message instanceof Message) {
            return null;
        }

        return $message->messageId();
    }
}
