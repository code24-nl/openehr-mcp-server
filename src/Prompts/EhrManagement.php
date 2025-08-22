<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'ehr_management')]
readonly final class EhrManagement
{
    /**
     * Guide users through EHR lifecycle actions: create, find, and inspect status.
     *
     * @return array<array<string, string>>
     */
    public function __invoke(): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help users manage openEHR EHRs: creation, lookup, and status.'
            ],
            [
                'role' => 'user',
                'content' => 'Create a new EHR (optionally with subject), then retrieve an EHR by ID and by subject, and check its status.'
            ],
            [
                'role' => 'assistant',
                'content' => 'Use: create (openehr_ehr_create), get (openehr_ehr_get), get by subject (openehr_ehr_get_by_subject), status (openehr_ehr_status_get). Optionally, manage contributions: create (openehr_ehr_contribution_create), get (openehr_ehr_contribution_get). Provide ehr_id or subject_id/subject_namespace as appropriate.'
            ],
        ];
    }
}
