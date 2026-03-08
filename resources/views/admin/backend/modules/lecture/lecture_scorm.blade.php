@extends('admin.admin_dashboard')

@section('admin')
<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        {{-- En-tête --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div class="min-w-0">
                <h1 class="text-[20px] font-varela text-bleuone truncate">
                    Prévisualisation : {{ $lecture->lecture_title }}
                </h1>
                <p class="text-xs text-gray-600 mt-1 break-all">
                    Chemin : {{ $scormPath ?? $lecture->scorm_index_path ?? $lecture->scorm_path }}
                </p>
            </div>

            {{-- Bouton retour robuste (pas de dépendance à une route) --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition"
                   style="transition: all .35s cubic-bezier(.22,1,.36,1);">
                    <i class="ti ti-arrow-left"></i>
                    Retour
                </a>
            </div>
        </div>

        {{-- Zone iframe --}}
        <div class="rounded-[16px] overflow-hidden border border-gray-200 bg-white"
             style="height: 80vh;">
            <iframe
                src="{{ $scormUrl ?? $lecture->scorm_asset_url }}"
                class="w-full h-full border-0"
                title="Prévisualisation SCORM : {{ $lecture->lecture_title }}"
                loading="lazy"
                allowfullscreen>
            </iframe>
        </div>

        {{-- Note accessibilité / debug léger (facultatif) --}}
        <div class="mt-3 text-xs text-gray-500">
            Si le module ne s’affiche pas : vérifier que le chemin pointe vers un fichier accessible depuis <strong>public/</strong>.
        </div>

    </div>
</div>
@endsection
