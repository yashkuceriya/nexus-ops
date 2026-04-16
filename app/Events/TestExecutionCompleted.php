<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\TestExecution;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TestExecutionCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly TestExecution $execution,
        public readonly User $completedBy,
    ) {}
}
