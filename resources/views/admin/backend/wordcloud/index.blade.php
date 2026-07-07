@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
      <div>
        <h1 class="text-[20px] font-varela text-bleuone">Jeu - Nuage de mot</h1>
        <p class="text-sm text-gray-600">Crée et pilote tes nuages pour les formations.</p>
      </div>
      <a href="{{ route('admin.nuage.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition">
        <i class="ti ti-plus"></i>
        Nouveau nuage
      </a>
    </div>

    @if(session('success'))
      <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if($wordClouds->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-700">
          <thead class="text-xs uppercase border-b">
            <tr>
              <th class="px-4 py-3">Titre</th>
              <th class="px-4 py-3">Code</th>
              <th class="px-4 py-3">Formation</th>
              <th class="px-4 py-3">Réponses</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($wordClouds as $cloud)
              <tr class="border-b border-gray-100">
                <td class="px-4 py-3 font-medium text-gray-900">{{ $cloud->title }}</td>
                <td class="px-4 py-3"><span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $cloud->access_code }}</span></td>
                <td class="px-4 py-3">{{ $cloud->module?->module_name ?? 'Non liée' }}</td>
                <td class="px-4 py-3">{{ $cloud->entries_count }}</td>
                <td class="px-4 py-3">
                  @if($cloud->is_active)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">Ouvert</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-700">Fermé</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <a class="text-bleuone hover:underline" href="{{ route('admin.nuage.show', $cloud) }}">Session</a>
                    <a class="text-bleuone hover:underline" href="{{ route('admin.nuage.live', $cloud) }}">Live</a>
                    <a class="text-bleuone hover:underline" href="{{ route('admin.nuage.edit', $cloud) }}">Éditer</a>
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-cloud-{{ $cloud->id }}')" class="text-red-600 hover:underline">Supprimer</button>
                    <x-confirm-modal
                      name="delete-cloud-{{ $cloud->id }}"
                      title="Supprimer ce nuage ?"
                      :action="route('admin.nuage.destroy', $cloud)"
                      method="DELETE"
                      confirm-label="Supprimer"
                    />
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4">{{ $wordClouds->links() }}</div>
    @else
      <p class="text-sm text-gray-600">Aucun nuage pour le moment.</p>
    @endif
  </div>
</div>

@endsection
