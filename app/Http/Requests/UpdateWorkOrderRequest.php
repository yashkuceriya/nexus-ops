<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'type' => ['sometimes', 'string', Rule::in(['corrective', 'preventive', 'inspection', 'emergency'])],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'sla_hours' => ['nullable', 'integer', 'min:1'],
            'sla_deadline' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
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
        ];
    }
}
