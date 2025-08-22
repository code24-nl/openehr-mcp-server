<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'medication_review')]
readonly final class MedicationReview
{
    /**
     * Review and update patient medication information
     *
     * @param string $ehr_id The EHR ID of the patient to review
     * @return array<array<string, string>>
     */
    public function __invoke(string $ehr_id): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help clinicians review and update medications using openEHR templates and compositions.'
            ],
            [
                'role' => 'user',
                'content' => "Review medications for EHR ID: {$ehr_id}."
            ],
            [
                'role' => 'assistant',
                'content' => "Use: list/get template (openehr_template_list / openehr_template_get), optionally fetch example (openehr_template_example_get, format=flat), then record changes as a COMPOSITION (openehr_composition_create). Include medication name, dose, route, frequency, indication, start/stop, and timing; use coded terms and UCUM units."
            ],
        ];
    }
}

