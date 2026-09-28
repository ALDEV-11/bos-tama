@props(['product'])

@php($status = $product->status())

<span class="badge badge--{{ $status->badge() }}">
    <i class="bi {{ $status->icon() }}"></i> {{ $status->label() }}
</span>
