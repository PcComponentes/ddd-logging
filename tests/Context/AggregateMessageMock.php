<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Context;

use PcComponentes\Ddd\Util\Message\AggregateMessage;

final class AggregateMessageMock extends AggregateMessage
{
    public static function messageName(): string
    {
        return 'aggregate_message_name';
    }

    public static function messageVersion(): string
    {
        return 'aggregate_message_version';
    }

    public static function messageType(): string
    {
        return 'aggregate_message_type';
    }

    protected function assertPayload(): void
    {
        // Empty on purpose for test doubles with primitive payloads.
    }
}
