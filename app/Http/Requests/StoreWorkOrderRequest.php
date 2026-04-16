<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Any authenticated user with a role that can create work orders
        return $user !== null && in_array($user->role, ['owner', 'admin', 'manager', 'technician'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'type' => ['required', 'string', Rule::in(['corrective', 'preventive', 'inspection', 'emergency'])],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'issue_id' => ['nullable', 'integer', 'exists:issues,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'sla_hours' => ['nullable', 'integer', 'min:1'],
            'sla_deadline' => ['nullable', 'date', 'after:now'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', Rule::in(['manual', 'sensor', 'inspection', 'sync'])],
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
            'priority.in' => 'Priority must be one of: low, medium, high, critical.',
            'type.in' => 'Type must be one of: corrective, preventive, inspection, emergency.',
            'sla_deadline.after' => 'SLA deadline must be a future date.',
        ];
    }
}
