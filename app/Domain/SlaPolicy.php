<?php

declare(strict_types=1);

namespace App\Domain;

use Carbon\Carbon;
use Carbon\CarbonInterval;

final class SlaPolicy
{
    public static function deadlineFor(Priority $priority, ?Carbon $from = null): Carbon
    {
        return $priority->slaDeadlineFrom($from ?? Carbon::now());
    }

    public static function isBreached(Priority $priority, Carbon $deadline, ?Carbon $completedAt = null): bool
    {
        $reference = $completedAt ?? Carbon::now();

        return $reference->isAfter($deadline);
    }

    public static function remainingTime(Carbon $deadline): ?CarbonInterval
    {
        if (Carbon::now()->isAfter($deadline)) {
            return null;
        }

        return Carbon::now()->diffAsCarbonInterval($deadline);
    }

    public static function percentElapsed(Carbon $created, Carbon $deadline): float
    {
        $totalSeconds = $created->diffInSeconds($deadline);

        if ($totalSeconds <= 0) {
            return 100.0;
        }

        $elapsedSeconds = $created->diffInSeconds(Carbon::now());

        return min(100.0, round(($elapsedSeconds / $totalSeconds) * 100, 2));
    }
}
