<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\ExternalSync\ExternalSyncClient;
use App\Services\ExternalSync\ExternalSyncException;
use App\Services\ExternalSync\ExternalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job that runs a full incremental sync from the external system for a single tenant.
 *
 * Overlap prevention ensures only one sync runs per tenant at a time.
 * The job retries up to 3 times with 60-second back-off on infrastructure failures.
 */
final class SyncExternalData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 300;

    public function __construct(
        public readonly Tenant $tenant,
    ) {
        $this->onQueue('external-sync');
    }

    /**
     * Prevent overlapping syncs for the same tenant.
     *
     * @return list<object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->tenant->id))
                ->releaseAfter(seconds: 300)
                ->expireAfter(seconds: 600),
        ];
    }

    public function handle(): void
    {
        Log::info('ExternalSync started.', ['tenant_id' => $this->tenant->id]);

        $client = new ExternalSyncClient($this->tenant);
        $service = new ExternalSyncService($client, $this->tenant);

        try {
            $service->syncAll();
        } catch (ExternalSyncException $e) {
            Log::error('ExternalSync failed.', [
                'tenant_id' => $this->tenant->id,
                'error_type' => $e->errorType,
                'detail' => $e->detail,
            ]);

            throw $e; // Let the queue worker handle retry / dead-lettering.
        }

        Log::info('ExternalSync completed.', ['tenant_id' => $this->tenant->id]);
    }

    /**
     * Determine if the job should be retried based on the exception.
     *
     * Authentication / forbidden errors should not be retried.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::critical('ExternalSync permanently failed.', [
            'tenant_id' => $this->tenant->id,
            'exception' => $exception?->getMessage(),
        ]);
    }

    /**
     * Determine whether the exception should cause the job to stop retrying.
     */
    public function shouldRetry(\Throwable $exception): bool
    {
        if ($exception instanceof ExternalSyncException) {
            // Do not retry auth or permission errors.
            return ! in_array($exception->errorType, ['authentication_error', 'forbidden'], true);
        }

        return true;
    }
}
