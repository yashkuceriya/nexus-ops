<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a full project sync from the external system completes.
 */
final class ProjectSynced
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $tenantId,
        public readonly int $projectId,
        public readonly int $issuesImported,
        public readonly int $assetsImported,
    ) {}
}
