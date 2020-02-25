<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\DomainTrace;

use PcComponentes\DddLogging\DomainTrace\Tracker;
use PHPUnit\Framework\TestCase;

final class TrackerTest extends TestCase
{
    public function testShouldReturnedExpectedCorrelationIdWhenAssign()
    {
        $correlationId = 'correlation_id_value';

        $tracker = new Tracker();

        $tracker->assignCorrelationId($correlationId);

        $this->assertEquals($correlationId, $tracker->correlationId());
    }

    public function testShouldReturnedExpectedReplyToWhenAssign()
    {
        $replyTo = 'reply_to_value';

        $tracker = new Tracker();

        $tracker->assignReplyTo($replyTo);

        $this->assertEquals($replyTo, $tracker->replyTo());
    }
}
