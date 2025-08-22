<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'composition_management')]
readonly final class CompositionManagement
{
    /**
     * Manage COMPOSITIONs within an EHR using EhrCompositionService tools.
     * Also leverage DefinitionService to fetch an example composition for a template.
     *
     * @param string $ehr_id Target EHR ID
     * @return array<array<string, string>>
     */
    public function __invoke(string $ehr_id): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help users create, retrieve, update, delete, and inspect revision history of openEHR COMPOSITIONs. When needed, obtain an example composition for a chosen template first.'
            ],
            [
                'role' => 'user',
                'content' => "Work with COMPOSITIONs in EHR ID: {$ehr_id}."
            ],
            [
                'role' => 'assistant',
                'content' => 'Use tools: example (openehr_template_example_get), create (openehr_composition_create), get (openehr_composition_get), update (openehr_composition_update), delete (openehr_composition_delete), revision history (openehr_composition_revision_history). Guidance: 1) Prefer flat JSON format for authoring (format="flat"). 2) When calling create/update, pass ehr_id, composition payload (compositionData), and format. 3) For get/revision history, pass composition_uid (versioned object UID). 4) For update/delete, set preceding_version_uid from the latest version. 5) Use openehr_template_get to inspect the template if needed.'
            ],
        ];
    }
}
