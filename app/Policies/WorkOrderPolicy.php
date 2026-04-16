<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    /**
     * Any authenticated user in the same tenant can view work orders.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Users can only view work orders belonging to their tenant.
     */
    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->tenant_id === $workOrder->tenant_id;
    }

    /**
     * Admins, managers, and technicians can create work orders.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin', 'manager', 'technician']);
    }

    /**
     * Admins, managers, or the assigned technician can update a work order.
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        if ($user->tenant_id !== $workOrder->tenant_id) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $user->id === $workOrder->assigned_to;
    }

    /**
     * Only admins can delete work orders.
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        if ($user->tenant_id !== $workOrder->tenant_id) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Admins, managers, or the assigned technician can transition status.
     */
    public function transitionStatus(User $user, WorkOrder $workOrder): bool
    {
        if ($user->tenant_id !== $workOrder->tenant_id) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $user->id === $workOrder->assigned_to;
    }

    /**
     * Only admins or managers can assign work orders.
     */
    public function assign(User $user, WorkOrder $workOrder): bool
    {
        if ($user->tenant_id !== $workOrder->tenant_id) {
            return false;
        }

        return $user->isManager();
    }
}
