@props([
    'name',
    'label' => '',
    'previewId' => null,
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
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control file w-full']) }}
        accept="image/*"
        onchange="previewImage(event, '{{ $previewId }}')"
    />

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

@once
<script>
    function previewImage(event, previewId) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById(previewId);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endonce
