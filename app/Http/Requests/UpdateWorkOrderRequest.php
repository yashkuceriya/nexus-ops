<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WorkOrder;
use App\Rules\BelongsToCurrentTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWorkOrderRequest extends FormRequest
{
    /**
     * Authorize via WorkOrderPolicy::update.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $workOrder = $this->route('id')
            ? WorkOrder::where('tenant_id', $user?->tenant_id)->find($this->route('id'))
            : null;

        return $user !== null && $workOrder !== null && $user->can('update', $workOrder);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'critical', 'emergency'])],
            'type' => ['sometimes', 'string', Rule::in(['corrective', 'preventive', 'inspection', 'emergency'])],
            'asset_id' => ['nullable', 'integer', new BelongsToCurrentTenant('assets')],
            'location_id' => ['nullable', 'integer', new BelongsToCurrentTenant('locations')],
            'assigned_to' => ['nullable', 'integer', new BelongsToCurrentTenant('users')],
            'vendor_id' => ['nullable', 'integer', new BelongsToCurrentTenant('vendors')],
            'sla_hours' => ['nullable', 'integer', 'min:1'],
            'sla_deadline' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'priority.in' => 'Priority must be one of: low, medium, high, critical, emergency.',
            'type.in' => 'Type must be one of: corrective, preventive, inspection, emergency.',
        ];
    }
}
