<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\ExceptionCatcher;

use Pccomponentes\Apixception\Core\Exception\SerializableException;
use PcComponentes\DddLogging\ExceptionCatcher\TraceOfExceptionProcessor;
use PHPUnit\Framework\TestCase;

final class TraceOfExceptionProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutExceptionContext()
    {
        $record = [
            'context' => []
        ];

        $recordResult = (new TraceOfExceptionProcessor())($record);

        $this->assertEquals($record, $recordResult);
    }

    public function testShouldReturnedRecordWithExceptionContext()
    {
        $exceptionMock = $this->createMock(SerializableException::class);
        $record = [
            'context' => [
                'exception' => $exceptionMock
            ]
        ];

        $exceptionMock
            ->expects($this->once())
            ->method('serialice')
            ->willReturn([]);

        $recordResult = (new TraceOfExceptionProcessor())($record);

        $this->assertArrayHasKey('data', $recordResult['context']['exception']);
    }

    public function testShouldReturnedRecordWithExceptionTraceContext()
    {
        $trace = [
            'method 1',
            'method 2'
        ];

        $record = [
            'context' => [
                'exception' => [
                    'trace' => $trace
                ]
            ]
        ];

        $recordResult = (new TraceOfExceptionProcessor())($record);

        $this->assertStringContainsString('method 1',$recordResult['context']['exception']['trace']);
        $this->assertStringContainsString('method 2',$recordResult['context']['exception']['trace']);
    }
}
