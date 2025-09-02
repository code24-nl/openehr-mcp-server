<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use Code24\OpenEHR\MCP\Server\Clients\CkmApi;
use Code24\OpenEHR\MCP\Server\Helpers\Map;
use GuzzleHttp\RequestOptions;
use PhpMcp\Schema\Content\TextContent;
use PhpMcp\Server\Attributes\McpTool;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

final readonly class CkmArchetypeService
{
    public function __construct(
        private CkmApi $apiClient,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * List archetypes from the CKM server
     *
     * This will list archetypes from the CKM server matching the given keyword searched in their main data.
     *
     * @param string $keyword Keyword to search archetypes for
     * @return array<array<string,mixed>> List of archetypes
     */
    #[McpTool(name: 'ckm_archetype_list')]
    public function archetypeList(string $keyword): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $response = $this->apiClient->get('v1/archetypes', [
                RequestOptions::QUERY => [
                    'search-text' => $keyword,
                    'restrict-search-to-main-data' => 'true',
                ],
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $this->logger->info('CKM archetypes listed', ['count' => is_countable($data) ? count($data) : null]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to list CKM archetypes', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to list CKM archetypes: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get a CKM archetype by its CID identifier
     *
     * This will retrieve in the specified format a CKM archetype by its CID identifier.
     *
     * @param string $cid CKM archetype CID identifier
     * @param string $format Format of to retrieve the archetype: 'adl', 'xml' or 'mindmap'
     * @return TextContent The CKM archetype in desired format
     */
    #[McpTool(name: 'ckm_archetype_get')]
    public function archetypeGet(string $cid, string $format = 'adl'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $archetypeFormat = Map::archetypeFormat($format);
            $contentType = Map::contentType($archetypeFormat);
            $cid = preg_replace('/[^\d.]/', '-', $cid);
            $response = $this->apiClient->get("v1/archetypes/{$cid}/{$archetypeFormat}", [
                RequestOptions::HEADERS => [
                    'Accept' => $contentType,
                ],
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('CKM archetype retrieved successfully', ['cid' => $cid, 'format' => $archetypeFormat, 'status' => $response->getStatusCode()]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to retrieve the CKM archetype', ['error' => $e->getMessage(), 'cid' => $cid, 'format' => $format]);
            throw new \RuntimeException('Failed to retrieve the CKM archetype: ' . $e->getMessage(), 0, $e);
        }
    }
}
