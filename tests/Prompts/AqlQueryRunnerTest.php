<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Prompts;

use Code24\OpenEHR\MCP\Server\Prompts\AqlQueryRunner;
use PHPUnit\Framework\TestCase;

final class AqlQueryRunnerTest extends TestCase
{
    public function testPromptReturnsWellFormedMessagesAndReferencesTools(): void
    {
        $prompt = new AqlQueryRunner();
        $messages = $prompt->__invoke();

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

        $this->assertStringContainsString('openehr_query_adhoc', $combinedContent);
        $this->assertStringContainsString('openehr_query_stored_execute', $combinedContent);
        $this->assertStringContainsString('openehr_stored_query_upload', $combinedContent);
        $this->assertStringContainsString('openehr_stored_query_list', $combinedContent);
        $this->assertStringContainsString('openehr_stored_query_get', $combinedContent);
    }
}
