@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Buzzer Quiz</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Buzzer Quiz</p>
        <p class="text-sm text-gray-500 mt-1">Posez une question, les stagiaires buzzent, le plus rapide tente sa chance.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8"
       x-data='{
         questions: ["", ""],
         mode: "presentiel",
         addQuestion() { this.questions.push(""); },
         removeQuestion(i) { if (this.questions.length > 2) this.questions.splice(i, 1); },
       }'>

    @if($groups->isEmpty())
      <div class="lg:col-span-1">
        <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
          <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau jeu</p>
          <div class="rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            Aucun groupe disponible. Créez un groupe pour lancer ce jeu.
          </div>
        </div>
      </div>
    @else
      <div class="lg:col-span-1 space-y-6">
        <form method="POST" action="{{ route('formateur.buzzer.store') }}" class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
          @csrf
          <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau jeu</p>

          @if(session('success'))
            <div class="mb-4 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
              {{ session('success') }}
            </div>
          @endif

          @if($errors->any())
            <div class="mb-4 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
              {{ $errors->first() }}
            </div>
          @endif

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
              <select name="group_id" required
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                <option value="">Choisir un groupe...</option>
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                    {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Titre (optionnel)</label>
              <input type="text" name="title" maxlength="255" value="{{ old('title') }}"
                     placeholder="Ex : Quiz culture générale"
                     class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mode de jeu</label>
              <div class="flex gap-2">
                <button type="button" @click="mode = 'presentiel'"
                        :class="mode === 'presentiel' ? 'border-red-600 bg-red-50' : 'border-gray-200 bg-white hover:border-red-300'"
                        class="flex-1 rounded-[8px] border-2 py-2 text-center transition">
                  <span class="block text-xs font-semibold text-gray-700">Présentiel</span>
                  <span class="block text-[10px] text-gray-400">Question lue à voix haute</span>
                </button>
                <button type="button" @click="mode = 'distance'"
                        :class="mode === 'distance' ? 'border-red-600 bg-red-50' : 'border-gray-200 bg-white hover:border-red-300'"
                        class="flex-1 rounded-[8px] border-2 py-2 text-center transition">
                  <span class="block text-xs font-semibold text-gray-700">À distance</span>
                  <span class="block text-[10px] text-gray-400">Question affichée</span>
                </button>
              </div>
              <input type="hidden" name="mode" :value="mode">
            </div>

            <template x-if="mode === 'presentiel'">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre de questions</label>
                <div class="flex items-center gap-3">
                  <input type="number" min="2" max="30" :value="questions.length"
                         @input="let n = parseInt($event.target.value) || 2; n = Math.max(2, Math.min(30, n)); questions = Array.from({length: n}, (_, i) => 'Question ' + (i + 1));"
                         class="w-24 rounded-[10px] border border-gray-300 px-3 py-2 text-sm text-center focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                  <span class="text-xs text-gray-500">question(s) lue(s) à voix haute</span>
                </div>
                <template x-for="(q, i) in questions" :key="'hidden-' + i">
                  <input type="hidden" :name="`questions[${i}]`" :value="q && q.trim() !== '' ? q : ('Question ' + (i + 1))">
                </template>
              </div>
            </template>

            <template x-if="mode === 'distance'">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Questions</label>
                <div class="space-y-2">
                  <template x-for="(q, i) in questions" :key="i">
                    <div class="flex gap-2">
                      <input type="text" :name="`questions[${i}]`" x-model="questions[i]" maxlength="500"
                             :placeholder="`Question ${i + 1}`"
                             class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                      <button type="button" @click="removeQuestion(i)" x-show="questions.length > 2"
                              class="shrink-0 rounded-[8px] border border-gray-300 px-2 text-xs font-semibold text-gray-500 hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition">
                        &times;
                      </button>
                    </div>
                  </template>
                </div>
                <button type="button" @click="addQuestion()"
                        class="mt-2 text-xs font-semibold text-red-600 hover:text-red-700">
                  + Ajouter une question
                </button>
              </div>
            </template>
          </div>

          <button type="submit"
                  class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition">
            Créer le jeu
          </button>
        </form>

        <div class="space-y-4">
          @forelse($sessions as $session)
            <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3">
              <div class="flex items-start gap-3 min-w-0">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $session->status !== 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }}">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                  </svg>
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2 mb-0.5">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $session->title }}</p>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $session->status !== 'closed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                      {{ $session->status !== 'closed' ? 'En cours' : 'Terminé' }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500 truncate">{{ $session->total_questions }} question{{ $session->total_questions > 1 ? 's' : '' }}</p>
                  <p class="text-[10px] text-gray-400 mt-1">
                    Groupe : <span class="font-semibold">{{ $session->group?->name ?? '—' }}</span>
                    - Code : <span class="font-mono font-semibold text-red-700">{{ $session->access_code }}</span>
                    - {{ $session->participants_count }} joueur{{ $session->participants_count > 1 ? 's' : '' }}
                  </p>
                </div>
              </div>
              <div class="flex gap-2 shrink-0">
                <a href="{{ route('formateur.buzzer.show', $session) }}"
                   class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-[8px] bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700 transition">
                  Ouvrir
                </a>
                <form method="POST" action="{{ route('formateur.buzzer.destroy', $session) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                          class="inline-flex items-center justify-center rounded-[8px] border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition">
                    Supprimer
                  </button>
                </form>
              </div>
            </div>
          @empty
            <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
              <p class="text-sm font-semibold text-gray-700">Aucun jeu créé</p>
              <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour lancer votre premier Buzzer Quiz.</p>
            </div>
          @endforelse
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="flex min-h-[420px] flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white p-10 text-center">
          <p class="text-sm font-semibold text-gray-700">Aperçu du jeu</p>
          <p class="text-xs text-gray-400 mt-1">Créez un jeu à gauche, puis ouvrez-le pour piloter les questions et suivre le classement en direct.</p>
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
