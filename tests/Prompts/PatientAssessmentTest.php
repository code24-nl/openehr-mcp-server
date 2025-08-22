<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Prompts;

use Code24\OpenEHR\MCP\Server\Prompts\PatientAssessment;
use PHPUnit\Framework\TestCase;

final class PatientAssessmentTest extends TestCase
{
    public function testPromptReturnsWellFormedMessagesAndReferencesTools(): void
    {
        $prompt = new PatientAssessment();
        $ehrId = 'ehr-123';
        $messages = $prompt->__invoke($ehrId);

        $this->assertIsArray($messages);
        $this->assertNotEmpty($messages);

        $allowedRoles = ['system','user','assistant'];
        $combinedContent = '';
        foreach ($messages as $msg) {
            $this->assertIsArray($msg);
            $this->assertArrayHasKey('role', $msg);
            $this->assertArrayHasKey('content', $msg);
            $this->assertContains($msg['role'], $allowedRoles);
            $this->assertIsString($msg['content']);
            $this->assertNotSame('', trim($msg['content']));
            $combinedContent .= "\n" . $msg['content'];
        }

        $this->assertStringContainsString('openehr_template_list', $combinedContent);
        $this->assertStringContainsString('openehr_composition_create', $combinedContent);
        $this->assertStringContainsString($ehrId, $combinedContent);
    }
}
