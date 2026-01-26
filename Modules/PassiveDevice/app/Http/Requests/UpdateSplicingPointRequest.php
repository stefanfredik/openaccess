<?php

namespace Modules\PassiveDevice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSplicingPointRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'infrastructure_area_id' => 'required|exists:infrastructure_areas,id',
            'joint_box_id' => 'required|exists:pd_joint_boxes,id',
            'code' => 'required|string|max:255|unique:pd_splicing_points,code,' . $this->route('splicing_point'),
            'name' => 'required|string|max:255',
            'tray_number' => 'nullable|string|max:255',
            'splicing_type' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Spare,Damaged',
            'loss' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'spliced_at' => 'nullable|date',
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
