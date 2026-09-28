@props(['name', 'label', 'checked' => false, 'hint' => null])

<div class="form-group">
    <label class="form-check">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))>
        <span>{{ $label }}</span>
    </label>
    @error($name)<span class="form-error">{{ $message }}</span>@enderror
    @if ($hint)<span class="form-hint">{{ $hint }}</span>@endif
</div>
