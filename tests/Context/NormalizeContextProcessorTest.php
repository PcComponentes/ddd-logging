<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\Context;

use Monolog\LogRecord;
use PcComponentes\DddLogging\Context\NormalizeMessageProcessor;
use PcComponentes\DddLogging\Tests\Mock\LogRecordMother;
use PHPUnit\Framework\TestCase;

final class NormalizeContextProcessorTest extends TestCase
{
    public function testShouldReturnedRecordWithoutMessage()
    {
        $record = LogRecordMother::default();

        $result = (new NormalizeMessageProcessor())($record);

        $this->assertEquals($record, $result);
    }

    public function testShouldReturnedRecordWithEncodedMessage()
    {
        $record = LogRecordMother::withContext([
                'message' => [
                    'This is a message',
                ],
            ],
        );

        $expectedEncodedMessage = \json_encode($record['context']['message']);

        $result = (new NormalizeMessageProcessor())($record);

        $this->assertEquals($expectedEncodedMessage, $result['context']['message']);
    }
}