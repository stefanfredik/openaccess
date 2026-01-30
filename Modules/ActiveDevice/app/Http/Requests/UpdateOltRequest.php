<?php

namespace Modules\ActiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOltRequest extends FormRequest
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
                Rule::unique('ad_olts')->where(fn ($query) => $query->where('company_id', $this->user()->company_id))
                    ->ignore($this->olt),
            ],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'mac_address' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'service_status' => ['nullable', 'array'],
            'purchase_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],
            'pon_type' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:Active,Inactive,Planned'],
            'installed_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'device_image' => ['nullable', 'image', 'max:2048'],
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
