<?php

namespace Modules\PassiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTowerRequest extends FormRequest
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
                \Illuminate\Validation\Rule::unique('pd_towers')
                    ->where(fn ($query) => $query->where('company_id', auth()->user()->company_id))
                    ->ignore($this->route('tower')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'height' => ['required', 'numeric', 'min:0'],
            'type' => ['required', \Illuminate\Validation\Rule::in(['SST', 'Monopole', 'Guyed'])],
            'ownership' => ['required', \Illuminate\Validation\Rule::in(['Sendiri', 'Sewa'])],
            'antenna_capacity' => ['nullable', 'string', 'max:255'],
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
