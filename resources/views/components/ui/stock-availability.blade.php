@props(['matrix' => [], 'locations' => []])

@php
    $selected = (int) old('product_id', 0);
    $perLocation = $selected === 0 ? [] : ($matrix[$selected] ?? []);
@endphp

<div
    class="availability{{ $selected === 0 ? ' availability--empty' : '' }}"
    data-stock-availability
    data-matrix="{{ json_encode($matrix) }}"
>
    <div class="availability__head">
        <span>Stok tersedia per lokasi</span>
        <strong class="mono" data-stock-availability-total>Total {{ array_sum($perLocation) }}</strong>
    </div>

    <ul class="availability__list" data-stock-availability-list>
        @foreach ($locations as $locationId => $label)
            @php($qty = $perLocation[$locationId] ?? 0)
            <li class="availability__item{{ $qty > 0 ? '' : ' availability__item--empty' }}" data-location="{{ $locationId }}">
                <span>{{ $label }}</span>
                <strong class="mono" data-stock-availability-qty>{{ $qty }}</strong>
            </li>
        @endforeach
    </ul>

    <p class="availability__hint">Pilih produk dulu untuk melihat stok di tiap lokasi.</p>
</div>
