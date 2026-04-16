<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SensorIngestRequest extends FormRequest
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
            'readings' => ['required', 'array', 'min:1', 'max:1000'],
            'readings.*.sensor_source_id' => ['required', 'integer'],
            'readings.*.value' => ['required', 'numeric'],
            'readings.*.recorded_at' => ['nullable', 'date'],
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
            'readings.required' => 'At least one sensor reading is required.',
            'readings.max' => 'A maximum of 1000 readings can be ingested per request.',
            'readings.*.sensor_source_id.required' => 'Each reading must include a sensor_source_id.',
            'readings.*.value.required' => 'Each reading must include a numeric value.',
        ];
    }
}
