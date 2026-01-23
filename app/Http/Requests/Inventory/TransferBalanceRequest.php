<?php

namespace App\Http\Requests\Inventory;

use App\Enums\ConditionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
            'from_condition' => ['required', Rule::enum(ConditionEnum::class)],
            'to_condition' => ['required', Rule::enum(ConditionEnum::class), 'different:from_condition'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_condition.different' => 'Kondisi tujuan harus berbeda dari kondisi asal.',
            'quantity.min' => 'Jumlah transfer minimal 1.',
        ];
    }
}
