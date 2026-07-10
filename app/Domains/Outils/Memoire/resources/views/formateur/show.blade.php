@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <header class="my-6 rounded-[20px] bg-white px-8 pb-6 pt-5 shadow-md">
    <x-oneduc.breadcrumb :items="[['label' => 'Outils numériques', 'url' => route('formateur.outils.index')], ['label' => 'Jeu de mémoire', 'url' => route('formateur.memoire.index')], ['label' => $session->title]]" />
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="font-raleway text-2xl text-bleuone">{{ $session->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $session->group_name }} · code <span class="font-mono font-bold text-bleuone">{{ $session->access_code }}</span></p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="btn-oneduc-outline !px-4 !py-2">Vue participant</a>
        <form method="POST" action="{{ route('formateur.memoire.toggle', $session->id) }}">
          @csrf
          <button type="submit" class="btn-oneduc-blue !px-4 !py-2">{{ $session->is_active ? 'Fermer' : 'Rouvrir' }}</button>
        </form>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <section class="rounded-[20px] bg-white p-6 shadow-md">
      <h2 class="mb-4 font-semibold text-bleuone">Paires à retrouver</h2>
      <div class="space-y-2">
        @foreach($session->pairs as $pair)
          <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 rounded-[8px] border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            <span class="font-semibold text-gray-800">{{ $pair['a'] }}</span>
            <span aria-hidden="true" class="text-gray-400">↔</span>
            <span class="text-gray-600">{{ $pair['b'] }}</span>
          </div>
        @endforeach
      </div>
    </section>

    <section class="rounded-[20px] bg-white p-6 shadow-md">
      <h2 class="mb-4 font-semibold text-bleuone">Classement en direct</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="text-left text-xs uppercase text-gray-400"><th class="py-2">Stagiaire</th><th>Erreurs</th><th>Coups</th><th>Durée</th></tr></thead>
          <tbody id="memoire-leaderboard"></tbody>
        </table>
      </div>
      <p id="memoire-empty" class="py-5 text-center text-sm text-gray-500">Aucune participation pour le moment.</p>
    </section>
  </div>
</div>

<script>
(() => {
  const endpoint = @json(route('formateur.memoire.state', $session->id));
  const body = document.getElementById('memoire-leaderboard');
  const empty = document.getElementById('memoire-empty');

  function cell(value, classes = '') {
    const element = document.createElement('td');
    element.className = `border-t border-gray-100 py-2 ${classes}`;
    element.textContent = value;
    return element;
  }

  function render(data) {
    body.replaceChildren(...data.leaderboard.map((row) => {
      const line = document.createElement('tr');
      line.append(cell(row.name, 'font-semibold text-gray-800'));
      line.append(cell(row.errors));
      line.append(cell(row.moves));
      line.append(cell(`${row.duration_seconds}s`));
      return line;
    }));
    empty.hidden = data.leaderboard.length > 0;
  }

  async function refresh() {
    try {
      const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
      if (response.ok) render(await response.json());
    } catch (_) {}
  }

  render(@json($state));
  setInterval(refresh, 3000);
})();
</script>
@endsection
