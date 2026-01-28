<?php

namespace Modules\ActiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSwitchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'infrastructure_area_id' => ['required', 'exists:infrastructure_areas,id'],
            'pop_id' => ['nullable', 'exists:pops,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ad_switches')->where(fn($query) => $query->where('company_id', auth()->user()->company_id))
                    ->ignore($this->switch),
            ],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'mac_address' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'port_count' => ['required', 'integer', 'min:0'],
            'switch_type' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'installed_at' => ['nullable', 'date'],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
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
