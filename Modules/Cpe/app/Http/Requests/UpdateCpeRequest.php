<?php

namespace Modules\Cpe\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCpeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'infrastructure_area_id' => 'required|exists:infrastructure_areas,id',
            'active_device_id' => 'nullable|integer',
            'active_device_type' => 'nullable|string',
            'code' => 'required|string|max:255|unique:cpes,code,' . $this->route('cpe')->id,
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'type' => 'required|string|in:ONT,Router,Radio CPE,Modem',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Inactive,Damaged',
            'installed_at' => 'nullable|date',
            'photo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
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
