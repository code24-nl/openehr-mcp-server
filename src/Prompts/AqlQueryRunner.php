<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'aql_query_runner')]
readonly final class AqlQueryRunner
{
    /**
     * Execute AQL queries (ad-hoc or stored) using QueryService and manage stored query definitions.
     *
     * @return array<array<string, string>>
     */
    public function __invoke(): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help users run openEHR AQL queries and manage stored queries.'
            ],
            [
                'role' => 'user',
                'content' => 'Run an ad-hoc query, then execute a stored query with parameters.'
            ],
            [
                'role' => 'assistant',
                'content' => 'Use: ad-hoc execute (openehr_query_adhoc), stored execute (openehr_query_stored_execute). Manage stored queries: upload (openehr_stored_query_upload), list (openehr_stored_query_list), get (openehr_stored_query_get). Tips: 1) Provide AQL text with proper FROM EHR clauses. 2) Use offset/fetch for pagination. 3) For stored execute, pass name, optional version, and params map. 4) Validate results and handle empty rows.'
            ],
        ];
    }
}
