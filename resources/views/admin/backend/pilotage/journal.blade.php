@extends('admin.admin_dashboard')

@section('admin')
<div class="mx-auto w-full max-w-[1400px] space-y-6">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <a href="{{ route('admin.pilotage.index') }}" class="text-sm text-blue-700 hover:underline">Retour au pilotage</a>
      <h1 class="mt-1 text-3xl font-semibold text-bleuone">Journal d'activite</h1>
      <p class="mt-1 text-sm text-gray-600">Trace des actions admin (creation, edition, suppression).</p>
    </div>
  </div>

  <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <form method="GET" action="{{ route('admin.pilotage.journal') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
      <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Recherche action/route"
             class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">

      <select name="action" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Toutes actions</option>
        @foreach($actions as $action)
          <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
        @endforeach
      </select>

      <input type="text" name="route_name" value="{{ $filters['route_name'] ?? '' }}" placeholder="Route admin.*"
             class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">

      <select name="user_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Tous admins</option>
        @foreach($users as $user)
          @php $label = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Admin #' . $user->id); @endphp
          <option value="{{ $user->id }}" @selected((string)($filters['user_id'] ?? '') === (string)$user->id)>{{ $label }}</option>
        @endforeach
      </select>

      <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
             class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
      <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
             class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">

      <div class="xl:col-span-6 flex flex-wrap items-center gap-2">
        <button type="submit" class="rounded-md bg-[#004461] px-4 py-2 text-sm font-medium text-white hover:bg-[#00364d]">Filtrer</button>
        <a href="{{ route('admin.pilotage.journal') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reinitialiser</a>
      </div>
    </form>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="px-3 py-2 text-left font-semibold">Date</th>
            <th class="px-3 py-2 text-left font-semibold">Admin</th>
            <th class="px-3 py-2 text-left font-semibold">Action</th>
            <th class="px-3 py-2 text-left font-semibold">Route</th>
            <th class="px-3 py-2 text-left font-semibold">Methode</th>
            <th class="px-3 py-2 text-left font-semibold">URL</th>
            <th class="px-3 py-2 text-left font-semibold">Contexte</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($entries as $entry)
            @php
              $adminLabel = $entry->user?->username ?: trim(($entry->user?->prenom ?? '') . ' ' . ($entry->user?->name ?? ''));
              $adminLabel = $adminLabel ?: 'N/A';
              $contextJson = $entry->context ? json_encode($entry->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '-';
            @endphp
            <tr class="align-top">
              <td class="px-3 py-3 whitespace-nowrap">{{ $entry->created_at?->format('d/m/Y H:i:s') }}</td>
              <td class="px-3 py-3">{{ $adminLabel }}</td>
              <td class="px-3 py-3">{{ $entry->action }}</td>
              <td class="px-3 py-3"><code class="text-xs">{{ $entry->route_name ?: '-' }}</code></td>
              <td class="px-3 py-3"><span class="rounded bg-gray-100 px-2 py-1 text-xs">{{ $entry->method }}</span></td>
              <td class="px-3 py-3"><span class="text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($entry->url, 70) }}</span></td>
              <td class="px-3 py-3">
                <details>
                  <summary class="cursor-pointer text-xs text-blue-700">Voir</summary>
                  <pre class="mt-2 max-w-md overflow-x-auto whitespace-pre-wrap rounded bg-gray-900 p-3 text-[11px] text-gray-100">{{ $contextJson }}</pre>
                </details>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-4 text-center text-sm text-gray-500">Aucune entree.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $entries->links() }}</div>
  </section>
</div>
@endsection

