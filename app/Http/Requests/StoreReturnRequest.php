<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreReturnRequest extends StockMovementRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::in(['retur_masuk', 'retur_keluar'])],
            'product_id' => ['required', Rule::exists('products', 'id')->where('is_active', true)],
            'location_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'qty' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'direction' => 'jenis retur',
        ];
    }
}
