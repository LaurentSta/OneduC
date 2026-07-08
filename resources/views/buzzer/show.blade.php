<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $session->title }} - Buzzer Quiz</title>
  @vite(['resources/css/app.css'])
  <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen bg-slate-100 p-4"
      x-data='{
        snapshot: @json($snapshot),
        busy: false,
        poll() {
          fetch(@json(route("buzzer.snapshot", $session->access_code)), {
            headers: { "Accept": "application/json" },
          })
            .then(r => r.json())
            .then(data => { this.snapshot = data; })
            .catch(() => {});
        },
        buzz() {
          if (this.busy || this.snapshot.has_buzzed) return;
          this.busy = true;
          fetch(@json(route("buzzer.buzz", $session->access_code)), {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "X-CSRF-TOKEN": @json(csrf_token()),
            },
            body: "{}",
          })
            .then(r => r.json())
            .then(data => { this.snapshot = data; })
            .catch(() => {})
            .finally(() => { this.busy = false; });
        },
        init() {
          setInterval(() => this.poll(), 2500);
        },
      }'>

  <div class="mx-auto w-full max-w-xl space-y-4">
    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-xs uppercase tracking-wide text-gray-400">Code - {{ $session->access_code }}</p>
      <h1 class="text-xl font-bold text-bleuone mt-1">{{ $session->title }}</h1>
    </div>

    <div x-show="snapshot.status === 'waiting'" x-cloak class="rounded-2xl bg-white shadow-lg p-8 text-center">
      <p class="text-lg font-semibold text-gray-700">En attente du démarrage...</p>
      <p class="text-sm text-gray-500 mt-2">Le formateur va bientôt lancer la première question.</p>
    </div>

    <div x-show="snapshot.status === 'question_open'" x-cloak class="rounded-2xl bg-white shadow-lg p-8 text-center">
      <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
        Question <span x-text="snapshot.current_position"></span> / <span x-text="snapshot.total_questions"></span>
      </p>

      <template x-if="snapshot.question_text">
        <p class="text-xl font-semibold text-gray-800 mb-8" x-text="snapshot.question_text"></p>
      </template>
      <template x-if="!snapshot.question_text">
        <p class="text-sm italic text-gray-500 mb-8">Écoutez la question annoncée par le formateur.</p>
      </template>

      <template x-if="!snapshot.has_buzzed">
        <button @click="buzz()" :disabled="busy"
                class="w-full rounded-full bg-red-600 py-10 text-3xl font-black text-white shadow-lg hover:bg-red-700 active:scale-95 transition disabled:opacity-50">
          BUZZ !
        </button>
      </template>

      <template x-if="snapshot.has_buzzed && snapshot.is_holder">
        <div class="rounded-xl bg-vertone/10 border border-vertone p-5">
          <p class="text-lg font-bold text-vertone">C'est à vous !</p>
          <p class="text-sm text-gray-600 mt-1">Répondez à voix haute, le formateur valide votre réponse.</p>
        </div>
      </template>

      <template x-if="snapshot.has_buzzed && !snapshot.is_holder && snapshot.my_result !== 'incorrect'">
        <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
          <p class="text-sm font-semibold text-gray-600">Vous avez buzzé, en attente du verdict...</p>
        </div>
      </template>

      <template x-if="snapshot.my_result === 'incorrect'">
        <div class="rounded-xl bg-red-50 border border-red-200 p-5">
          <p class="text-sm font-semibold text-red-600">Ce n'était pas la bonne réponse. En attente de la suite...</p>
        </div>
      </template>
    </div>

    <div x-show="snapshot.status === 'revealed'" x-cloak class="rounded-2xl bg-white shadow-lg p-8 text-center">
      <template x-if="snapshot.winner_name">
        <p class="text-xl font-bold text-vertone"><span x-text="snapshot.winner_name"></span> remporte le point !</p>
      </template>
      <template x-if="!snapshot.winner_name">
        <p class="text-xl font-bold text-gray-500">Personne n'a trouvé la bonne réponse.</p>
      </template>
      <p class="text-sm text-gray-500 mt-2">La question suivante arrive...</p>
    </div>

    <div x-show="snapshot.status === 'closed'" x-cloak class="rounded-2xl bg-white shadow-lg p-8 text-center">
      <p class="text-xl font-bold text-bleuone">Le jeu est terminé !</p>
    </div>

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <h2 class="text-sm font-bold text-bleuone mb-3">Classement</h2>
      <div class="space-y-2">
        <template x-for="(row, i) in snapshot.leaderboard" :key="row.name + i">
          <div class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2">
            <span class="text-sm font-semibold text-gray-700">
              <span class="text-gray-400 mr-1" x-text="(i + 1) + '.'"></span>
              <span x-text="row.name"></span>
            </span>
            <span class="text-sm font-bold text-red-700" x-text="row.score"></span>
          </div>
        </template>
        <p x-show="snapshot.leaderboard.length === 0" x-cloak class="text-sm text-gray-400 text-center py-4">
          Aucun point pour le moment.
        </p>
      </div>
    </div>
  </div>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
