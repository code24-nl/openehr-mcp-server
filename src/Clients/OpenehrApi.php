<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\ClientTrait;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class OpenehrApi
{

    use ClientTrait;

    private readonly Client $client;

    public function __construct(
        protected readonly LoggerInterface $logger,
    )
    {
        $apiConfig = [
            'base_uri' => OPENEHR_API_BASE_URL,
            RequestOptions::VERIFY => HTTP_SSL_VERIFY,
            RequestOptions::TIMEOUT => HTTP_TIMEOUT,
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ],
        ];
        $this->client = new Client($apiConfig);
        $this->logger->info('OpenEHR API client built.', $apiConfig);
    }

    public function request(string $method, $uri, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        return $this->client->requestAsync($method, $uri, $options);
    }

}
