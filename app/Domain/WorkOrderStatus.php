<?php

declare(strict_types=1);

namespace App\Domain;

enum WorkOrderStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Verified = 'verified';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending    => [self::Assigned, self::InProgress, self::Cancelled],
            self::Assigned   => [self::InProgress, self::OnHold, self::Cancelled],
            self::InProgress => [self::OnHold, self::Completed, self::Cancelled],
            self::OnHold     => [self::InProgress, self::Cancelled],
            self::Completed  => [self::Verified, self::InProgress],
            self::Verified   => [],
            self::Cancelled  => [self::Pending],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Assigned   => 'Assigned',
            self::InProgress => 'In Progress',
            self::OnHold     => 'On Hold',
            self::Completed  => 'Completed',
            self::Verified   => 'Verified',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'blue',
            self::Assigned   => 'indigo',
            self::InProgress => 'amber',
            self::OnHold     => 'orange',
            self::Completed  => 'emerald',
            self::Verified   => 'green',
            self::Cancelled  => 'red',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending    => 'bg-blue-100 text-blue-800',
            self::Assigned   => 'bg-indigo-100 text-indigo-800',
            self::InProgress => 'bg-amber-100 text-amber-800',
            self::OnHold     => 'bg-orange-100 text-orange-800',
            self::Completed  => 'bg-emerald-100 text-emerald-800',
            self::Verified   => 'bg-green-100 text-green-800',
            self::Cancelled  => 'bg-red-100 text-red-800',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Label to display when transitioning *to* this status (action label).
     */
    public function transitionLabel(): string
    {
        return match ($this) {
            self::Pending    => 'Reopen',
            self::Assigned   => 'Assign',
            self::InProgress => 'Start Work',
            self::OnHold     => 'Put On Hold',
            self::Completed  => 'Complete',
            self::Verified   => 'Verify',
            self::Cancelled  => 'Cancel',
        };
    }
}
