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

    protected function prepareForValidation(): void
    {
        // Handle direct MMYY input (from Structured Tag mode)
        if ($this->has('arrival_mmyy_text') && $this->input('tracking_mode') === TrackingModeEnum::STRUCTURED_TAG->value) {
            $this->merge([
                'arrival_mmyy' => $this->input('arrival_mmyy_text'),
            ]);
        }
        // Handle split input (from other modes)
        elseif ($this->has(['arrival_month', 'arrival_year'])) {
            $this->merge([
                'arrival_mmyy' => $this->input('arrival_month') . substr($this->input('arrival_year'), -2),
            ]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'tracking_mode' => ['required', Rule::enum(TrackingModeEnum::class)],
            'item_id' => ['required_without:new_item_name', 'nullable', 'exists:items,id'],
            'new_item_name' => ['required_without:item_id', 'nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'batch_id' => ['required'], // 'new' or valid ID
            'condition' => ['required', Rule::enum(ConditionEnum::class)],
        ];
        
        // Validate batch_id exists if it's not 'new'
        if ($this->input('batch_id') !== 'new') {
            $rules['batch_id'][] = 'exists:batches,id';
        }

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
            $rules['asset_type_code'] = ['required', 'string', 'min:1', 'max:5'];
            $rules['university_asset_code_prefix'] = ['required', 'string', 'max:100'];
        } elseif ($mode === TrackingModeEnum::SEAT_NUMBER->value) {
            $rules['quantity'] = ['required', 'integer', 'min:1', 'max:999'];
            $rules['start_seat'] = ['nullable', 'integer', 'min:1'];
            $rules['university_asset_code_prefix'] = ['required', 'string', 'max:100'];
        } elseif ($mode === TrackingModeEnum::AGGREGATE->value) {
            $rules['quantity'] = ['required', 'integer', 'min:1'];
            $rules['university_asset_code_prefix'] = ['required', 'string', 'max:100'];
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
            'asset_type_code.required' => 'Kode tipe aset wajib dipilih untuk mode Structured Tag.',
            'university_asset_code_prefix.required' => 'Kode aset universitas wajib diisi.',
        ];
    }
}
