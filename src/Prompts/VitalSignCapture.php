<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(name: 'vital_sign_capture')]
readonly final class VitalSignCapture
{
    /**
     * Capture vital signs for a specific EHR ID
     *
     * @param string $ehr_id The EHR ID of the patient to capture vital signs for
     * @return array<array<string, string>>
     */
    public function __invoke(string $ehr_id): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You help clinicians capture vital signs into openEHR as structured COMPOSITIONs.'
            ],
            [
                'role' => 'user',
                'content' => "Capture vital signs for EHR ID: {$ehr_id}."
            ],
            [
                'role' => 'assistant',
                'content' => "Flow: 1) choose template_id and fetch example (openehr_template_example_get) in flat JSON, 2) fill values and codes (UCUM units) with timestamps, 3) submit COMPOSITION (openehr_composition_create) for EHR ID. Include BP, HR, RR, Temp, SpO2; specify device if relevant."
            ],
        ];
    }
}

