<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'openehr_type_specification_explorer')]
readonly final class TypeSpecificationExplorer
{
    /**
     * Explore openEHR Type specifications expressed as BMM (Basic Meta-Model) JSON.
     *
     * @return array<array<string, string>>
     */
    public function __invoke(): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help users discover and retrieve openEHR Type specifications (i.e. schema with properties, documentation, methods, functions, etc.) bundled with this server. These files describe types defined under reference-model components (RM/AM/BASE) as BMM (Basic Meta-Model) JSON.'
            ],
            [
                'role' => 'user',
                'content' => 'Find type definitions for ENTRY related to activity.'
            ],
            [
                'role' => 'assistant',
                'content' => 'Use: list (openehr_type_specification_list) with namePattern (glob on type name, * is wildcard), and optional keyword to filter by JSON content. Example: namePattern="*ENTRY*", keyword="activity". Then use get (openehr_type_specification_get) with either the relative file path from list results or the exact type name (filename without extension) to retrieve the BMM JSON.'
            ],
            [
                'role' => 'user',
                'content' => 'Retrieve COMPOSITION type definition or schema.'
            ],
            [
                'role' => 'assistant',
                'content' => 'Use: get (openehr_type_specification_get) with a name or pattern to retrieve the BMM JSON of the type. Example: type="COMPOSITION" or type="*COMPOSITION".'
            ],
        ];
    }
}
