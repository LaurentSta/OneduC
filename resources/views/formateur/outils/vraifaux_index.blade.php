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
            <li class="text-gray-400">Vrai ou Faux</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Vrai ou Faux</p>
        <p class="text-sm text-gray-500 mt-1">Créez des affirmations et diffusez-les via un code d'accès.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6"
           x-data='{
             questions: [{ statement: "", answer: "1", explanation: "" }],
             addQuestion() {
               if (this.questions.length < 20) this.questions.push({ statement: "", answer: "1", explanation: "" });
             },
             removeQuestion(index) {
               if (this.questions.length > 1) this.questions.splice(index, 1);
             }
           }'>
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau Vrai/Faux</p>

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
            Aucun groupe disponible. Créez un groupe pour diffuser un Vrai/Faux.
          </div>
        @else
          <form method="POST" action="{{ route('formateur.vraifaux.store') }}" class="space-y-4">
            @csrf

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
              <select name="group_id" required
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200">
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
                     placeholder="Ex : Mythes et réalités du numérique"
                     class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200">
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-semibold text-gray-600">Affirmations</label>
                <button type="button"
                        @click="addQuestion()"
                        class="inline-flex items-center gap-1 rounded-[6px] border border-orange-300 bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 transition">
                  Ajouter
                </button>
              </div>

              <template x-for="(question, index) in questions" :key="index">
                <div class="rounded-[12px] border border-gray-200 p-3 space-y-3">
                  <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-500" x-text="'Affirmation ' + (index + 1)"></p>
                    <button type="button"
                            x-show="questions.length > 1"
                            @click="removeQuestion(index)"
                            class="text-[11px] font-semibold text-red-500 hover:text-red-600">
                      Supprimer
                    </button>
                  </div>

                  <textarea :name="'questions[' + index + '][statement]'"
                            x-model="question.statement"
                            rows="2"
                            required
                            maxlength="500"
                            class="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200"
                            placeholder="Écrivez une affirmation courte..."></textarea>

                  <fieldset class="flex items-center gap-4">
                    <legend class="text-[11px] font-semibold text-gray-500">Réponse correcte</legend>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" :name="'questions[' + index + '][answer]'" value="1" x-model="question.answer"
                             class="text-vertone focus:ring-vertone">
                      <span class="text-sm font-semibold text-green-700">Vrai</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" :name="'questions[' + index + '][answer]'" value="0" x-model="question.answer"
                             class="text-orangeone focus:ring-orangeone">
                      <span class="text-sm font-semibold text-orangeone">Faux</span>
                    </label>
                  </fieldset>

                  <div>
                    <label class="block text-[11px] font-semibold text-gray-500 mb-1">Explication (optionnelle)</label>
                    <textarea :name="'questions[' + index + '][explanation]'"
                              x-model="question.explanation"
                              rows="2"
                              maxlength="1000"
                              class="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200"
                              placeholder="Visible par le stagiaire après sa réponse."></textarea>
                  </div>
                </div>
              </template>
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-orangeone px-4 py-2.5 text-sm font-bold text-white hover:bg-orangeone-hover transition">
              Créer et lancer le Vrai/Faux
            </button>
          </form>
        @endif
      </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
      @forelse($sessions as $session)
        @php
          $questions = collect($session->questions ?? []);
          $first = $questions->first();
        @endphp
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $session->is_active ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <p class="text-sm font-bold text-gray-900 truncate">{{ $session->title }}</p>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $session->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                  {{ $session->is_active ? 'Ouvert' : 'Fermé' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 truncate">{{ $first['statement'] ?? 'Affirmation non définie' }}</p>
              <p class="text-[10px] text-gray-400 mt-1">
                Groupe : <span class="font-semibold">{{ $session->group?->name ?? '—' }}</span>
                · Code : <span class="font-mono font-semibold text-orangeone">{{ $session->access_code }}</span>
                · {{ $questions->count() }} affirmation{{ $questions->count() > 1 ? 's' : '' }}
                · {{ $session->responses_count }} réponse{{ $session->responses_count > 1 ? 's' : '' }}
              </p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route('formateur.vraifaux.show', $session) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-orangeone px-3 py-1.5 text-xs font-bold text-white hover:bg-orangeone-hover transition">
              Ouvrir
            </a>
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <p class="text-sm font-semibold text-gray-700">Aucun Vrai/Faux créé</p>
          <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour lancer votre première activité.</p>
        </div>
      @endforelse
    </div>

  </div>
</div>
@endsection
