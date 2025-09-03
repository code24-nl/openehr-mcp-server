<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use GuzzleHttp\RequestOptions;
use PhpMcp\Server\Attributes\McpTool;
use Psr\Http\Client\ClientExceptionInterface;

readonly final class QueryService extends AbstractOpenehrService
{
    /**
     * Execute an ad-hoc AQL query.
     *
     * @param string $aql The AQL statement to execute
     * @param int $offset Optional pagination offset
     * @param int $fetch Optional pagination fetch/limit
     * @return array<string, mixed> The result of the query
     */
    #[McpTool(name: 'openehr_query_adhoc')]
    public function adhoc(string $aql, int $offset = 0, int $fetch = 0): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $payload = ['q' => $aql];
            if ($offset > 0) {
                $payload['offset'] = $offset;
            }
            if ($fetch > 0) {
                $payload['fetch'] = $fetch;
            }
            $response = $this->apiClient->post('v1/query/aql', [
                RequestOptions::JSON => $payload,
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('AQL executed', [
                'status' => $response->getStatusCode(),
                'rows' => isset($data['rows']) ? count($data['rows']) : null,
            ]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to execute AQL', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to execute AQL: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Execute a Stored Query by name and version.
     *
     * @param string $name Qualified query name
     * @param string $version Query version (e.g., 1.0.0)
     * @param array<string,mixed> $parameters Parameters map for the query (will be sent as JSON)
     * @return array<string, mixed> The result of the query
     */
    #[McpTool(name: 'openehr_query_stored_execute')]
    public function stored(string $name, string $version = '', array $parameters = []): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/query/' . rawurlencode($name);
            if (!empty($version)) {
                $endpoint .= '/' . rawurlencode($version);
            }
            $options = [];
            if (!empty($parameters)) {
                $options[RequestOptions::JSON] = ['params' => $parameters];
            }
            $response = $this->apiClient->post($endpoint, $options);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Stored query executed', [
                'name' => $name,
                'version' => $version,
                'rows' => isset($data['rows']) ? count($data['rows']) : null,
            ]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to execute stored query', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to execute stored query: ' . $e->getMessage(), 0, $e);
        }
    }
}
