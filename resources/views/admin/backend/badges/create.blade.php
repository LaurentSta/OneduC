{{-- resources/views/admin/backend/badges/create.blade.php --}}
@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-4xl mx-auto mt-8 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-bleuone">Créer un badge</h2>
                <p class="text-sm text-gray-500 font-medium">Définis un badge et associe des compétences.</p>
            </div>
            <a href="{{ route('admin.badges.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">
                Retour
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
                <div class="mb-2">Certaines informations sont incorrectes :</div>
                <ul class="list-disc ml-5 space-y-1 font-normal">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.badges.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-extrabold text-bleuone uppercase ml-1">Libellé du badge</label>
                <input type="text" name="label" value="{{ old('label') }}"
                       class="mt-2 w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-orangeone text-lg font-bold text-gray-800 shadow-inner"
                       required>
            </div>

            <div>
                <label class="block text-sm font-extrabold text-bleuone uppercase ml-1">Image du badge</label>
                <input type="file"
                       name="image"
                       accept=".svg,image/svg+xml"
                       class="mt-2 w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orangeone text-sm text-gray-700">
                <p class="text-xs text-gray-500 mt-2">
                    Format obligatoire : SVG. Dimension recommandée : 200x200.
                </p>
                @error('image')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gray-50 rounded-2xl p-5">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                           class="h-5 w-5 rounded-lg text-orangeone border-gray-300 focus:ring-orangeone">
                    <span class="font-semibold text-gray-800">Badge actif</span>
                </label>
                <p class="text-xs text-gray-500 mt-2">Si inactif, il n’est pas proposé dans les associations.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Compétences associées (optionnel)</label>

                <select name="competency_ids[]"
                        class="mt-2 w-full rounded-xl border-gray-300"
                        multiple
                        size="10">
                    @foreach($competencies as $c)
                        <option value="{{ $c->id }}"
                            {{ in_array($c->id, old('competency_ids', [])) ? 'selected' : '' }}>
                            {{ $c->code ? $c->code.' — ' : '' }}{{ $c->label }}
                        </option>
                    @endforeach
                </select>

                <p class="text-xs text-gray-500 mt-2">
                    Maintiens Ctrl (Windows) / Cmd (Mac) pour sélectionner plusieurs compétences.
                </p>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="submit"
                        class="px-8 py-3 rounded-xl bg-orangeone text-white font-semibold hover:opacity-90">
                    Créer
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
