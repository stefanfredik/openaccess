<?php

namespace Modules\PassiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJointBoxRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'infrastructure_area_id' => ['required', 'exists:infrastructure_areas,id'],
            'code' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('pd_joint_boxes')->where(fn ($query) => $query->where('company_id', auth()->user()->company_id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'core_capacity' => ['required', 'integer', 'min:0'],
            'input_count' => ['required', 'integer', 'min:0'],
            'output_count' => ['required', 'integer', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'installed_at' => ['nullable', 'date'],
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
