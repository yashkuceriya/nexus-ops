<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched every time an issue is imported or updated from FacilityGrid.
 */
final class IssueImported
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int    $tenantId,
        public readonly int    $issueId,
        public readonly string $facilityGridIssueId,
        public readonly bool   $wasCreated,
    ) {}
}
