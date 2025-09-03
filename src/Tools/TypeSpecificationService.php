<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use Generator;
use PhpMcp\Schema\Content\TextContent;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;
use Psr\Log\LoggerInterface;
use SplFileInfo;

readonly final class TypeSpecificationService
{
    public const string BMM_DIR = APP_RESOURCES_DIR . '/bmm';

    public function __construct(
        private LoggerInterface $logger,
    )
    {
        if (!is_dir(self::BMM_DIR) || !is_readable(self::BMM_DIR)) {
            $this->logger->warning('BMM base path not found.', ['dir' => self::BMM_DIR]);
        }
    }

    private function getCandidateFiles(string $namePattern): Generator
    {
        // prepare glob-like regex from the pattern (supports * wildcard)
        $namePattern = str_replace(['\\*', '\\?'], ['[\w-]*', '[\w-]'], preg_quote($namePattern, '/'));
        $regex = '/^org\.openehr\.(?:[\w-]+\.)*' . $namePattern . '\.bmm\.json$/i';

        $results = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::BMM_DIR, \FilesystemIterator::SKIP_DOTS));
        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->isReadable()
                && (strtolower($fileInfo->getExtension()) === 'json')
                && $fileInfo->getSize()
                && preg_match($regex, $fileInfo->getFilename())
            ) {
                yield $fileInfo;
            }
        }
    }

    /**
     * Search and list all openEHR Types matching a name-pattern and an optional keyword.
     *
     * This searches in available openEHR specifications for types matching given criteria.
     * The results are filtered by keyword, if provided, and will be returned as a list of type metadata containing the following fields:
     * - type: the openEHR type name
     * - description: the openEHR type description (from JSON if present)
     * - component: the specification Component containing the openEHR Type (e.g., AM, RM, etc.)
     * - file: the relative path to the file.
     *
     * @param string $namePattern The name pattern to match against (e.g., ARCHETYPE_SLOT or ARCHETYPE_SL*)
     * @param string $keyword Optional keyword to filter on. If provided, only types containing the keyword in their JSON content will be returned.
     * @return array<int, array<string, string>> The list of matching types metadata.
     */
    #[McpTool(name: 'openehr_type_specification_list')]
    public function list(string $namePattern, string $keyword = ''): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        $namePattern = trim($namePattern);
        $keyword = trim($keyword);
        if (!$namePattern) {
            return [];
        }

        $results = [];
        foreach ($this->getCandidateFiles($namePattern) as $fileInfo) {
            try {
                $json = (string)file_get_contents($fileInfo->getPathname());
                if ($json) {
                    // keyword filter on content if provided
                    if ($keyword && !str_contains($json, $keyword)) {
                        continue;
                    }
                    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($data)) {
                        $results[] = [
                            'type' => (string)($data['name'] ?? $fileInfo->getFilename()),
                            'description' => (string)($data['documentation'] ?? $data['name'] ?? ''),
                            'component' => basename($fileInfo->getPath()),
                            'file' => str_replace(self::BMM_DIR . '/', '', $fileInfo->getPathname()),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $this->logger->error('Failed to read/parse JSON', ['file' => $fileInfo->getPathname(), 'error' => $e->getMessage()]);
            }
        }
        $this->logger->info('BMM list results', ['count' => count($results), 'namePattern' => $namePattern, 'keyword' => $keyword]);
        $this->logger->debug('BMM list results', $results);
        return $results ?: [['error' => 'not found', 'namePattern' => $namePattern, 'keyword' => $keyword]];
    }

    /**
     * Retrieve an openEHR Type specification as BMM JSON content.
     *
     * The identifier can be:
     * - a file name, representing the exact relative path under resources/bmm (e.g., AM/org.openehr.am.aom2.constraint_model.archetype_slot.bmm.json)
     * - or an exact Type name or pattern (e.g., ARCHETYPE_SLOT or ARCHETYPE_SL*);
     * The first match wins.
     *
     * @param string $typeOrFile The type identifier.
     * @return TextContent The type specification as BMM JSON content.
     */
    #[McpTool(name: 'openehr_type_specification_get')]
    public function get(string $typeOrFile): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        // Normalize identifier
        $typeOrFile = trim((string)str_replace('..', '', $typeOrFile));
        if (!$typeOrFile) {
            throw new \InvalidArgumentException('Identifier cannot be empty');
        }
        // First, try as a relative path
        $candidate = self::BMM_DIR . '/' . str_replace('\\', '/', $typeOrFile);
        if (is_file($candidate) && is_readable($candidate)) {
            $this->logger->info('Found bmm', ['filename' => $candidate]);
            $json = (string)file_get_contents($candidate);
            return TextContent::code($json, 'application/json');
        }
        // Then, search by type name
        foreach ($this->getCandidateFiles($typeOrFile) as $fileInfo) {
            $this->logger->info('Found bmm', ['pattern' => $fileInfo->getFilename()]);
            $json = (string)file_get_contents($fileInfo->getPathname());
            return TextContent::code($json, 'application/json');
        }
        $this->logger->info('Bmm not found', ['identifier' => $typeOrFile]);
        $json = (string)json_encode(['error' => 'not found', 'identifier' => $typeOrFile], JSON_PRETTY_PRINT);
        return TextContent::code($json, 'application/json');
    }
}
