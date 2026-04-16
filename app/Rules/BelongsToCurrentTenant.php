<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * Validates that a foreign key ID references a row belonging to the currently
 * authenticated user's tenant. Prevents cross-tenant ID injection attacks
 * where a user could reference another tenant's project, asset, user, etc.
 */
final class BelongsToCurrentTenant implements ValidationRule
{
    /**
     * @param  string  $table  Database table to check (e.g. 'projects')
     * @param  string  $column  Column to match against (default: 'id')
     * @param  string  $tenantColumn  Tenant column name (default: 'tenant_id')
     */
    public function __construct(
        private readonly string $table,
        private readonly string $column = 'id',
        private readonly string $tenantColumn = 'tenant_id',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $user = auth()->user();

        if (! $user) {
            $fail("The {$attribute} could not be validated (no authenticated user).");

            return;
        }

        $exists = DB::table($this->table)
            ->where($this->column, $value)
            ->where($this->tenantColumn, $user->tenant_id)
            ->exists();

        if (! $exists) {
            $fail("The selected {$attribute} is invalid or does not belong to your organization.");
        }
    }
}
