<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Util;

use PcComponentes\DddLogging\Util\AssocSerializer;
use PHPUnit\Framework\TestCase;

final class AssocSerializerTest extends TestCase
{
    public function testShouldSerializeDefaultExceptionCodeAsString()
    {
        $result = AssocSerializer::from(new \Exception());

        $this->assertSame('0', $result['code']);
    }

    public function testShouldSerializeIntegerExceptionCodeAsString()
    {
        $result = AssocSerializer::from(new \Exception('message', 123));

        $this->assertSame('123', $result['code']);
    }

    public function testShouldPreserveAlphanumericExceptionCode()
    {
        $exception = new class extends \Exception {
            public function __construct()
            {
                $this->code = 'HY000';
            }
        };

        $result = AssocSerializer::from($exception);

        $this->assertSame('HY000', $result['code']);
    }
}
