@php
  $groupesDisponibles = collect($accessibleGroups ?? []);
  $groupesAffectes = collect($assignedGroupIds ?? [])->map(fn ($id) => (int) $id)->all();
@endphp

<form method="POST" action="{{ $urlSynchronisationGroupes }}" class="space-y-3">
  @csrf
  @method('PUT')

  <fieldset>
    <legend class="sr-only">Groupes auxquels affecter la formation</legend>
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($groupesDisponibles as $group)
        <label class="flex min-h-10 items-center gap-2 rounded-[8px] px-2 text-sm text-gray-700 transition hover:bg-gray-50">
          <input type="checkbox" name="group_ids[]" value="{{ $group->id }}"
                 @checked(in_array((int) $group->id, $groupesAffectes, true))
                 class="rounded border-gray-300 text-orangeone focus:ring-orangeone">
          <span>{{ $group->name }}</span>
        </label>
      @empty
        <p class="text-xs text-gray-400">{{ ($constructeurAdmin ?? false) ? 'Aucun groupe disponible.' : "Vous n'avez aucun groupe pour le moment." }}</p>
      @endforelse
    </div>
  </fieldset>

  @if($groupesDisponibles->isNotEmpty())
    <button type="submit" class="btn-oneduc-outline !px-4 !py-2 !text-xs">Mettre à jour les groupes</button>
  @endif
</form>
