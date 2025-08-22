<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Prompts;

use Code24\OpenEHR\MCP\Server\Prompts\CompositionManagement;
use PHPUnit\Framework\TestCase;

final class CompositionManagementTest extends TestCase
{
    public function testPromptReturnsWellFormedMessagesAndReferencesTools(): void
    {
        $prompt = new CompositionManagement();
        $ehrId = 'ehr-999';
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

        $this->assertStringContainsString('openehr_template_example_get', $combinedContent);
        $this->assertStringContainsString('openehr_composition_create', $combinedContent);
        $this->assertStringContainsString('openehr_composition_get', $combinedContent);
        $this->assertStringContainsString('openehr_composition_update', $combinedContent);
        $this->assertStringContainsString('openehr_composition_delete', $combinedContent);
        $this->assertStringContainsString('openehr_composition_revision_history', $combinedContent);
        $this->assertStringContainsString($ehrId, $combinedContent);
    }
}
