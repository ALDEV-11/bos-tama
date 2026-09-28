<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends StockMovementRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists('products', 'id')->where('is_active', true)],
            'from_location_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'to_location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('is_active', true),
                Rule::notIn(array_filter([$this->input('from_location_id')])),
            ],
            'qty' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'from_location_id' => 'lokasi asal',
            'to_location_id' => 'lokasi tujuan',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'to_location_id.not_in' => 'Lokasi tujuan tidak boleh sama dengan lokasi asal.',
        ];
    }
}
