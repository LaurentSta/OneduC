@props([
    'label' => '',
    'name',
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'required' => false
])

<div class="card p-5">
    <label for="{{ $name }}" class="label block mb-2 text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
    <div class="custom-select">
        <select name="{{ $name }}" id="{{ $name }}"
        class="form-control text-gray-700 bg-white"
        {{ $required ? 'required' : '' }}>
        @php
        $selected = old($name) ?? ($attributes['value'] ?? '');
    @endphp

    <option value="" disabled {{ $selected == '' ? 'selected' : '' }}>
        {{ $placeholder ?? 'Sélectionner une option' }}
    </option>
    @foreach ($options as $option)
        @php
            $value = is_array($option) ? $option[$optionValue] : $option->{$optionValue};
            $label = is_array($option) ? $option[$optionLabel] : $option->{$optionLabel};
        @endphp
        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach


        </select>
        <div class="custom-select-icon la la-caret-down"></div>
    </div>
    @error($name)
        <small class="block mt-2 text-red-600 text-xs">{{ $message }}</small>
    @enderror
</div>
