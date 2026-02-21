{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/badges/edit.blade.php --}}
@extends('admin.admin_dashboard')

@push('styles')
<style>
    .form-card { background: rgba(0, 68, 97, 0.04); border: 1px solid rgba(0, 68, 97, 0.1); border-radius: 20px; padding: 20px; }
    .form-card-title { font-size: 1rem; font-weight: 800; color: #004461; display: flex; align-items: center; gap: 8px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #004461; }
    .btn-action { transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; border-radius: 12px; border: none; cursor: pointer; }
    .btn-action:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>
@endpush

@section('admin')
<div class="max-w-4xl mx-auto mt-8 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        {{-- Top Bar --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="page-title text-2xl">Édition du badge</h2>
                <p class="text-sm text-gray-500 font-medium">Association du badge à des compétences</p>
            </div>

            <a href="{{ route('admin.badges.index') ?? url()->previous() }}"
               class="btn-action px-4 py-2 bg-gray-100 text-gray-600 text-sm hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
        </div>

        {{-- Alertes --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm flex items-center gap-3 font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

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

        @php
            // Pré-sélection: pivot badge_competency
            $selectedIds = old('competency_ids', $badge->competencies->pluck('id')->all());
            // Tri par pivot position si présent
            $selectedIds = is_array($selectedIds) ? $selectedIds : [];
        @endphp

        <form method="POST" action="{{ route('admin.badges.update', $badge->id) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- 1) Infos badge --}}
            <div class="form-card">
                <div class="form-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.866-3.134 7-7 7a7 7 0 010-14c3.866 0 7 3.134 7 7zm0 0c0 3.866 3.134 7 7 7a7 7 0 000-14c-3.866 0-7 3.134-7 7z" />
                    </svg>
                    Informations
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/50 p-5 rounded-2xl">
                    <div>
                        <label class="block text-sm font-extrabold text-bleuone uppercase ml-1">Libellé du badge</label>
                        <input type="text"
                               name="label"
                               value="{{ old('label', $badge->label) }}"
                               class="mt-2 w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orangeone text-lg font-bold text-gray-800"
                               required>
                        @error('label')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-4 cursor-pointer p-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', (int)$badge->is_active) ? 'checked' : '' }}
                                   class="h-6 w-6 rounded-lg text-orangeone border-gray-300 focus:ring-orangeone">
                            <div>
                                <span class="block font-bold text-gray-800">Activer le badge</span>
                                <span class="text-xs text-gray-500">Visible et attribuable</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-6 bg-white/50 p-5 rounded-2xl">
                    <label class="block text-sm font-extrabold text-bleuone uppercase ml-1">Image du badge</label>

                    @if(!empty($badge->image_path))
                        <div class="mt-3 flex items-center gap-4">
                            <div class="h-20 w-20 rounded-xl border border-gray-200 bg-white flex items-center justify-center p-2">
                                <img src="{{ asset('storage/'.$badge->image_path) }}"
                                     alt="Badge {{ $badge->label }}"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                            <span class="text-xs text-gray-500">Image actuelle</span>
                        </div>
                    @endif

                    <input type="file"
                           name="image"
                           accept=".svg,image/svg+xml"
                           class="mt-3 w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orangeone text-sm text-gray-700">

                    <p class="text-xs text-gray-500 mt-2">
                        Format obligatoire : SVG. Dimension recommandée : 200x200.
                    </p>

                    @if(!empty($badge->image_path))
                        <label class="mt-3 inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   name="remove_image"
                                   value="1"
                                   {{ old('remove_image') ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-orangeone focus:ring-orangeone">
                            <span class="text-sm text-gray-700">Supprimer l’image actuelle</span>
                        </label>
                    @endif

                    @error('image')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 2) Compétences liées --}}
            <div class="form-card">
                <div class="form-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Compétences associées
                </div>

                <div class="bg-white/50 p-5 rounded-2xl space-y-4">
                    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
                        <div>
                            <div class="font-bold text-gray-800">Sélection multiple</div>
                            <div class="text-xs text-gray-500">Maintiens Ctrl (Windows) / Cmd (Mac) pour sélectionner plusieurs compétences.</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    id="btn-select-all"
                                    class="btn-action px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs shadow-sm hover:bg-gray-50">
                                Tout sélectionner
                            </button>
                            <button type="button"
                                    id="btn-clear-all"
                                    class="btn-action px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs shadow-sm hover:bg-gray-50">
                                Tout désélectionner
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Compétences</label>
                        <select name="competency_ids[]"
                                id="competency_ids"
                                class="mt-2 w-full rounded-lg border-gray-300"
                                multiple
                                size="10">
                            @foreach($competencies as $c)
                                <option value="{{ $c->id }}"
                                    {{ in_array($c->id, $selectedIds, true) ? 'selected' : '' }}>
                                    {{ $c->code ? $c->code.' — ' : '' }}{{ $c->label }}
                                </option>
                            @endforeach
                        </select>

                        @error('competency_ids')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                        @error('competency_ids.*')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror

                        <p class="text-xs text-gray-500 mt-2">
                            L’ordre est recalculé automatiquement selon ta sélection (position 1..n).
                            Si tu veux un ordre manuel plus tard, on ajoutera une interface de tri.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="flex flex-col md:flex-row items-center justify-end gap-4 pt-6 border-t border-gray-100">
                <button type="submit" class="btn-action w-full md:w-auto px-10 py-4 bg-orangeone text-white shadow-lg shadow-orangeone/20 hover:opacity-90">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const select = document.getElementById('competency_ids');
    const btnAll = document.getElementById('btn-select-all');
    const btnNone = document.getElementById('btn-clear-all');

    if (!select) return;

    if (btnAll) {
        btnAll.addEventListener('click', () => {
            [...select.options].forEach(opt => opt.selected = true);
            select.dispatchEvent(new Event('change'));
        });
    }

    if (btnNone) {
        btnNone.addEventListener('click', () => {
            [...select.options].forEach(opt => opt.selected = false);
            select.dispatchEvent(new Event('change'));
        });
    }
})();
</script>
@endsection
