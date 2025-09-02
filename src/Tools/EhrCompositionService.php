<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use Code24\OpenEHR\MCP\Server\Helpers\Map;
use GuzzleHttp\RequestOptions;
use PhpMcp\Schema\Content\TextContent;
use PhpMcp\Server\Attributes\McpTool;
use Psr\Http\Client\ClientExceptionInterface;

readonly final class EhrCompositionService extends AbstractOpenehrService
{
    /**
     * Create a new COMPOSITION in the given EHR.
     *
     * @param string $ehrId EHR identifier
     * @param string $compositionData Composition data
     * @param string $format Payload format: 'json', 'xml', 'flat', 'structured', 'application/xml', 'application/json', 'application/openehr.wt.flat+json' or 'application/openehr.wt.structured+json'; the default is 'json'
     * @return TextContent The created COMPOSITION data.
     */
    #[McpTool(name: 'openehr_composition_create')]
    public function create(string $ehrId, string $compositionData, string $format = 'json'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/composition';
            $contentType = Map::contentType($format);
            $response = $this->apiClient->post($endpoint, [
                'headers' => [
                    'Content-Type' => $contentType,
                    'Accept' => $contentType,
                ],
                RequestOptions::BODY => $compositionData,
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('Composition created', ['ehr_id' => $ehrId, 'status' => $response->getStatusCode()]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to create composition', ['error' => $e->getMessage(), 'ehr_id' => $ehrId]);
            throw new \RuntimeException('Failed to create composition: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get a COMPOSITION by its versioned object UID.
     *
     * @param string $ehrId EHR identifier
     * @param string $compositionUid Versioned object UID (can include version ID, server may return latest if not)
     * @param string $format Payload format: 'json', 'xml', 'flat', 'structured', 'application/xml', 'application/json', 'application/openehr.wt.flat+json' or 'application/openehr.wt.structured+json'; the default is 'json'
     * @return TextContent The retrieved COMPOSITION data.
     */
    #[McpTool(name: 'openehr_composition_get')]
    public function get(string $ehrId, string $compositionUid, string $format = 'json'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $contentType = Map::contentType($format);
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/composition/' . rawurlencode($compositionUid);
            $response = $this->apiClient->get($endpoint, [
                'headers' => [
                    'Accept' => $contentType,
                ],
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('Composition retrieved', ['ehr_id' => $ehrId, 'uid' => $compositionUid]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get composition', ['error' => $e->getMessage(), 'ehr_id' => $ehrId, 'uid' => $compositionUid]);
            throw new \RuntimeException('Failed to get composition: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update an existing COMPOSITION (creates a new version).
     *
     * @param string $ehrId EHR identifier
     * @param string $precedingVersionUid Preceding version UID of the COMPOSITION (full versioned uid)
     * @param string $compositionData Updated composition payload
     * @param string $format Payload format: 'json', 'xml', 'flat', 'structured', 'application/xml', 'application/json', 'application/openehr.wt.flat+json' or 'application/openehr.wt.structured+json'; the default is 'json'
     * @return TextContent The updated COMPOSITION data.
     */
    #[McpTool(name: 'openehr_composition_update')]
    public function update(string $ehrId, string $precedingVersionUid, string $compositionData, string $format = 'json'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/composition';
            $contentType = Map::contentType($format);
            $response = $this->apiClient->put($endpoint, [
                'headers' => [
                    'Content-Type' => $contentType,
                    'Accept' => $contentType,
                    'If-Match' => $precedingVersionUid,
                ],
                RequestOptions::BODY => $compositionData,
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('Composition updated', ['ehr_id' => $ehrId, 'preceding_version_uid' => $precedingVersionUid]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update composition', ['error' => $e->getMessage(), 'ehr_id' => $ehrId]);
            throw new \RuntimeException('Failed to update composition: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete a COMPOSITION (marks as deleted by creating a new version with deletion status).
     *
     * @param string $ehrId EHR identifier
     * @param string $precedingVersionUid Preceding version UID
     * @return array<string,mixed>
     */
    #[McpTool(name: 'openehr_composition_delete')]
    public function delete(string $ehrId, string $precedingVersionUid): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/composition';
            $response = $this->apiClient->delete($endpoint, [
                'headers' => [
                    'If-Match' => $precedingVersionUid,
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Composition deleted', ['ehr_id' => $ehrId, 'preceding_version_uid' => $precedingVersionUid]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to delete composition', ['error' => $e->getMessage(), 'ehr_id' => $ehrId]);
            throw new \RuntimeException('Failed to delete composition: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get revision history of a COMPOSITION.
     *
     * @param string $ehrId EHR identifier
     * @param string $compositionUid Versioned object UID
     * @return array<string,mixed>
     */
    #[McpTool(name: 'openehr_composition_revision_history')]
    public function revisionHistory(string $ehrId, string $compositionUid): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/composition/' . rawurlencode($compositionUid) . '/revision_history';
            $response = $this->apiClient->get($endpoint, [
                'headers' => ['Accept' => 'application/json'],
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Composition revision history retrieved', ['ehr_id' => $ehrId, 'uid' => $compositionUid]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get revision history', ['error' => $e->getMessage(), 'ehr_id' => $ehrId, 'uid' => $compositionUid]);
            throw new \RuntimeException('Failed to get revision history: ' . $e->getMessage(), 0, $e);
        }
    }
}
