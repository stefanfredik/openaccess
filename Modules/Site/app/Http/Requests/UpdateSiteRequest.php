<?php

namespace Modules\Site\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:infrastructure_areas,id',
            'code' => 'nullable|string|max:50',
            'type' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|string|in:Active,Inactive,Planned',
            'photos.*' => 'nullable|image|max:10240', // 10MB max
            'photos' => 'nullable|array',
            'electrical_capacity' => 'nullable|integer',
            'power_source' => 'nullable|string|in:PLN,Solar,Genset,Hybrid',
            'has_backup_power' => 'nullable|boolean',
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
