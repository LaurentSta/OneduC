@props([
    'name',
    'rows' => 4,
    'placeholder' => '',
    'value' => '',
    'label' => null,
])

<div class="card p-5">
    <label for="{{ $name }}" class="label block mb-2 text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows ?? 4 }}"
        placeholder="{{ $placeholder ?? '' }}"
        class="form-control mt-1 block w-full"
    >{{ old($name, $value ?? '') }}</textarea>
    @error($name)
        <small class="block mt-2 text-red-600 text-xs">{{ $message }}</small>
    @enderror
</div>
