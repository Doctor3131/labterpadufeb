<?php

namespace App\Http\Requests\Inventory;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'tracking_mode' => ['required', Rule::enum(TrackingModeEnum::class)],
            'item_id' => ['required_without:new_item_name', 'nullable', 'exists:items,id'],
            'new_item_name' => ['required_without:item_id', 'nullable', 'string', 'max:255'],
            'batch_id' => ['required_without:new_batch', 'nullable', 'exists:batches,id'],
            'condition' => ['required', Rule::enum(ConditionEnum::class)],
        ];

        // New batch fields
        if ($this->input('batch_id') === null || $this->input('batch_id') === 'new') {
            $rules['proc_source_code'] = ['required', 'string', 'max:10'];
            $rules['arrival_mmyy'] = ['required', 'string', 'size:4'];
            $rules['source_description'] = ['nullable', 'string', 'max:255'];
            $rules['unit_price'] = ['nullable', 'numeric', 'min:0'];
        }

        // Mode-specific rules
        $mode = $this->input('tracking_mode');

        if ($mode === TrackingModeEnum::STRUCTURED_TAG->value) {
            $rules['quantity'] = ['required', 'integer', 'min:1', 'max:999'];
            $rules['start_seq'] = ['nullable', 'integer', 'min:1'];
            $rules['subtype'] = ['nullable', 'string', 'max:50'];
            $rules['asset_type_code_id'] = ['required', 'exists:asset_type_codes,id'];
        } elseif ($mode === TrackingModeEnum::SEAT_NUMBER->value) {
            $rules['seat_numbers'] = ['required', 'string'];
        } elseif ($mode === TrackingModeEnum::AGGREGATE->value) {
            $rules['quantity'] = ['required', 'integer', 'min:1'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'item_id.required_without' => 'Pilih item atau masukkan nama item baru.',
            'new_item_name.required_without' => 'Pilih item atau masukkan nama item baru.',
            'batch_id.required_without' => 'Pilih batch atau buat batch baru.',
            'proc_source_code.required' => 'Kode sumber pengadaan wajib diisi untuk batch baru.',
            'arrival_mmyy.required' => 'Bulan/tahun datang wajib diisi (format: MMYY).',
            'arrival_mmyy.size' => 'Format bulan/tahun harus 4 digit (MMYY).',
            'quantity.required' => 'Jumlah wajib diisi.',
            'seat_numbers.required' => 'Nomor kursi wajib diisi.',
            'asset_type_code_id.required' => 'Kode tipe aset wajib dipilih untuk mode Structured Tag.',
        ];
    }
}
