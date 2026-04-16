<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Any authenticated user in the same tenant can view projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Users can only view projects belonging to their tenant.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->tenant_id === $project->tenant_id;
    }

    /**
     * Only admins can create projects.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins can update projects.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->tenant_id !== $project->tenant_id) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Only admins can delete projects.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($user->tenant_id !== $project->tenant_id) {
            return false;
        }

        return $user->isAdmin();
    }
}
