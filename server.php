<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Code24\OpenEHR\MCP\Server\Clients\OpenehrApi;
use Monolog\Handler\StreamHandler;
use Monolog\Level as LogLevel;
use Monolog\Logger;
use PhpMcp\Server\Defaults\BasicContainer;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;
use Psr\Log\LoggerInterface;

try {
    $container = new BasicContainer();

    // Initialize logger
    $logger = new Logger('openehr-mcp-server');
    $logger->pushHandler(new StreamHandler('php://stderr', LogLevel::fromName(LOG_LEVEL)));
    $container->set(LoggerInterface::class, $logger);

    // Detect transport from command line arguments, acceptable values: stdio, streamable-http
    $transport = 'streamable-http';
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--transport=')) {
            $transport = substr($arg, strlen('--transport='));
        }
    }

    $logger->info('Starting ...', [
        'version' => APP_VERSION,
        'transport' => $transport,
        'env' => APP_ENV,
        'log' => LOG_LEVEL,
    ]);

    // Build server configuration
    $server = Server::make()
        ->withServerInfo('openEHR MCP Server', APP_VERSION)
        ->withContainer($container)
        ->withLogger($logger)
        ->build();
    $logger->info('Server definition built.');

    // Discover MCP elements via attributes
    $server->discover(
        basePath: __DIR__,
        scanDirs: ['src/Prompts', 'src/Tools'],
    );

    // Define server transport
    $transport = match ($transport) {
        // Listening via stdio transport
        'stdio' => new StdioServerTransport(),
        // Listening via streamable transport with resumability
        'streamable-http' => new StreamableHttpServerTransport(
            host: '0.0.0.0',
            port: 8242,
            mcpPath: '/mcp_openehr',
            enableJsonResponse: true,
            stateless: false
        ),
        default => throw new \InvalidArgumentException("Unsupported transport: {$transport}"),
    };
    $server->listen($transport);

    $logger->info('Server listener stopped gracefully.');
    exit(0);

} catch (\Throwable $e) {
    fwrite(STDERR, "[MCP SERVER CRITICAL ERROR]\n");
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    fwrite(STDERR, 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}