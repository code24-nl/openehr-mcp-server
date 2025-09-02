<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Clients;

use Code24\OpenEHR\MCP\Server\Clients\CkmApi;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class CkmApiTest extends TestCase
{
    public function testRequestDelegatesToGuzzleClient(): void
    {
        $logger = new NullLogger();

        $mockClient = $this->createMock(Client::class);
        $api = new CkmApi($logger, $mockClient);

        $mockClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'v1/archetypes', ['query' => ['q' => 'bp']])
            ->willReturn(new Response(200, [], 'ok'));

        $res = $api->request('GET', 'v1/archetypes', ['query' => ['q' => 'bp']]);
        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('ok', (string) $res->getBody());
    }

    public function testRequestAsyncDelegatesToGuzzleClient(): void
    {
        $logger = new NullLogger();

        $mockClient = $this->createMock(Client::class);
        $api = new CkmApi($logger, $mockClient);
        $promise = $this->createMock(PromiseInterface::class);

        $mockClient
            ->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'v1/things', ['json' => ['a' => 1]])
            ->willReturn($promise);

        $p = $api->requestAsync('POST', 'v1/things', ['json' => ['a' => 1]]);
        $this->assertSame($promise, $p);
    }
}
