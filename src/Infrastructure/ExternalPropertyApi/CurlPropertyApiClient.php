<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalPropertyApi;

class CurlPropertyApiClient
{
    private string $baseUrl;

    private string $apiKey;

    private int $pageSize;

    private int $timeoutSeconds;

    public function __construct(string $baseUrl, string $apiKey, int $pageSize, int $timeoutSeconds = 30)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->pageSize = min(100, max(1, $pageSize));
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * @return array{
     *     current_page: int,
     *     last_page: int,
     *     per_page: int,
     *     data: array<int, array<string, mixed>>
     * }
     */
    public function fetchPage(int $pageNumber): array
    {
        $query = http_build_query([
            'page' => [
                'number' => $pageNumber,
                'size' => $this->pageSize,
            ],
            'api_key' => $this->apiKey,
        ]);

        $url = $this->baseUrl . '/api/properties?' . $query;

        if (!function_exists('curl_init')) {
            throw new PropertyApiException('PHP cURL extension is required for API sync.');
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new PropertyApiException('Failed to initialize cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new PropertyApiException('API request failed: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new PropertyApiException("API returned HTTP {$httpCode}.");
        }

        /** @var mixed $decoded */
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new PropertyApiException('API response is not valid JSON.');
        }

        if (isset($decoded['status']) && $decoded['status'] === 'error') {
            $message = is_string($decoded['error'] ?? null) ? $decoded['error'] : 'Unknown API error';

            throw new PropertyApiException($message);
        }

        if (!isset($decoded['data'], $decoded['current_page'], $decoded['last_page']) || !is_array($decoded['data'])) {
            throw new PropertyApiException('API response missing required pagination fields.');
        }

        return [
            'current_page' => (int) $decoded['current_page'],
            'last_page' => (int) $decoded['last_page'],
            'per_page' => (int) ($decoded['per_page'] ?? $this->pageSize),
            'data' => $decoded['data'],
        ];
    }
}
