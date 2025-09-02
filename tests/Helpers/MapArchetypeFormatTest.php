<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tests\Helpers;

use Code24\OpenEHR\MCP\Server\Helpers\Map;
use PHPUnit\Framework\TestCase;

final class MapArchetypeFormatTest extends TestCase
{
    public function testArchetypeFormatMappings(): void
    {
        $this->assertSame('adl', Map::archetypeFormat('adl'));
        $this->assertSame('xml', Map::archetypeFormat('xml'));
        $this->assertSame('mindmap', Map::archetypeFormat('mindmap'));
    }

    public function testArchetypeFormatThrowsOnInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Map::archetypeFormat('invalid');
    }
}
