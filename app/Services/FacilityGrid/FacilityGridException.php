<?php

declare(strict_types=1);

namespace App\Services\FacilityGrid;

use RuntimeException;
use Throwable;

/**
 * Typed exception for FacilityGrid API errors.
 *
 * Carries RFC 9457 Problem Details fields so callers can build
 * consistent error responses without inspecting raw HTTP data.
 */
final class FacilityGridException extends RuntimeException
{
    /**
     * @param string      $errorType  A URI or short token identifying the error category
     *                                (e.g. "rate_limited", "authentication_error", "not_found").
     * @param int         $status     The originating HTTP status code (0 when not HTTP-related).
     * @param string      $detail     A human-readable explanation of the specific occurrence.
     * @param string|null $instance   Optional URI reference identifying the specific occurrence.
     */
    public function __construct(
        public readonly string  $errorType,
        public readonly int     $status,
        public readonly string  $detail,
        public readonly ?string $instance = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($detail, $status, $previous);
    }

    /* ------------------------------------------------------------------
     | Named constructors for common error scenarios
     | ----------------------------------------------------------------*/

    public static function authentication(string $detail, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'authentication_error',
            status: 401,
            detail: $detail,
            previous: $previous,
        );
    }

    public static function forbidden(string $detail, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'forbidden',
            status: 403,
            detail: $detail,
            previous: $previous,
        );
    }

    public static function notFound(string $resource, string|int $id, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'not_found',
            status: 404,
            detail: "The requested {$resource} ({$id}) was not found on FacilityGrid.",
            instance: "{$resource}/{$id}",
            previous: $previous,
        );
    }

    public static function rateLimited(int $retryAfter, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'rate_limited',
            status: 429,
            detail: "FacilityGrid API rate limit exceeded. Retry after {$retryAfter}s.",
            previous: $previous,
        );
    }

    public static function serverError(int $status, string $detail, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'server_error',
            status: $status,
            detail: $detail,
            previous: $previous,
        );
    }

    public static function timeout(string $detail, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'timeout',
            status: 504,
            detail: $detail,
            previous: $previous,
        );
    }

    public static function connectionFailed(string $detail, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'connection_failed',
            status: 0,
            detail: $detail,
            previous: $previous,
        );
    }

    public static function retriesExhausted(int $attempts, ?Throwable $previous = null): self
    {
        return new self(
            errorType: 'retries_exhausted',
            status: 0,
            detail: "All {$attempts} retry attempts against FacilityGrid API have been exhausted.",
            previous: $previous,
        );
    }

    /* ------------------------------------------------------------------
     | Serialisation helpers
     | ----------------------------------------------------------------*/

    /**
     * Return an RFC 9457 Problem Details representation.
     *
     * @return array{type: string, status: int, title: string, detail: string, instance: string|null}
     */
    public function toProblemDetails(): array
    {
        return [
            'type'     => $this->errorType,
            'status'   => $this->status,
            'title'    => $this->humanTitle(),
            'detail'   => $this->detail,
            'instance' => $this->instance,
        ];
    }

    private function humanTitle(): string
    {
        return match ($this->errorType) {
            'authentication_error' => 'Authentication Error',
            'forbidden'            => 'Forbidden',
            'not_found'            => 'Not Found',
            'rate_limited'         => 'Rate Limited',
            'server_error'         => 'Server Error',
            'timeout'              => 'Gateway Timeout',
            'connection_failed'    => 'Connection Failed',
            'retries_exhausted'    => 'Retries Exhausted',
            default                => 'FacilityGrid API Error',
        };
    }
}
