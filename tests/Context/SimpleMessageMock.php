<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Context;

use PcComponentes\Ddd\Util\Message\SimpleMessage;

final class SimpleMessageMock extends SimpleMessage
{
    public static function messageName(): string
    {
        return 'message_name';
    }

    public static function messageVersion(): string
    {
        return 'message_version';
    }

    public static function messageType(): string
    {
        return 'message_type';
    }

    protected function assertPayload(): void
    {
        // Empty on purpose for test doubles with primitive payloads.
    }
}
