@php
  $pickerId = 'co-trainer-picker-' . ($mode ?? 'default');
  $selectedCoFormateurs = collect($selectedCoFormateurs ?? []);
  $groupId = isset($group) && $group ? (int) $group->id : 0;
  $isEditable = (bool) ($canManageCoFormateurs ?? false);
  $wrapperClass = (string) ($wrapperClass ?? 'min-w-0');
  $panelClass = (string) ($panelClass ?? 'border-gray-200 bg-slate-50/70');
@endphp

<div
  id="{{ $pickerId }}"
  data-co-trainer-picker
  data-search-url="{{ route('formateur.groupes.co-formateurs.search') }}"
  data-group-id="{{ $groupId }}"
  data-can-manage="{{ $isEditable ? '1' : '0' }}"
  data-min-chars="3"
  class="{{ $wrapperClass }}"
>
  <div class="h-full rounded-[18px] border px-4 py-4 {{ $panelClass }}">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
      <div>
        <h3 class="text-base font-medium text-gray-900">Co-formateurs</h3>
        <p class="mt-1 text-sm text-gray-500">
          Ajoutez un ou plusieurs formateurs actifs deja inscrits pour coanimer ce groupe.
        </p>
      </div>

      @if(! $isEditable)
        <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-600">
          Lecture seule
        </span>
      @endif
    </div>

    @if($isEditable)
      <div class="mt-4">
        <label for="{{ $pickerId }}-input" class="block text-sm font-medium text-gray-900">Rechercher par email</label>
        <input
          id="{{ $pickerId }}-input"
          type="text"
          data-co-trainer-search-input
          autocomplete="off"
          class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
          placeholder="Saisissez les 3 premiers caracteres de l'email"
        >
        <p class="mt-2 text-xs text-gray-500">
          Seuls les formateurs actifs deja inscrits sont proposes.
        </p>
        <div data-co-trainer-feedback class="mt-3 hidden rounded-lg border px-3 py-2 text-sm"></div>
        <div data-co-trainer-results class="mt-3 hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"></div>
      </div>
    @else
      <div class="mt-4 rounded-lg border border-gray-200 bg-white px-3 py-3 text-sm text-gray-600">
        Seul le formateur principal peut ajouter ou retirer des co-formateurs.
      </div>
    @endif

    <div class="mt-4">
      <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Formateurs associes</p>

      <div data-co-trainer-selected class="mt-3 flex flex-wrap gap-2">
        @forelse($selectedCoFormateurs as $coFormateur)
          <div
            data-co-trainer-chip
            data-id="{{ (int) data_get($coFormateur, 'id') }}"
            data-email="{{ (string) data_get($coFormateur, 'email') }}"
            class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-3 py-2 text-sm font-medium text-bleuone"
          >
            <span>{{ (string) data_get($coFormateur, 'email') }}</span>

            @if($isEditable)
              <input type="hidden" name="co_formateurs[]" value="{{ (int) data_get($coFormateur, 'id') }}">
              <button
                type="button"
                data-co-trainer-remove
                class="inline-flex h-6 w-6 items-center justify-center rounded-full text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                aria-label="Retirer ce co-formateur"
                title="Retirer ce co-formateur"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            @endif
          </div>
        @empty
          <p data-co-trainer-empty class="rounded-lg border border-dashed border-gray-300 bg-white px-3 py-3 text-sm text-gray-500">
            Aucun co-formateur ajoute pour le moment.
          </p>
        @endforelse
      </div>

      @if($selectedCoFormateurs->isNotEmpty())
        <p data-co-trainer-empty class="hidden rounded-lg border border-dashed border-gray-300 bg-white px-3 py-3 text-sm text-gray-500">
          Aucun co-formateur ajoute pour le moment.
        </p>
      @endif

      @error('co_formateurs')
        <p class="mt-3 text-sm text-red-700">{{ $message }}</p>
      @enderror

      @error('co_formateurs.*')
        <p class="mt-3 text-sm text-red-700">{{ $message }}</p>
      @enderror
    </div>
  </div>
</div>
