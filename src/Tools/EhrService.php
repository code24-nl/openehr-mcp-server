<?php

declare(strict_types=1);

namespace Code24\OpenEHR\MCP\Server\Tools;

use GuzzleHttp\RequestOptions;
use PhpMcp\Server\Attributes\McpTool;
use Psr\Http\Client\ClientExceptionInterface;

readonly final class EhrService extends AbstractApiService
{

    /**
     * Create a new EHR in the openEHR server.
     * If subjectId is provided, it will be associated with the EHR.
     *
     * @param string|null $subjectId Optional subject identifier
     * @param string $subjectNamespace Subject namespace (default: "default")
     * @return array<string, mixed> The created EHR data.
     */
    #[McpTool(name: 'openehr_ehr_create')]
    public function create(?string $subjectId = null, string $subjectNamespace = 'default'): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $options = [];
            if (!empty($subjectId)) {
                $options[RequestOptions::JSON] = [
                    '_type' => 'EHR_STATUS',
                    'name' => [
                        'value' => 'EHR Status'],
                    'archetype_node_id' => 'openEHR-EHR-EHR_STATUS.generic.v1',
                    'subject' => [
                        'external_ref' => [
                            'id' => [
                                '_type' => 'GENERIC_ID',
                                'value' => $subjectId,
                                'scheme' => 'PID',
                            ],
                            'namespace' => $subjectNamespace,
                            'type' => 'PERSON'
                        ],
                    ],
                    'is_queryable' => true,
                    'is_modifiable' => true,
                ];
            }
            $response = $this->apiClient->post('v1/ehr', $options);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('EHR created', ['status' => $response->getStatusCode()]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to create EHR', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to create EHR: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve an EHR by its EHR identifier.
     *
     * @param string $ehrId The EHR identifier
     * @return array<string, mixed> The retrieved EHR data.
     */
    #[McpTool(name: 'openehr_ehr_get')]
    public function get(string $ehrId): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $response = $this->apiClient->get('v1/ehr/' . rawurlencode($ehrId));
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('EHR retrieved', ['status' => $response->getStatusCode(), 'ehr_id' => $ehrId,]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get EHR', ['error' => $e->getMessage(), 'ehr_id' => $ehrId]);
            throw new \RuntimeException('Failed to get EHR: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve an EHR by given subject identifier and namespace.
     *
     * @param string $subjectId The subject identifier
     * @param string $subjectNamespace The subject namespace (default: "default")
     * @return array<string, mixed> The retrieved EHR data.
     */
    #[McpTool(name: 'openehr_ehr_get_by_subject')]
    public function getBySubject(string $subjectId, string $subjectNamespace = 'default'): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $response = $this->apiClient->get('v1/ehr', [
                'query' => [
                    'subject_id' => $subjectId,
                    'subject_namespace' => $subjectNamespace,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('EHR retrieved by subject', ['status' => $response->getStatusCode(), 'subject' => $subjectId]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get EHR by subject', ['error' => $e->getMessage(), 'subject' => $subjectId, 'namespace' => $subjectNamespace]);
            throw new \RuntimeException('Failed to get EHR by subject: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve the EHR_STATUS of an EHR.
     *
     * @param string $ehrId The EHR identifier
     * @return array<string, mixed> The retrieved EHR_STATUS data.
     */
    #[McpTool(name: 'openehr_ehr_status_get')]
    public function getStatus(string $ehrId): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $response = $this->apiClient->get('v1/ehr/' . rawurlencode($ehrId) . '/ehr_status');
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('EHR status retrieved', ['status' => $response->getStatusCode(), 'ehr_id' => $ehrId,]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get EHR status', ['error' => $e->getMessage(), 'ehr_id' => $ehrId]);
            throw new \RuntimeException('Failed to get EHR status: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create a Contribution associated with the given EHR.
     *
     * @param string $ehrId EHR identifier
     * @param string $contributionData JSON string of contribution data
     * @return array<string,mixed> The created Contribution data.
     */
    #[McpTool(name: 'openehr_ehr_contribution_create')]
    public function createContribution(string $ehrId, string $contributionData): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/contribution';
            $response = $this->apiClient->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                RequestOptions::BODY => $contributionData,
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Contribution created', ['ehr_id' => $ehrId]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to create contribution', ['error' => $e->getMessage(), 'ehr_id' => $ehrId]);
            throw new \RuntimeException('Failed to create contribution: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve a Contribution associated with the given EHR.
     *
     * @param string $ehrId EHR identifier
     * @param string $contributionUid Contribution UID (versioned object UID)
     * @return array<string,mixed> The retrieved Contribution data.
     */
    #[McpTool(name: 'openehr_ehr_contribution_get')]
    public function getContribution(string $ehrId, string $contributionUid): array
    {
        $this->logger->debug('called ' . __METHOD__, func_get_args());
        try {
            $endpoint = 'v1/ehr/' . rawurlencode($ehrId) . '/contribution/' . rawurlencode($contributionUid);
            $response = $this->apiClient->get($endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true) ?? [];
            $this->logger->info('Contribution retrieved', ['ehr_id' => $ehrId, 'uid' => $contributionUid]);
            $this->logger->debug(__METHOD__, ['response' => $data]);
            return $data;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Failed to get contribution', ['error' => $e->getMessage(), 'ehr_id' => $ehrId, 'uid' => $contributionUid]);
            throw new \RuntimeException('Failed to get contribution: ' . $e->getMessage(), 0, $e);
        }
    }
}
