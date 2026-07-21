{{--
  Contenu d'un sondage (liste des questions + ajout manuel).
  Réutilisé à la fois dans l'assistant de lancement (étape "Construction") et
  dans le panneau replié "Gérer mes sondages".

  Paramètres :
  - $questionnaire : PollQuestionnaire
  - $wizardContext (bool, défaut false) : si true, les actions (ajouter,
    supprimer) renvoient l'utilisateur sur l'étape 3 de l'assistant plutôt
    que sur l'index simple.
--}}
@php $wizardContext = $wizardContext ?? false; @endphp

<div class="space-y-2">
  @forelse($questionnaire->questions as $index => $question)
    <div class="flex items-center justify-between gap-2 rounded-[8px] bg-gray-50 px-3 py-2">
      <div class="min-w-0">
        <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-bold text-gray-600 mr-2">
          {{ count($question['choices'] ?? []) }} choix
        </span>
        <span class="text-xs text-gray-700 truncate">{{ $question['question'] ?? '' }}</span>
      </div>
      <form method="POST" action="{{ route('formateur.sondage-questionnaires.questions.destroy', [$questionnaire, $index]) }}"
            onsubmit="return confirm('Supprimer cette question ?');">
        @csrf
        @method('DELETE')
        @if($wizardContext)
          <input type="hidden" name="wizard" value="1">
        @endif
        <button type="submit" class="shrink-0 text-gray-300 hover:text-red-500 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </form>
    </div>
  @empty
    <p class="text-xs text-gray-400">Aucune question dans ce sondage.</p>
  @endforelse

  <div x-data="sondageQuestionnaireQuestionForm()">
    <button type="button" @click="showForm = !showForm"
            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-[10px] border-2 border-dashed px-4 py-3 text-sm font-bold transition"
            :class="showForm ? 'border-bleuone bg-bleuone/5 text-bleuone' : 'border-bleuone/30 text-bleuone hover:border-bleuone hover:bg-bleuone/5'">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 transition-transform" :class="showForm ? 'rotate-45' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      <span x-text="showForm ? 'Fermer' : 'Ajouter une question'"></span>
    </button>

    <form x-show="showForm" x-cloak method="POST"
          action="{{ route('formateur.sondage-questionnaires.questions.store', $questionnaire) }}"
          class="mt-3 space-y-4 rounded-[14px] border border-gray-200 p-5">
      @csrf
      @if($wizardContext)
        <input type="hidden" name="wizard" value="1">
      @endif

      <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">Énoncé de la question</label>
        <textarea name="question" x-model="questionText" required rows="2" maxlength="500"
                  placeholder="Écrivez votre question..."
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20 resize-none"></textarea>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-sm font-bold text-gray-700">Choix de réponse</label>
          <button type="button" x-show="choices.length < 5" @click="choices.push('')"
                  class="text-xs font-semibold text-bleuone hover:underline">
            + Ajouter un choix
          </button>
        </div>
        <template x-for="(choice, ci) in choices" :key="ci">
          <div class="flex items-center gap-2">
            <input type="text" :name="'choices[' + ci + ']'" x-model="choices[ci]" required maxlength="200"
                   :placeholder="'Choix ' + (ci + 1)"
                   class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
            <button type="button" x-show="choices.length > 2" @click="choices.splice(ci, 1)"
                    class="shrink-0 rounded-[6px] border border-red-200 bg-white px-2 py-1 text-[11px] font-semibold text-red-500 hover:bg-red-50">
              X
            </button>
          </div>
        </template>
      </div>

      <button type="submit" class="w-full rounded-xl bg-bleuone px-4 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition">
        Ajouter la question
      </button>
    </form>
  </div>
</div>
