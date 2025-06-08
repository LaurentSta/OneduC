@props([
    'name',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false
])

<div class="space-y-1">
    @if ($label)
        <label for="{{ $name }}" class="label block mb-2 text-sm font-medium text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control block w-full text-sm rounded-md shadow-sm']) }}
    />

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
