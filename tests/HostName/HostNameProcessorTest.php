<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\HostName;

use PcComponentes\DddLogging\Hostname\HostnameProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\TestCase;

final class HostNameProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithHostname()
    {
        $result = (new HostnameProcessor())(LogRecordMother::default());

        $this->assertArrayHasKey('extra', $result);
        $this->assertArrayHasKey('hostname', $result['extra']);
    }
}
