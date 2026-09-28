@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'required' => false, 'hint' => null, 'step' => null])

<div class="form-group">
    @if ($label)
        <label class="form-label {{ $required ? 'form-label--required' : '' }}" for="{{ $name }}">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if ($step) step="{{ $step }}" @endif
        {{ $attributes->merge(['class' => 'form-input '.($errors->has($name) ? 'form-input--error' : '')]) }}
    >

    @error($name)<span class="form-error">{{ $message }}</span>@enderror
    @if ($hint)<span class="form-hint">{{ $hint }}</span>@endif
</div>
