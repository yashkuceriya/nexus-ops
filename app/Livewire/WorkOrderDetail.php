<?php

namespace App\Livewire;

use App\Domain\WorkOrderStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\WorkOrder\WorkOrderService;
use Livewire\Component;

class WorkOrderDetail extends Component
{
    public WorkOrder $workOrder;

    public function mount(int $id): void
    {
        $this->workOrder = WorkOrder::with([
            'project:id,name',
            'asset:id,name,asset_tag,qr_code',
            'location:id,name,type',
            'issue:id,title,status,priority',
            'assignee:id,name,email',
            'creator:id,name,email',
            'vendor',
        ])
            ->findOrFail($id);
    }

    public function getAllowedTransitionsProperty(): array
    {
        $current = WorkOrderStatus::tryFrom($this->workOrder->status);

        if (! $current) {
            return [];
        }

        return collect($current->allowedTransitions())
            ->map(fn (WorkOrderStatus $target) => [
                'status' => $target->value,
                'label' => $target->transitionLabel(),
                'color' => $target->color(),
            ])
            ->all();
    }

    public function getAuditLogsProperty()
    {
        return AuditLog::where('auditable_type', (new WorkOrder)->getMorphClass())
            ->where('auditable_id', $this->workOrder->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function getAvailableUsersProperty()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getAvailableVendorsProperty()
    {
        return Vendor::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function transitionStatus(string $status): void
    {
        $service = app(WorkOrderService::class);

        try {
            $this->workOrder = $service->updateStatus($this->workOrder, $status);
            $this->workOrder->load([
                'project:id,name',
                'asset:id,name,asset_tag,qr_code',
                'location:id,name,type',
                'issue:id,title,status,priority',
                'assignee:id,name,email',
                'creator:id,name,email',
            ]);

            $statusLabel = str_replace('_', ' ', ucfirst($status));
            session()->flash('success', "Status updated to {$statusLabel}.");
            $this->dispatch('toast', type: 'success', message: "Work order status changed to {$statusLabel}.");
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function assignTo(int $userId): void
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($userId);
        $service = app(WorkOrderService::class);

        $this->workOrder = $service->assignWorkOrder($this->workOrder, $user);
        $this->workOrder->load([
            'project:id,name',
            'asset:id,name,asset_tag,qr_code',
            'location:id,name,type',
            'issue:id,title,status,priority',
            'assignee:id,name,email',
            'creator:id,name,email',
            'vendor',
        ]);

        session()->flash('success', 'Work order reassigned to '.$user->name.'.');
        $this->dispatch('toast', type: 'success', message: "Work order reassigned to {$user->name}.");
    }

    /**
     * Assign a vendor to the work order. Pass 0 (or empty string) to remove the
     * current vendor. Scoped to the current tenant to prevent cross-tenant
     * vendor assignment via a forged dropdown value.
     */
    public function assignVendor(string $vendorId): void
    {
        $id = (int) $vendorId;
        $tenantId = auth()->user()->tenant_id;

        if ($id === 0) {
            $this->workOrder->update(['vendor_id' => null]);
            $message = 'Vendor removed from work order.';
        } else {
            $vendor = Vendor::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->findOrFail($id);

            $this->workOrder->update(['vendor_id' => $vendor->id]);
            $message = "Vendor assigned: {$vendor->name}.";
        }

        AuditLog::record(
            action: 'work_order_vendor_assigned',
            model: $this->workOrder->refresh(),
            newValues: ['vendor_id' => $this->workOrder->vendor_id],
        );

        $this->workOrder->load([
            'project:id,name',
            'asset:id,name,asset_tag,qr_code',
            'location:id,name,type',
            'issue:id,title,status,priority',
            'assignee:id,name,email',
            'creator:id,name,email',
            'vendor',
        ]);

        session()->flash('success', $message);
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function render()
    {
        return view('livewire.work-order-detail')
            ->layout('layouts.app', ['title' => $this->workOrder->wo_number]);
    }
}
