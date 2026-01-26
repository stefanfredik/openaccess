<?php

namespace Modules\Server\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'area_id' => 'required|exists:infrastructure_areas,id',
            'pop_id' => 'nullable|exists:pops,id',
            'code' => 'required|string|unique:servers,code,' . $this->route('server'),
            'name' => 'required|string|max:255',
            'function' => 'required|in:Server,OLT,Core Network,NOC',
            'building' => 'nullable|string',
            'floor' => 'nullable|string',
            'area_location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive,Planned',
            'photos' => 'nullable|array',
            'photos.Room.*' => 'nullable|image|max:10240',
            'photos.Rack.*' => 'nullable|image|max:10240',
            'photos.Installation.*' => 'nullable|image|max:10240',
            'photos.Cabling.*' => 'nullable|image|max:10240',
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
