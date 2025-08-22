<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Tools;

use Code24\OpenEHR\MCP\Server\Tools\DefinitionService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PhpMcp\Schema\Content\TextContent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\NullLogger;

final class DefinitionServiceTest extends TestCase
{
    /** @var Client&MockObject */
    private Client $client;
    private NullLogger $logger;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->logger = new NullLogger();
    }

    public function testTemplateListReturnsDecodedArray(): void
    {
        $payload = ['templates' => [['id' => 'T1'], ['id' => 'T2']]];
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('v1/definition/template/adl1.4')
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($payload, JSON_THROW_ON_ERROR)));

        $svc = new DefinitionService($this->client, $this->logger);
        $result = $svc->templateList();
        $this->assertSame($payload, $result);
    }

    public function testTemplateGetRespectsAcceptHeaderAndReturnsBody(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with(
                'v1/definition/template/adl1.4/hello',
                $this->callback(function (array $opts): bool {
                    return isset($opts['headers']['Accept']) && $opts['headers']['Accept'] === 'application/json';
                })
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}'));

        $svc = new DefinitionService($this->client, $this->logger);
        $body = $svc->templateGet('hello', 'json', 'adl1.4');
        $this->assertStringContainsString('{"ok":true}', $body->text);
    }

    public function testTemplateUploadSendsBodyAndContentType(): void
    {
        $this->client
            ->expects($this->once())
            ->method('post')
            ->with(
                'v1/definition/template/adl1.4',
                $this->callback(function (array $opts): bool {
                    return ($opts['headers']['Content-Type'] ?? null) === 'application/xml'
                        && ($opts['headers']['Accept'] ?? null) === 'application/xml'
                        && ($opts['body'] ?? null) === '<opt />';
                })
            )
            ->willReturn(new Response(201, ['Content-Type' => 'application/xml'], '<ok/>'));

        $svc = new DefinitionService($this->client, $this->logger);
        $body = $svc->templateUpload('<opt />', 'opt', 'adl1.4');
        $this->assertStringContainsString('<ok/>', $body->text);
    }

    public function testStoredQueryUploadWithAndWithoutVersion(): void
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('put')
            ->willReturnCallback(function (string $endpoint, array $opts) {
                static $i = 0;
                $i++;
                if ($i === 1) {
                    $this->assertSame('v1/definition/query/' . rawurlencode('ns::q1'), $endpoint);
                    $this->assertSame('text/plain', $opts['headers']['Content-Type'] ?? null);
                    $this->assertSame('application/json', $opts['headers']['Accept'] ?? null);
                    $this->assertSame('SELECT 1', $opts['body'] ?? null);
                    return new Response(200, ['Content-Type' => 'application/json'], json_encode(['ok' => true], JSON_THROW_ON_ERROR));
                }
                $this->assertSame('v1/definition/query/' . rawurlencode('ns::q1') . '/' . rawurlencode('1.0.0'), $endpoint);
                $this->assertIsArray($opts);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode(['ok' => true], JSON_THROW_ON_ERROR));
            });

        $svc = new DefinitionService($this->client, $this->logger);
        $res1 = $svc->storedQueryUpload('ns::q1', 'SELECT 1', null);
        $this->assertTrue($res1['ok']);
        $res2 = $svc->storedQueryUpload('ns::q1', 'SELECT 1', '1.0.0');
        $this->assertTrue($res2['ok']);
    }

    public function testStoredQueryListAndGet(): void
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function (string $endpoint, array $opts) {
                static $i = 0;
                $i++;
                $this->assertSame('application/json', $opts['headers']['Accept'] ?? '');
                if ($i === 1) {
                    $this->assertSame('v1/definition/query/' . rawurlencode('pattern'), $endpoint);
                    return new Response(200, ['Content-Type' => 'application/json'], json_encode([['name' => 'a']], JSON_THROW_ON_ERROR));
                }
                $this->assertSame('v1/definition/query/aql/' . rawurlencode('q1') . '/' . rawurlencode('1.2.3'), $endpoint);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode(['q' => 'body'], JSON_THROW_ON_ERROR));
            });

        $svc = new DefinitionService($this->client, $this->logger);
        $list = $svc->storedQueryList('pattern');
        $this->assertIsArray($list);
        $this->assertCount(1, $list);
        $get = $svc->storedQueryGet('q1', '1.2.3');
        $this->assertSame(['q' => 'body'], $get);
    }

    public function testExceptionsAreWrappedAsRuntimeException(): void
    {
        $exception = new class('boom') extends \RuntimeException implements ClientExceptionInterface {
        };

        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('v1/definition/template/adl1.4')
            ->willThrowException($exception);

        $svc = new DefinitionService($this->client, $this->logger);
        $this->expectException(\RuntimeException::class);
        $svc->templateList();
    }
}
