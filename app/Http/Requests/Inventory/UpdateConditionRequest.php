<?php

namespace App\Http\Requests\Inventory;

use App\Enums\ConditionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_ids' => ['required', 'array', 'min:1'],
            'unit_ids.*' => ['required', 'integer', 'exists:asset_units,id'],
            'condition' => ['required', Rule::enum(ConditionEnum::class)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_ids.required' => 'Pilih minimal satu unit.',
            'unit_ids.min' => 'Pilih minimal satu unit.',
            'condition.required' => 'Kondisi baru wajib dipilih.',
        ];
    }
}
