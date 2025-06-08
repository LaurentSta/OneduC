
@props([
    'name',
    'label' => '',
    'checked' => false
])

<label class="custom-checkbox">
    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" {{ old($name, $checked) ? 'checked' : '' }}>
    <span></span>
    <span>{{ $label }}</span>
</label>
