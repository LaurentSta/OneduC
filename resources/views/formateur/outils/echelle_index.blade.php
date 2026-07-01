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
            <li class="text-gray-400">Échelle de positionnement</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Échelle de positionnement</p>
        <p class="text-sm text-gray-500 mt-1">Un curseur de 1 à 10 pour mesurer la perception ou le ressenti de chaque stagiaire.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Formulaire de création --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6"
           x-data='{
             questions: [{ question: "", label_min: "Pas du tout", label_max: "Tout à fait", min: 1, max: 10 }],
             addQuestion() {
               if (this.questions.length < 10)
                 this.questions.push({ question: "", label_min: "Pas du tout", label_max: "Tout à fait", min: 1, max: 10 });
             },
             removeQuestion(index) {
               if (this.questions.length > 1) this.questions.splice(index, 1);
             }
           }'>
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouvelle échelle</p>

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

        @if($groups->isEmpty())
          <div class="rounded-[10px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
            Aucun groupe disponible. Créez un groupe pour diffuser une échelle.
          </div>
        @else

        <form method="POST" action="{{ route('formateur.echelle.store') }}" class="space-y-4">
          @csrf

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Groupe</label>
            <select name="group_id" required
                    class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
              <option value="">— Choisir un groupe —</option>
              @foreach($groups as $group)
                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                  {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Titre (optionnel)</label>
            <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                   placeholder="Ex. : Brise-glace numérique"
                   class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
          </div>

          {{-- Questions dynamiques --}}
          <template x-for="(q, qi) in questions" :key="qi">
            <div class="rounded-[12px] border border-gray-200 p-4 space-y-3">
              <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-bold text-gray-500 uppercase" x-text="'Question ' + (qi + 1)"></p>
                <button type="button" @click="removeQuestion(qi)"
                        x-show="questions.length > 1"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="text-xs text-red-400 hover:text-red-600 transition">Supprimer</button>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Question</label>
                <input type="text" :name="'questions[' + qi + '][question]'" x-model="q.question"
                       required maxlength="500"
                       placeholder="Ex. : À quel point vous sentez-vous à l'aise avec un ordinateur ?"
                       class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
              </div>

              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Libellé gauche</label>
                  <input type="text" :name="'questions[' + qi + '][label_min]'" x-model="q.label_min"
                         maxlength="100" placeholder="Pas du tout"
                         class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Libellé droite</label>
                  <input type="text" :name="'questions[' + qi + '][label_max]'" x-model="q.label_max"
                         maxlength="100" placeholder="Tout à fait"
                         class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Valeur min</label>
                  <input type="number" :name="'questions[' + qi + '][min]'" x-model.number="q.min"
                         min="1" max="9" required
                         class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Valeur max</label>
                  <input type="number" :name="'questions[' + qi + '][max]'" x-model.number="q.max"
                         min="2" max="10" required
                         class="w-full rounded-[10px] border border-gray-200 px-3 py-2 text-sm focus:border-bleuone focus:ring-1 focus:ring-bleuone">
                </div>
              </div>
            </div>
          </template>

          <button type="button" @click="addQuestion"
                  x-show="questions.length < 10"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 scale-95"
                  x-transition:enter-end="opacity-100 scale-100"
                  x-transition:leave="transition ease-in duration-150"
                  x-transition:leave-start="opacity-100 scale-100"
                  x-transition:leave-end="opacity-0 scale-95"
                  class="w-full rounded-[10px] border-2 border-dashed border-gray-200 py-2 text-sm font-semibold text-gray-400 hover:border-bleuone hover:text-bleuone transition">
            + Ajouter une question
          </button>

          <button type="submit"
                  class="w-full rounded-[10px] bg-bleuone px-4 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition">
            Créer et lancer
          </button>
        </form>

        @endif
      </div>
    </div>

    {{-- Sessions récentes --}}
    <div class="lg:col-span-2 space-y-4">
      <p class="font-varela text-base font-bold text-bleuone">Sessions récentes</p>

      @forelse($sessions as $session)
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-wrap items-center justify-between gap-4">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-bleuone text-sm truncate">{{ $session->title }}</span>
              @if($session->is_active)
                <span class="inline-flex items-center rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-bold text-teal-700 uppercase tracking-wide">En cours</span>
              @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500 uppercase tracking-wide">Fermée</span>
              @endif
            </div>
            <p class="text-xs text-gray-400 mt-0.5">
              Groupe : {{ $session->group?->name ?? '—' }}
              · Code : <span class="font-mono font-bold text-teal-700">{{ $session->access_code }}</span>
              · {{ count($session->questions ?? []) }} question{{ count($session->questions ?? []) > 1 ? 's' : '' }}
              · {{ $session->responses_count }} réponse{{ $session->responses_count > 1 ? 's' : '' }}
            </p>
          </div>
          <a href="{{ route('formateur.echelle.show', $session) }}"
             class="rounded-[10px] border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition whitespace-nowrap">
            Voir les résultats
          </a>
        </div>
      @empty
        <div class="bg-white rounded-[20px] shadow-md p-8 text-center text-gray-400 text-sm">
          Aucune session créée pour l'instant.
        </div>
      @endforelse
    </div>

  </div>
</div>
@endsection
