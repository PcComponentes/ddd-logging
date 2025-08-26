<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\ExceptionCatcher;

use _PHPStan_b8e553790\Symfony\Component\Console\Exception\LogicException;
use PcComponentes\DddLogging\ExceptionCatcher\TraceOfExceptionProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\TestCase;

final class TraceOfExceptionProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutExceptionContext()
    {
        $record = LogRecordMother::default();

        $recordResult = (new TraceOfExceptionProcessor())($record);

        $this->assertEquals($record, $recordResult);
    }

    public function testShouldReturnedRecordWithExceptionContext()
    {
        $exceptionMock = $this->createMock(\JsonSerializable::class);
        $record = LogRecordMother::withContext(
            [
                'exception' => $exceptionMock,
            ],
        );

        $exceptionMock
            ->expects($this->atLeastOnce())
            ->method('jsonSerialize')
            ->willReturn([]);

        $recordResult = (new TraceOfExceptionProcessor())($record);

        $this->assertArrayHasKey('data', $recordResult['context']['exception']);
    }

    public function testShouldReturnedRecordWithExceptionTraceContext()
    {
        $trace = [
            'method 1',
            'method 2',
        ];

        $record = LogRecordMother::withContext([
            'exception' => [
                'trace' => $trace,
            ],
        ]);

        $recordResult = (new TraceOfExceptionProcessor())($record);

        $this->assertStringContainsString('method 1', $recordResult['context']['exception']['trace']);
        $this->assertStringContainsString('method 2', $recordResult['context']['exception']['trace']);
    }

    public function testShouldReturnedRecordWithThrowableStringTraceContext()
    {
        $exception = new LogicException();

        $record = LogRecordMother::withContext([
            'exception' => $exception,
        ]);

        $recordResult = (new TraceOfExceptionProcessor())($record);


        $this->assertStringContainsString('#00 ', $recordResult['context']['exception']['trace']);
        $this->assertStringContainsString(
            'testShouldReturnedRecordWithThrowableStringTraceContext',
            $recordResult['context']['exception']['trace'],
        );
    }
}
