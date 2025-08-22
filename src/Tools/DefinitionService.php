<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use Code24\OpenEHR\MCP\Server\Helpers\Map;
use GuzzleHttp\RequestOptions;
use PhpMcp\Schema\Content\TextContent;
use PhpMcp\Server\Attributes\McpTool;
use Psr\Http\Client\ClientExceptionInterface;

readonly final class DefinitionService extends AbstractApiService
{

    /**
     * List all available Templates at the openEHR server Definitions endpoint.
     *
     * This will return the list of all Template metadata available on the server, including the Template ID.
     *
     * @param string $adl ADL formalism ('adl1.4' or 'adl2', default is 'adl1.4')
     * @return array<array<string, mixed>> The list of Template metadata.
     */
    #[McpTool(name: 'openehr_template_list')]
    public function templateList(string $adl = 'adl1.4'): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/definition/template/' . Map::adlVersion($adl);
            $response = $this->apiClient->get($endpoint);
            $data = json_decode($response->getBody()->getContents(), true);
            $this->logger->info('Templates listed successfully', ['count' => count($data['templates'] ?? [])]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to list templates', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to list templates: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get a specific Template from the openEHR server Definitions endpoint.
     *
     * This will return the Template definition in the specified format, according to defined formalism:
     * - for adl1.4 the format is OPT XML: 'opt'
     * - for adl2 the format is ADL: 'adl2'
     *
     * @param string $templateId The ID of the Template to retrieve.
     * @param string $format The format of the Template to retrieve ('opt', 'xml', 'application/xml' or 'adl'; default is 'opt').
     * @param string $adl ADL formalism ('adl1.4' or 'adl2'; default is 'adl1.4')
     * @return TextContent The Template definition.
     */
    #[McpTool(name: 'openehr_template_get')]
    public function templateGet(string $templateId, string $format = 'opt', string $adl = 'adl1.4'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $contentType = Map::contentType($format);
            $endpoint = 'v1/definition/template/' . Map::adlVersion($adl) . '/' . $templateId;
            $response = $this->apiClient->get($endpoint, [
                'headers' => [
                    'Accept' => $contentType,
                ]
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('Template retrieved successfully', ['template_id' => $templateId, 'status' => $response->getStatusCode()]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to retrieve template', ['error' => $e->getMessage(), 'template_id' => $templateId]);
            throw new \RuntimeException('Failed to retrieve template: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Upload a Template to the openEHR server Definitions endpoint.
     *
     * This will upload a new Template definition to the server and return the Template ID.
     * The server typically responds with a full representation of Template definition in XML format.
     *
     * @param string $templateContent The Template content (OPT XML or ADL string, depending on format argument)
     * @param string $format The content type format ('opt', 'xml', 'application/xml' or 'adl'; default is 'opt').
     * @param string $adl ADL formalism ('adl1.4' or 'adl2'; default 'adl1.4')
     * @return TextContent The saved Template full representation.
     */
    #[McpTool(name: 'openehr_template_upload')]
    public function templateUpload(string $templateContent, string $format = 'opt', string $adl = 'adl1.4'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, ['format' => $format, 'adl' => $adl]);
        try {
            $contentType = Map::contentType($format);
            $endpoint = 'v1/definition/template/' . Map::adlVersion($adl);
            $response = $this->apiClient->post($endpoint, [
                'headers' => [
                    'Content-Type' => $contentType,
                    'Accept' => $contentType,
                ],
                RequestOptions::BODY => $templateContent,
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('Template uploaded successfully', ['status' => $response->getStatusCode()]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to upload template', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to upload template: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get an example Composition for a given Template from the openEHR Definitions endpoint.
     *
     * This generates and returns an example Composition instance that conforms to the specified Template.
     * The server typically responds with Flat JSON format.
     *
     * @param string $templateId The ID of the Template to generate an example for.
     * @param string $format The format of the example to retrieve ('flat', 'json', 'xml', 'canonical', 'structured', 'application/xml', 'application/json', 'application/openehr.wt.flat+json', 'application/openehr.wt.structured+json'; default is 'flat' json).
     * @param string $adl ADL formalism ('adl1.4' or 'adl2'; default 'adl1.4')
     * @return TextContent The example Composition content.
     */
    #[McpTool(name: 'openehr_template_example_get')]
    public function templateExampleGet(string $templateId, string $format = 'flat', string $adl = 'adl1.4'): TextContent
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $contentType = Map::contentType($format);
            $endpoint = 'v1/definition/template/' . Map::adlVersion($adl) . '/' . $templateId . '/example';
            $response = $this->apiClient->get($endpoint, [
                'headers' => [
                    'Accept' => $contentType,
                ]
            ]);
            $data = $response->getBody()->getContents();
            $this->logger->info('Template example retrieved successfully', ['template_id' => $templateId]);
            $this->logger->debug(__METHOD__, [$contentType => $data]);
            return TextContent::code($data, $contentType);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to retrieve template example', ['error' => $e->getMessage(), 'template_id' => $templateId]);
            throw new \RuntimeException('Failed to retrieve template example: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Upload a Stored Query (AQL) definition to the server.
     *
     * @param string $name Query name (qualified path)
     * @param string $queryBody Query content (AQL text)
     * @param string|null $version Optional query version (e.g., 1.0.0)
     * @return array<string,mixed> The response from the server.
     */
    #[McpTool(name: 'openehr_stored_query_upload')]
    public function storedQueryUpload(string $name, string $queryBody, ?string $version = null): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/definition/query/' . rawurlencode($name);
            if (!empty($version)) {
                $endpoint .= '/' . rawurlencode($version);
            }
            $response = $this->apiClient->put($endpoint, [
                'headers' => [
                    'Content-Type' => 'text/plain',
                    'Accept' => 'application/json',
                ],
                RequestOptions::BODY => $queryBody,
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Stored query uploaded', ['name' => $name, 'version' => $version]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to upload stored query', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to upload stored query: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * List Stored Query (AQL) metadata.
     *
     * Retrieves the list of all Stored Queries on the system matched by the specified query name as a pattern.
     * The pattern is given be in the format of [{namespace}::]{query-name}, it will be treated as "wildcard" in the search.
     * When empty, it will return all Stored Queries.
     *
     * @param string $name Query name (or pattern) to search for. If empty, it will return all Stored Query metadata.
     * @return array<array<string,mixed>> Query metadata list.
     */
    #[McpTool(name: 'openehr_stored_query_list')]
    public function storedQueryList(string $name = ''): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/definition/query/' . rawurlencode($name);
            $response = $this->apiClient->get($endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Stored query metadata listed', ['name' => $name, 'count' => is_countable($data) ? count($data) : null]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to list stored queries', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to list stored queries: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get a specific Stored Query (AQL) definition.
     *
     * @param string $name Query name
     * @param string $version Query version
     * @return array<string,mixed> The Stored Query definition.
     */
    #[McpTool(name: 'openehr_stored_query_get')]
    public function storedQueryGet(string $name, string $version): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/definition/query/aql/' . rawurlencode($name) . '/' . rawurlencode($version);
            $response = $this->apiClient->get($endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Stored query retrieved', ['name' => $name, 'version' => $version]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get stored query', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to get stored query: ' . $e->getMessage(), 0, $e);
        }
    }

}