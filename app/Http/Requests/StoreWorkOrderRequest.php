<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WorkOrder;
use App\Rules\BelongsToCurrentTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Delegates to WorkOrderPolicy::create so authorization stays consistent
     * between API, Livewire, and policy-driven code.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', WorkOrder::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * All foreign key references are scoped to the current tenant to prevent
     * cross-tenant ID injection.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new BelongsToCurrentTenant('projects')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'critical', 'emergency'])],
            'type' => ['required', 'string', Rule::in(['corrective', 'preventive', 'inspection', 'emergency'])],
            'asset_id' => ['nullable', 'integer', new BelongsToCurrentTenant('assets')],
            'location_id' => ['nullable', 'integer', new BelongsToCurrentTenant('locations')],
            'issue_id' => ['nullable', 'integer', new BelongsToCurrentTenant('issues')],
            'assigned_to' => ['nullable', 'integer', new BelongsToCurrentTenant('users')],
            'vendor_id' => ['nullable', 'integer', new BelongsToCurrentTenant('vendors')],
            'sla_hours' => ['nullable', 'integer', 'min:1'],
            'sla_deadline' => ['nullable', 'date', 'after:now'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', Rule::in(['manual', 'sensor', 'inspection', 'sync', 'external'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'priority.in' => 'Priority must be one of: low, medium, high, critical, emergency.',
            'type.in' => 'Type must be one of: corrective, preventive, inspection, emergency.',
            'sla_deadline.after' => 'SLA deadline must be a future date.',
        ];
    }
}
