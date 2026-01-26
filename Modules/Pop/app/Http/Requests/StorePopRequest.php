<?php

namespace Modules\Pop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePopRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'area_id' => 'required|exists:infrastructure_areas,id',
            'code' => 'required|string|unique:pops,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'province' => 'nullable|string',
            'regency' => 'nullable|string',
            'district' => 'nullable|string',
            'village' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'electrical_capacity' => 'nullable|integer',
            'power_source' => 'required|in:PLN,Solar,Genset,Hybrid',
            'has_backup_power' => 'required|boolean',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive,Planned',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:10240',
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
