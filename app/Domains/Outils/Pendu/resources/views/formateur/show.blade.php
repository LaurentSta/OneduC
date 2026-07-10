@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <header class="my-6 rounded-[20px] bg-white px-8 pb-6 pt-5 shadow-md">
    <x-oneduc.breadcrumb :items="[['label' => 'Outils numériques', 'url' => route('formateur.outils.index')], ['label' => 'Jeu du pendu', 'url' => route('formateur.pendu.index')], ['label' => $session->title]]" />
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="font-raleway text-2xl text-bleuone">{{ $session->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $session->group_name }} · code <span class="font-mono font-bold text-orangeone">{{ $session->access_code }}</span></p>
        <p class="mt-2 text-sm text-gray-700">Mot choisi : <span class="font-semibold">{{ $session->word }}</span></p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="btn-oneduc-outline !px-4 !py-2">Vue participant</a>
        <form method="POST" action="{{ route('formateur.pendu.toggle', $session->id) }}">
          @csrf
          <button type="submit" class="btn-oneduc !px-4 !py-2">{{ $session->is_active ? 'Fermer' : 'Rouvrir' }}</button>
        </form>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <section class="rounded-[20px] bg-white p-6 shadow-md lg:col-span-2">
      <div id="pendu-status" class="mb-5 rounded-[8px] border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700"></div>
      <p class="mb-3 text-xs font-bold uppercase text-gray-400">Mot à deviner</p>
      <div id="pendu-letters" class="mb-6 flex flex-wrap gap-2"></div>
      <p class="mb-3 text-xs font-bold uppercase text-gray-400">Lettres incorrectes</p>
      <div id="pendu-wrong" class="flex flex-wrap gap-2"></div>
    </section>

    <section class="flex flex-col items-center justify-center rounded-[20px] bg-white p-6 shadow-md">
      <svg viewBox="0 0 120 140" class="h-48 w-48" aria-label="Progression du pendu">
        <line x1="10" y1="130" x2="90" y2="130" stroke="#374151" stroke-width="4" stroke-linecap="round"/>
        <line x1="30" y1="130" x2="30" y2="10" stroke="#374151" stroke-width="4" stroke-linecap="round"/>
        <line x1="30" y1="10" x2="80" y2="10" stroke="#374151" stroke-width="4" stroke-linecap="round"/>
        <line x1="80" y1="10" x2="80" y2="28" stroke="#374151" stroke-width="4" stroke-linecap="round"/>
        <circle data-pendu-part="1" cx="80" cy="40" r="12" stroke="#E94D2A" stroke-width="4" fill="none"/>
        <line data-pendu-part="2" x1="80" y1="52" x2="80" y2="90" stroke="#E94D2A" stroke-width="4"/>
        <line data-pendu-part="3" x1="80" y1="60" x2="65" y2="75" stroke="#E94D2A" stroke-width="4"/>
        <line data-pendu-part="4" x1="80" y1="60" x2="95" y2="75" stroke="#E94D2A" stroke-width="4"/>
        <line data-pendu-part="5" x1="80" y1="90" x2="65" y2="115" stroke="#E94D2A" stroke-width="4"/>
        <line data-pendu-part="6" x1="80" y1="90" x2="95" y2="115" stroke="#E94D2A" stroke-width="4"/>
      </svg>
      <p class="text-sm text-gray-600">Essais restants : <span id="pendu-remaining" class="font-bold text-orangeone"></span></p>
    </section>
  </div>
</div>

<script>
(() => {
  const endpoint = @json(route('formateur.pendu.state', $session->id));
  const letters = document.getElementById('pendu-letters');
  const wrong = document.getElementById('pendu-wrong');
  const status = document.getElementById('pendu-status');
  const remaining = document.getElementById('pendu-remaining');

  function render(data) {
    letters.replaceChildren(...data.letters.map((letter) => {
      const element = document.createElement('span');
      element.className = letter.is_letter
        ? 'flex h-10 w-8 items-center justify-center rounded-[6px] border-2 border-gray-300 text-lg font-bold text-bleuone'
        : 'px-1 text-lg font-bold text-gray-400';
      element.textContent = letter.char === ' ' ? '\u00a0' : letter.char;
      return element;
    }));

    wrong.replaceChildren(...data.wrong_letters.map((letter) => {
      const element = document.createElement('span');
      element.className = 'flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-700';
      element.textContent = letter;
      return element;
    }));

    remaining.textContent = data.remaining_attempts;
    status.textContent = data.status === 'won'
      ? 'Partie gagnée.'
      : (data.status === 'lost' ? 'Partie perdue.' : `${data.respondents} participant(s) ont proposé une lettre.`);
    document.querySelectorAll('[data-pendu-part]').forEach((part) => {
      part.style.display = Number(part.dataset.penduPart) <= data.wrong_letters.length ? '' : 'none';
    });
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
