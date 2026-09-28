@props(['name', 'label' => null, 'value' => null, 'required' => false, 'hint' => null, 'rows' => 3])

<div class="form-group">
    @if ($label)
        <label class="form-label {{ $required ? 'form-label--required' : '' }}" for="{{ $name }}">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-textarea '.($errors->has($name) ? 'form-input--error' : '')]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)<span class="form-error">{{ $message }}</span>@enderror
    @if ($hint)<span class="form-hint">{{ $hint }}</span>@endif
</div>
