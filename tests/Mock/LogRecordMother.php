<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Mock;

use Monolog\Level;
use Monolog\LogRecord;

class LogRecordMother extends LogRecord
{
    public static function default(): self
    {
        return new self(
            new \DateTimeImmutable('now'),
            'channel',
            Level::Info,
            '',
            [],
            [],
        );
    }

    public static function withContext(array $context): self
    {
        return new self(
            new \DateTimeImmutable('now'),
            'channel',
            Level::Info,
            '',
            $context,
            [],
        );
    }
}
