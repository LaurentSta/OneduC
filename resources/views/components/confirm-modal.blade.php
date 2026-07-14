@props([
    'name',
    'title' => 'Confirmer',
    'message' => 'Êtes-vous sûr ?',
    'action',
    'method' => 'DELETE',
    'confirmLabel' => 'Confirmer',
    'cancelLabel' => 'Annuler',
])

<x-modal
    :name="$name"
    maxWidth="sm"
    focusable
    :aria-labelledby="$name.'-titre'"
    :aria-describedby="$name.'-description'"
>
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <h2 id="{{ $name }}-titre" class="text-lg font-raleway font-medium text-bleuone">{{ $title }}</h2>
        <p id="{{ $name }}-description" class="mt-2 text-sm text-gray-600">{{ $message }}</p>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="btn-oneduc-outline !px-4 !py-2 !text-sm" x-on:click="$dispatch('close')">
                {{ $cancelLabel }}
            </button>
            <button type="submit" class="btn-oneduc-danger !px-4 !py-2 !text-sm">
                {{ $confirmLabel }}
            </button>
        </div>
    </form>
</x-modal>
