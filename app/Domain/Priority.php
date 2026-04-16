<?php

declare(strict_types=1);

namespace App\Domain;

use Carbon\Carbon;

enum Priority: string
{
    case Emergency = 'emergency';
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function slaHours(): int
    {
        return match ($this) {
            self::Emergency => 2,
            self::Critical  => 4,
            self::High      => 8,
            self::Medium    => 24,
            self::Low       => 48,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Emergency => 'Emergency',
            self::Critical  => 'Critical',
            self::High      => 'High',
            self::Medium    => 'Medium',
            self::Low       => 'Low',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Emergency => 'red',
            self::Critical  => 'orange',
            self::High      => 'amber',
            self::Medium    => 'yellow',
            self::Low       => 'green',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Emergency => 'bg-red-100 text-red-800',
            self::Critical  => 'bg-orange-100 text-orange-800',
            self::High      => 'bg-amber-100 text-amber-800',
            self::Medium    => 'bg-yellow-100 text-yellow-800',
            self::Low       => 'bg-green-100 text-green-800',
        };
    }

    public function slaDeadlineFrom(Carbon $start): Carbon
    {
        return $start->copy()->addHours($this->slaHours());
    }
}
