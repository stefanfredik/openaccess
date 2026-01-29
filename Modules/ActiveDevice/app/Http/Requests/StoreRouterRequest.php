<?php

namespace Modules\ActiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRouterRequest extends FormRequest
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
                Rule::unique('ad_routers')->where(fn ($query) => $query->where('company_id', auth()->user()->company_id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'mac_address' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'port_count' => ['required', 'integer', 'min:0'],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'installed_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'service_ports' => ['nullable', 'array'],
            'service_ports.*.name' => ['required', 'string', 'max:255'],
            'service_ports.*.port' => ['required', 'integer'],
            'service_ports.*.status' => ['required', 'string', 'in:Active,Inactive'],
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
