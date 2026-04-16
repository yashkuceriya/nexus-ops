<?php

declare(strict_types=1);

namespace App\Services\FacilityGrid;

use App\Models\Tenant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP client for the FacilityGrid external API.
 *
 * Features:
 *  - Tenant-scoped authentication (Bearer / API-Key).
 *  - Exponential back-off with jitter for transient errors (429 / 5xx).
 *  - Cursor-based pagination helper.
 *  - Typed exceptions mapped to RFC 9457 Problem Details.
 */
final class FacilityGridClient
{
    private const int MAX_RETRIES = 5;

    private const int TOTAL_BUDGET_SECONDS = 30;

    private const int BASE_DELAY_MS = 200;

    private const int CONNECT_TIMEOUT = 10;

    private const int REQUEST_TIMEOUT = 30;

    private const array RETRYABLE_STATUSES = [429, 502, 503, 504];

    private Client $http;

    public function __construct(
        private readonly Tenant $tenant,
        ?Client $client = null,
    ) {
        $this->http = $client ?? new Client([
            'base_uri' => rtrim($this->tenant->facilitygrid_api_url, '/').'/',
            RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT,
            RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::HEADERS => $this->buildAuthHeaders(),
        ]);
    }

    /* ------------------------------------------------------------------
     | Public resource methods
     | ----------------------------------------------------------------*/

    /**
     * @return array{data: list<array>, meta: array}
     */
    public function getProjects(array $params = []): array
    {
        return $this->paginatedGet('projects', $params);
    }

    public function getProject(string|int $id): array
    {
        return $this->requestWithRetry('GET', "projects/{$id}");
    }

    /**
     * @return array{data: list<array>, meta: array}
     */
    public function getIssues(string|int $projectId, array $params = []): array
    {
        return $this->paginatedGet("projects/{$projectId}/issues", $params);
    }

    /**
     * @return array{data: list<array>, meta: array}
     */
    public function getAssets(string|int $projectId, array $params = []): array
    {
        return $this->paginatedGet("projects/{$projectId}/assets", $params);
    }

    /**
     * @return array{data: list<array>, meta: array}
     */
    public function getTests(string|int $projectId, array $params = []): array
    {
        return $this->paginatedGet("projects/{$projectId}/tests", $params);
    }

    /**
     * @return array{data: list<array>, meta: array}
     */
    public function getDocuments(string|int $projectId, array $params = []): array
    {
        return $this->paginatedGet("projects/{$projectId}/documents", $params);
    }

    /* ------------------------------------------------------------------
     | Pagination
     | ----------------------------------------------------------------*/

    /**
     * Exhaustively follow cursor-based pages and merge all results.
     *
     * The API is expected to return:
     *   { "data": [...], "meta": { "next_cursor": "..." } }
     *
     * When `next_cursor` is null/absent, pagination stops.
     *
     * @return array{data: list<array>, meta: array}
     */
    private function paginatedGet(string $uri, array $params = []): array
    {
        $allData = [];
        $cursor = $params['cursor'] ?? null;
        $meta = [];

        do {
            $query = $params;
            if ($cursor !== null) {
                $query['cursor'] = $cursor;
            }

            $page = $this->requestWithRetry('GET', $uri, ['query' => $query]);
            $allData = array_merge($allData, $page['data'] ?? []);
            $meta = $page['meta'] ?? [];
            $cursor = $meta['next_cursor'] ?? null;
        } while ($cursor !== null);

        return ['data' => $allData, 'meta' => $meta];
    }

    /* ------------------------------------------------------------------
     | HTTP transport with exponential back-off + jitter
     | ----------------------------------------------------------------*/

    /**
     * Send a request, retrying on transient failures.
     *
     * @throws FacilityGridException
     */
    private function requestWithRetry(string $method, string $uri, array $options = []): array
    {
        $attempt = 0;
        $deadline = hrtime(true) + (self::TOTAL_BUDGET_SECONDS * 1_000_000_000);

        while (true) {
            $attempt++;

            try {
                $response = $this->http->request($method, $uri, $options);
            } catch (ConnectException $e) {
                if ($attempt >= self::MAX_RETRIES || hrtime(true) >= $deadline) {
                    throw FacilityGridException::connectionFailed(
                        "Failed to connect to FacilityGrid API: {$e->getMessage()}",
                        $e,
                    );
                }

                $this->sleep($this->calculateDelay($attempt));

                continue;
            } catch (RequestException $e) {
                // Guzzle threw without a response (network-level issue).
                if ($e->getResponse() === null) {
                    if ($attempt >= self::MAX_RETRIES || hrtime(true) >= $deadline) {
                        throw FacilityGridException::connectionFailed(
                            "FacilityGrid API request failed: {$e->getMessage()}",
                            $e,
                        );
                    }

                    $this->sleep($this->calculateDelay($attempt));

                    continue;
                }

                $response = $e->getResponse();
            }

            $status = $response->getStatusCode();

            // Happy path.
            if ($status >= 200 && $status < 300) {
                return $this->decodeResponse($response);
            }

            // Non-retryable client errors.
            if (! in_array($status, self::RETRYABLE_STATUSES, true)) {
                $this->throwForStatus($status, $response, $uri);
            }

            // Retryable - but budget/attempt check first.
            if ($attempt >= self::MAX_RETRIES || hrtime(true) >= $deadline) {
                throw FacilityGridException::retriesExhausted(
                    $attempt,
                    new \RuntimeException("Last status: {$status}"),
                );
            }

            $delay = ($status === 429)
                ? $this->retryAfterDelay($response, $attempt)
                : $this->calculateDelay($attempt);

            Log::warning('FacilityGrid: retrying after transient error', [
                'tenant_id' => $this->tenant->id,
                'uri' => $uri,
                'status' => $status,
                'attempt' => $attempt,
                'delay_ms' => $delay,
            ]);

            $this->sleep($delay);
        }
    }

    /**
     * Exponential back-off with full jitter: delay = random(0, base * 2^attempt).
     *
     * @return int Delay in milliseconds.
     */
    private function calculateDelay(int $attempt): int
    {
        $ceiling = min(self::BASE_DELAY_MS * (2 ** $attempt), self::TOTAL_BUDGET_SECONDS * 1000);

        return random_int(0, (int) $ceiling);
    }

    /**
     * Honour `Retry-After` header when present; fall back to exponential back-off.
     *
     * @return int Delay in milliseconds.
     */
    private function retryAfterDelay(ResponseInterface $response, int $attempt): int
    {
        $header = $response->getHeaderLine('Retry-After');

        if ($header !== '') {
            $seconds = is_numeric($header)
                ? (int) $header
                : max(0, strtotime($header) - time());

            // Clamp to remaining budget.
            return min($seconds * 1000, self::TOTAL_BUDGET_SECONDS * 1000);
        }

        return $this->calculateDelay($attempt);
    }

    /* ------------------------------------------------------------------
     | Response handling
     | ----------------------------------------------------------------*/

    private function decodeResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new FacilityGridException(
                errorType: 'invalid_response',
                status: 0,
                detail: 'FacilityGrid API returned a non-object/non-array JSON payload.',
            );
        }

        return $decoded;
    }

    /**
     * @throws FacilityGridException
     */
    private function throwForStatus(int $status, ResponseInterface $response, string $uri): never
    {
        $body = (string) $response->getBody();
        $detail = $this->extractDetail($body) ?? "HTTP {$status} on {$uri}";

        throw match (true) {
            $status === 401 => FacilityGridException::authentication($detail),
            $status === 403 => FacilityGridException::forbidden($detail),
            $status === 404 => FacilityGridException::notFound('resource', $uri),
            $status === 408 => FacilityGridException::timeout($detail),
            $status >= 500 => FacilityGridException::serverError($status, $detail),
            default => new FacilityGridException(
                errorType: 'client_error',
                status: $status,
                detail: $detail,
            ),
        };
    }

    private function extractDetail(string $body): ?string
    {
        if ($body === '') {
            return null;
        }

        try {
            $json = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);

            // Support standard "detail", "message", or nested "error.message".
            return $json['detail']
                ?? $json['message']
                ?? $json['error']['message']
                ?? null;
        } catch (\JsonException) {
            return null;
        }
    }

    /* ------------------------------------------------------------------
     | Auth
     | ----------------------------------------------------------------*/

    /**
     * @return array<string, string>
     */
    private function buildAuthHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $token = $this->tenant->facilitygrid_api_token;
        $authType = $this->tenant->facilitygrid_auth_type ?? 'bearer';

        $headers = match ($authType) {
            'bearer' => array_merge($headers, ['Authorization' => "Bearer {$token}"]),
            'api_key' => array_merge($headers, ['X-API-Key' => $token]),
            default => array_merge($headers, ['Authorization' => "Bearer {$token}"]),
        };

        return $headers;
    }

    /* ------------------------------------------------------------------
     | Helpers (seam for testing)
     | ----------------------------------------------------------------*/

    /**
     * Sleep for the given number of milliseconds.
     * Extracted so tests can override without real delays.
     */
    protected function sleep(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }
}
