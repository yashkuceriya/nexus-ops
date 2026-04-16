<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    /**
     * Any authenticated user in the same tenant can view assets.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Users can only view assets belonging to their tenant.
     */
    public function view(User $user, Asset $asset): bool
    {
        return $user->tenant_id === $asset->tenant_id;
    }

    /**
     * Admins or managers can create assets.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Admins or managers can update assets.
     */
    public function update(User $user, Asset $asset): bool
    {
        if ($user->tenant_id !== $asset->tenant_id) {
            return false;
        }

        return $user->isManager();
    }

    /**
     * Only admins can delete assets.
     */
    public function delete(User $user, Asset $asset): bool
    {
        if ($user->tenant_id !== $asset->tenant_id) {
            return false;
        }

        return $user->isAdmin();
    }
}
