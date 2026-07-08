@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8"
     x-data='{
       snapshot: @json($snapshot),
       poll() {
         fetch("{{ route('formateur.buzzer.snapshot', $session) }}")
           .then(r => r.json())
           .then(data => { this.snapshot = data; })
           .catch(() => {});
       },
       init() {
         setInterval(() => this.poll(), 2500);
       },
     }'>

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li><a href="{{ route('formateur.buzzer.index') }}" class="text-orangeone hover:underline">Buzzer Quiz</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $session->title }}</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">{{ $session->title }}</p>
        <p class="text-sm text-gray-500 mt-1">
          Groupe : <span class="font-semibold">{{ $session->group?->name ?? '—' }}</span>
          - Code : <span class="font-mono font-semibold text-red-700">{{ $session->access_code }}</span>
          - <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $session->mode === 'distance' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
              {{ $session->mode === 'distance' ? 'À distance' : 'Présentiel' }}
            </span>
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank"
           class="inline-flex items-center rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Ouvrir la vue stagiaire
        </a>

        @if($session->status !== 'closed')
          <form method="POST" action="{{ route('formateur.buzzer.close', $session) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center rounded-[10px] bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600 transition">
              Fermer le jeu
            </button>
          </form>
        @endif

        <form method="POST" action="{{ route('formateur.buzzer.destroy', $session) }}">
          @csrf
          @method('DELETE')
          <button type="submit"
                  class="inline-flex items-center rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition">
            Supprimer
          </button>
        </form>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">

      <div x-show="snapshot.status === 'waiting'" x-cloak class="text-center py-10">
        <p class="text-lg font-semibold text-gray-700 mb-4">Le jeu n'a pas encore démarré.</p>
        <p class="text-sm text-gray-500 mb-6">{{ $session->total_questions }} question{{ $session->total_questions > 1 ? 's' : '' }} prête{{ $session->total_questions > 1 ? 's' : '' }}. Partagez le code <span class="font-mono font-bold text-red-700">{{ $session->access_code }}</span> puis démarrez.</p>
        <form method="POST" action="{{ route('formateur.buzzer.start', $session) }}">
          @csrf
          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-[10px] bg-red-600 px-6 py-3 text-base font-bold text-white hover:bg-red-700 transition">
            Démarrer le jeu
          </button>
        </form>
      </div>

      <div x-show="snapshot.status === 'question_open'" x-cloak>
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
          Question <span x-text="snapshot.current_position"></span> / <span x-text="snapshot.total_questions"></span>
        </p>
        <p class="text-2xl font-bold text-bleuone mb-6" x-text="snapshot.question_text"></p>

        <div x-show="snapshot.holder_name" x-cloak class="rounded-[14px] bg-red-50 border border-red-200 p-5 mb-4">
          <p class="text-sm text-gray-600 mb-1">A buzzé en premier :</p>
          <p class="text-xl font-bold text-red-700 mb-4">
            <span x-text="snapshot.holder_name"></span>
            <span class="text-sm font-normal text-gray-500">(<span x-text="snapshot.holder_reaction_ms"></span> ms)</span>
          </p>
          <div class="flex gap-3">
            <form method="POST" action="{{ route('formateur.buzzer.correct', $session) }}">
              @csrf
              <button type="submit" class="rounded-[10px] bg-vertone px-5 py-2.5 text-sm font-bold text-white hover:opacity-90 transition">
                Bonne réponse
              </button>
            </form>
            <form method="POST" action="{{ route('formateur.buzzer.incorrect', $session) }}">
              @csrf
              <button type="submit" class="rounded-[10px] bg-gray-200 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-300 transition">
                Mauvaise réponse
              </button>
            </form>
          </div>
        </div>

        <div x-show="!snapshot.holder_name" x-cloak class="rounded-[14px] bg-gray-50 border border-gray-200 p-5 mb-4 text-center">
          <p class="text-sm text-gray-500 mb-4">En attente d'un buzz...</p>
          <form method="POST" action="{{ route('formateur.buzzer.skip', $session) }}">
            @csrf
            <button type="submit" class="rounded-[10px] border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">
              Passer la question
            </button>
          </form>
        </div>
      </div>

      <div x-show="snapshot.status === 'revealed'" x-cloak class="text-center py-10">
        <template x-if="snapshot.winner_name">
          <p class="text-xl font-bold text-vertone mb-2">
            <span x-text="snapshot.winner_name"></span> remporte le point !
          </p>
        </template>
        <template x-if="!snapshot.winner_name">
          <p class="text-xl font-bold text-gray-500 mb-2">Personne n'a trouvé la bonne réponse.</p>
        </template>
        <form method="POST" action="{{ route('formateur.buzzer.next', $session) }}" class="mt-4">
          @csrf
          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-[10px] bg-red-600 px-6 py-3 text-base font-bold text-white hover:bg-red-700 transition">
            <span x-text="snapshot.current_position >= snapshot.total_questions ? 'Terminer le jeu' : 'Question suivante'"></span>
          </button>
        </form>
      </div>

      <div x-show="snapshot.status === 'closed'" x-cloak class="text-center py-10">
        <p class="text-xl font-bold text-bleuone mb-2">Le jeu est terminé.</p>
        <p class="text-sm text-gray-500">Retrouvez le classement final ci-contre.</p>
      </div>
    </section>

    <section class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-base font-bold text-bleuone mb-4">Classement</h2>
      <div class="space-y-2">
        <template x-for="(row, i) in snapshot.leaderboard" :key="row.name + i">
          <div class="flex items-center justify-between gap-2 rounded-[10px] bg-gray-50 px-3 py-2">
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
    </section>
  </div>
</div>
@endsection
