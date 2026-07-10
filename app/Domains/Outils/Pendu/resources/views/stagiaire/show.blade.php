<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $session->title }} - Jeu du pendu</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 p-4 font-lisible">
  <main class="mx-auto w-full max-w-2xl space-y-4">
    <header class="rounded-[20px] bg-white p-6 shadow-md">
      <p class="text-xs uppercase text-gray-400">Code {{ $session->access_code }}</p>
      <h1 class="mt-1 font-raleway text-2xl text-bleuone">{{ $session->title }}</h1>
      @error('letter')<p class="mt-3 rounded-[8px] bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
      @unless($peutJouer)<p class="mt-3 rounded-[8px] bg-blue-50 px-3 py-2 text-sm text-blue-700">Aperçu formateur : les propositions sont désactivées.</p>@endunless
    </header>

    <section class="rounded-[20px] bg-white p-6 shadow-md">
      <div id="pendu-status" class="mb-5 rounded-[8px] border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700"></div>
      <div id="pendu-letters" class="mb-6 flex flex-wrap justify-center gap-2"></div>
      <form method="POST" action="{{ route('pendu.submit', $session->access_code) }}">
        @csrf
        <fieldset @disabled(! $peutJouer)>
          <legend class="mb-3 text-sm font-semibold text-gray-700">Proposer une lettre</legend>
          <div id="pendu-keyboard" class="grid grid-cols-7 gap-2 sm:grid-cols-9"></div>
        </fieldset>
      </form>
    </section>
  </main>

<script>
(() => {
  const endpoint = @json(route('pendu.data', $session->access_code));
  const peutJouer = @json($peutJouer);
  const letters = document.getElementById('pendu-letters');
  const keyboard = document.getElementById('pendu-keyboard');
  const status = document.getElementById('pendu-status');
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

  function render(data) {
    letters.replaceChildren(...data.letters.map((letter) => {
      const element = document.createElement('span');
      element.className = letter.is_letter
        ? 'flex h-10 w-8 items-center justify-center rounded-[6px] border-2 border-gray-300 text-lg font-bold text-bleuone'
        : 'px-1 text-lg font-bold text-gray-400';
      element.textContent = letter.char === ' ' ? '\u00a0' : letter.char;
      return element;
    }));

    keyboard.replaceChildren(...alphabet.map((letter) => {
      const button = document.createElement('button');
      const proposed = data.guessed_letters.includes(letter);
      button.type = 'submit';
      button.name = 'letter';
      button.value = letter;
      button.textContent = letter;
      button.disabled = proposed || !peutJouer || !data.is_active || data.status !== 'in_progress';
      button.className = proposed
        ? 'rounded-[8px] border border-gray-200 bg-gray-100 py-2 text-sm font-bold text-gray-400'
        : 'rounded-[8px] border border-orangeone/30 bg-white py-2 text-sm font-bold text-bleuone hover:bg-orange-50 disabled:opacity-40';
      return button;
    }));

    status.textContent = data.status === 'won'
      ? 'Bravo, le mot a été trouvé.'
      : (data.status === 'lost' ? 'La partie est terminée.' : `${data.remaining_attempts} erreur(s) encore autorisée(s).`);
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
</body>
</html>
