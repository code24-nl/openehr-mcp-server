<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'ckm_archetype_explorer')]
final readonly class CkmArchetypeExplorer
{
    /**
     * Explore archetypes from the openEHR CKM. Use tools to list and fetch definitions by CID.
     * @return array<array<string,string>>
     */
    public function __invoke(): array
    {
        return [
            ['role' => 'system', 'content' => 'You help users explore openEHR CKM archetypes.'],
            ['role' => 'user', 'content' => 'List archetypes and retrieve the selected one by CID.'],
            ['role' => 'assistant', 'content' => 'Use tools: ckm_archetype_list to list available archetypes; ckm_archetype_get to fetch the Archetype as ADL, XML or as a Mindmap.'],
        ];
    }
}
