@props(['name', 'label' => null, 'options' => [], 'selected' => null, 'placeholder' => '— Pilih —', 'required' => false, 'hint' => null])

<div class="form-group">
    @if ($label)
        <label class="form-label {{ $required ? 'form-label--required' : '' }}" for="{{ $name }}">{{ $label }}</label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-select '.($errors->has($name) ? 'form-select--error' : '')]) }}
    >
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif

        @foreach ($options as $key => $option)
            <option value="{{ $key }}" @selected((string) old($name, $selected) === (string) $key)>{{ $option }}</option>
        @endforeach
    </select>

    @error($name)<span class="form-error">{{ $message }}</span>@enderror
    @if ($hint)<span class="form-hint">{{ $hint }}</span>@endif
</div>
