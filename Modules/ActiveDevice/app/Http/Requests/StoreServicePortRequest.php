<?php

namespace Modules\ActiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicePortRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'portable_id' => ['required', 'integer'],
            'portable_type' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
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
