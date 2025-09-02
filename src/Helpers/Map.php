<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Helpers;

readonly final class Map
{
    public static function contentType(string $format): string
    {
        return match (strtolower($format)) {
            'json', 'canonical json', 'application/json' => 'application/json',
            'web template', 'flat', 'application/openehr.wt.flat+json' => 'application/openehr.wt.flat+json',
            'structured', 'application/openehr.wt.structured+json' => 'application/openehr.wt.structured+json',
            'xml', 'canonical', 'opt', 'mindmap', 'application/xml' => 'application/xml',
            'adl', 'adl2', 'text', 'aql', 'text/plain' => 'text/plain',
            default => throw new \InvalidArgumentException("Invalid format: {$format}"),
        };
    }

    public static function adlVersion(string $type): string
    {
        return match (strtolower($type)) {
            'adl2' => 'adl2',
            'adl1.4', 'adl' => 'adl1.4',
            default => throw new \InvalidArgumentException("Invalid ADL type: {$type}"),
        };
    }

    public static function archetypeFormat(string $format): string
    {
        $archetypeFormat = strtolower($format);
        if (!in_array($archetypeFormat, ['adl', 'xml', 'mindmap'])) {
            throw new \InvalidArgumentException("Invalid archetype format: {$format}");
        }
        return $archetypeFormat;
    }
}