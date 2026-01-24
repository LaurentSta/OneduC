@extends('formateur.dashboard')

@section('formateur')
<div class="max-w-[1248px] mx-auto px-4 py-8 space-y-6">

  <div class="bg-white rounded-[20px] shadow-md p-6">
    <h1 class="text-xl font-semibold text-bleuone">Personnaliser les leçons</h1>
    <p class="text-gray-600 mt-1">
      Groupe : <span class="font-medium">{{ $group->name }}</span> —
      Module : <span class="font-medium">{{ $module->module_title }}</span>
    </p>

    <div class="mt-4 flex gap-2">
      <a href="{{ route('formateur.groupes.edit', $group->id) }}"
         class="btn-oneduc-blue !px-4 !py-2 !border-2">
        Retour au groupe
      </a>

      <form method="POST" action="{{ route('formateur.groupes.modules.lecons.reset', ['group' => $group->id, 'module' => $module->id]) }}">
        @csrf
        <button type="submit" class="btn-oneduc-blue !px-4 !py-2 !border-2">
          Réinitialiser
        </button>
      </form>
    </div>
  </div>

  @foreach($sections as $section)
    <div class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-lg font-semibold text-gray-900">{{ $section->section_title }}</h2>

      <div class="mt-4 space-y-2">
        @forelse($section->lectures as $lec)
          @php
            $row = $rows[$lec->id] ?? null;
            $enabled = $row ? (bool) $row->is_enabled : true;
          @endphp

          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border border-gray-200 rounded-lg px-4 py-3">
            <div class="flex-1">
              <div class="font-medium text-gray-900">
                {{ $lec->lecture_title }}
              </div>
              <div class="text-sm text-gray-600">
                Statut : <span class="{{ $enabled ? 'text-green-700' : 'text-gray-500' }}">
                  {{ $enabled ? 'Active' : 'Désactivée' }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <form method="POST" action="{{ route('formateur.groupes.modules.lecons.toggle', ['group' => $group->id, 'module' => $module->id, 'lecture' => $lec->id]) }}">
                @csrf
                <button type="submit" class="btn-oneduc-blue !px-4 !py-2 !border-2">
                  {{ $enabled ? 'Désactiver' : 'Activer' }}
                </button>
              </form>

              <form method="POST" action="{{ route('formateur.groupes.modules.lecons.move.up', ['group' => $group->id, 'module' => $module->id, 'lecture' => $lec->id]) }}">
                @csrf
                <button type="submit" class="btn-oneduc-blue !px-3 !py-2 !border-2" aria-label="Monter la leçon">
                  Monter
                </button>
              </form>

              <form method="POST" action="{{ route('formateur.groupes.modules.lecons.move.down', ['group' => $group->id, 'module' => $module->id, 'lecture' => $lec->id]) }}">
                @csrf
                <button type="submit" class="btn-oneduc-blue !px-3 !py-2 !border-2" aria-label="Descendre la leçon">
                  Descendre
                </button>
              </form>
            </div>
          </div>
        @empty
          <p class="text-gray-600">Aucune leçon dans cette section.</p>
        @endforelse
      </div>
    </div>
  @endforeach

</div>
@endsection
