<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'template_management')]
readonly final class TemplateManagement
{
    /**
     * Manage openEHR Templates and examples via DefinitionService tools
     *
     * @return array<array<string, string>>
     */
    public function __invoke(): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help users manage openEHR Templates and retrieve example compositions.'
            ],
            [
                'role' => 'user',
                'content' => 'List templates, inspect one, upload a new one, and fetch an example.'
            ],
            [
                'role' => 'assistant',
                'content' => 'Use: list (openehr_template_list), get (openehr_template_get), upload (openehr_template_upload), example (openehr_template_example_get). Tips: 1) ADL defaults to adl1.4; specify adl2 when required. 2) When authoring composition examples, prefer example in flat JSON (format="flat"). 3) For get, choose format: opt (XML).'
            ],
        ];
    }
}
