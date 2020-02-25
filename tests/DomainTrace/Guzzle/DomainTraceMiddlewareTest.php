<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Tests\DomainTrace\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PcComponentes\DddLogging\DomainTrace\Guzzle\DomainTraceMiddleware;
use PcComponentes\DddLogging\DomainTrace\Tracker;
use PHPUnit\Framework\TestCase;

final class DomainTraceMiddlewareTest extends TestCase
{
    public function testShouldAddedHeadersToRequest()
    {
        $container = [];
        $history = Middleware::history($container);
        $correlationId = "correlation_id_value";
        $replyTo = "reply_to_value";

        $messageTrackerMock = $this->createMock(Tracker::class);
        $messageTrackerMock
            ->expects($this->once())
            ->method('correlationId')
            ->willReturn($correlationId);

        $messageTrackerMock
            ->expects($this->once())
            ->method('replyTo')
            ->willReturn($replyTo);

        $guzzleMockHandler = new MockHandler([
            new Response(202, [], 'Hello, World'),
        ]);

        $handleStack = HandlerStack::create($guzzleMockHandler);
        $handleStack->push(DomainTraceMiddleware::trace($messageTrackerMock));
        $handleStack->push($history);

        $client = new Client(['handler' => $handleStack]);
        $client->request('GET', '/');

        $headers = $container[0]['request']->getHeaders();

        $this->assertArrayHasKey('x-correlation-id', $headers);
        $this->assertEquals($correlationId, $headers['x-correlation-id'][0]);
        $this->assertArrayHasKey('x-reply-to', $headers);
        $this->assertEquals($replyTo, $headers['x-reply-to'][0]);
    }
}
