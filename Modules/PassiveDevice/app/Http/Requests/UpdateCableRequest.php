<?php

namespace Modules\PassiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCableRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'infrastructure_area_id' => ['sometimes', 'exists:infrastructure_areas,id'],
            'code' => [
                'sometimes',
                'string',
                \Illuminate\Validation\Rule::unique('pd_cables')->where(fn($query) => $query->where('company_id', auth()->user()->company_id))->ignore($this->route('cable')),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'length' => ['sometimes', 'numeric', 'min:0'],
            'core_count' => ['sometimes', 'integer', 'min:0'],
            'type' => ['sometimes', \Illuminate\Validation\Rule::in(['Single Mode', 'Multi Mode'])],
            'brand' => ['nullable', 'string', 'max:255'],
            'start_point' => ['nullable', 'string', 'max:255'],
            'end_point' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'installed_at' => ['nullable', 'date'],
            'path' => ['nullable', 'array'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
