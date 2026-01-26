<?php

namespace Modules\ActiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceConnectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'source_id' => 'required|integer',
            'source_type' => 'required|string',
            'destination_id' => 'required|integer',
            'destination_type' => 'required|string',
            'connection_type' => 'required|string|in:Uplink,Downlink,Fiber',
            'source_port' => 'nullable|string|max:255',
            'destination_port' => 'nullable|string|max:255',
            'port_name' => 'nullable|string|max:255',
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
