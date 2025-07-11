<?php

namespace App\Api;

use Dflydev\Hawk\Client\ClientBuilder;
use Dflydev\Hawk\Credentials\Credentials;
use GuzzleHttp\Client as Guzzle;

class AbsenceClient
{
    private string $apiId;
    private string $apiKey;
    private string $baseUrl = 'https://app.absence.io/api/v2';
    private Guzzle $httpClient;

    public function __construct(string $apiId, string $apiKey)
    {
        $this->apiId = $apiId;
        $this->apiKey = $apiKey;
        $this->httpClient = new Guzzle(['http_errors' => false]);
    }

    public function fetchAbsences(string $startDate, string $endDate, int $limit = 500): array
    {
        $payload = [
            'skip'   => 0,
            'limit'  => $limit,
            'filter' => [
                'start' => ['$gte' => $startDate . 'T23:59:59.999Z'],
                'end'   => ['$lte' => $endDate . 'T00:00:00.000Z'],
            ],
            'relations' => ['assignedToId']
        ];

        $response = $this->makeRequest('/absences', 'POST', $payload);
        
        return $response['data'] ?? [];
    }

    private function makeRequest(string $endpoint, string $method, array $payload): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        
        $credentials = new Credentials($this->apiKey, 'sha256', $this->apiId);
        $hawk = ClientBuilder::create()->build();
        $header = $hawk->createRequest(
            $credentials,
            $this->baseUrl . $endpoint,
            $method,
            ['payload' => $body, 'content_type' => 'application/json']
        )->header()->fieldValue();

        $response = $this->httpClient->request(
            $method,
            $this->baseUrl . $endpoint,
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => $header
                ],
                'body' => $body
            ]
        );

        return json_decode($response->getBody(), true) ?? [];
    }
} 