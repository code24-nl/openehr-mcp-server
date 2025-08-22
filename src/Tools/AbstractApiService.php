<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractApiService
{

    public function __construct(
        protected readonly Client $apiClient,
        protected readonly LoggerInterface $logger,
    )
    {
    }

}