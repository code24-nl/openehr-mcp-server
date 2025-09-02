<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use Code24\OpenEHR\MCP\Server\Clients\OpenehrApi;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractOpenehrService
{

    public function __construct(
        protected OpenehrApi $apiClient,
        protected LoggerInterface $logger,
    )
    {
    }

}