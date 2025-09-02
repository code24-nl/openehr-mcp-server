<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Clients;

use Code24\OpenEHR\MCP\Server\Clients\OpenehrApi;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class OpenehrApiTest extends TestCase
{
    public function testRequestDelegatesToGuzzleClient(): void
    {
        $logger = new NullLogger();

        $mockClient = $this->createMock(Client::class);
        $api = new OpenehrApi($logger, $mockClient);
        $mockClient
            ->expects($this->once())
            ->method('request')
            ->with('PUT', 'v1/foo', ['json' => ['x' => 1]])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}'));

        $res = $api->request('PUT', 'v1/foo', ['json' => ['x' => 1]]);
        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('{"ok":true}', (string) $res->getBody());
    }

    public function testRequestAsyncDelegatesToGuzzleClient(): void
    {
        $logger = new NullLogger();

        $mockClient = $this->createMock(Client::class);
        $api = new OpenehrApi($logger, $mockClient);
        $promise = $this->createMock(PromiseInterface::class);

        $mockClient
            ->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', 'v1/bar', [])
            ->willReturn($promise);

        $p = $api->requestAsync('DELETE', 'v1/bar', []);
        $this->assertSame($promise, $p);
    }
}
