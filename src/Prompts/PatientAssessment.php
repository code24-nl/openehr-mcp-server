<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'patient_assessment')]
readonly final class PatientAssessment
{
    /**
     * Conduct a comprehensive patient assessment using openEHR templates
     *
     * @param string $ehr_id The EHR ID of the patient to assess
     * @return array<array<string, string>>
     */
    public function __invoke(string $ehr_id): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help conduct and record patient assessments using openEHR templates.'
            ],
            [
                'role' => 'user',
                'content' => "Perform patient assessment for EHR ID: {$ehr_id}."
            ],
            [
                'role' => 'assistant',
                'content' => "Steps: 1) find template (openehr_template_list / openehr_template_get) and optionally fetch example (openehr_template_example_get, format=flat), 2) structure findings, 3) store as COMPOSITION (openehr_composition_create). Include assessment type, observations, codes/units, onset/time, and clinician signature/context."
            ],
        ];
    }
}

