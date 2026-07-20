@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Sondages</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Sondages</p>
        <p class="text-sm text-gray-500 mt-1">Créez un sondage pour un groupe et diffusez-le via un code d'accès.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6"
           x-data='{
             questions: [{ question: "", choices: ["", ""] }],
             groupId: "{{ old("group_id", "") }}",
             mode: "{{ old("group_id") || $groups->isNotEmpty() ? "lancer" : "modele" }}",
             addQuestion() {
               if (this.questions.length < 10) this.questions.push({ question: "", choices: ["", ""] });
             },
             removeQuestion(index) {
               if (this.questions.length > 1) this.questions.splice(index, 1);
             },
             addChoice(qi) {
               if (this.questions[qi].choices.length < 5) this.questions[qi].choices.push("");
             },
             removeChoice(qi, ci) {
               if (this.questions[qi].choices.length > 2) this.questions[qi].choices.splice(ci, 1);
             }
           }'>
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau sondage</p>

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

          <form method="POST" action="{{ route('formateur.sondages.store') }}" class="space-y-4">
            @csrf

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-2">Que voulez-vous faire ?</label>
              <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="mode = 'lancer'"
                        {{ $groups->isEmpty() ? 'disabled' : '' }}
                        :class="mode === 'lancer' ? 'border-teal-500 bg-teal-50' : 'border-gray-200 hover:border-gray-300'"
                        class="rounded-[10px] border-2 px-3 py-2.5 text-left transition disabled:cursor-not-allowed disabled:opacity-40">
                  <span class="block text-sm font-bold" :class="mode === 'lancer' ? 'text-teal-700' : 'text-gray-700'">Lancer pour un groupe</span>
                  <span class="block text-[11px] text-gray-500 mt-0.5">
                    @if($groups->isEmpty())
                      Créez d'abord un groupe
                    @else
                      Disponible tout de suite pour vos stagiaires
                    @endif
                  </span>
                </button>
                <button type="button" @click="mode = 'modele'; groupId = ''"
                        :class="mode === 'modele' ? 'border-teal-500 bg-teal-50' : 'border-gray-200 hover:border-gray-300'"
                        class="rounded-[10px] border-2 px-3 py-2.5 text-left transition">
                  <span class="block text-sm font-bold" :class="mode === 'modele' ? 'text-teal-700' : 'text-gray-700'">Créer un modèle</span>
                  <span class="block text-[11px] text-gray-500 mt-0.5">À réutiliser plus tard dans un parcours</span>
                </button>
              </div>
            </div>

            <div x-show="mode === 'lancer'" x-cloak>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
              <select name="group_id" x-model="groupId" :required="mode === 'lancer'"
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                <option value="">Choisir un groupe…</option>
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                    {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                  </option>
                @endforeach
              </select>
            </div>

            <div x-show="mode === 'modele'" x-cloak class="rounded-[10px] border border-violet-200 bg-violet-50 px-3 py-2.5 text-[11px] text-violet-700">
              Ce sondage sera enregistré comme modèle, disponible dans le catalogue de vos parcours. Vous pourrez le lancer pour un groupe plus tard.
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Titre (optionnel)</label>
              <input type="text" name="title" maxlength="255" value="{{ old('title') }}"
                     placeholder="Ex : Vérification des acquis"
                     class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-semibold text-gray-600">Questions</label>
                <button type="button"
                        @click="addQuestion()"
                        class="inline-flex items-center gap-1 rounded-[6px] border border-teal-300 bg-teal-50 px-2 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-100 transition">
                  Ajouter
                </button>
              </div>

              <template x-for="(q, qi) in questions" :key="qi">
                <div class="rounded-[12px] border border-gray-200 p-3 space-y-3">
                  <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-500" x-text="'Question ' + (qi + 1)"></p>
                    <button type="button"
                            x-show="questions.length > 1"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click="removeQuestion(qi)"
                            class="text-[11px] font-semibold text-red-500 hover:text-red-600">
                      Supprimer
                    </button>
                  </div>

                  <textarea :name="'questions[' + qi + '][question]'"
                            x-model="q.question"
                            rows="2"
                            required
                            maxlength="500"
                            class="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"
                            placeholder="Écrivez votre question..."></textarea>

                  <div class="space-y-2">
                    <div class="flex items-center justify-between">
                      <p class="text-[11px] font-semibold text-gray-500">Choix de réponse</p>
                      <button type="button"
                              @click="addChoice(qi)"
                              class="text-[11px] font-semibold text-teal-600 hover:text-teal-700">
                        Ajouter un choix
                      </button>
                    </div>
                    <template x-for="(choice, ci) in q.choices" :key="ci">
                      <div class="flex items-center gap-2">
                        <input type="text"
                               :name="'questions[' + qi + '][choices][' + ci + ']'"
                               x-model="q.choices[ci]"
                               required
                               maxlength="200"
                               class="flex-1 rounded-[8px] border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"
                               :placeholder="'Choix ' + (ci + 1)">
                        <button type="button"
                                x-show="q.choices.length > 2"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                @click="removeChoice(qi, ci)"
                                class="shrink-0 rounded-[6px] border border-red-200 bg-white px-2 py-1 text-[11px] font-semibold text-red-500 hover:bg-red-50">
                          X
                        </button>
                      </div>
                    </template>
                  </div>
                </div>
              </template>
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700 transition">
              <span x-text="mode === 'lancer' ? 'Créer et lancer le sondage' : 'Enregistrer le modèle'"></span>
            </button>
          </form>
      </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
      @forelse($sessions as $session)
        @php
          $questions = collect($session->questions ?? []);
          $first = $questions->first();
          $isDraft = $session->group_id === null;
        @endphp
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $session->is_active ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-500' }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <p class="text-sm font-bold text-gray-900 truncate">{{ $session->title }}</p>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isDraft ? 'bg-violet-100 text-violet-700' : ($session->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500') }}">
                  {{ $isDraft ? 'Modèle (parcours)' : ($session->is_active ? 'Ouvert' : 'Fermé') }}
                </span>
              </div>
              <p class="text-xs text-gray-500 truncate">{{ $first['question'] ?? 'Question non définie' }}</p>
              <p class="text-[10px] text-gray-400 mt-1">
                @if($isDraft)
                  Pas encore lancé pour un groupe
                  · {{ $questions->count() }} question{{ $questions->count() > 1 ? 's' : '' }}
                @else
                  Groupe : <span class="font-semibold">{{ $session->group?->name }}</span>
                  · Code : <span class="font-mono font-semibold text-teal-700">{{ $session->access_code }}</span>
                  · {{ $questions->count() }} question{{ $questions->count() > 1 ? 's' : '' }}
                  · {{ $session->responses_count }} réponse{{ $session->responses_count > 1 ? 's' : '' }}
                @endif
              </p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route('formateur.sondages.show', $session) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
              Ouvrir
            </a>
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <p class="text-sm font-semibold text-gray-700">Aucun sondage créé</p>
          <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour lancer votre premier sondage.</p>
        </div>
      @endforelse
    </div>

  </div>
</div>
@endsection
